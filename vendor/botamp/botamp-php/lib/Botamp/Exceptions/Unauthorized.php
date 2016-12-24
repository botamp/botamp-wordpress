<?php

namespace Botamp\Exceptions;

class Unauthorized extends Base
{
    public function __construct()
    {
        parent::__construct("No valid API key provided.");
    }
}
