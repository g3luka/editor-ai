<?php

namespace EditorAI;

use EditorAI\Agents\Agents;
use EditorAI\Agents\DictionaryAgent;
use EditorAI\Playground;
use EditorAI\Shared\Traits\HasHooks;
use EditorAI\Shared\Traits\UseRest;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\NeuronException;
use NeuronAI\Exceptions\ProviderException;
use Throwable;
use WP_REST_Server;

class Rest
{
    use HasHooks, UseRest;

    public function __construct()
    {
        $this->addRestRoute(namespace: 'editor-ai/v1', route: '/playground', methods: WP_REST_Server::CREATABLE, callback: [Playground::class, 'restCallback']);

        $this->addRestRoute(namespace: 'editor-ai/v1', route: '/agents', methods: WP_REST_Server::READABLE, callback: [Agents::class, 'restAgents'], permission: 'edit_posts');
        $this->addRestRoute(namespace: 'editor-ai/v1', route: '/agents/(?P<id>\d+)', methods: WP_REST_Server::READABLE, callback: [Agents::class, 'restAgent'], permission: 'edit_posts');

        $this->addRestRoute(namespace: 'editor-ai/v1', route: '/agents/test', methods: WP_REST_Server::READABLE, callback: function () {
            try {
                $agent = DictionaryAgent::make()->chat(new UserMessage("análise"));
                return [
                    ...$agent->jsonSerialize(),
                    'content' => json_decode($agent->getContent()),
                ];
            } catch (NeuronException $e) {
                return [
                    'exception' => 'NeuronAI',
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            } catch (ProviderException $e) {
                return [
                    'exception' => 'Provider',
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            } catch (Throwable $e) {
                return [
                    'exception' => 'Business',
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        });
        $this->registerRest();
    }
}
