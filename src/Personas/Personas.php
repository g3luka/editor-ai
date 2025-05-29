<?php

namespace EditorAI\Personas;

use EditorAI\AbstractType;
use Exception;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Personas extends AbstractType
{
    public string $typeName = 'editor-ai-persona';
    public string $name = 'Personas';
    public string $singularName = 'Persona';

    public static function restList()
    {
        $query = new WP_Query([
            'post_type' => 'editor-ai-persona',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $items = [];
        while ($query->have_posts()) {
            $query->the_post();
            array_push($items, [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'instruction' => get_field('instruction'),
            ]);
        }
        return $items;
    }

    public static function restItem(WP_REST_Request $request)
    {
        if (!$request->get_url_params('id')) {
            throw new Exception('Nenhum id informado');
        }
        if (!$item = WP_Post::get_instance($request->get_url_params('id'))) {
            throw new Exception('Nenhum item encontrado com este id');
        }
        return [
            'id' => $item->ID,
            'title' => get_the_title($item->ID),
            'instruction' => get_field('instruction', $item->ID),
        ];
    }
}