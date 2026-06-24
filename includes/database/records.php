<?php
/* =====================================================================
   RECORDS CRUD (dynamic table)
===================================================================== */

function fyp_crud_real_table_name( $table_key ) {
    global $wpdb;
    return $wpdb->prefix . sanitize_key( $table_key );
}

function fyp_crud_get_records( $table_key ) {
    global $wpdb;
    $t = fyp_crud_real_table_name( $table_key );
    return $wpdb->get_results( "SELECT * FROM `$t` ORDER BY 1 DESC" );
}

function fyp_crud_get_record( $table_key, $id ) {
    global $wpdb;
    $t = fyp_crud_real_table_name( $table_key );
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$t` WHERE id = %d", absint( $id ) ) );
}

function fyp_crud_insert_record( $table_key, $data ) {
    global $wpdb;
    $t = fyp_crud_real_table_name( $table_key );
    return $wpdb->insert( $t, $data );
}

function fyp_crud_update_record( $table_key, $id, $data ) {
    global $wpdb;
    $t = fyp_crud_real_table_name( $table_key );
    return $wpdb->update( $t, $data, [ 'id' => absint( $id ) ] );
}

function fyp_crud_delete_record( $table_key, $id ) {
    global $wpdb;
    $t = fyp_crud_real_table_name( $table_key );
    return $wpdb->delete( $t, [ 'id' => absint( $id ) ], [ '%d' ] );
}

function fyp_crud_delete_records_bulk( $table_key, $ids ) {
    global $wpdb;
    $t    = fyp_crud_real_table_name( $table_key );
    $safe = implode( ',', array_map( 'absint', $ids ) );
    return $wpdb->query( "DELETE FROM `$t` WHERE id IN ($safe)" );
}
?>