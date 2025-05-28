<?php

namespace EditorAI\Guardrails;

use EditorAI\Settings;
use EditorAI\Shared\Traits\HasHooks;
use Exception;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Guardrails
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('init', [$this, 'postType']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->registerHooks();
    }

    public static function restGuardrails() {
        $query = new WP_Query([
            'post_type' => 'editor-ai-guardrails',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $items = [];
        while ($query->have_posts()) : $query->the_post();
            array_push($items, [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'instruction' => get_field('instruction'),
            ]);
        endwhile;
        return $items;
    }

    public static function restGuardrail(WP_REST_Request $request) {
        if (!$request->get_url_params('id')) {
            throw new Exception('Nenhum id informado');
        }
        if (!$guardrail = WP_Post::get_instance($request->get_url_params('id'))) {
            throw new Exception('Nenhum Guardrail encontrado com este id');
        }
        return [
            'id' => $guardrail->ID,
            'title' => get_the_title($guardrail->ID),
            'instruction' => get_field('instruction', $guardrail->ID),
        ];
    }

    public function adminMenu()
    {
        add_submenu_page(
            'editor-ai',
            __('Guardrails', 'editor-ai'),
            __('Guardrails', 'editor-ai'),
            'edit_posts',
            'edit.php?post_type=editor-ai-guardrail',
        );
    }

    public function postType()
    {
        register_post_type('editor-ai-guardrail', [
            'label'              => __('Guardrails', 'editor-ai'),
            'labels'             => [
                'menu_name'             => __('Editor AI', 'editor-ai'),
                'name'                  => __('Guardrails', 'editor-ai'),
                'singular_name'         => __('Guardrail', 'editor-ai'),
                'name_admin_bar'        => __('Guardrail', 'editor-ai'),
                'add_new'               => __('Add new', 'editor-ai'),
                'add_new_item'          => __('Add new Guardrail', 'editor-ai'),
                'new_item'              => __('New Guardrail', 'editor-ai'),
                'edit_item'             => __('Edit Guardrail', 'editor-ai'),
                'view_item'             => __('View Guardrail', 'editor-ai'),
                'all_items'             => __('All Guardrails', 'editor-ai'),
                'search_items'          => __('Search Guardrails', 'editor-ai'),
                'parent_item_colon'     => __('Parent Guardrail:', 'editor-ai'),
                'not_found'             => __('No Guardrails found', 'editor-ai'),
                'not_found_in_trash'    => __('No Guardrails found in trash', 'editor-ai'),
                'featured_image'        => __('Guardrail image', 'editor-ai'),
                'set_featured_image'    => __('Set Guardrail image', 'editor-ai'),
                'remove_featured_image' => __('Remove Guardrail image', 'editor-ai'),
                'use_featured_image'    => __('Use as Guardrail image', 'editor-ai'),
                'archives'              => __('Guardrails archive', 'editor-ai'),
                'insert_into_item'      => __('Insert Guardrail', 'editor-ai'),
                'uploaded_to_this_item' => __('Imported into this Guardrail', 'editor-ai'),
                'filter_items_list'     => __('Filtered list of Guardrails', 'editor-ai'),
                'items_list_navigation' => __('Navigation list Guardrails', 'editor-ai'),
                'items_list'            => __('List of Guardrails', 'editor-ai'),
            ],
            'description'        => __('EditorAI Guardrails', 'editor-ai'),
            'public'             => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_nav_menus'  => false,
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 99,
            'menu_icon'          => Settings::ICON,
            'supports'           => ['title']
        ]);
    }
}