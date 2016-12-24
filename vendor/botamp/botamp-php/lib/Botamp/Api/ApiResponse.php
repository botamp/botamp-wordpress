<?php

namespace Botamp\Api;
use Botamp\Exceptions;
use Botamp\Utils;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiResponse
 *
 * @package Botamp\Api
 */

abstract class ApiResponse
{
    use Utils\HttpCodes;

    public static function getContent(ResponseInterface $response)
    {
        $status = (Integer)$response->getStatusCode();
        $body = self::unserialize($response);
        $httpRes = self::getHttpCodes();

        if(in_array($status, [ $httpRes['ok'], $httpRes['created'], $httpRes['noContent'] ]))
            return $body;
        switch($status)
        {
            case $httpRes['unauthorized']:
                throw new Exceptions\Unauthorized();
                break;
            case $httpRes['notFound']:
                throw new Exceptions\NotFound();
                break;
            case $httpRes['notAcceptable']:
                throw new Exceptions\NotAcceptable();
                break;
            case $httpRes['unprocessableEntity']:
                throw new Exceptions\UnprocessableEntity(self::extractErrors($body));
                break;
            case $httpRes['tooManyRequests']:
                throw new Exceptions\TooManyRequests();
                break;
            default:
                throw new Exceptions\Base("Unexpected error.");
                break;
        }
    }

    protected static function unserialize(ResponseInterface $response)
    {
        return json_decode($response->getBody(true), true);
    }

    protected static function extractErrors($body)
    {
        $errorDetails = '';
        foreach($body['errors'] as $error)
        {
            $attribute  = substr(strrchr($error['source']['pointer'], '/'), 1);
            $errorDetails .= strtoupper($attribute)." {$error['detail']} ";
        }
        return rtrim($errorDetails);
    }
}
