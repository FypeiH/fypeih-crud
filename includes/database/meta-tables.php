<?php 

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

?>