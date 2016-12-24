<?php

namespace Botamp\Exceptions;

class NotFound extends Base
{
    public function __construct()
    {
        parent::__construct("The resource doesn't exist.");
    }
}
