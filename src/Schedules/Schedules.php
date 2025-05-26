<?php

namespace EditorAI\Schedules;

use EditorAI\Settings;
use EditorAI\Shared\Traits\HasHooks;
use WP_Query;

class Schedules
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('init', [$this, 'postType']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->registerHooks();
    }

    public static function restSchedules() {
        $query = new WP_Query([
            'post_type' => 'editor-ai-agent',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $agents = [];
        while ($query->have_posts()) : $query->the_post();
            array_push($agents, [
                'title' => get_the_title(),
                'buttonLabel' => get_field('label'),
                'icon' => get_field('icon'),
                'provider' => get_field('provider'),
                'model' => get_field('model'),
                'prompt' => get_field('prompt'),
                'personaWriter' => get_field('persona_writer'),
                'personaTarget' => get_field('persona_target'),
                'context' => get_field('context'),
                'useContent' => get_field('use_content'),
                'maxTokens' => (int) get_field('max_tokens'),
                'temperature' => (int) get_field('temperature'),
                'topP' => (float) get_field('top_p'),
                'topK' => (int) get_field('top_k'),
            ]);
        endwhile;
        return $agents;
    }

    public function adminMenu()
    {
        add_submenu_page(
            'editor-ai',
            __('Schedules', 'editor-ai'),
            __('Schedules', 'editor-ai'),
            'edit_posts',
            'edit.php?post_type=editor-ai-schedule',
        );
        // add_submenu_page(
        //     'editor-ai',
        //     __('Add Schedule', 'editor-ai'),
        //     __('Add Schedule', 'editor-ai'),
        //     'edit_posts',
        //     'post-new.php?post_type=editor-ai-schedule',
        // );
    }

    public function postType()
    {
        register_post_type('editor-ai-schedule', [
            'label'              => __('Schedules', 'editor-ai'),
            'labels'             => [
                'menu_name'             => __('Editor AI', 'editor-ai'),
                'name'                  => __('Schedules', 'editor-ai'),
                'singular_name'         => __('Schedule', 'editor-ai'),
                'name_admin_bar'        => __('Schedule', 'editor-ai'),
                'add_new'               => __('Add new', 'editor-ai'),
                'add_new_item'          => __('Add new Schedule', 'editor-ai'),
                'new_item'              => __('New Schedule', 'editor-ai'),
                'edit_item'             => __('Edit Schedule', 'editor-ai'),
                'view_item'             => __('View Schedule', 'editor-ai'),
                'all_items'             => __('All Schedules', 'editor-ai'),
                'search_items'          => __('Search Schedules', 'editor-ai'),
                'parent_item_colon'     => __('Parent Schedule:', 'editor-ai'),
                'not_found'             => __('No Schedules found', 'editor-ai'),
                'not_found_in_trash'    => __('No Schedules found in trash', 'editor-ai'),
                'featured_image'        => __('Schedule image', 'editor-ai'),
                'set_featured_image'    => __('Set Schedule image', 'editor-ai'),
                'remove_featured_image' => __('Remove Schedule image', 'editor-ai'),
                'use_featured_image'    => __('Use as Schedule image', 'editor-ai'),
                'archives'              => __('Schedules archive', 'editor-ai'),
                'insert_into_item'      => __('Insert Schedule', 'editor-ai'),
                'uploaded_to_this_item' => __('Imported into this Schedule', 'editor-ai'),
                'filter_items_list'     => __('Filtered list of Schedules', 'editor-ai'),
                'items_list_navigation' => __('Navigation list Schedules', 'editor-ai'),
                'items_list'            => __('List of Schedules', 'editor-ai'),
            ],
            'description'        => __('EditorAI Schedules', 'editor-ai'),
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