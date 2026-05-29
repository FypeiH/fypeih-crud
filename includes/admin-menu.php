<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function gig_crud_register_admin_menu() {
    add_menu_page(
        'Gigantic CRUD',
        'Gigantic CRUD',
        'manage_options',
        GIGANTIC_CRUD_SLUG,
        'gig_crud_render_admin_page',
        'dashicons-database',
        26
    );
}

add_action( 'admin_menu', 'gig_crud_register_admin_menu' );