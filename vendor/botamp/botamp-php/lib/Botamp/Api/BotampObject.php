<?php
namespace Botamp\Api;

use Botamp\Utils\PaginationIterator;

class BotampObject extends \ArrayObject
{
    private $body;
    private $resource;

    public function __construct($body, $resource)
    {
        parent::__construct(!empty($body['data']) ? $body['data'] : []);
        $this->body = $body;
        $this->resource = $resource;
    }

    public function paginationIterator()
    {
        return new PaginationIterator($this->body, $this->resource);
    }

    public function getBody()
    {
        return $this->body;
    }
}
