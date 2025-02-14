<?php

namespace EditorAI;

use EditorAI\Shared\Traits\HasHooks;
use EditorAI\UseCases\Playground;

class Settings
{
    use HasHooks;

    public function __construct()
    {
        $this->addAction('admin_init', [$this, 'settings']);
        $this->addAction('admin_menu', [$this, 'adminMenu']);
        $this->registerHooks();
    }

    public function adminMenu()
    {
        add_menu_page(
            'Editor AI',
            'Editor AI',
            'manage_options',
            'editor-ai',
            [$this, 'form'],
            'dashicons-lightbulb',
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
