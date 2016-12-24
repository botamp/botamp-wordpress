<?php

namespace Botamp\Api;

use Botamp\Client;
use Botamp\TestCase;

class BotampObjectTest extends TestCase
{
    private $body;
    private $resource;
    private $collection;
    private $botampObject;

    public function setUp()
    {
        $this->body = [
                        'data' =>
                        [
                            [ 'id' => 1, 'type' => 'entities', 'attributes' => ['url' => 'my/url/1'] ],
                            [ 'id' => 2, 'type' => 'entities', 'attributes' => ['url' => 'my/url/2'] ],
                            [ 'id' => 3, 'type' => 'entities', 'attributes' => ['url' => 'my/url/3'] ]
                        ]
                    ];
        $this->resource = new ApiResource('entities', new Client('123456789'));
        $this->botampObject = new BotampObject($this->body, $this->resource);
    }

    public function testShouldPerformSimpleIteration()
    {
        $position = 0;
        foreach($this->botampObject as $entity){
            $this->assertEquals($this->body['data'][$position], $entity);
            $position++;
        }
        $this->assertEquals(count($this->body['data']), $position);
    }

    public function testShouldGetBody()
    {
        $this->assertEquals($this->body, $this->botampObject->getBody());
    }

    public function testShouldReturnInstanceOfPaginationIterator()
    {
        $paginationIterator =  $this->botampObject->paginationIterator();
        $this->assertInstanceOf('Botamp\Utils\PaginationIterator',  $paginationIterator);
        $this->assertAttributeEquals($this->body, 'body', $paginationIterator);
        $this->assertAttributeEquals($this->resource, 'resource', $paginationIterator);
    }
}
