<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ----------------------------------------------------------------
   Resolve active tab, active table, messages
---------------------------------------------------------------- */
$fyp_tab   = sanitize_key( $_GET['fyp_tab']   ?? 'tables' );
$fyp_table = sanitize_key( $_GET['fyp_table'] ?? '' );
$fyp_msg   = sanitize_key( $_GET['fyp_msg']   ?? '' );
$all_tables = fyp_crud_get_all_meta_tables();
$uid        = get_current_user_id();

/* Active table meta */
/* Active table meta */
$active_meta    = $fyp_table ? fyp_crud_get_meta_table( $fyp_table ) : null;
$active_managed = (bool) $active_meta; // true = gerida pelo Fypeih, false = externa

if ( $active_meta ) {
    // Tabela Fypeih — schema vem do nosso meta
    $active_schema = json_decode( $active_meta->schema_json, true ) ?: [];
} elseif ( $fyp_table ) {
    // Tabela externa — lê schema via DESCRIBE
    global $wpdb;
    $active_schema = fyp_crud_get_external_columns( $wpdb->prefix . $fyp_table );
    // Simula um $active_meta mínimo para o template não ficar em branco
    $active_meta              = new stdClass();
    $active_meta->table_key   = $fyp_table;
    $active_meta->table_label = fyp_crud_humanize( $fyp_table );
    $active_meta->schema_json = json_encode( $active_schema );
} else {
    $active_schema = [];
}

/* Edit record */
$edit_record = null;
if ( $fyp_tab === 'records' && isset( $_GET['fyp_edit_record'] ) && $fyp_table ) {
    $edit_record = fyp_crud_get_record( $fyp_table, absint( $_GET['fyp_edit_record'] ) );
}

/* Edit table schema */
$edit_table_key = sanitize_key( $_GET['fyp_edit_table'] ?? '' );
$edit_table_meta = $edit_table_key ? fyp_crud_get_meta_table( $edit_table_key ) : null;

/* SQL results */
$sql_result = get_transient( 'fyp_crud_sql_result_' . $uid );
$sql_last   = get_transient( 'fyp_crud_sql_last_'   . $uid );
if ( $fyp_tab === 'sql' ) {
    delete_transient( 'fyp_crud_sql_result_' . $uid );
    delete_transient( 'fyp_crud_sql_last_'   . $uid );
}

// Substitui $all_tables por $all_db_tables em todo o template
$all_db_tables = fyp_crud_get_all_db_tables();
// Mantém $all_tables para compatibilidade com o modal JS
$all_tables = array_filter( $all_db_tables, fn($t) => $t->managed );
$all_tables = array_values( $all_tables );

/* Nav shortcuts */
$base_url = admin_url( 'admin.php?page=' . FYPEIH_CRUD_SLUG );

function fyp_tab_url( $tab, $extra = [] ) {
    global $base_url;
    return add_query_arg( array_merge( [ 'fyp_tab' => $tab ], $extra ), $base_url );
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


/**
 * Sidebar — lista tabelas Fypeih + externas agrupadas por origem.
 * Usa $all_db_tables injetada pelo admin-page.php (ver abaixo).
 */

// Agrupa por source
$sidebar_groups = [];
foreach ( $all_db_tables as $tbl ) {
    $sidebar_groups[ $tbl->source ][] = $tbl;
}

// Ordem de apresentação
$source_order = [ 'fypeih', 'jetengine', 'woocommerce', 'gravityforms', 'acf', 'buddypress', 'learndash', 'rankmath', 'yoast', 'wpcore', 'other' ];
uksort( $sidebar_groups, function( $a, $b ) use ( $source_order ) {
    $ai = array_search( $a, $source_order );
    $bi = array_search( $b, $source_order );
    return ( $ai === false ? 99 : $ai ) <=> ( $bi === false ? 99 : $bi );
});

/**
 * Tab Tabelas — mostra todas as tabelas da DB agrupadas por origem.
 * Tabelas Fypeih têm ações completas. Externas têm apenas "Ver" e "Importar".
 */

// Agrupa por source para a tab
$tab_groups = [];
foreach ( $all_db_tables as $tbl ) {
    $tab_groups[ $tbl->source ][] = $tbl;
}

$source_order = [ 'fypeih', 'jetengine', 'woocommerce', 'gravityforms', 'acf', 'buddypress', 'learndash', 'rankmath', 'yoast', 'other', 'wpcore' ];
uksort( $tab_groups, function( $a, $b ) use ( $source_order ) {
    $ai = array_search( $a, $source_order );
    $bi = array_search( $b, $source_order );
    return ( $ai === false ? 99 : $ai ) <=> ( $bi === false ? 99 : $bi );
});

$source_badge_class = [
    'fypeih'    => 'fyp-badge-green',
    'jetengine'   => 'fyp-badge-orange',
    'woocommerce' => 'fyp-badge-purple',
    'wpcore'      => 'fyp-badge-blue',
    'other'       => 'fyp-badge-grey',
];
?>