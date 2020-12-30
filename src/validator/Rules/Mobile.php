<?php

namespace CloudS\Hu\Api\Http\validator\Rules;

use Rakit\Validation\Rule;

class Mobile extends Rule
{

    protected $message = "The :attribute is not valid mobile";

    public function check($value)
    {
        return preg_match("/^1[2-9][0-9]\d{8}$/", $value);
    }

}
