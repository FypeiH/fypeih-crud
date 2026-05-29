<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =====================================================================
   METADATA TABLE — stores user-created table definitions
===================================================================== */

function gig_crud_meta_table() {
    global $wpdb;
    return $wpdb->prefix . 'gig_crud_meta';
}

function gig_crud_create_meta_table() {
    global $wpdb;
    $t  = gig_crud_meta_table();
    $ch = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $t (
        id         mediumint(9)  NOT NULL AUTO_INCREMENT,
        table_key  varchar(64)   NOT NULL UNIQUE,
        table_label varchar(120) NOT NULL,
        schema_json longtext     NOT NULL,
        created_at  datetime     DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $ch;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

/* =====================================================================
   TABLE MANAGEMENT
===================================================================== */

function gig_crud_get_all_meta_tables() {
    global $wpdb;
    return $wpdb->get_results( 'SELECT * FROM ' . gig_crud_meta_table() . ' ORDER BY created_at ASC' );
}

function gig_crud_get_meta_table( $table_key ) {
    global $wpdb;
    $t = gig_crud_meta_table();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE table_key = %s", $table_key ) );
}

function gig_crud_save_meta_table( $table_key, $table_label, $schema_json ) {
    global $wpdb;
    $t = gig_crud_meta_table();
    $existing = gig_crud_get_meta_table( $table_key );
    if ( $existing ) {
        return $wpdb->update( $t,
            [ 'table_label' => $table_label, 'schema_json' => $schema_json ],
            [ 'table_key'   => $table_key ],
            [ '%s', '%s' ], [ '%s' ]
        );
    }
    return $wpdb->insert( $t,
        [ 'table_key' => $table_key, 'table_label' => $table_label, 'schema_json' => $schema_json ],
        [ '%s', '%s', '%s' ]
    );
}

function gig_crud_delete_meta_table( $table_key ) {
    global $wpdb;
    $meta = gig_crud_get_meta_table( $table_key );
    if ( ! $meta ) return false;
    $real = $wpdb->prefix . sanitize_key( $table_key );
    $wpdb->query( "DROP TABLE IF EXISTS `$real`" );
    return $wpdb->delete( gig_crud_meta_table(), [ 'table_key' => $table_key ], [ '%s' ] );
}

/* Build and (re)create the real MySQL table from schema JSON */
function gig_crud_sync_real_table( $table_key, $schema_json ) {
    global $wpdb;
    $fields = json_decode( $schema_json, true );
    if ( ! is_array( $fields ) || empty( $fields ) ) return false;

    $real = $wpdb->prefix . sanitize_key( $table_key );
    $ch   = $wpdb->get_charset_collate();
    $cols = [];
    $pk   = [];

    foreach ( $fields as $f ) {
        $name = '`' . esc_sql( sanitize_key( $f['name'] ) ) . '`';
        $type = strtoupper( $f['type'] ?? 'VARCHAR' );
        $size = intval( $f['size'] ?? 0 );

        $col = $name . ' ';
        switch ( $type ) {
            case 'VARCHAR':  $col .= 'varchar(' . ( $size ?: 191 ) . ')'; break;
            case 'TEXT':     $col .= 'text'; break;
            case 'LONGTEXT': $col .= 'longtext'; break;
            case 'INT':      $col .= 'int(' . ( $size ?: 11 ) . ')'; break;
            case 'MEDIUMINT':$col .= 'mediumint(9)'; break;
            case 'BIGINT':   $col .= 'bigint(20)'; break;
            case 'TINYINT':  $col .= 'tinyint(1)'; break;
            case 'DECIMAL':  $col .= 'decimal(' . ( $size ?: 10 ) . ',2)'; break;
            case 'FLOAT':    $col .= 'float'; break;
            case 'DATETIME': $col .= 'datetime'; break;
            case 'DATE':     $col .= 'date'; break;
            case 'BOOLEAN':  $col .= 'tinyint(1)'; break;
            default:         $col .= 'varchar(191)';
        }

        if ( ! empty( $f['auto_increment'] ) ) $col .= ' NOT NULL AUTO_INCREMENT';
        elseif ( ! empty( $f['required'] ) )   $col .= ' NOT NULL';
        else                                    $col .= " DEFAULT ''";

        if ( ! empty( $f['primary_key'] ) ) $pk[] = sanitize_key( $f['name'] );

        $cols[] = $col;
    }

    if ( ! empty( $pk ) ) {
        $cols[] = 'PRIMARY KEY (' . implode( ', ', array_map( fn($k) => "`$k`", $pk ) ) . ')';
    }

    $sql = "CREATE TABLE $real (\n  " . implode( ",\n  ", $cols ) . "\n) $ch;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    return dbDelta( $sql );
}

/* =====================================================================
   RECORDS CRUD (dynamic table)
===================================================================== */

function gig_crud_real_table_name( $table_key ) {
    global $wpdb;
    return $wpdb->prefix . sanitize_key( $table_key );
}

function gig_crud_get_records( $table_key ) {
    global $wpdb;
    $t = gig_crud_real_table_name( $table_key );
    return $wpdb->get_results( "SELECT * FROM `$t` ORDER BY 1 DESC" );
}

function gig_crud_get_record( $table_key, $id ) {
    global $wpdb;
    $t = gig_crud_real_table_name( $table_key );
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$t` WHERE id = %d", absint( $id ) ) );
}

function gig_crud_insert_record( $table_key, $data ) {
    global $wpdb;
    $t = gig_crud_real_table_name( $table_key );
    return $wpdb->insert( $t, $data );
}

function gig_crud_update_record( $table_key, $id, $data ) {
    global $wpdb;
    $t = gig_crud_real_table_name( $table_key );
    return $wpdb->update( $t, $data, [ 'id' => absint( $id ) ] );
}

function gig_crud_delete_record( $table_key, $id ) {
    global $wpdb;
    $t = gig_crud_real_table_name( $table_key );
    return $wpdb->delete( $t, [ 'id' => absint( $id ) ], [ '%d' ] );
}

function gig_crud_delete_records_bulk( $table_key, $ids ) {
    global $wpdb;
    $t    = gig_crud_real_table_name( $table_key );
    $safe = implode( ',', array_map( 'absint', $ids ) );
    return $wpdb->query( "DELETE FROM `$t` WHERE id IN ($safe)" );
}

/* =====================================================================
   SQL RUNNER
===================================================================== */

function gig_crud_run_sql( $sql ) {
    global $wpdb;
    $wpdb->show_errors();
    $sql_trim = trim( $sql );
    $upper    = strtoupper( $sql_trim );

    if ( str_starts_with( $upper, 'SELECT' ) || str_starts_with( $upper, 'SHOW' ) || str_starts_with( $upper, 'DESCRIBE' ) || str_starts_with( $upper, 'EXPLAIN' ) ) {
        $rows = $wpdb->get_results( $sql_trim, ARRAY_A );
        return [ 'type' => 'select', 'rows' => $rows, 'error' => $wpdb->last_error ];
    }

    $result = $wpdb->query( $sql_trim );
    return [
        'type'    => 'exec',
        'affected'=> $result,
        'error'   => $wpdb->last_error,
    ];
}

/* =====================================================================
   ACTIVATION
===================================================================== */

function gig_crud_create_table() {
    gig_crud_create_meta_table();
}
