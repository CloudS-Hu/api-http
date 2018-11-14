<?php

namespace CloudS\Hu\Api\Http;

/**
 * Interface ApiInterface
 * @package CloudS\Hu\Api\Http
 */
Interface ApiInterface
{
    /**
     * Add request parameter
     *
     * @param $name
     * @param $value
     * @param $rule
     * @return $this
     */
    public function addParam($name, $value, $rule = '');

    /**
     * Batch add parameter
     *
     * @param $params
     * @param $rules
     * @return $this
     */
    public function addParams($params, $rules = []);

    /**
     * Set url
     *
     * @param $uri
     * @return $this
     */
    public function setUri($uri);

    /**
     * Set method
     *
     * @param $method
     * @return $this
     */
    public function setMethod($method);
}