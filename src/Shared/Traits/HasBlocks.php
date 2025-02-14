<?php

namespace EditorAI\Shared\Traits;

trait HasBlocks
{
    public array $blocks = [];
    public string $blocksPath = EDITORAI_CORE_PATH . "blocks";
    public string $blocksNamespace = 'EditorAI\\Blocks';

    public function getRegister(): object
    {
        return (object) include $this->blocksPath . "/register.php";
    }

    public function registerBlocks(): void
    {
        $register = $this->getRegister();
        foreach ($register->blocks as $block) {
            $this->registerBlock($block);
        }
    }
    public function registerSidebars(): void
    {
        $register = $this->getRegister();
        foreach ($register->sidebars as $sidebar) {
            $this->registerSidebar($sidebar);
        }
    }

    public function registerBlock(string $blockName, array $options = []): void
    {
        $blockPath = "{$this->blocksPath}/build/{$blockName}";
        $blockJsonPath = "{$blockPath}/block.json";

        if (!file_exists($blockJsonPath)) return;
        $metadata = register_block_type($blockJsonPath, $options);

        /** @var object|false $metadata */
        if ($metadata?->morpheus?->init && file_exists($blockPath . "/Block.php")) {
            $blockClass = "{$this->blocksNamespace}\\{$this->getBlockFilteredName($blockName)}\\Block";
            $this->blocks[$blockName] = new $blockClass;
        }
    }

    public function registerSidebar(string $sidebarName): void
    {
        $sidebarPath = "{$this->blocksPath}/build/{$sidebarName}/sidebar.php";
        if (!file_exists($sidebarPath)) return;
        $metadata = (object) require $sidebarPath;

        add_action('enqueue_block_editor_assets', function () use ($sidebarName, $metadata) {
            global $current_screen;
            if ($metadata->postType && !in_array($current_screen->post_type, $metadata->postType)) return;
            wp_enqueue_script(
                $metadata->name,
                EDITORAI_CORE_URL . "blocks/build/{$sidebarName}/index.js",
                $metadata->dependencies ?? [],
                $metadata->version ?? '1.0',
                $metadata->args ?? null,
            );
        });
    }

    public function getBlockFilteredName(string $blockName): string
    {
        return preg_replace('/^[^\/]\/(.+)$/', '$1', $blockName);
    }
}
