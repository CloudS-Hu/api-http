<?php

namespace CloudS\Hu\Api\Http\tests;

use CloudS\Hu\Api\Http\Api;
use CloudS\Hu\Api\Http\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGetApi()
    {
        $api = new Api();
        $api->setUri('v1/member/lyt/check')
            ->setMethod('post')
            ->addParam('partner_id', 'yzjf')
            ->addParam('mobile', '15905021111')
            ->addParam('password', '123456')
            ->setRules([
                'partner_id' => 'required',
                'mobile' => 'required',
                'password' => 'required'
            ])
            ->setMessage([
                'required' => ':attribute 不能为空！'
            ]);
        $client = new Client($api, 'http://httpbin.org/');
        $getApi = $client->getApi();
        $this->assertTrue($getApi instanceof Api);
    }
}
