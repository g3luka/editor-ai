<?php

use EditorAI\Shared\Utils\Helper;

if (!function_exists('redirectTo')) {
    function redirectTo($url, $status = 301) {
        Helper::redirectTo($url, $status);
    }
}
