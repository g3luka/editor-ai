<?php

namespace EditorAI\Shared\Traits;

use EditorAI\Shared\ValueObjects\Hook;

trait HasHooks
{
    private $actions = [];
    private $filters = [];
    private $removeActions = [];
    private $removeFilters = [];

    public function registerHooks(): void
    {
        if ($this->hasActions()) {
            foreach ($this->actions as $hook) {
                add_action($hook->name, $hook->callback, $hook->priority, $hook->acceptArgs);
            }
        }
        if ($this->hasRemoveActions()) {
            foreach ($this->removeActions as $hook) {
                remove_action($hook->name, $hook->callback, $hook->priority, $hook->acceptArgs);
            }
        }
        if ($this->hasRemoveFilters()) {
            foreach ($this->removeFilters as $hook) {
                remove_filter($hook->name, $hook->callback, $hook->priority, $hook->acceptArgs);
            }
        }
        if ($this->hasFilters()) {
            foreach ($this->filters as $hook) {
                add_filter($hook->name, $hook->callback, $hook->priority, $hook->acceptArgs);
            }
        }
    }

    public function addAction(string $name, callable $callback, int $priority = 10, int $acceptArgs = 1, bool $ignoreOnImport = false)
    {
        if ($ignoreOnImport && self::isImport()) return;
        $this->actions[] = new Hook($name, $callback, $priority, $acceptArgs);
    }

    public function addFilter(string $name, callable $callback, int $priority = 10, int $acceptArgs = 1, bool $ignoreOnImport = false)
    {
        if ($ignoreOnImport && self::isImport()) return;
        $this->filters[] = new Hook($name, $callback, $priority, $acceptArgs);
    }

    public function removeAction(string $name, callable $callback, int $priority = 10, int $acceptArgs = 1, bool $ignoreOnImport = false)
    {
        if ($ignoreOnImport && self::isImport()) return;
        $this->removeActions[] = new Hook($name, $callback, $priority, $acceptArgs);
    }

    public function removeFilter(string $name, callable $callback, int $priority = 10, int $acceptArgs = 1, bool $ignoreOnImport = false)
    {
        if ($ignoreOnImport && self::isImport()) return;
        $this->removeFilters[] = new Hook($name, $callback, $priority, $acceptArgs);
    }

    public function hasActions(): bool
    {
        return count($this->actions) > 0;
    }

    public function hasFilters(): bool
    {
        return count($this->filters) > 0;
    }

    public function hasRemoveActions(): bool
    {
        return count($this->removeActions) > 0;
    }

    public function hasRemoveFilters(): bool
    {
        return count($this->removeFilters) > 0;
    }

    public static function isImport(): bool
    {
        return defined('DOING_IMPORT') && DOING_IMPORT;
    }

    public static function setDoingImport(): void
    {
        if (defined('DOING_IMPORT')) return;
        define('DOING_IMPORT', true);
    }
}
