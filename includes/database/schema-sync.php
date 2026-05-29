<?php
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
?>