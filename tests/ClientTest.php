<?php

namespace CloudS\Hu\Api\Http\tests;

use CloudS\Hu\Api\Http\Api;
use CloudS\Hu\Api\Http\Client;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function testRequest()
    {
        $api = new Api();
        $api->setUri('/v1/login/access_token')
            ->setMethod('post')
            ->addParam('partner_id', 'yzjf')
            ->setRules(['partner_id' => 'required'])
            ->setHeaders([
                'authorization' => 'Basic bW9jaG91OjAzZjg4M2NmMGNhMTQ4NjgyMzczODI0NTZmZGFhZTI3',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]);
        // 日志对象
        if (!realpath('log/')) {
            mkdir('log/');
        }
        $stream = fopen('log/' . date('Y-m-d') . '.log', 'a+');
        $streamHandler = new StreamHandler($stream, Logger::DEBUG);
        $streamHandler->setFormatter(new LineFormatter(null, null, true, true));
        $logger = new Logger('api-http');
        $logger->pushHandler($streamHandler);
        $client = new Client($api, [
            'base_uri' => 'http://172.18.1.218:944',
            'headers' => [],
            'timeout' => 30,
            'connect_timeout' => 3,
            'max_retries' => 3,
            'retry_interval' => 1000
        ], $logger);
        $result = $client->request();
        echo "\r\n==========" . __METHOD__ . "=========\r\n";
        print_r($result);
        $this->assertTrue($result['code'] === 200);
    }
}
