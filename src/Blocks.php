<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasBlocks;
use EditorAI\Shared\Traits\HasHooks;
use EditorAI\Shared\Traits\Singleton;

class Blocks
{
    use Singleton, HasHooks, HasBlocks;

    public array $blocks = [];
    public string $blocksPath = EDITORAI_CORE_PATH . "blocks";
    public string $blocksNamespace = 'EditorAI\\Blocks';

    public function __construct()
    {
        $this->addAction('init',                            [$this, 'registerBlocks']);
        $this->registerHooks();
        $this->registerSidebars();
    }
}
