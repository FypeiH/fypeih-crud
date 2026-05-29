<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ----------------------------------------------------------------
   Resolve active tab, active table, messages
---------------------------------------------------------------- */
$gig_tab   = sanitize_key( $_GET['gig_tab']   ?? 'tables' );
$gig_table = sanitize_key( $_GET['gig_table'] ?? '' );
$gig_msg   = sanitize_key( $_GET['gig_msg']   ?? '' );
$all_tables = gig_crud_get_all_meta_tables();
$uid        = get_current_user_id();

/* Active table meta */
$active_meta   = $gig_table ? gig_crud_get_meta_table( $gig_table ) : null;
$active_schema = $active_meta ? json_decode( $active_meta->schema_json, true ) : [];

/* Edit record */
$edit_record = null;
if ( $gig_tab === 'records' && isset( $_GET['gig_edit_record'] ) && $gig_table ) {
    $edit_record = gig_crud_get_record( $gig_table, absint( $_GET['gig_edit_record'] ) );
}

/* Edit table schema */
$edit_table_key = sanitize_key( $_GET['gig_edit_table'] ?? '' );
$edit_table_meta = $edit_table_key ? gig_crud_get_meta_table( $edit_table_key ) : null;

/* SQL results */
$sql_result = get_transient( 'gig_crud_sql_result_' . $uid );
$sql_last   = get_transient( 'gig_crud_sql_last_'   . $uid );
if ( $gig_tab === 'sql' ) {
    delete_transient( 'gig_crud_sql_result_' . $uid );
    delete_transient( 'gig_crud_sql_last_'   . $uid );
}

/* Nav shortcuts */
$base_url = admin_url( 'admin.php?page=' . GIGANTIC_CRUD_SLUG );

function gig_tab_url( $tab, $extra = [] ) {
    global $base_url;
    return add_query_arg( array_merge( [ 'gig_tab' => $tab ], $extra ), $base_url );
}

/* Messages map */
$messages = [
    'table_saved'   => [ 'success', 'Tabela guardada com sucesso.' ],
    'table_deleted' => [ 'success', 'Tabela eliminada com sucesso.' ],
    'bulk_deleted'  => [ 'success', 'Seleção eliminada com sucesso.' ],
    'record_saved'  => [ 'success', 'Registo criado com sucesso.' ],
    'record_updated'=> [ 'success', 'Registo atualizado com sucesso.' ],
    'record_deleted'=> [ 'success', 'Registo eliminado com sucesso.' ],
    'sql_run'       => [ 'success', 'Query executada com sucesso.' ],
];
?>