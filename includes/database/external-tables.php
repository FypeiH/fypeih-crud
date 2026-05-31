<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Devolve tabelas relevantes para o frontend:
 *   1. Tabelas geridas pelo Gigantic CRUD
 *   2. Tabelas da DB que correspondem a CPTs criados pelo utilizador
 *      (JetEngine CCTs, CPTs custom, etc.)
 *
 * Cada item é stdClass com:
 *   ->table_key    string  — nome sem prefixo
 *   ->table_name   string  — nome real na DB
 *   ->table_label  string  — label legível
 *   ->source       string  — 'gigantic' | 'jetengine' | 'cpt'
 *   ->source_label string  — nome legível da origem
 *   ->schema_json  string  — JSON do schema (só tabelas Gigantic)
 *   ->row_count    int     — número de registos
 *   ->managed      bool    — true se gerida pelo Gigantic CRUD
 */
function gig_crud_get_all_db_tables(): array {
    global $wpdb;

    $prefix  = $wpdb->prefix;
    $results = [];

    // ----------------------------------------------------------------
    // 1. Tabelas Gigantic
    // ----------------------------------------------------------------
    foreach ( gig_crud_get_all_meta_tables() as $t ) {
        $table_name = $prefix . $t->table_key;

        $item                = new stdClass();
        $item->table_name    = $table_name;
        $item->table_key     = $t->table_key;
        $item->table_label   = $t->table_label;
        $item->schema_json   = $t->schema_json;
        $item->source        = 'gigantic';
        $item->source_label  = 'Gigantic CRUD';
        $item->managed       = true;
        $item->created_at    = $t->created_at;
        $item->row_count     = gig_crud_row_count( $table_name );

        $results[ $table_name ] = $item;
    }

    // ----------------------------------------------------------------
    // 2. CPTs criados pelo utilizador → procura tabelas correspondentes
    // ----------------------------------------------------------------
    $user_cpts = gig_crud_get_user_post_types();

    // Todas as tabelas da DB (só nomes, para cruzar)
    $all_db_table_names = $wpdb->get_col( $wpdb->prepare(
        'SELECT TABLE_NAME FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = %s AND TABLE_NAME LIKE %s',
        DB_NAME,
        $wpdb->esc_like( $prefix ) . '%'
    ) );

    foreach ( $user_cpts as $cpt_slug => $cpt_label ) {
        // Padrões de tabelas que o JetEngine e outros plugins criam para este CPT
        $candidates = [
            $prefix . $cpt_slug . '_meta',       // pgk_clinicas_meta  (JetEngine meta storage)
            $prefix . $cpt_slug,                 // pgk_clinicas
            $prefix . 'jet_cct_' . $cpt_slug,    // pgk_jet_cct_clinicas  (JetEngine CCT)
            $prefix . $cpt_slug . '_data',        // pgk_clinicas_data
        ];

        foreach ( $candidates as $candidate ) {
            // Já está listada (Gigantic) ou não existe na DB → salta
            if ( isset( $results[ $candidate ] ) ) continue;
            if ( ! in_array( $candidate, $all_db_table_names, true ) ) continue;

            $table_key = substr( $candidate, strlen( $prefix ) );

            // Detecta origem pelo padrão do nome
            if ( str_contains( $table_key, 'jet_cct_' ) ) {
                $source       = 'jetengine';
                $source_label = 'JetEngine CCT';
            } elseif ( str_ends_with( $table_key, '_meta' ) ) {
                $source       = 'jetengine';
                $source_label = 'JetEngine';
            } else {
                $source       = 'cpt';
                $source_label = 'Custom Post Type';
            }

            $item               = new stdClass();
            $item->table_name   = $candidate;
            $item->table_key    = $table_key;
            $item->table_label  = $cpt_label;
            $item->schema_json  = '[]';
            $item->source       = $source;
            $item->source_label = $source_label;
            $item->managed      = false;
            $item->created_at   = null;
            $item->row_count    = gig_crud_row_count( $candidate );

            $results[ $candidate ] = $item;
        }
    }

    return array_values( $results );
}

/**
 * Devolve os CPTs criados pelo utilizador — exclui os built-in do WP
 * e os CPTs internos de plugins (nav_menu_item, revision, etc.).
 */
function gig_crud_get_user_post_types(): array {
    // CPTs built-in do WordPress core e de plugins a ignorar
    $exclude = [
        // WP core
        'post', 'page', 'attachment', 'revision',
        'nav_menu_item', 'custom_css', 'customize_changeset',
        'oembed_cache', 'user_request', 'wp_block',
        'wp_template', 'wp_template_part', 'wp_global_styles',
        'wp_navigation', 'wp_font_face', 'wp_font_family',
        // Elementor
        'elementor_library', 'elementor-hf',
        // JetEngine interno
        'jet-engine', 'jet_popup', 'jet-smart-filter',
        'jet-woo-builder', 'jet-menu',
        // WooCommerce
        'product', 'product_variation', 'shop_order',
        'shop_order_refund', 'shop_coupon', 'shop_webhook',
        // Outros plugins comuns
        'acf-field-group', 'acf-field',
        'edd_download', 'edd_payment', 'edd_discount',
        'mc4wp-form', 'neve_custom_layouts',
        'brizy_template', 'e-landing-page',
        'frm_display', 'frm_form_actions',
    ];

    $all_cpts = get_post_types( [], 'objects' );

    $user_cpts = [];
    foreach ( $all_cpts as $slug => $obj ) {
        if ( in_array( $slug, $exclude, true ) ) continue;

        // Ignora CPTs com nomes que claramente são de plugins (contêm hífens de namespace)
        // mas mantém os criados pelo utilizador (geralmente sem hífen ou com slug simples)
        $label = $obj->label ?: gig_crud_humanize( $slug );
        $user_cpts[ $slug ] = $label;
    }

    return $user_cpts;
}

/**
 * Conta registos de uma tabela via information_schema (sem SELECT COUNT).
 * Nota: TABLE_ROWS é uma estimativa para InnoDB; para MyISAM é exato.
 */
function gig_crud_row_count( string $table_name ): int {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        'SELECT TABLE_ROWS FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s',
        DB_NAME,
        $table_name
    ) );
}

/**
 * Colunas reais de uma tabela via DESCRIBE — usado para tabelas externas.
 */
function gig_crud_get_external_columns( string $table_name ): array {
    global $wpdb;
    $cols = $wpdb->get_results( "DESCRIBE `$table_name`" );
    if ( ! $cols ) return [];
    return array_map( fn( $c ) => [
        'name'           => $c->Field,
        'type'           => strtoupper( strtok( $c->Type, '(' ) ),
        'size'           => '',
        'required'       => $c->Null === 'NO',
        'primary_key'    => $c->Key === 'PRI',
        'auto_increment' => str_contains( $c->Extra, 'auto_increment' ),
    ], $cols );
}

/**
 * Transforma um table_key em label legível.
 */
function gig_crud_humanize( string $key ): string {
    return ucwords( str_replace( [ '_', '-' ], ' ', $key ) );
}