<?php

namespace EditorAI\Shared\Contracts;

interface Importable
{
    public function import(array $data): void;
}
