<?php

namespace CloudS\Hu\Api\Http;

/**
 * Class Api
 * @package CloudS\Hu\Api\Http
 */
class Api
{
    /** @var array request parameters */
    private $params;

    /** @var string request uri */
    private $uri;

    /** @var string request method */
    private $method;

    /** @var array validate rules */
    private $rules;

    /** @var array user defined validate error message */
    private $messages = [];

    /** @var array $headers */
    private $headers = [];

    /**
     * Api constructor.
     * @param string $uri
     * @param array $params
     * @param array $rules
     * @param array $headers
     * @param string $method
     */
    public function __construct($uri = '/', $params = [], $rules = [], $headers = [], $method = 'post')
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->params = $params;
        $this->rules = $rules;
        $this->headers = $headers;
    }

    /**
     * @param $name
     * @param $value
     * @param string $rule
     * @return $this
     */
    public function addParam($name, $value, $rule = '')
    {
        $this->params[$name] = $value;
        $this->rules[$name] = $rule;
        return $this;
    }

    /**
     * @param array $messages
     * @return $this
     * @throws \Exception
     */
    public function setMessage($messages)
    {
        mergeInto($this->messages, $messages);
        return $this;
    }

    /**
     * @param array $params
     * @param array $rules
     * @return $this
     * @throws \Exception
     */
    public function addParams($params, $rules = [])
    {
        mergeInto($this->params, $params);
        mergeInto($this->rules, $rules);
        return $this;
    }

    /**
     * @param array $rules
     * @return $this
     * @throws \Exception
     */
    public function setRules($rules)
    {
        mergeInto($this->rules, $rules);
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
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
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function validate()
    {
        if (empty($this->rules)) {
            return true;
        }
        $validate = new Validate($this->params, $this->rules, $this->messages);
        $validate->validate();
        $this->params = $validate->getParams();
    }
}