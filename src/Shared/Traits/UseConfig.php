<?php

namespace EditorAI\Shared\Traits;

trait UseConfig
{
    public function getOption(string $name, $defaultValue = '')
    {
        return get_option($name, $defaultValue);
    }
}
