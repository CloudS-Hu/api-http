<?php

namespace CloudS\Hu\Api\Http;

/**
 * @param $arr
 * @param $arr1
 * @return bool
 * @throws \Exception
 */
function mergeInto(&$arr, $arr1)
{
    if (!is_array($arr) || !is_array($arr1)) {
        throw new \Exception('Incorrect parameter Setting');
    }
    foreach ($arr1 as $key => $value) {
        if (!is_string($key)) {
            throw new \Exception('Incorrect parameter Setting');
        }
        $arr[$key] = $value;
    }
    return true;
}