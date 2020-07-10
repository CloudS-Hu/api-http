<?php

namespace CloudS\Hu\Api\Http\tests;

use CloudS\Hu\Api\Http\Validate;
use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testValidate()
    {
        $validate = new Validate([], [], ['remove_custom' => 1]);
        $dictionary = [
            'id' => 'account',
            'key' => 'password',
            'name' => 'realName',
        ];
        $rules = [
            'id' => 'required|numeric|between:0,99999',
            'key' => 'required|md5',
            'name' => 'required|length:0,16,utf8',
            'type' => 'default:0|in:0,1,2',
            'mobile' => 'mobile',
            'card' => 'id_card',
            'company' => 'required|array',
            'companyRules' => [
                'name' => 'required|length:0,64,utf8',
                'url' => 'required|url:http,https,ssh',
                'tel' => 'length:0,16,utf8'
            ],
            'property' => 'array',
            'propertyListRules' => [
                'type' => 'required|in:character,hobbies,story',
                'describe' => 'required|length:0,128,utf8',
            ],
        ];
        $params = [
            'id' => 2022,
            'key' => '3f571ab24d54baf70b44bb4ce6c88214',
            'name' => 'clouds.hu',
            'card' => '11010119900307387X',
            'mobile' => '13800138000',
            'company' => [
                'name' => '2020 GitHub, Inc.',
                'url' => 'https://www.github.com',
                'tel' => '0592-23333333',
            ],
            'property' => [
                [
                    'type' => 'character',
                    'describe' => 'I\'m an outgoing person, and like socialising / hanging out with friends.',
                ],
                [
                    'type' => 'hobbies',
                    'describe' => 'basketball, football, cooking',
                ],
            ],
        ];
        $validate->setDictionary($dictionary)
            ->setRules($rules)
            ->setParams($params)
            ->validate(2);
        echo "\r\n==========" . __METHOD__ . "=========\r\n";
        $validateParams = $validate->getParams();
        print_r($validateParams);
        $this->assertTrue(isset($validateParams['id'], $validateParams['key'], $validateParams['name'], $validateParams['type']));
    }
}
