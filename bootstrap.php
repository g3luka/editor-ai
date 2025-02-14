<?php
/**
 * Editor AI
 *
 * @author      Giovanni de Luca
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Editor AI
 * Plugin URI:  https://github.com/g3luka/editor-ai
 * Description: WordPress Editor with Artificial Intelligence
 * Version:     1.0
 * Author:      Giovanni de Luca
 * Author URI:  https://github.com/g3luka
 * Text Domain: editor-ai
 * License:     MIT
 */

//  Exit if accessed directly.
defined('ABSPATH') || exit;

if (!defined('EDITORAI_CORE_PATH')) define('EDITORAI_CORE_PATH', plugin_dir_path(__FILE__));
if (!defined('EDITORAI_CORE_URL')) define('EDITORAI_CORE_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/vendor/autoload.php';
global $editoai;
$editorai = EditorAI\Kernel::getInstance();

register_activation_hook(__FILE__,      [$editorai, 'activation']);
register_deactivation_hook(__FILE__,    [$editorai, 'deactivation']);
