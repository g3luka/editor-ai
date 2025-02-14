<?php

namespace EditorAI\Shared\Contracts;

interface Filterable
{
    public function __invoke(...$params);
}
