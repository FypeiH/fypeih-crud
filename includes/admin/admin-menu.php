<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fyp_crud_register_admin_menu() {
    add_menu_page(
        'Fypeih CRUD',
        'Fypeih CRUD',
        'manage_options',
        FYPEIH_CRUD_SLUG,
        'fyp_crud_render_admin_page',
        'dashicons-database',
        26
    );
}
add_action( 'admin_menu', 'fyp_crud_register_admin_menu' );

function fyp_crud_disable_wp_router() {
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, FYPEIH_CRUD_SLUG ) === false ) {
        return;
    }

    wp_add_inline_script( 'wp-hooks',
        'window.__experimentalEnableExperimentalRouter = false;',
        'before'
    );

    wp_add_inline_script( 'jquery-core',
        '(function(){' .
            // Intercepta na capture phase — corre antes de qualquer listener do WP
            'document.addEventListener("click", function(e){' .
                'var a = e.target.closest("a[href]");' .
                'if (!a) return;' .
                'var href = a.getAttribute("href") || "";' .
                // Detecta qualquer link nosso: tem fyp_tab= OU page=fypeih-crud-manager
                'if (href.indexOf("fyp_tab=") !== -1 || href.indexOf("page=' . FYPEIH_CRUD_SLUG . '") !== -1) {' .
                    'e.preventDefault();' .
                    'e.stopImmediatePropagation();' .
                    // Garante que o page= está sempre presente
                    'if (href.indexOf("page=") === -1) {' .
                        'href = href + (href.indexOf("?") !== -1 ? "&" : "?") + "page=' . FYPEIH_CRUD_SLUG . '";' .
                    '}' .
                    'window.location.href = href;' .
                '}' .
            '}, true);' .
        '})();',
        'after'
    );
}
add_action( 'admin_enqueue_scripts', 'fyp_crud_disable_wp_router' );