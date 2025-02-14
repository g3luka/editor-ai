<?php

namespace EditorAI\Shared\Contracts;

interface ClientInterface
{
    public function setConnection(...$args): void;
    public function find(...$args);
    public function query(...$args);
    public function update(...$args);
    public function save(...$args);
    public function remove(...$args);
}
