<?php

namespace EditorAI;

use EditorAI\Agents\Agents;
use EditorAI\Personas\Personas;
use EditorAI\Schedules\Schedules;
use EditorAI\Shared\Traits\HasHooks;

class Settings
{
    final const ICON = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjEwMHB4IiBoZWlnaHQ9IjEwMHB4IiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8cGF0aCBpZD0icm9ib3QiIHN0eWxlPSJmaWxsOiMwMGJlNjI7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87b3BhY2l0eToxO3N0cm9rZTpub25lOyIgZD0iTTQ5Ljk0MTQsMC4wMDA0NTgxMTIgQzQ3Ljg3MzQsMC4wMzI3NjU0LDQ2LjIyMjMsMS43MzQwNSw0Ni4yNTIsMy44MDIxIEM0Ni4yNTIsNS4wNTE0Miw0Ni4yNTIsNi4zMDA3NSw0Ni4yNTIsNy41NTAwNyBDNDAuNDIxOCw3LjU1MDA3LDM0LjU5MTcsNy41NTAwNywyOC43NjE1LDcuNTUwMDcgQzIyLjU2MjQsNy41NTAwNywxNy41MTc2LDEyLjU5NDgsMTcuNTE3NiwxOC43OTQgQzE3LjUxNzYsMjYuMjg5OSwxNy41MTc2LDMzLjc4NTksMTcuNTE3Niw0MS4yODE4IEMxNy41MTc2LDQ3LjQ4MSwyMi41NjI0LDUyLjUyNTcsMjguNzYxNSw1Mi41MjU3IEM0Mi45MjA1LDUyLjUyNTcsNTcuMDc5NSw1Mi41MjU3LDcxLjIzODUsNTIuNTI1NyBDNzcuNDM3Niw1Mi41MjU3LDgyLjQ4MjQsNDcuNDgxLDgyLjQ4MjQsNDEuMjgxOCBDODIuNDgyNCwzMy43ODU5LDgyLjQ4MjQsMjYuMjg5OSw4Mi40ODI0LDE4Ljc5NCBDODIuNDgyNCwxMi41OTQ4LDc3LjQzNzYsNy41NTAwNyw3MS4yMzg1LDcuNTUwMDcgQzY1LjQwODMsNy41NTAwNyw1OS41NzgxLDcuNTUwMDcsNTMuNzQ4LDcuNTUwMDcgQzUzLjc0OCw2LjMwMDc1LDUzLjc0OCw1LjA1MTQyLDUzLjc0OCwzLjgwMjEgQzUzLjc2MjUsMi43ODg2LDUzLjM2NiwxLjgxMjQzLDUyLjY0ODgsMS4wOTYxNiBDNTEuOTMxNiwwLjM3OTkwMiw1MC45NTQ5LC0wLjAxNTM4MzQsNDkuOTQxNCwwLjAwMDQ1ODExMiB6IE0zOC43NTYxLDIyLjU0MiBDNDIuMjA2NywyMi41NDIsNDUuMDAyNywyNS4zMzc5LDQ1LjAwMjcsMjguNzg4NiBDNDUuMDAyNywzMi4yMzkyLDQyLjIwNjcsMzUuMDM1MiwzOC43NTYxLDM1LjAzNTIgQzM1LjMwNTUsMzUuMDM1MiwzMi41MDk1LDMyLjIzOTIsMzIuNTA5NSwyOC43ODg2IEMzMi41MDk1LDI1LjMzNzksMzUuMzA1NSwyMi41NDIsMzguNzU2MSwyMi41NDIgeiBNNjEuMjQzOSwyMi41NDIgQzY0LjY5NDUsMjIuNTQyLDY3LjQ5MDUsMjUuMzM3OSw2Ny40OTA1LDI4Ljc4ODYgQzY3LjQ5MDUsMzIuMjM5Miw2NC42OTQ1LDM1LjAzNTIsNjEuMjQzOSwzNS4wMzUyIEM1Ny43OTMzLDM1LjAzNTIsNTQuOTk3MywzMi4yMzkyLDU0Ljk5NzMsMjguNzg4NiBDNTQuOTk3MywyNS4zMzc5LDU3Ljc5MzMsMjIuNTQyLDYxLjI0MzksMjIuNTQyIHogTTIxLjI2NTYsNjAuMDIxNyBDMTUuMDY2NCw2MC4wMjE3LDEwLjAyMTYsNjUuMDY2NCwxMC4wMjE2LDcxLjI2NTYgQzEwLjAyMTYsNzEuOSwxMC4wMjE2LDcyLjUzNDQsMTAuMDIxNiw3My4xNjg4IEMxMC4wMjE2LDc5LjA1MzIsMTIuNDY5MSw4NC43MDUyLDE2LjczNjgsODguNjc4IEMyMi4yODEzLDkzLjg0MjgsMzIuNTUxOSwxMDAsNTAsMTAwIEM2Ny40NDgxLDEwMCw3Ny43MjEyLDkzLjg0MjgsODMuMjYzMiw4OC42NzggQzg3LjUzMzQsODQuNzA1Miw4OS45NzgzLDc5LjA1MDcsODkuOTc4Myw3My4xNjg4IEM4OS45NzgzLDcyLjUzNDQsODkuOTc4Myw3MS45LDg5Ljk3ODMsNzEuMjY1NiBDODkuOTc4Myw2NS4wNjY0LDg0LjkzMzYsNjAuMDIxNyw3OC43MzQ0LDYwLjAyMTcgQzc4LjczNDQsNjAuMDIxNywyMS4yNjU2LDYwLjAyMTcsMjEuMjY1Niw2MC4wMjE3IHoiLz4KPC9zdmc+Cg==";
    final const ADMIN_MENU = 'admin.php?page=editor-ai';

    public Agents $agents;
    public Personas $personas;
    public Schedules $schedules;

    use HasHooks;

    public function __construct()
    {
        $this->agents = new Agents;
        $this->personas = new Personas;
        $this->schedules = new Schedules;
        new Yoast;

        $this->addAction('admin_init', [$this, 'settings']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->addFilter('wp_sitemaps_post_types', [$this, 'removeSitemap']);
        $this->registerHooks();
    }

    public function removeSitemap($postTypes)
    {
        unset($postTypes['editor-ai-agent']);
        unset($postTypes['editor-ai-persona']);
        unset($postTypes['editor-ai-schedule']);
        return $postTypes;
    }

    public function adminMenu()
    {
        add_menu_page(
            __('Editor AI', 'editor-ai'),
            __('Editor AI', 'editor-ai'),
            'manage_options',
            'editor-ai',
            '',
            self::ICON,
            100,
        );
        add_submenu_page(
            'editor-ai',
            __('Settings', 'editor-ai'),
            __('Settings', 'editor-ai'),
            'manage_options',
            'editor-ai-settings',
            [$this, 'form'],
            100,
        );
    }

    public function settings()
    {
        add_settings_section('editorai_conection_section', 'Informações de Conexão', [$this, 'displayConnectionSection'], 'editorai_group');

        add_settings_field('editorai_athena_endpoint', 'Endpoint Athena', [$this, 'displayEndpointElement'], 'editorai_group', 'editorai_conection_section', ['label_for' => 'editorai_athena_endpoint']);
        register_setting('editorai_group', 'editorai_athena_endpoint');

        add_settings_section('editorai_defaults_section', 'Configurações Padrão', [$this, 'displayDefaultsSection'], 'editorai_group');

        add_settings_field('editorai_default_model', 'Modelo', [$this, 'displayModelElement'], 'editorai_group', 'editorai_defaults_section', ['label_for' => 'editorai_default_model']);
        register_setting('editorai_group', 'editorai_default_model');
        add_settings_field('editorai_default_temperature', 'Temperatura', [$this, 'displayTemperatureElement'], 'editorai_group', 'editorai_defaults_section', ['label_for' => 'editorai_default_temperature']);
        register_setting('editorai_group', 'editorai_default_temperature');
        add_settings_field('editorai_default_topp', 'Top P', [$this, 'displayTopPElement'], 'editorai_group', 'editorai_defaults_section', ['label_for' => 'editorai_default_topp']);
        register_setting('editorai_group', 'editorai_default_topp');
        add_settings_field('editorai_default_topk', 'Top K', [$this, 'displayTopKElement'], 'editorai_group', 'editorai_defaults_section', ['label_for' => 'editorai_default_topk']);
        register_setting('editorai_group', 'editorai_default_topk');
        add_settings_field('editorai_default_maxtokens', 'Max tokens', [$this, 'displayMaxTokensElement'], 'editorai_group', 'editorai_defaults_section', ['label_for' => 'editorai_default_maxtokens']);
        register_setting('editorai_group', 'editorai_default_maxtokens');
    }

    public function form()
    {
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()) ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('editorai_group');
                do_settings_sections('editorai_group');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function displayConnectionSection()
    {
        echo 'Configurações de conexão com a API da Athena';
    }

    public function displayDefaultsSection()
    {
        echo 'Valores padrão dos parâmetros do modelo';
    }

    public function displayEndpointElement(array $args)
    {
        $name = 'editorai_athena_endpoint';
        $value = get_option($name);
        ?>
        <input
            id="<?= esc_attr($args['label_for']) ?>"
            type="text"
            name="<?= $name ?>"
            value="<?= esc_attr($value) ?>"
            class="large-text"
            required
        >
        <?php
    }

    public function displayModelElement(array $args)
    {
        $name = 'editorai_default_model';
        $value = get_option($name);
        $models = Playground::MODELS;
        array_unshift($models, ["name" => "", "label" => "-- Selecione um Modelo --"]);
        ?>
        <select
            id="<?= esc_attr($args['label_for']) ?>"
            name="<?= $name ?>"
            required
        >
            <?php foreach ($models as $model) : ?>
            <option value="<?= $model['name'] ?>" <?= (esc_attr($value) === $model['name']) ? 'selected' : '' ?>><?= $model['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function displayTemperatureElement(array $args)
    {
        $name = 'editorai_default_temperature';
        $value = get_option($name);
        ?>
        <input
            id="<?= esc_attr($args['label_for']) ?>"
            name="<?= $name ?>"
            value="<?= esc_attr($value) ?>"
            type="number"
            step="0.1"
            min="0"
            max="1"
            required
        >
        <?php
    }

    public function displayTopPElement(array $args)
    {
        $name = 'editorai_default_topp';
        $value = get_option($name);
        ?>
        <input
            id="<?= esc_attr($args['label_for']) ?>"
            name="<?= $name ?>"
            value="<?= esc_attr($value) ?>"
            type="number"
            step="0.001"
            min="0"
            max="1"
            required
        >
        <?php
    }

    public function displayTopKElement(array $args)
    {
        $name = 'editorai_default_topk';
        $value = get_option($name);
        ?>
        <input
            id="<?= esc_attr($args['label_for']) ?>"
            name="<?= $name ?>"
            value="<?= esc_attr($value) ?>"
            type="number"
            step="1"
            min="1"
            max="500"
            required
        >
        <?php
    }

    public function displayMaxTokensElement(array $args)
    {
        $name = 'editorai_default_maxtokens';
        $value = get_option($name);
        ?>
        <input
            id="<?= esc_attr($args['label_for']) ?>"
            name="<?= $name ?>"
            value="<?= esc_attr($value) ?>"
            type="number"
            step="1"
            min="100"
            max="5000"
            required
        >
        <?php
    }
}
