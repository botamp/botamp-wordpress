<?php

namespace Botamp\Utils;

trait HttpCodes
{
    private static function getHttpCodes()
    {
        return [
            'ok' => 200,
            'created' => 201,
            'noContent' => 204,
            'notAcceptable' => 406,
            'notFound' => 404,
            'unauthorized' => 401,
            'unprocessableEntity' => 422,
            'tooManyRequests' => 429
        ];
    }
}
