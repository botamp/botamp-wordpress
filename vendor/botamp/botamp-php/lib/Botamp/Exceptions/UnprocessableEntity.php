<?php

namespace Botamp\Exceptions;

class UnprocessableEntity extends Base
{
    public function __construct($errorDetail = '')
    {
        $message = "The request could not be processed.";
        parent::__construct($errorDetail == '' ? $message : $message.' '.$errorDetail);
    }
}
