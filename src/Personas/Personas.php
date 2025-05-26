<?php

namespace EditorAI\Personas;

use EditorAI\Settings;
use EditorAI\Shared\Traits\HasHooks;
use Exception;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Personas
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('init', [$this, 'postType']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->registerHooks();
    }

    public static function restPersonas() {
        $query = new WP_Query([
            'post_type' => 'editor-ai-persona',
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

    public static function restPersona(WP_REST_Request $request) {
        if (!$request->get_url_params('id')) {
            throw new Exception('Nenhum id informado');
        }
        if (!$persona = WP_Post::get_instance($request->get_url_params('id'))) {
            throw new Exception('Nenhuma Persona encontrada com este id');
        }
        return [
            'id' => $persona->ID,
            'title' => get_the_title($persona->ID),
            'instruction' => get_field('instruction', $persona->ID),
        ];
    }

    public function adminMenu()
    {
        add_submenu_page(
            'editor-ai',
            __('Personas', 'editor-ai'),
            __('Personas', 'editor-ai'),
            'edit_posts',
            'edit.php?post_type=editor-ai-persona',
        );
        // add_submenu_page(
        //     'editor-ai',
        //     __('Add Persona', 'editor-ai'),
        //     __('Add Persona', 'editor-ai'),
        //     'edit_posts',
        //     'post-new.php?post_type=editor-ai-persona',
        // );
    }

    public function postType()
    {
        register_post_type('editor-ai-persona', [
            'label'              => __('Personas', 'editor-ai'),
            'labels'             => [
                'menu_name'             => __('Editor AI', 'editor-ai'),
                'name'                  => __('Personas', 'editor-ai'),
                'singular_name'         => __('Persona', 'editor-ai'),
                'name_admin_bar'        => __('Persona', 'editor-ai'),
                'add_new'               => __('Add new', 'editor-ai'),
                'add_new_item'          => __('Add new Persona', 'editor-ai'),
                'new_item'              => __('New Persona', 'editor-ai'),
                'edit_item'             => __('Edit Persona', 'editor-ai'),
                'view_item'             => __('View Persona', 'editor-ai'),
                'all_items'             => __('All Personas', 'editor-ai'),
                'search_items'          => __('Search Personas', 'editor-ai'),
                'parent_item_colon'     => __('Parent Persona:', 'editor-ai'),
                'not_found'             => __('No Personas found', 'editor-ai'),
                'not_found_in_trash'    => __('No Personas found in trash', 'editor-ai'),
                'featured_image'        => __('Persona image', 'editor-ai'),
                'set_featured_image'    => __('Set Persona image', 'editor-ai'),
                'remove_featured_image' => __('Remove Persona image', 'editor-ai'),
                'use_featured_image'    => __('Use as Persona image', 'editor-ai'),
                'archives'              => __('Personas archive', 'editor-ai'),
                'insert_into_item'      => __('Insert Persona', 'editor-ai'),
                'uploaded_to_this_item' => __('Imported into this Persona', 'editor-ai'),
                'filter_items_list'     => __('Filtered list of Personas', 'editor-ai'),
                'items_list_navigation' => __('Navigation list Personas', 'editor-ai'),
                'items_list'            => __('List of Personas', 'editor-ai'),
            ],
            'description'        => __('EditorAI Personas', 'editor-ai'),
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