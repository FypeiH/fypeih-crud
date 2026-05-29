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

function gig_crud_disable_wp_router() {
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, GIGANTIC_CRUD_SLUG ) === false ) {
        return;
    }

    wp_add_inline_script( 'wp-hooks',
        'window.__experimentalEnableExperimentalRouter = false;',
        'before'
    );

    wp_add_inline_script( 'jquery-core',
        '(function(){' .
            // Intercept na capture phase — corre antes de qualquer listener do WP
            'document.addEventListener("click", function(e){' .
                'var a = e.target.closest("a[href]");' .
                'if (!a) return;' .
                'var href = a.getAttribute("href") || "";' .
                // Detecta qualquer link nosso: tem gig_tab= OU page=gigantic-crud-manager
                'if (href.indexOf("gig_tab=") !== -1 || href.indexOf("page=' . GIGANTIC_CRUD_SLUG . '") !== -1) {' .
                    'e.preventDefault();' .
                    'e.stopImmediatePropagation();' .
                    // Garante que o page= está sempre presente
                    'if (href.indexOf("page=") === -1) {' .
                        'href = href + (href.indexOf("?") !== -1 ? "&" : "?") + "page=' . GIGANTIC_CRUD_SLUG . '";' .
                    '}' .
                    'window.location.href = href;' .
                '}' .
            '}, true);' .
        '})();',
        'after'
    );
}
add_action( 'admin_enqueue_scripts', 'gig_crud_disable_wp_router' );