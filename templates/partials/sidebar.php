<aside class="fyp-sidebar">
    <div class="fyp-sidebar-logo">
        <img src="<?php echo esc_url( FYPEIH_CRUD_URL . 'assets/fypeihLogo.png' ); ?>" alt="Fypeih Logo" width="28" height="28">
        <div class="fyp-sidebar-logo-text">
            CRUD Manager
            <span>Filipe Bravo</span>
        </div>
    </div>

    <div class="fyp-nav-section">
        <div class="fyp-nav-label">Navegação</div>
        <a href="<?php echo esc_url( fyp_tab_url('tables') ); ?>"
           class="fyp-nav-item <?php echo $fyp_tab === 'tables' ? 'active' : ''; ?>">
            <svg width="14" height="14" fill="none" viewBox="0 0 16 16"><rect x="1" y="1" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1" y="7" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1" y="13" width="6" height="2" rx="1" stroke="currentColor" stroke-width="1.4"/></svg>
            Todas as Tabelas
            <span class="fyp-nav-badge"><?php echo count( $all_db_tables ); ?></span>
        </a>
        <a href="<?php echo esc_url( fyp_tab_url('sql') ); ?>"
           class="fyp-nav-item <?php echo $fyp_tab === 'sql' ? 'active' : ''; ?>">
            <svg width="14" height="14" fill="none" viewBox="0 0 16 16"><path d="M3 4l4 4-4 4M9 12h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            SQL Editor
        </a>

        <?php foreach ( $sidebar_groups as $source => $tables ) :
            // Esconde WP core por defeito na sidebar (ainda aparece na tab de tabelas)
            if ( $source === 'wpcore' ) continue;

            $source_label = $tables[0]->source_label ?? ucfirst( $source );
            $source_icon  = fyp_crud_source_icon( $source );
        ?>
            <div class="fyp-nav-label" style="margin-top:16px;">
                <?php echo $source_icon; ?>
                <?php echo esc_html( $source_label ); ?>
                <span style="margin-left:auto;font-size:9px;opacity:.5;"><?php echo count($tables); ?></span>
            </div>
            <?php foreach ( $tables as $tbl ) : ?>
                <a href="<?php echo esc_url( fyp_tab_url('records', ['fyp_table' => $tbl->table_key]) ); ?>"
                   class="fyp-nav-item <?php echo ( $fyp_tab === 'records' && $fyp_table === $tbl->table_key ) ? 'active' : ''; ?>">
                    <svg width="13" height="13" fill="none" viewBox="0 0 16 16"><rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M1 7h14M6 3v10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    <?php echo esc_html( $tbl->table_label ); ?>
                    <?php if ( ! $tbl->managed ) : ?>
                        <span class="fyp-nav-badge" title="Não gerida pelo CRUD Manager">ext</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <div class="fyp-sidebar-footer">
        v<?php echo FYPEIH_CRUD_VERSION; ?>
        · <?php echo count( array_filter( $all_db_tables, fn($t) => $t->managed ) ); ?> geridas
        · <?php echo count( $all_db_tables ); ?> total
    </div>
</aside>

<?php
function fyp_crud_source_icon( string $source ): string {
    $icons = [
        'fypeih'     => '<svg width="10" height="10" fill="none" viewBox="0 0 12 12" style="vertical-align:middle;margin-right:4px;"><rect width="12" height="12" rx="2" fill="var(--fyp-accent)"/><path d="M3 4h6M3 6h4M3 8h5" stroke="#0a0a0b" stroke-width="1.3" stroke-linecap="round"/></svg>',
        'jetengine'    => '<svg width="10" height="10" fill="none" viewBox="0 0 12 12" style="vertical-align:middle;margin-right:4px;"><circle cx="6" cy="6" r="5" stroke="#ff6b35" stroke-width="1.4"/><path d="M4 6l2-2 2 2-2 2-2-2z" fill="#ff6b35"/></svg>',
        'woocommerce'  => '<svg width="10" height="10" fill="none" viewBox="0 0 12 12" style="vertical-align:middle;margin-right:4px;"><circle cx="6" cy="6" r="5" stroke="#7f54b3" stroke-width="1.4"/></svg>',
        'wpcore'       => '<svg width="10" height="10" fill="none" viewBox="0 0 12 12" style="vertical-align:middle;margin-right:4px;"><circle cx="6" cy="6" r="5" stroke="var(--fyp-blue)" stroke-width="1.4"/></svg>',
        'other'        => '<svg width="10" height="10" fill="none" viewBox="0 0 12 12" style="vertical-align:middle;margin-right:4px;"><circle cx="6" cy="6" r="5" stroke="var(--fyp-text-3)" stroke-width="1.4"/></svg>',
    ];
    return $icons[ $source ] ?? $icons['other'];
}
?>