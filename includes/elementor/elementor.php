<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! did_action( 'elementor/loaded' ) ) {
    return;
}

add_action(
    'elementor/dynamic_tags/register',
    'fyp_crud_register_dynamic_tags'
);

function fyp_crud_register_dynamic_tags( $dynamic_tags_manager ) {

    require_once FYPEIH_CRUD_PATH . 'includes/elementor/dynamic-tags/field-tag.php';
    require_once FYPEIH_CRUD_PATH . 'includes/elementor/dynamic-tags/url-tag.php';
    require_once FYPEIH_CRUD_PATH . 'includes/elementor/dynamic-tags/image-tag.php';

    \Elementor\Plugin::$instance->dynamic_tags->register_group(
        'fypeih-crud',
        [ 'title' => 'Fypeih CRUD' ]
    );

    $dynamic_tags_manager->register( new Fypeih_CRUD_Field_Tag() );
    $dynamic_tags_manager->register( new Fypeih_CRUD_URL_Tag() );
    $dynamic_tags_manager->register( new Fypeih_CRUD_Image_Tag() );
}