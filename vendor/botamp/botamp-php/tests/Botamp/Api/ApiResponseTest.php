<?php

namespace Botamp\Api;

use Botamp\Api\ApiResponse;
use Botamp\TestCase;
use Botamp\Exceptions;
use Botamp\Utils;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ApiResponseTest extends TestCase
{
    use Utils\HttpCodes;

    private $httpCodes;

    public function setUp()
    {
        $this->httpCodes = $this->getHttpCodes();
    }

    public function testShouldUnserialize()
    {
        $body = array('foo' => 'bar');
        $response = new Response(
        200,
        array('Content-Type'=>'application/vnd.api+json'),
        \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        $this->assertEquals($body, ApiResponseInstance::unserialize($response));
    }

    public function test_ShouldExtractErrors()
    {
        $body = array('errors' => [
            ['source' => ['pointer' => 'data/attributes/first'], 'detail' => 'first details.'],
            ['source' => ['pointer' => 'data/attributes/second'], 'detail' => 'second details.'],
            ['source' => ['pointer' => 'data/attributes/third'], 'detail' => 'third details.'],
        ]);
        $expectedDetails = "FIRST first details. SECOND second details. THIRD third details.";

        $this->assertEquals($expectedDetails, ApiResponseInstance::extractErrors($body));
    }

    public function testShouldGetContent()
    {
        $body =  ['data' => ['id' => 1, 'type' => 'entities', 'attributes' => array('url' => 'my/url')]];

        $response = new Response(
            200,
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        $this->assertEquals($body, ApiResponse::getContent($response));
    }

    /**
     * @expectedException Botamp\Exceptions\NotAcceptable
     * @expectedExceptionMessage The request content type must be set to application/vnd.api+json.
     */
    public function test_shouldThrowNotAcceptableException()
    {
        $body = array('errors' => [['source' => ['pointer' => ''], 'detail' => 'some details.']]);
        $response = new Response(
            $this->httpCodes['notAcceptable'],
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }

    /**
     * @expectedException Botamp\Exceptions\NotFound
     * @expectedExceptionMessage The resource doesn't exist.
     */
    public function test_shouldThrowNotFoundException()
    {
        $body = array('errors' => [['source' => ['pointer' => ''], 'detail' => 'some details.']]);
        $response = new Response(
            $this->httpCodes['notFound'],
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }

    /**
     * @expectedException Botamp\Exceptions\Unauthorized
     * @expectedExceptionMessage No valid API key provided.
     */
    public function test_shouldThrowUnauthorizedException()
    {
        $body = array('errors' => [['source' => ['pointer' => ''], 'detail' => 'some details.']]);
        $response = new Response(
            $this->httpCodes['unauthorized'],
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }

    /**
     * @expectedException Botamp\Exceptions\TooManyRequests
     * @expectedExceptionMessage API rate limit exceeded. Please try again in an hour.
     */
    public function test_shouldThrowTooManyRequestsException()
    {
        $body = array('errors' => [['source' => ['pointer' => ''], 'detail' => 'some details.']]);
        $response = new Response(
            $this->httpCodes['tooManyRequests'],
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }

    /**
     * @expectedException Botamp\Exceptions\UnprocessableEntity
     * @expectedExceptionMessage The request could not be processed. FIRST first details.
     */
    public function test_shouldThrowUnprocessableEntityException()
    {
        $body = array('errors' => [
            ['source' => ['pointer' => 'data/attributes/first'], 'detail' => 'first details.']
        ]);

        $response = new Response(
            $this->httpCodes['unprocessableEntity'],
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }

    /**
     * @expectedException Botamp\Exceptions\Base
     * @expectedExceptionMessage Unexpected error.
     */
    public function test_shouldThrowBaseException()
    {
        $unexpectedStatus = 500;
        $body = array('errors' => [
            ['source' => ['pointer' => 'data/attributes/first'], 'detail' => 'first details.']
        ]);

        $response = new Response(
            $unexpectedStatus,
            array('Content-Type'=>'application/vnd.api+json'),
            \GuzzleHttp\Psr7\stream_for(json_encode($body))
        );
        ApiResponse::getContent($response);
    }
}

class ApiResponseInstance extends ApiResponse
{
    public static function unserialize(ResponseInterface $response)
    {
        return parent::unserialize($response);
    }

    public static function extractErrors($body)
    {
        return parent::extractErrors($body);
    }
}
