<?php
/**
 * Plugin Name: Rogue White Label CRM
 * Plugin URI: https://github.com/RogueAssassin/rogue-white-label-crm
 * Description: A white-label CRM for WordPress with customers, jobs, notes, branding settings, roles, exports, and GitHub-ready documentation.
 * Version: 1.0.0
 * Author: RogueAssassin
 * Author URI: https://github.com/RogueAssassin
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rogue-white-label-crm
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) { exit; }

define('RWLC_VERSION', '1.0.0');
define('RWLC_FILE', __FILE__);
define('RWLC_PATH', plugin_dir_path(__FILE__));
define('RWLC_URL', plugin_dir_url(__FILE__));
define('RWLC_SLUG', 'rogue-white-label-crm');

require_once RWLC_PATH . 'includes/class-installer.php';
require_once RWLC_PATH . 'includes/class-settings.php';
require_once RWLC_PATH . 'includes/class-admin.php';
require_once RWLC_PATH . 'includes/class-crud.php';
require_once RWLC_PATH . 'includes/class-export.php';
require_once RWLC_PATH . 'includes/helpers.php';

register_activation_hook(__FILE__, ['RWLC_Installer', 'activate']);
register_deactivation_hook(__FILE__, ['RWLC_Installer', 'deactivate']);

add_action('plugins_loaded', function () {
    load_plugin_textdomain('rogue-white-label-crm', false, dirname(plugin_basename(__FILE__)) . '/languages');
    RWLC_Settings::init();
    RWLC_Admin::init();
});
