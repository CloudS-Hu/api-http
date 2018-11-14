<?php

namespace CloudS\Hu\Api\Http;

use Rakit\Validation\Validator;

/**
 * Class Api
 * @package CloudS\Hu\Api\Http
 */
class Api implements ApiInterface
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

    /**
     * Api constructor.
     * @param string $uri
     * @param array $params
     * @param array $rules
     * @param string $method
     */
    public function __construct($uri = '/', $params = [], $rules = [], $method = 'post')
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->params = $params;
        $this->rules = $rules;
    }

    /**
     * @param $name
     * @param $value
     * @param string $rule
     * @return $this|ApiInterface
     */
    public function addParam($name, $value, $rule = '')
    {
        $this->params[$name] = $value;
        $this->rules[$name] = $rule;
        return $this;
    }

    /**
     * @param $messages
     * @return $this
     */
    public function setMessage($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param $params
     * @param array $rules
     * @return $this|ApiInterface
     * @throws \Exception
     */
    public function addParams($params, $rules = [])
    {
        mergeInto($this->params, $params);
        mergeInto($this->rules, $rules);
        return $this;
    }

    /**
     * @param $rules
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
     * @return $this|ApiInterface
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param $method
     * @return $this|ApiInterface
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getMethod()
    {
        return $this->method;
    }

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
        $this->filterRules();
        if (empty($this->params) || empty($this->rules)) {
            return true;
        }
        $validator = new Validator();
        $validation = $validator->make($this->params, $this->rules, $this->messages);
        $validation->validate();
        if ($validation->fails()) {
            $errors = $validation->errors();
            if ($errors->count() > 0) {
                $errorMsg = current(current($errors->toArray()));
            } else {
                $errorMsg = 'unknown error';
            }
            throw new \Exception($errorMsg);
        } else {
            $this->params = $validation->getValidatedData();
            return true;
        }
    }

    /**
     * 过滤无效的 rule 参数
     */
    private function filterRules()
    {
        $diffKey = array_diff_key($this->rules, $this->params);
        foreach ($diffKey as $key) {
            unset($this->rules[$key]);
        }
    }
}