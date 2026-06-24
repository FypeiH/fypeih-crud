<?php
/**
 * Plugin Name:       FypeiH CRUD Manager
 * Description:       Plugin interno para gerir tabelas e registos personalizados na base de dados.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Filipe Bravo
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fypeih-crud-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FYPEIH_CRUD_VERSION', '0.2.0' );
define( 'FYPEIH_CRUD_PATH', plugin_dir_path( __FILE__ ) );
define( 'FYPEIH_CRUD_URL', plugin_dir_url( __FILE__ ) );
define( 'FYPEIH_CRUD_SLUG', 'fypeih-crud-manager' );

require_once FYPEIH_CRUD_PATH . 'includes/database/database.php';
require_once FYPEIH_CRUD_PATH . 'includes/admin/admin-menu.php';
require_once FYPEIH_CRUD_PATH . 'includes/admin/admin-page.php';
require_once FYPEIH_CRUD_PATH . 'includes/admin/field-renderer.php';
require_once FYPEIH_CRUD_PATH . 'includes/elementor/elementor.php';

register_activation_hook( __FILE__, 'fyp_crud_create_table' );
