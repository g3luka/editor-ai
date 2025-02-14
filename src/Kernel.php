<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;
use EditorAI\Shared\Traits\Singleton;
use EditorAI\Shared\Traits\UseACF;

class Kernel
{
    use Singleton, HasHooks, UseACF;

    public function __construct()
    {
        new Settings;
        new Rest;
        new Blocks;
    }

    public function activation()
    {
        flush_rewrite_rules();
    }

    public function deactivation()
    {
        flush_rewrite_rules();
    }
}
