<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;
use WP_REST_Request;

abstract class AbstractType
{
    use HasHooks;

    public string $typeName;
    public string $name;
    public string $singularName;
    public array $supports = ['title'];

    public function __construct()
    {
        $this->addAction('init', [$this, 'registerPostType']);
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);

        /** Remove Yoast SEO */
        $this->addFilter('wpseo_accessible_post_types', [$this, 'yoastDisableMetabox']);
        $this->addFilter("manage_edit-{$this->typeName}_columns", [$this, 'yoastRemoveAdminColumns'], 10, 1);

        $this->registerHooks();
    }

    abstract public static function restList();
    abstract public static function restItem(WP_REST_Request $request);

    public function addAdminMenu()
    {
        add_submenu_page(
            'editor-ai',
            __($this->name, 'editor-ai'),
            __($this->name, 'editor-ai'),
            'edit_posts',
            "edit.php?post_type=" . $this->typeName,
        );
    }

    public function registerPostType()
    {
        register_post_type($this->typeName, [
            'label'                 => __($this->name, 'editor-ai'),
            'labels'                => [
                'menu_name'             => __('Editor AI', 'editor-ai'),
                'name'                  => __($this->name, 'editor-ai'),
                'singular_name'         => __($this->singularName, 'editor-ai'),
                'name_admin_bar'        => __($this->singularName, 'editor-ai'),
                'add_new'               => __('Add new', 'editor-ai'),
                'add_new_item'          => __("Add new {$this->singularName}", 'editor-ai'),
                'new_item'              => __("New {$this->singularName}", 'editor-ai'),
                'edit_item'             => __("Edit {$this->singularName}", 'editor-ai'),
                'view_item'             => __("View {$this->singularName}", 'editor-ai'),
                'all_items'             => __("All {$this->name}", 'editor-ai'),
                'search_items'          => __("Search {$this->name}", 'editor-ai'),
                'parent_item_colon'     => __("Parent {$this->singularName}:", 'editor-ai'),
                'not_found'             => __("No {$this->name} found", 'editor-ai'),
                'not_found_in_trash'    => __("No {$this->name} found in trash", 'editor-ai'),
                'featured_image'        => __("{$this->singularName} image", 'editor-ai'),
                'set_featured_image'    => __("Set {$this->singularName} image", 'editor-ai'),
                'remove_featured_image' => __("Remove {$this->singularName} image", 'editor-ai'),
                'use_featured_image'    => __("Use as {$this->singularName} image", 'editor-ai'),
                'archives'              => __("{$this->name} archive", 'editor-ai'),
                'insert_into_item'      => __("Insert {$this->singularName}", 'editor-ai'),
                'uploaded_to_this_item' => __("Imported into this {$this->singularName}", 'editor-ai'),
                'filter_items_list'     => __("Filtered list of {$this->name}", 'editor-ai'),
                'items_list_navigation' => __("Navigation list {$this->name}", 'editor-ai'),
                'items_list'            => __("List of {$this->name}", 'editor-ai'),
            ],
            'description'           => __("EditorAI {$this->name}", 'editor-ai'),
            'public'                => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_nav_menus'     => false,
            'query_var'             => true,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => 99,
            'menu_icon'             => Settings::ICON,
            'supports'              => $this->supports,
            'can_export'            => true,
        ]);
    }

    public function yoastDisableMetabox($post_types)
    {
        unset($post_types[$this->typeName]);
        return $post_types;
    }


    public function yoastRemoveAdminColumns($columns)
    {
        unset($columns['wpseo-score']);
        unset($columns['wpseo-score-readability']);
        unset($columns['wpseo-title']);
        unset($columns['wpseo-metadesc']);
        unset($columns['wpseo-focuskw']);
        unset($columns['wpseo-links']);
        unset($columns['wpseo-linked']);
        unset($columns['wpseo-cornerstone']);
        return $columns;
    }
}