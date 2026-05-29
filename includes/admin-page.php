<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function gig_crud_admin_assets( $hook ) {
    if ( false === strpos( $hook, GIGANTIC_CRUD_SLUG ) ) {
        return;
    }
    wp_enqueue_style( 'gig-crud-admin', GIGANTIC_CRUD_URL . 'assets/admin.css', [], GIGANTIC_CRUD_VERSION );
}
add_action( 'admin_enqueue_scripts', 'gig_crud_admin_assets' );

/* =====================================================================
   ACTION DISPATCHER
===================================================================== */
function gig_crud_handle_actions() {
    /* ---- CREATE / EDIT TABLE ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'save_table' ) {
        check_admin_referer( 'gig_crud_save_table' );
        $key   = sanitize_key( $_POST['table_key'] ?? '' );
        $label = sanitize_text_field( $_POST['table_label'] ?? '' );
        $schema= wp_unslash( $_POST['schema_json'] ?? '[]' );

        if ( $key && $label ) {
            gig_crud_save_meta_table( $key, $label, $schema );
            gig_crud_sync_real_table( $key, $schema );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'tables',
                    'gig_msg' => 'table_saved'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- DELETE TABLE ---- */
    if ( isset( $_GET['gig_delete_table'] ) ) {
        check_admin_referer( 'gig_crud_delete_table' );
        gig_crud_delete_meta_table( sanitize_key( $_GET['gig_delete_table'] ) );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'tables',
                    'gig_msg' => 'table_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );
        
        exit;
    }

    /* ---- BULK DELETE TABLES ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'bulk_delete_tables' ) {
        check_admin_referer( 'gig_crud_bulk_tables' );
        foreach ( (array) ( $_POST['table_keys'] ?? [] ) as $k ) {
            gig_crud_delete_meta_table( sanitize_key( $k ) );
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'tables',
                    'gig_msg' => 'bulk_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- INSERT RECORD ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'insert_record' ) {
        check_admin_referer( 'gig_crud_save_record' );
        $tk   = sanitize_key( $_POST['table_key'] ?? '' );
        $data = array_map( 'sanitize_text_field', (array)( $_POST['rec'] ?? [] ) );
        if ( $tk ) gig_crud_insert_record( $tk, $data );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'records',
                    'gig_table' => $tk,
                    'gig_msg' => 'record_saved'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- UPDATE RECORD ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'update_record' ) {
        check_admin_referer( 'gig_crud_save_record' );
        $tk  = sanitize_key( $_POST['table_key'] ?? '' );
        $id  = absint( $_POST['rec_id'] ?? 0 );
        $data= array_map( 'sanitize_text_field', (array)( $_POST['rec'] ?? [] ) );
        if ( $tk && $id ) gig_crud_update_record( $tk, $id, $data );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'records',
                    'gig_table' => $tk,
                    'gig_msg' => 'record_updated'
                ],
                admin_url( 'admin.php' )
            )
        );
        
        exit;
    }

    /* ---- DELETE RECORD ---- */
    if ( isset( $_GET['gig_delete_record'] ) ) {
        check_admin_referer( 'gig_crud_delete_record' );
        $tk = sanitize_key( $_GET['gig_table'] ?? '' );
        gig_crud_delete_record( $tk, absint( $_GET['gig_delete_record'] ) );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'records',
                    'gig_table' => $tk,
                    'gig_msg' => 'record_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- BULK DELETE RECORDS ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'bulk_delete_records' ) {
        check_admin_referer( 'gig_crud_bulk_records' );
        $tk  = sanitize_key( $_POST['table_key'] ?? '' );
        $ids = array_map( 'absint', (array)( $_POST['rec_ids'] ?? [] ) );
        if ( $tk && ! empty( $ids ) ) gig_crud_delete_records_bulk( $tk, $ids );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'records',
                    'gig_table' => $tk,
                    'gig_msg' => 'bulk_deleted'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }

    /* ---- RUN SQL ---- */
    if ( isset( $_POST['gig_action'] ) && $_POST['gig_action'] === 'run_sql' ) {
        check_admin_referer( 'gig_crud_run_sql' );
        $sql = wp_unslash( $_POST['sql_query'] ?? '' );
        // stored in transient so template can read it
        set_transient( 'gig_crud_sql_result_' . get_current_user_id(), gig_crud_run_sql( $sql ), 30 );
        set_transient( 'gig_crud_sql_last_'   . get_current_user_id(), $sql, 30 );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => GIGANTIC_CRUD_SLUG,
                    'gig_tab' => 'sql',
                    'gig_msg' => 'sql_run'
                ],
                admin_url( 'admin.php' )
            )
        );

        exit;
    }
}

add_action( 'admin_init', 'gig_crud_handle_actions' );

/* =====================================================================
   RENDER
===================================================================== */
function gig_crud_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    require GIGANTIC_CRUD_PATH . 'templates/admin-page.php';
}
