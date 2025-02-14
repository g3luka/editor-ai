<?php

namespace EditorAI\Shared\Utils;

class Dev
{
    public static function isLocal()
    {
        return in_array(wp_get_environment_type(), ['local', 'development']);
    }
}
