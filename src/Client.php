<?php

namespace CloudS\Hu\Api\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Created by PhpStorm.
 * User: T133
 * Date: 2018/10/26
 * Time: 15:47
 */
class Client
{
    protected $api;

    protected $params;

    protected $method;

    protected $baseUri;

    protected $uri;

    protected $headers;

    protected $handlerStack;

    protected $config;

    /**
     * http client 实例
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient = null;

    /**
     * 日志文件资源
     *
     * @var resource
     */
    protected $logFile = null;

    /**
     * 日志实例
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     * Client constructor.
     * @param Api $api
     * @param array $config
     * @param LoggerInterface $logger
     * @throws \Exception
     */
    public function __construct(Api $api, $config = [], LoggerInterface $logger = null)
    {
        //先进行api的参数校验
        $api->validate();
        $this->api = $api;
        $this->params = $api->getParams();
        $this->method = $api->getMethod();
        $this->uri = $api->getUri();
        $this->baseUri = !empty($config['base_uri']) ? $config['base_uri'] : '/';
        $this->headers = !empty($config['headers']) ? $config['headers'] : [
            'Accept-Encoding' => 'gzip',
            'Connection' => 'keep-alive'
        ]; // 头部参数
        mergeInto($this->headers, $api->getHeaders());
        $this->config = [
            'timeout' => isset($config['timeout']) ? $config['timeout'] : 30, // 超时时间
            'connect_timeout' => isset($config['connect_timeout']) ? $config['connect_timeout'] : 3, // 连接超时，单位秒
            'max_retries' => isset($config['max_retries']) ? $config['max_retries'] : 1, // 重试次数
            'retry_interval' => isset($config['retry_interval']) ? $config['retry_interval'] : 1000, // 重试间隔，毫秒
        ];
        // 日志
        $this->logger = $logger;
        // 处理器
        $this->handlerStack = HandlerStack::create();
        // 创建 httpClient 实例
        $this->setHttpClient();
    }

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param $baseUri
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    /**
     * 添加日志中间件
     *
     * @throws \Exception
     */
    protected function pushLog()
    {
        // 日志格式为
        // ">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{error}"
        $this->handlerStack->push(Middleware::log($this->getLogger(), new MessageFormatter(MessageFormatter::DEBUG)));
    }

    /**
     * 获取日志实例
     *
     * @return LoggerInterface
     * @throws \Exception
     */
    protected function getLogger(): LoggerInterface
    {
        if (!($this->logger instanceof LoggerInterface)) {
            // 这里使用 php 临时文件进行存储，最多允许使用 0.5MB 内存
            // 超过 0.5MB 内存将使用临时文件进行存储
            // v.a. http://php.net/manual/en/wrappers.php.php#wrappers.php.memory
            $this->logFile = fopen('php://temp/maxmemory:524288', 'a');

            $stream = new StreamHandler($this->logFile, Logger::DEBUG);
            $stream->setFormatter(new LineFormatter(null, null, true, true));

            $this->logger = new Logger('api-http');
            $this->logger->pushHandler($stream);
        }

        return $this->logger;
    }

    /**
     * 请求重试
     *
     * @return $this
     */
    public function pushRetry()
    {
        $this->handlerStack->push(Middleware::retry(function (
            $retries,
            \GuzzleHttp\Psr7\Request $request,
            Response $response = null,
            \Exception $exception = null
        ) {
            if ($retries > $this->config['max_retries']) {
                return false;
            }
            // 只有在没有响应或者 500 错误的时候才去重试
            if ($exception instanceof ConnectException
                || ($response && $response->getStatusCode() >= 500)
            ) {
                return true;
            }
            return false;
        }, function () {
            return $this->config['retry_interval']; // 重试间隔
        }));
        return $this;
    }

    /**
     * @param $headers
     * @return $this
     * @throws \Exception
     */
    public function setHeaders($headers)
    {
        mergeInto($this->headers, $headers);
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function setHttpClient()
    {
        $this->pushLog();
        $this->pushRetry();
        $this->httpClient = new \GuzzleHttp\Client(
            [
                'timeout' => $this->config['timeout'], // 超时时间
                'connect_timeout' => $this->config['connect_timeout'], // 连接超时，单位秒
                'handler' => $this->handlerStack,
                'headers' => $this->headers
            ]
        );
        return $this;
    }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * 发起请求
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function request()
    {
        $response = $this->httpClient->request(
            $this->method,
            $this->baseUri . $this->uri,
            [
                'form_params' => $this->params,
                'headers' => $this->headers
            ]
        );
        $body = $response->getBody();
        return @json_decode($body, true);
    }
}