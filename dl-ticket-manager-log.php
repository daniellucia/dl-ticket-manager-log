<?php

/**
 * Plugin Name: Logging for Ticket Manager
 * Description: Gestión de log para el gestor de tickets.
 * Version: 0.0.2
 * Author: Daniel Lúcia
 * Author URI: http://www.daniellucia.es
 * textdomain: dl-ticket-manager-log
 * Requires Plugins: dl-ticket-manager
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/src/Plugin.php';
require_once __DIR__ . '/src/Hooks.php';
require_once __DIR__ . '/src/Columns.php';
require_once __DIR__ . '/src/Cpt.php';

add_action('plugins_loaded', function () {

    load_plugin_textdomain('dl-ticket-manager-log', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new TMLogManagementPlugin();
    $plugin->init();
});
