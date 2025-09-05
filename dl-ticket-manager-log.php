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

use DL\TicketsLog\Plugin;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

add_action('plugins_loaded', function () {

    load_plugin_textdomain('dl-ticket-manager-log', false, dirname(plugin_basename(__FILE__)) . '/languages');

    (new Plugin())->init();
});
