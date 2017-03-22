<?php

namespace Botamp;

use Botamp\Api\ApiResource;
use Botamp\Api\ApiResponse;
use Botamp\Exceptions;
use Http\Client\Common;
use Http\Client\Common\Plugin;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\Authentication\BasicAuth;

/**
 * Class Client
 *
 * @package Botamp
 */
class Client
{
    private $httpClient;

    private $apiKey;

    private $apiBase = 'https://app.botamp.com/api';

    private $apiVersion = 'v1';

    private static $allApiVersions = ['v1'];

    public $entities;

    public $me;

    public $subscriptions;

    public $contacts;

    public function __construct($apiKey, HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;

        $this->setHttpClient($httpClient ?: HttpClientDiscovery::find(), MessageFactoryDiscovery::find());

        $this->bindResources();
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiBase($apiBase)
    {
        $this->apiBase = $apiBase;
        $this->bindResources();
    }

    public function getApiBase()
    {
        return $this->apiBase;
    }

    public function setApiVersion($apiVersion)
    {
        $apiVersion = strtolower($apiVersion);

        if(!in_array($apiVersion, self::$allApiVersions))
            throw new Exceptions\Base("No valid api version provided.");
        else
            $this->apiVersion = $apiVersion;

        $this->bindResources();
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    private function setHttpClient(HttpClient $httpClient, MessageFactory $messageFactory)
    {
        $plugins = [
            new Plugin\HeaderDefaultsPlugin(['Content-Type' => 'application/vnd.api+json']),
            new Plugin\AuthenticationPlugin(new BasicAuth($this->apiKey, ''))
        ];

        $this->httpClient = new Common\HttpMethodsClient(new Common\PluginClient($httpClient, $plugins), $messageFactory);
    }

    private function bindResources()
    {
        $this->contacts = new ApiResource('contacts', $this);
        $this->entities = new ApiResource('entities', $this);
        $this->entityTypes = new ApiResource('entity_types', $this);
        $this->me = new ApiResource('me', $this);
        $this->optins = new ApiResource('optins', $this);
        $this->subscriptions = new ApiResource('subscriptions', $this);
    }
}
