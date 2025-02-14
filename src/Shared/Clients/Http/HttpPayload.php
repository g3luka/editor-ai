<?php

namespace EditorAI\Shared\Clients\Http;

use EditorAI\Shared\Contracts\Exportable;
use EditorAI\Shared\Traits\UseConfig;

class HttpPayload implements Exportable
{
    use UseConfig;

    public array $payload = [];

    public function __construct()
    {
        $this->payload['headers'] = [];
    }

    private function validate()
    {
        if (empty($this->payload['endpoint'])) throw new \InvalidArgumentException('Endpoint não foi informado.');
        if (empty($this->payload['body'])) throw new \InvalidArgumentException('Body não foi informado.');
    }

    private function parse(): array
    {
        if (empty($this->payload['headers'])) $this->addHeader('Content-Type', 'application/json');
        return $this->payload;
    }

    public function export(): array
    {
        $this->validate();
        return $this->parse();
    }

    public function setEndpoint(string $endpoint)
    {
        $this->payload['endpoint'] = $endpoint;
        return $this;
    }

    public function addHeader(string $key, string $value)
    {
        $this->payload['headers'][$key] = $value;
        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->payload['headers'] = $headers;
        return $this;
    }

    public function setBody(array $body)
    {
        $this->payload['body'] = json_encode($body);
        return $this;
    }
}
