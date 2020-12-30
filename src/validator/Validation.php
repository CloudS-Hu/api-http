<?php

namespace CloudS\Hu\Api\Http\validator;

use Rakit\Validation\Attribute;
use Rakit\Validation\Rule;
use Rakit\Validation\Validator;

class Validation extends \Rakit\Validation\Validation
{
    protected $dictionaries = [];

    public function __construct(Validator $validator, array $inputs, array $rules, array $messages = array(), array $dictionaries = array())
    {
        parent::__construct($validator, $inputs, $rules, $messages);
        $this->dictionaries = $dictionaries;
    }

    protected function resolveMessage(Attribute $attribute, $value, Rule $validator)
    {
        $primaryAttribute = $attribute->getPrimaryAttribute();
        $params = $validator->getParameters();
        $attributeKey = $attribute->getKey();
        $ruleKey = $validator->getKey();
        $alias = $attribute->getAlias() ?: $this->resolveAttributeName($attribute);
        $message = $validator->getMessage(); // default rule message
        $message_keys = [
            $attributeKey.$this->messageSeparator.$ruleKey,
            $attributeKey,
            $ruleKey
        ];

        if ($primaryAttribute) {
            // insert primaryAttribute keys
            // $message_keys = [
            //     $attributeKey.$this->messageSeparator.$ruleKey,
            //     >> here [1] <<
            //     $attributeKey,
            //     >> and here [3] <<
            //     $ruleKey
            // ];
            $primaryAttributeKey = $primaryAttribute->getKey();
            array_splice($message_keys, 1, 0, $primaryAttributeKey.$this->messageSeparator.$ruleKey);
            array_splice($message_keys, 3, 0, $primaryAttributeKey);
        }

        foreach($message_keys as $key) {
            if (isset($this->messages[$key])) {
                $message = $this->messages[$key];
                break;
            }
        }

        // Replace message params
        $vars = array_merge($params, [
            'attribute' => $alias,
            'value' => $value,
        ]);

        // 替换字典
        $attributeValue = strtolower(str_replace(' ', '_', $vars['attribute']));
        if (isset($this->dictionaries[$attributeValue])) {
            $vars['attribute'] = $this->dictionaries[$attributeValue];
        }

        foreach($vars as $key => $value) {
            $value = $this->stringify($value);
            $message = str_replace(':'.$key, $value, $message);
        }

        // Replace key indexes
        $keyIndexes = $attribute->getKeyIndexes();
        foreach ($keyIndexes as $pathIndex => $index) {
            $replacers = [
                "[{$pathIndex}]" => $index,
            ];

            if (is_numeric($index)) {
                $replacers["{{$pathIndex}}"] = $index + 1;
            }

            $message = str_replace(array_keys($replacers), array_values($replacers), $message);
        }

        return $message;
    }
}
