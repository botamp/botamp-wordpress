<?php

namespace Botamp\Utils;

use Botamp\TestCase;

class HttpCodesTest extends TestCase
{
    public function testShouldGetHttpCodes()
    {
        $expectedArray = [
            'ok' => 200,
            'created' => 201,
            'noContent' => 204,
            'notAcceptable' => 406,
            'notFound' => 404,
            'unauthorized' => 401,
            'unprocessableEntity' => 422,
            'tooManyRequests' => 429
        ];

        $this->assertEquals($expectedArray, ClassUsingHttpCodes::getCodes());
    }
}

class ClassUsingHttpCodes
{
    use HttpCodes;

    public static function getCodes()
    {
        return self::getHttpCodes();
    }
}
