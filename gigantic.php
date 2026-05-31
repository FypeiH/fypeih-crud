<?php
/**
 * Plugin Name:       Gigantic CRUD Manager
 * Description:       Plugin interno para gerir tabelas e registos personalizados na base de dados.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Gigantic Digital Growth (Filipe Bravo)
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gigantic-crud-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GIGANTIC_CRUD_VERSION', '0.2.0' );
define( 'GIGANTIC_CRUD_PATH', plugin_dir_path( __FILE__ ) );
define( 'GIGANTIC_CRUD_URL', plugin_dir_url( __FILE__ ) );
define( 'GIGANTIC_CRUD_SLUG', 'gigantic-crud-manager' );

require_once GIGANTIC_CRUD_PATH . 'includes/database/database.php';
require_once GIGANTIC_CRUD_PATH . 'includes/admin/admin-menu.php';
require_once GIGANTIC_CRUD_PATH . 'includes/admin/admin-page.php';
require_once GIGANTIC_CRUD_PATH . 'includes/admin/field-renderer.php';
require_once GIGANTIC_CRUD_PATH . 'includes/elementor/elementor.php';

register_activation_hook( __FILE__, 'gig_crud_create_table' );
