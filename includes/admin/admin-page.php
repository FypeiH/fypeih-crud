<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fyp_crud_admin_assets( $hook ) {
    if ( false === strpos( $hook, FYPEIH_CRUD_SLUG ) ) {
        return;
    }

    wp_enqueue_style(
        'fyp-crud-admin',
        FYPEIH_CRUD_URL . 'assets/admin.css',
        [],
        FYPEIH_CRUD_VERSION
    );

    wp_enqueue_media();

    wp_enqueue_script(
        'fyp-crud-admin',
        FYPEIH_CRUD_URL . 'assets/admin.js',
        [ 'jquery', 'media-upload' ],
        FYPEIH_CRUD_VERSION,
        true
    );

    wp_localize_script(
        'fyp-crud-admin',
        'GigCrudAdmin',
        [
            'tables' => array_map(
                function ( $t ) {
                    return [
                        'key'    => $t->table_key,
                        'label'  => $t->table_label,
                        'schema' => json_decode( $t->schema_json, true ),
                    ];
                },
                fyp_crud_get_all_meta_tables()
            ),
        ]
    );
}

add_action( 'admin_enqueue_scripts', 'fyp_crud_admin_assets' );

/* =====================================================================
   ACTION DISPATCHER
===================================================================== */
function fyp_crud_handle_actions() {
    /* ---- CREATE / EDIT TABLE ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'save_table' ) {
        check_admin_referer( 'fyp_crud_save_table' );
        $key   = sanitize_key( $_POST['table_key'] ?? '' );
        $label = sanitize_text_field( $_POST['table_label'] ?? '' );
        $schema= wp_unslash( $_POST['schema_json'] ?? '[]' );

        if ( $key && $label ) {
            fyp_crud_save_meta_table( $key, $label, $schema );
            fyp_crud_sync_real_table( $key, $schema );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'tables',
                    'fyp_msg' => 'table_saved'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- DELETE TABLE ---- */
    if ( isset( $_GET['fyp_delete_table'] ) ) {
        check_admin_referer( 'fyp_crud_delete_table' );
        fyp_crud_delete_meta_table( sanitize_key( $_GET['fyp_delete_table'] ) );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'tables',
                    'fyp_msg' => 'table_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );
        
        exit;
    }

    /* ---- BULK DELETE TABLES ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'bulk_delete_tables' ) {
        check_admin_referer( 'fyp_crud_bulk_tables' );
        foreach ( (array) ( $_POST['table_keys'] ?? [] ) as $k ) {
            fyp_crud_delete_meta_table( sanitize_key( $k ) );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'tables',
                    'fyp_msg' => 'bulk_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- INSERT RECORD ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'insert_record' ) {
        check_admin_referer( 'fyp_crud_save_record' );
        $tk   = sanitize_key( $_POST['table_key'] ?? '' );
        $data = array_map( 'sanitize_text_field', (array)( $_POST['rec'] ?? [] ) );
        if ( $tk ) fyp_crud_insert_record( $tk, $data );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'records',
                    'fyp_table' => $tk,
                    'fyp_msg' => 'record_saved'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- UPDATE RECORD ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'update_record' ) {
        check_admin_referer( 'fyp_crud_save_record' );
        $tk  = sanitize_key( $_POST['table_key'] ?? '' );
        $id  = absint( $_POST['rec_id'] ?? 0 );
        $data= array_map( 'sanitize_text_field', (array)( $_POST['rec'] ?? [] ) );
        if ( $tk && $id ) fyp_crud_update_record( $tk, $id, $data );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'records',
                    'fyp_table' => $tk,
                    'fyp_msg' => 'record_updated'
                ],
                admin_url( 'admin.php' )
            )
        );
        
        exit;
    }

    /* ---- DELETE RECORD ---- */
    if ( isset( $_GET['fyp_delete_record'] ) ) {
        check_admin_referer( 'fyp_crud_delete_record' );
        $tk = sanitize_key( $_GET['fyp_table'] ?? '' );
        fyp_crud_delete_record( $tk, absint( $_GET['fyp_delete_record'] ) );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'records',
                    'fyp_table' => $tk,
                    'fyp_msg' => 'record_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- BULK DELETE RECORDS ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'bulk_delete_records' ) {
        check_admin_referer( 'fyp_crud_bulk_records' );
        $tk  = sanitize_key( $_POST['table_key'] ?? '' );
        $ids = array_map( 'absint', (array)( $_POST['rec_ids'] ?? [] ) );
        if ( $tk && ! empty( $ids ) ) fyp_crud_delete_records_bulk( $tk, $ids );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'records',
                    'fyp_table' => $tk,
                    'fyp_msg' => 'bulk_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- RUN SQL ---- */
    if ( isset( $_POST['fyp_action'] ) && $_POST['fyp_action'] === 'run_sql' ) {
        check_admin_referer( 'fyp_crud_run_sql' );
        $sql = wp_unslash( $_POST['sql_query'] ?? '' );
        // stored in transient so template can read it
        set_transient( 'fyp_crud_sql_result_' . get_current_user_id(), fyp_crud_run_sql( $sql ), 30 );
        set_transient( 'fyp_crud_sql_last_'   . get_current_user_id(), $sql, 30 );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => FYPEIH_CRUD_SLUG,
                    'fyp_tab' => 'sql',
                    'fyp_msg' => 'sql_run'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }
}

add_action( 'admin_init', 'fyp_crud_handle_actions' );

/* =====================================================================
   RENDER
===================================================================== */
function fyp_crud_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    require FYPEIH_CRUD_PATH . 'templates/admin-page.php';
}
