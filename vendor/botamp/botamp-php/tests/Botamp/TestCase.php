<?php

namespace Botamp;

use Http\Client\Common\Plugin;
use GuzzleHttp\Psr7\Response;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public static function getMethod($class, $method)
    {
         $method = new \ReflectionMethod($class, $method);
         $method->setAccessible(true);
         return $method;
    }

    public static function getProperty($class, $method)
    {
        $property = new \ReflectionProperty($class, $method);
        $property->setAccessible(true);
        return $property;
    }

    protected function getHttpMethodsMock(array $methods = array())
    {
        $methods = array_merge(array('sendRequest'), $methods);
        $mock = $this->getMock('Http\Client\Common\HttpMethodsClient', $methods, array(), '', false);
        $mock
            ->expects($this->any())
            ->method('sendRequest');

        return $mock;
    }

    protected function getPSR7Response($expectedArray)
    {
        return new Response(
            200,
            array('Content-Type' => 'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($expectedArray))
        );
    }
}
