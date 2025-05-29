<?php

namespace EditorAI\Agents;

use EditorAI\AbstractType;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Agents extends AbstractType
{
    public string $typeName = 'editor-ai-agent';
    public string $name = 'Agents';
    public string $singularName = 'Agent';

    final const PROVIDERS = [
        'open-ia' => 'OpenAI',
        'anthropic' => 'Anthropic',
        'gemini' => 'Gemini',
        'ollama' => 'Ollama',
        'deepseek' => 'DeepSeek',
        'mistral' => 'Mistral',
        'azure-openai' => 'Azure OpenAI',
        'aws-bedrock' => 'AWS Bedrock',
    ];

    final const MODELS = [
        'open-ai' => [
            'o4-mini',
            'o3',
            'o3-mini',
            'o1',
            'o1-mini',
            'o1-pro',
            'gpt-4.1',
            'gpt-4.1-mini',
            'gpt-4.1-nano',
            'gpt-4o',
            'gpt-4o-mini',
            'chatgpt-4o-latest',
        ],
        'anthropic' => [
            'claude-3-7-sonnet',
            'claude-3-5-sonnet',
            'claude-3-5-haiku',
            'claude-3-opus',
            'claude-3-haiku',
        ],
        'gemini' => [],
        'ollama' => [],
        'deepseek' => [],
        'mistral' => [],
        'azure-openai' => [],
        'aws-bedrock' => [],
    ];

    public static function restList()
    {
        $query = new WP_Query([
            'post_type' => 'editor-ai-agent',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $agents = [];
        while ($query->have_posts()) {
            $query->the_post();
            $agents[] = self::getFields($query->post);
        }
        return $agents;
    }

    public static function restItem(WP_REST_Request $request)
    {
        $post = get_post($request->get_param('id'));
        if (!$post) return new WP_Error('agent_not_found', 'Agent not found', ['status' => 404]);
        return self::getFields($post);
    }

    public static function getFields(WP_Post $post)
    {
        return [
            'title' => get_the_title($post->ID),
            'buttonLabel' => get_field('label', $post->ID),
            'icon' => get_field('icon', $post->ID),
            'provider' => get_field('provider', $post->ID),
            'model' => get_field('model', $post->ID),
            'prompt' => get_field('prompt', $post->ID),
            'personaWriter' => get_field('persona_writer', $post->ID),
            'personaTarget' => get_field('persona_target', $post->ID),
            'context' => get_field('context', $post->ID),
            'useContent' => get_field('use_content', $post->ID),
            'maxTokens' => (int) get_field('max_tokens', $post->ID),
            'temperature' => (int) get_field('temperature', $post->ID),
            'topP' => (float) get_field('top_p', $post->ID),
            'topK' => (int) get_field('top_k', $post->ID),
        ];
    }
}