<?php

namespace CloudS\Hu\Api\Http\validator\Rules;

use Rakit\Validation\Rule;

class Length extends Rule
{

    protected $message = "The :attribute must be between :min and :max";

    protected $fillable_params = ['min', 'max'];

    /**
     * @param $value
     * @return bool
     * @throws \Rakit\Validation\MissingRequiredParameterException
     */
    public function check($value)
    {
        $this->requireParameters($this->fillable_params);

        $min = (int) $this->parameter('min');
        $max = (int) $this->parameter('max');
        $charset = $this->parameter('charset');
        if (is_array($value)) {
            return false;
        } else {
            return iconv_strlen($value, $charset) >= $min AND iconv_strlen($value, $charset) <= $max;
        }
    }

}
