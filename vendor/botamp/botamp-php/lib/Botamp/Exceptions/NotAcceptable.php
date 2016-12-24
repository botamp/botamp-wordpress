<?php

namespace Botamp\Exceptions;

class NotAcceptable extends Base
{
    public function __construct()
    {
        parent::__construct("The request content type must be set to application/vnd.api+json.");
    }
}
