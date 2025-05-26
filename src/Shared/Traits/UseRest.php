<?php

namespace EditorAI\Shared\Traits;

use EditorAI\Shared\ValueObjects\Rest;
use WP_Error;
use WP_Http;

trait UseRest
{
    use HasHooks;

    private $restRoutes = [];

    public function registerRest()
    {
        $this->addAction('rest_api_init', [$this, 'registerRestCallback']);
        $this->registerHooks();
    }

    public function registerRestCallback()
    {
        if (!$this->hasRestRoutes()) return;
        foreach ($this->restRoutes as $rest) {
            $permission = (is_string($rest->permission) && $rest->permission !== '__return_true')
                ? fn() => $this->restPermission($rest->permission)
                : $rest->permission;
            register_rest_route($rest->namespace, $rest->route, [
                'methods' => $rest->methods,
                'callback' => $rest->callback,
                'permission_callback' => $permission,
            ], $rest->override);
        }
    }

    public function addRestRoute(string $route, $callback, string $methods = 'GET', string $namespace = 'athena/v1', $permission = '__return_true', bool $override = false)
    {
        $this->restRoutes[] = new Rest($namespace, $route, $methods, $callback, $permission, $override);
    }

    public function hasRestRoutes(): bool
    {
        return count($this->restRoutes) > 0;
    }

    public function restPermission($capability = 'manage_options')
    {
        if (!current_user_can($capability)) {
            return new WP_Error('rest_forbidden', esc_html__('You are not allowed to access this resource.'), ['status' => WP_Http::FORBIDDEN]);
        }
        return true;
    }
}
