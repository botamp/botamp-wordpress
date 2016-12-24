<?php

namespace Botamp\Api;

use Botamp\Client;
use Botamp\Api\ApiResponse;
use Botamp\TestCase;
use GuzzleHttp\Psr7\Response;

class ApiRequestorTest extends TestCase
{
    private $client;
    private $url;
    private $apiRequestor;

    public function setUp()
    {
        $this->client = new Client('123456789');
        $this->apiRequestor = new ApiRequestor('entities', $this->client);
        $this->url = $this->client->getApiBase().'/'.$this->client->getApiVersion().'/entities';
    }

    /**
     * @expectedException Botamp\Exceptions\Base
     * @expectedExceptionMessage Unexpected error. Unknown action 'badaction'.
     */
    public function testShouldThrowExceptionIfUnknownAction()
    {
        $this->apiRequestor->send('badAction');
    }

    /**
    * @dataProvider pageParamsProvider
    */
    public function testShouldSerializeUrlWithPageParams($params, $query_params)
    {
        $serializeUrlMethod = TestCase::getMethod('\Botamp\Api\ApiRequestor', 'serializeUrl');
        $url = $this->url."?$query_params";
        $this->assertEquals($url, $serializeUrlMethod->invokeArgs($this->apiRequestor, [$params]));
    }

    public function testShouldSerializeBody()
    {
        $serializeBodyMethod = TestCase::getMethod('\Botamp\Api\ApiRequestor', 'serializeBody');

        $params = ['title' => 'Title 1', 'description' => 'Desc 1'];
        $jsonRes = '{"data":{"type":"entities","attributes":{"title":"Title 1","description":"Desc 1"}}}';

        $this->assertEquals($jsonRes, $serializeBodyMethod->invokeArgs($this->apiRequestor, [$params]));

        return $serializeBodyMethod;
    }

    /**
    * @depends testShouldSerializeBody
    */
    public function testShouldSerializeBodyWithId($serializeBodyMethod)
    {
        $params = ['title' => 'Title 1', 'description' => 'Desc 1', 'id' => '1'];
        $jsonRes = '{"data":{"type":"entities","id":"1","attributes":{"title":"Title 1","description":"Desc 1"}}}';

        $this->assertEquals($jsonRes, $serializeBodyMethod->invokeArgs($this->apiRequestor, [$params]));
    }

    public function pageParamsProvider()
    {
        return [
            ['params' => ['page[number]' => 2], 'query_params' => 'page[number]=2'],
            ['params' => ['page[size]' => 100], 'query_params' => 'page[size]=100'],
            [
                'params' => ['page[number]' => 2, 'page[size]' => 100],
                'query_params' => 'page[number]=2&page[size]=100'
            ]
        ];
    }
}
