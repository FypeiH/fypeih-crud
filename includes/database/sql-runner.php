<?php
/* =====================================================================
   SQL RUNNER
===================================================================== */

function fyp_crud_run_sql( $sql ) {
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
?>