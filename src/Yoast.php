<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;

class Yoast
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('admin_init', [$this, 'removeAdminFilters'], 20);
        $this->addFilter('wpseo_accessible_post_types', [$this, 'disableMetabox']);
        $this->addFilter('manage_edit-editor-ai-agent_columns', [$this, 'removeAdminColumns'], 10, 1);
        $this->addFilter('manage_edit-editor-ai-persona_columns', [$this, 'removeAdminColumns'], 10, 1);
        $this->addFilter('manage_edit-editor-ai-schedule_columns', [$this, 'removeAdminColumns'], 10, 1);
        $this->registerHooks();
    }

    public function disableMetabox($post_types)
    {
        unset($post_types['editor-ai-agent']);
        unset($post_types['editor-ai-persona']);
        unset($post_types['editor-ai-schedule']);
        return $post_types;
    }

    public function removeAdminFilters()
    {
        global $wpseo_meta_columns ;
        if (!$wpseo_meta_columns) return;
        remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown']);
        remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown_readability']);
    }

    public function removeAdminColumns($columns)
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