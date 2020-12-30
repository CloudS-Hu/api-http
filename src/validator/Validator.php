<?php

namespace CloudS\Hu\Api\Http\validator;

use CloudS\Hu\Api\Http\validator\Rules\Md5;
use CloudS\Hu\Api\Http\validator\Rules\Timestamp;
use CloudS\Hu\Api\Http\validator\Rules\IdCard;
use CloudS\Hu\Api\Http\validator\Rules\Mobile;
use CloudS\Hu\Api\Http\validator\Rules\Length;

class Validator extends \Rakit\Validation\Validator
{
    protected function registerBaseValidators()
    {
        parent::registerBaseValidators();
        $baseValidator = [
            'md5'                       => new Md5,
            'timestamp'                 => new Timestamp,
            'id_card'                   => new IdCard,
            'mobile'                    => new Mobile,
            'length'                    => new Length,
        ];
        foreach($baseValidator as $key => $validator) {
            $this->setValidator($key, $validator);
        }
    }

    public function make(array $inputs, array $rules, array $messages = array(), array $dictionaries = array())
    {
        $messages = array_merge($this->messages, $messages);
        return new Validation($this, $inputs, $rules, $messages, $dictionaries);
    }
}
