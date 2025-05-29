<?php

namespace EditorAI\Schedules;

use EditorAI\AbstractType;
use Exception;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Schedules extends AbstractType
{
    public string $typeName = 'editor-ai-schedule';
    public string $name = 'Schedules';
    public string $singularName = 'Schedule';

    public static function restList()
    {
        $query = new WP_Query([
            'post_type' => 'editor-ai-agent',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $items = [];
        while ($query->have_posts()) {
            $query->the_post();
            $items[] = Schedules::getFields($query->post);
        };
        return $items;
    }

    public static function restItem(WP_REST_Request $request)
    {
        if (!$request->get_url_params('id')) {
            throw new Exception('Nenhum id informado');
        }
        if (!$post = WP_Post::get_instance($request->get_url_params('id'))) {
            throw new Exception('Nenhum item encontrado com este id');
        }
        return Schedules::getFields($post);
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