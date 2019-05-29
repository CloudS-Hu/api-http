<?php

namespace CloudS\Hu\Api\Http;

use CloudS\Hu\Api\Http\exceptions\ApiValidateException;
use Rakit\Validation\Validator;

/**
 * Class Api
 * @package CloudS\Hu\Api\Http
 */
class Validate
{
    private $params;

    private $rules;

    private $messages;

    private $dictionaries;

    /**
     * Validate constructor.
     * @param array $params
     * @param array $rules
     * @param array $messages
     * @param array $dictionaries
     * @throws \Exception
     */
    public function __construct($params = [], $rules = [], $messages = [], $dictionaries = [])
    {
        $this->rules = $rules;
        $this->params = $params;
        $this->messages = [
            'required' => ':attribute 不能为空！',
            'numeric' => ':attribute 必须是数字！',
            'date' => ':attribute 日期格式非法！',
            'email' => ':attribute 邮箱格式错误！',
            'in' => ':attribute 值不在允许的范围内！',
            'length' => ':attribute 长度不合法！',
            'between' => ':attribute 值不在允许的范围内！',
            'digits' => ':attribute 值不是数值型或值长度不合法！',
            'required_if' => ':attribute 不能为空！',
            'min' => ':attribute 值小于最低值！',
            'array' => ':attribute 必须是数组！',
            'md5' => ':attribute 必须是 MD5 字符串！',
            'json' => ':attribute 必须是 JSON 字符串！',
            'id_card' => ':attribute 身份证号非法！',
        ];
        if (isset($messages['remove_custom']) && $messages['remove_custom']) { // 不需要自定义信息时可以传递该字段
            $this->messages = [];
        }
        mergeInto($this->messages, $messages);
        $this->dictionaries = $dictionaries;
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
     * @param $dictionaries
     * @return $this
     * @throws \Exception
     */
    public function setDictionary($dictionaries)
    {
        mergeInto($this->dictionaries, $dictionaries);
        return $this;
    }

    /**
     * @param $params
     * @return $this
     * @throws \Exception
     */
    public function setParams($params)
    {
        mergeInto($this->params, $params);
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

    public function getParams()
    {
        return $this->params;
    }

    /**
     * 校验
     *
     * @param int $validateType 传入1时只做普通校验，传入2时做递归校验
     * @return bool
     * @throws ApiValidateException
     */
    public function validate($validateType = 1)
    {
        if (empty($this->rules)) {
            return true;
        }
        if ($validateType === 1) {
            $this->params = $this->commonValidate($this->params, $this->rules);
        } elseif ($validateType === 2) {
            $this->params = $this->recursiveValidate();
        }
        return true;
    }

    /**
     * 普通检验
     *
     * @param $params
     * @param $rules
     * @return array
     * @throws ApiValidateException
     */
    public function commonValidate($params, $rules)
    {
        if (empty($params) || empty($rules)) {
            return [];
        }
        $validator = new Validator();
        $validation = $validator->make($params, $rules, $this->messages, $this->dictionaries);
        $validation->validate();
        if ($validation->fails()) {
            $errors = $validation->errors();
            if ($errors->count() > 0) {
                $errorMsg = current(current($errors->toArray()));
            } else {
                $errorMsg = 'unknown error';
            }
            throw new ApiValidateException($errorMsg);
        } else {
            $params = $validation->getValidatedData();
            if (is_array($params)) {
                foreach ($params as &$param) {
                    if (is_string($param)) {
                        $param = trim($param, '"\'');
                    }
                }
            }
            return $params;
        }
    }

    /**
     * 递归校验
     * 需要递归校验的字段需要设置递归字段,
     * 普通类型（['key1' => '', 'key2' => '', ...]）字段，递归字段为 key + "Rules",
     * 如果是 list 类型('key' => [['key1' => '', ...], ['key1' => '', ...], ...])的字段，递归字段为 key + "ListRules"
     * 第三级开始不做 list 类型校验，list 类型下不做 list 类型校验
     *
     * @return array
     * @throws ApiValidateException
     */
    public function recursiveValidate()
    {
        $params = $this->filterValidate($this->params, $this->rules);
        foreach ($params as $k1 => $p1) {
            if (is_array($p1) && (isset($this->rules[$k1 . 'Rules']) || isset($this->rules[$k1 . 'ListRules']))) {
                if (isset($this->rules[$k1 . 'Rules'])) {
                    $params[$k1] = $this->filterValidate($p1, $this->rules[$k1 . 'Rules'], [$k1]);
                    foreach ($p1 as $k2 => $p2) {
                        if (is_array($p2) && (isset($this->rules[$k1 . 'Rules'][$k2 . 'Rules']) || isset($this->rules[$k1 . 'Rules'][$k2 . 'ListRules']))) {
                            if (isset($this->rules[$k1 . 'Rules'][$k2 . 'Rules'])) {
                                $params[$k1][$k2] = $this->filterValidate($p2, $this->rules[$k1 . 'Rules'][$k2 . 'Rules'], [$k1, $k2]);
                                foreach ($p2 as $k3 => $p3) { // 第三级不检验 ListRules
                                    if (is_array($p3) && isset($this->rules[$k1 . 'Rules'][$k2 . 'Rules'][$k3 . 'Rules'])) {
                                        $params[$k1][$k2][$k3] = $this->filterValidate($p3, $this->rules[$k1 . 'Rules'][$k2 . 'Rules'][$k3 . 'Rules'], [$k1, $k2, $k3]);
                                    }
                                }
                            } elseif (isset($this->rules[$k1 . 'Rules'][$k2 . 'ListRules'])) {
                                foreach ($p2 as $k3 => $p3) {
                                    $params[$k1][$k2][$k3] = $this->filterValidate($p3, $this->rules[$k1 . 'Rules'][$k2 . 'ListRules'], [$k1, $k2, $k3]);
                                    foreach ($p3 as $k4 => $p4) { // ListRules 下面不检验 listRules 且只校验到 ListRules 的下一级
                                        if (is_array($p4) && isset($this->rules[$k1 . 'Rules'][$k2 . 'ListRules'][$k4 . 'Rules'])) {
                                            $params[$k1][$k2][$k3][$k4] = $this->filterValidate($p4, $this->rules[$k1 . 'Rules'][$k2 . 'ListRules'][$k4 . 'Rules'], [$k1, $k2, $k3, $k4]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif (isset($this->rules[$k1 . 'ListRules'])) {
                    foreach ($p1 as $k2 => $p2) {
                        $params[$k1][$k2] = $this->filterValidate($p2, $this->rules[$k1 . 'ListRules'], [$k1, $k2]);
                        foreach ($p2 as $k3 => $p3) { // ListRules 下面不检验 listRules 且只校验到 ListRules 的下一级
                            if (is_array($p3) && isset($this->rules[$k1 . 'ListRules'][$k3 . 'Rules'])) {
                                $params[$k1][$k2][$k3] = $this->filterValidate($p3, $this->rules[$k1 . 'ListRules'][$k3 . 'Rules'], [$k1, $k2, $k3]);
                            }
                        }
                    }
                }
            }
        }
        return $params;
    }

    /**
     * 过滤规则并校验
     *
     * @param $params
     * @param $rules
     * @param array $keyArr
     * @return array
     * @throws ApiValidateException
     */
    public function filterValidate($params, $rules, $keyArr = [])
    {
        $filterRules = [];
        foreach ($rules as $key => $rule) {
            if (!strpos($key, 'Rules')) {
                $filterRules[$key] = $rule;
            }
        }
        try {
            $params = $this->commonValidate($params, $filterRules);
            return $params;
        } catch (\Exception $e) {
            $keyLink = implode('.', $keyArr);
            $message = $e->getMessage();
            $message = lcfirst(str_replace('The ', '', $message));
            $throwMsg = empty($keyArr) ? $e->getMessage() : ($keyLink . '.' . $message);
            throw new ApiValidateException($throwMsg);
        }
    }
}