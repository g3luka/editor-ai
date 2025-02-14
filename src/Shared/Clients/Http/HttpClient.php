<?php

namespace EditorAI\Shared\Clients\Http;

use EditorAI\Shared\Contracts\EventDriver;

class HttpClient implements EventDriver
{
    public function send(array $payload)
    {
        $endpoint = apply_filters('editorai_client_http_payload', $payload['endpoint']);
        unset($payload['endpoint']);
        $payload = apply_filters('editorai_client_http_payload', [
            ...$payload,
            'sslverify'         => apply_filters('editorai_client_http_sslverify', false),
            'timeout'           => apply_filters('editorai_client_http_timeout', 120),
        ]);
        $response = wp_remote_post($endpoint, $payload);
        if (is_wp_error($response)) return false;
        if (wp_remote_retrieve_response_code($response) === 204) return true;
        if (wp_remote_retrieve_response_code($response) !== 200) return false;
        $result = wp_remote_retrieve_body($response);
        return json_decode($result, true);
    }
}
