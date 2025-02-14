<?php

namespace EditorAI\Shared\ValueObjects;

class Rest
{
    public function __construct(
        public string $namespace,
        public string $route,
        public string $methods,
        public $callback,
        public $permission,
        public ?bool $override,
    ) {
    }
}
