<?php

namespace Botamp\Exceptions;

class TooManyRequests extends Base
{
    public function __construct()
    {
        parent::__construct('API rate limit exceeded. Please try again in an hour.');
    }
}
