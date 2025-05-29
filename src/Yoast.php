<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;

class Yoast
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('admin_init', [$this, 'removeAdminFilters'], 20);
        $this->registerHooks();
    }

    public function removeAdminFilters()
    {
        global $wpseo_meta_columns ;
        if (!$wpseo_meta_columns) return;
        remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown']);
        remove_action('restrict_manage_posts', [$wpseo_meta_columns, 'posts_filter_dropdown_readability']);
    }
}