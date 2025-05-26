<?php

namespace EditorAI\Agents;

use EditorAI\Settings;
use EditorAI\Shared\Traits\HasHooks;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class Agents
{
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

    use HasHooks;

    public function __construct()
    {
        $this->addAction('init', [$this, 'postType']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->registerHooks();
    }

    public static function restAgents() {
        $query = new WP_Query([
            'post_type' => 'editor-ai-agent',
            'post_status' => 'publish',
        ]);
        if (!$query->have_posts()) return [];
        $agents = [];
        while ($query->have_posts()) {
            $query->the_post();
            $agents[] = self::getAgentFields($query->post);
        }
        return $agents;
    }

    public static function restAgent(WP_REST_Request $request) {
        $post = get_post($request->get_param('id'));
        if (!$post) return new WP_Error('agent_not_found', 'Agent not found', ['status' => 404]);
        return self::getAgentFields($post);
    }

    public static function getAgentFields(WP_Post $post)
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

    public function adminMenu()
    {
        add_submenu_page(
            'editor-ai',
            __('Agents', 'editor-ai'),
            __('Agents', 'editor-ai'),
            'edit_posts',
            'edit.php?post_type=editor-ai-agent',
        );
        // add_submenu_page(
        //     'editor-ai',
        //     __('Add Agent', 'editor-ai'),
        //     __('Add Agent', 'editor-ai'),
        //     'edit_posts',
        //     'post-new.php?post_type=editor-ai-agent',
        // );
    }

    public function postType()
    {
        register_post_type('editor-ai-agent', [
            'label'              => __('Agents', 'editor-ai'),
            'labels'             => [
                'menu_name'             => __('Editor AI', 'editor-ai'),
                'name'                  => __('Agents', 'editor-ai'),
                'singular_name'         => __('Agent', 'editor-ai'),
                'name_admin_bar'        => __('Agent', 'editor-ai'),
                'add_new'               => __('Add new', 'editor-ai'),
                'add_new_item'          => __('Add new Agent', 'editor-ai'),
                'new_item'              => __('New Agent', 'editor-ai'),
                'edit_item'             => __('Edit Agent', 'editor-ai'),
                'view_item'             => __('View Agent', 'editor-ai'),
                'all_items'             => __('All Agents', 'editor-ai'),
                'search_items'          => __('Search Agents', 'editor-ai'),
                'parent_item_colon'     => __('Parent Agent:', 'editor-ai'),
                'not_found'             => __('No Agents found', 'editor-ai'),
                'not_found_in_trash'    => __('No Agents found in trash', 'editor-ai'),
                'featured_image'        => __('Agent image', 'editor-ai'),
                'set_featured_image'    => __('Set Agent image', 'editor-ai'),
                'remove_featured_image' => __('Remove Agent image', 'editor-ai'),
                'use_featured_image'    => __('Use as Agent image', 'editor-ai'),
                'archives'              => __('Agents archive', 'editor-ai'),
                'insert_into_item'      => __('Insert Agent', 'editor-ai'),
                'uploaded_to_this_item' => __('Imported into this Agent', 'editor-ai'),
                'filter_items_list'     => __('Filtered list of Agents', 'editor-ai'),
                'items_list_navigation' => __('Navigation list Agents', 'editor-ai'),
                'items_list'            => __('List of Agents', 'editor-ai'),
            ],
            'description'        => __('EditorAI Agents', 'editor-ai'),
            'public'             => true,
            'publicly_queryable' => false,
            'exclude_from_search'=> true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_nav_menus'  => false,
            'show_in_rest'       => true,
            'query_var'          => false,
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