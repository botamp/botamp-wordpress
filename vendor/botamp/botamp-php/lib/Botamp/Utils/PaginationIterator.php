<?php
namespace Botamp\Utils;

class PaginationIterator implements \Iterator
{
    private $body;
    private $collection;
    private $nextPageUrl;
    private $position = 0;
    private $resource;

    public function __construct($body, $resource)
    {
        $this->body = $body;
        $this->collection = empty($body['data']) ? [] : $body['data'];
        $this->nextPageUrl =  empty($body['links']['next']) ? '' : $body['links']['next'];
        $this->resource = $resource;
    }

    public function rewind()
    {

    }

    public function current()
    {
        return $this->collection[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
        if($this->position === count($this->collection))
        {
            if(($body = $this->getNextPage()) !== false)
            {
                $this->collection = $body['data'];
                $this->nextPageUrl = empty($body['links']['next'])? '' : $body['links']['next'];
                $this->position = 0;
            }
            else
                return false;
        }
    }

    public function valid()
    {
        return isset($this->collection[$this->position]);
    }

    private function getNextPage()
    {
        if ($this->getNextPageParams() !== false)
            return $this->resource->all($this->getNextPageParams())->getBody();
        return false;
    }

    private function getNextPageParams()
    {
        if(!empty($this->nextPageUrl))
        {
            parse_str(parse_url($this->nextPageUrl)['query'], $query);
            return ['page[number]' => $query['page']['number'], 'page[size]' => $query['page']['size']];
        }
        return false;
    }
}
