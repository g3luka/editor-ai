<?php

namespace EditorAI\Providers;

use GuzzleHttp\Client;
use NeuronAI\Providers\OpenAI\OpenAI;

class AzureOpenAI extends OpenAI
{
    protected string $baseUri = "https://%s/openai/deployments/%s";

    public function __construct(
        protected string $key,
        protected string $endpoint,
        protected string $model,
        protected string $version,
        protected array $parameters = [],
    ) {
        $this->setBaseUrl();
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'query'    => [
                'api-version' => $this->version,
            ],
            'headers' => [
                'Authorization' => 'Bearer '.$this->key,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    private function setBaseUrl()
    {
        $this->endpoint = preg_replace('/^https?:\/\/([^\/]*)\/?$/', '$1', $this->endpoint);
        $this->baseUri = sprintf($this->baseUri, $this->endpoint, $this->model);
        $this->baseUri = trim($this->baseUri, '/').'/';
    }
}

