<?php

namespace CloudS\Hu\Api\Http\validator\Rules;

use Rakit\Validation\Rule;

class Md5 extends Rule
{
    protected $message = "The :attribute  must be md5";

    public function check($value)
    {
        return preg_match("/^[a-fA-F0-9]{32}$/", $value);
    }
}
