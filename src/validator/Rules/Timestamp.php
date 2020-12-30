<?php

namespace CloudS\Hu\Api\Http\validator\Rules;

use Rakit\Validation\Rule;

class Timestamp extends Rule
{
    protected $message = "The :attribute  must be timestamp";

    public function check($value)
    {
        return strtotime(date('m-d-Y H:i:s',$value)) === $value;
    }
}