<?php

namespace EditorAI\Shared\Contracts;

interface Actionable
{
    public function __invoke(...$params): void;
}
