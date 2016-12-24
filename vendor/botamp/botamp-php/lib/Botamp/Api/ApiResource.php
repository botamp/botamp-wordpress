<?php

namespace Botamp\Api;

use Botamp\Client;

/**
 * Class ApiResource

 *
 * @package Botamp\Api
 */

class ApiResource
{
    private $apiRequestor;

    public function __construct($resourceName, Client $client)
    {
        $this->apiRequestor = new ApiRequestor($resourceName, $client);
    }

    public function all(array $params = [])
    {
        $response = $this->apiRequestor->send('all', $params);
        return new BotampObject(ApiResponse::getContent($response), $this);
    }

    public function get($id = null)
    {
        $response = $this->apiRequestor->send('get', ['id' => $id]);
        return new BotampObject(ApiResponse::getContent($response), $this);
    }

    public function create($attributes)
    {
        $response = $this->apiRequestor->send('create', $attributes);
        return new BotampObject(ApiResponse::getContent($response), $this);
    }

    public function update($id, $attributes)
    {
        $attributes['id'] = $id;
        $response = $this->apiRequestor->send('update', $attributes);
        return new BotampObject(ApiResponse::getContent($response), $this);
    }

    public function delete($id)
    {
        $response = $this->apiRequestor->send('delete', ['id' => $id]);
        return new BotampObject(ApiResponse::getContent($response), $this);
    }
}
