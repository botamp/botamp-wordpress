<?php

namespace Botamp\Utils;

use Botamp\Api\BotampObject;
use Botamp\Api\ApiResource;
use Botamp\Client;
use Botamp\TestCase;
use Botamp\Utils;
use GuzzleHttp\Psr7\Response;

class PaginationIteratorTest extends TestCase
{
    private $iterator;
    private $body;
    private $bodyNext;
    private $resourceUrl = 'https://app.botamp.com/api/v1/entities';

    public function setUp()
    {
        $this->body = [
                        'data' =>
                        [
                            [ 'id' => 1, 'type' => 'entities', 'attributes' => ['url' => 'my/url/1'] ],
                            [ 'id' => 2, 'type' => 'entities', 'attributes' => ['url' => 'my/url/2'] ],
                            [ 'id' => 3, 'type' => 'entities', 'attributes' => ['url' => 'my/url/3'] ]
                        ],
                        'links' =>
                        [
                            'self'  => $this->resourceUrl.'?page[number]=1&page[size]=3',
                            'next'  => $this->resourceUrl.'?page[number]=2&page[size]=3',
                            'last'  => $this->resourceUrl.'?page[number]=2&page[size]=3'
                        ]
                    ];

        $this->bodyNext = [
                        'data' =>
                        [
                            [ 'id' => 4, 'type' => 'entities', 'attributes' => ['url' => 'my/url/4'] ],
                            [ 'id' => 5, 'type' => 'entities', 'attributes' => ['url' => 'my/url/5'] ],
                            [ 'id' => 6, 'type' => 'entities', 'attributes' => ['url' => 'my/url/6'] ]
                        ],
                        'links' =>
                        [
                            'self'   => $this->resourceUrl.'?page[number]=2&page[size]=3',
                            'first'  => $this->resourceUrl.'?page[number]=1&page[size]=3',
                            'prev'   => $this->resourceUrl.'?page[number]=1&page[size]=3',
                        ]
                    ];
        $entities = new ApiResource('entities', new Client('123456789'));
        $this->paginationIterator = (new BotampObject($this->body, $entities))->paginationIterator();
    }

    public function testShouldGetCurrentKey()
    {
        $this->assertEquals(0, $this->paginationIterator->key());
        $this->paginationIterator->next();
        $this->assertEquals(1, $this->paginationIterator->key());
    }

    public function testShouldAutoPaginate()
    {
        $httpClient = $this->getHttpMethodsMock(array('get'));
        $httpClient
            ->expects($this->any())
            ->method('get')
            ->with($this->resourceUrl.'?page[number]=2&page[size]=3')
            ->will($this->returnValue($this->getPSR7Response($this->bodyNext)));

        $client = $this->getMock('Botamp\Client', array('getHttpClient'), array('123456789'));
        $client->expects($this->any())
            ->method('getHttpClient')
            ->willReturn($httpClient);

        $paginationIterator = (new BotampObject($this->body, new ApiResource('entities', $client)))->paginationIterator();

        $allEntities = array_merge($this->body['data'], $this->bodyNext['data']);

        $position = 0;
        foreach ($paginationIterator as $entity)
        {
            $this->assertEquals($allEntities[$position], $entity);
            $position++;
        }

        $this->assertEquals(count($allEntities), $position);

    }

    public function testShouldGetNextPage()
    {
        $httpClient = $this->getHttpMethodsMock(array('get'));
        $httpClient
            ->expects($this->any())
            ->method('get')
            ->with($this->resourceUrl.'?page[number]=2&page[size]=3')
            ->will($this->returnValue($this->getPSR7Response($this->bodyNext)));

        $client = $this->getMock('Botamp\Client', array('getHttpClient'), array('123456789'));
        $client->expects($this->any())
            ->method('getHttpClient')
            ->willReturn($httpClient);

        $getNextPageMethod = TestCase::getMethod('Botamp\Utils\PaginationIterator', 'getNextPage');

        $paginationIterator = (new BotampObject($this->body, new ApiResource('entities', $client)))->paginationIterator();

        $this->assertEquals($this->bodyNext, $getNextPageMethod->invoke($paginationIterator));

        return $getNextPageMethod;
    }

    /**
     * @depends testShouldGetNextPage
     */
    public function testShouldGetFalseIfNoNextPage($getNextPageMethod)
    {
        $body = $this->body;
        unset($body['links']['next']);

        $paginationIterator = (new BotampObject($body, new ApiResource('entities', new Client('123456789'))))->paginationIterator();

        $this->assertEquals(false, $getNextPageMethod->invoke($paginationIterator));
    }

    public function testShouldGetNextPageParams()
    {
        $expectedArray = ['page[number]' => 2, 'page[size]' => 3];
        $getNextPageParamsMethod = TestCase::getMethod('Botamp\Utils\PaginationIterator', 'getNextPageParams');
        $this->assertEquals($expectedArray, $getNextPageParamsMethod->invoke($this->paginationIterator));
        return $getNextPageParamsMethod;
    }

    /**
     * @depends testShouldGetNextPageParams
     */
    public function testShouldGetFalseIfNoNextPageParams($getNextPageParamsMethod)
    {
        $body = $this->body;
        unset($body['links']['next']);
        $paginationIterator = (new BotampObject($body, new ApiResource('entities', new Client('123456789'))))->paginationIterator();
        $this->assertEquals(false, $getNextPageParamsMethod->invoke($paginationIterator));
    }

}
