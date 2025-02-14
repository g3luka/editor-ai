<?php

namespace EditorAI\Shared\ValueObjects;

class Hook
{
    public function __construct(
        public string $name,
        public $callback,
        public ?int $priority = 10,
        public ?int $acceptArgs = 1
    ) {
    }
}
