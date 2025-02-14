<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;
use EditorAI\Shared\Traits\UseRest;
use EditorAI\UseCases\Playground;

class Rest
{
    use HasHooks, UseRest;

    public function __construct()
    {
        $this->addRestRoute(namespace: 'editor-ai/v1', route: '/playground', methods: \WP_REST_Server::CREATABLE, callback: [Playground::class, 'restCallback']);
        $this->registerRest();
    }
}
