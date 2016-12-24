<?php

namespace Botamp\Api;

use Botamp\Client;
use Botamp\Exceptions;

/**
 * Class ApiRequestor

 *
 * @package Botamp\Api
 */

class ApiRequestor
{
    private $url;
    private $httpClient;
    private $resourceName;

    public function __construct($resourceName, Client $client)
    {
        $this->resourceName = $resourceName;
        $this->url  = $client->getApiBase().'/'.$client->getApiVersion().'/'.$this->resourceName;
        $this->httpClient = $client->getHttpClient();
    }

    public function send($action, $params = [])
    {
        $action = strtolower($action);

        switch($action)
        {
            case 'all':
                return $this->httpClient->get($this->serializeUrl($params));
            case 'get':
                $url = $this->url.( $params['id'] !== null ? '/'.$params['id'] : '');
                return $this->httpClient->get($url);
            case 'create':
                return $this->httpClient->post($this->url, [], $this->serializeBody($params));
            case 'update':
                return $this->httpClient->put($this->url."/{$params['id']}", [], $this->serializeBody($params));
            case 'delete':
                return $this->httpClient->delete($this->url."/{$params['id']}");
            default:
                throw new Exceptions\Base("Unexpected error. Unknown action '$action'.");
                break;
        }
    }

    private function serializeUrl(Array $params)
    {
        return empty($params) ? $this->url : $this->url.'?'.urldecode(http_build_query($params));
    }

    private function serializeBody(array $params)
    {
        if (empty($params))
            return null;

        if (isset($params['id']))
        {
            $id = $params['id'];
            unset($params['id']);
            $params = ['data' => ['type' => $this->resourceName, 'id' => $id, 'attributes' => $params]];
        }
        else
            $params = ['data' => ['type' => $this->resourceName, 'attributes' => $params]];

        return json_encode($params);
    }
}
