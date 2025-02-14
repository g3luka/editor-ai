<?php

namespace EditorAI\Shared\Contracts;

interface EventDriver
{
    public function send(array $payload);
}
