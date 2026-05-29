<!-- ================================================================
     SIDEBAR
================================================================ -->
<aside class="gig-sidebar">
    <div class="gig-sidebar-logo">
         <img src="<?php echo GIGANTIC_CRUD_URL; ?>assets/giganticLogo.png" alt="Gigantic Logo" width="28" height="28">
        <div class="gig-sidebar-logo-text">
            CRUD Manager
            <span>Gigantic Digital Growth</span>
        </div>
    </div>

    <div class="gig-nav-section">
        <div class="gig-nav-label">Navegação</div>
        <a href="<?php echo esc_url( gig_tab_url('tables') ); ?>"
           class="gig-nav-item <?php echo $gig_tab === 'tables' ? 'active' : ''; ?>">
            <svg width="14" height="14" fill="none" viewBox="0 0 16 16"><rect x="1" y="1" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1" y="7" width="14" height="4" rx="1.5" stroke="currentColor" stroke-width="1.4"/><rect x="1" y="13" width="6" height="2" rx="1" stroke="currentColor" stroke-width="1.4"/></svg>
            Tabelas
            <span class="gig-nav-badge"><?php echo count($all_tables); ?></span>
        </a>
        <a href="<?php echo esc_url( gig_tab_url('sql') ); ?>"
           class="gig-nav-item <?php echo $gig_tab === 'sql' ? 'active' : ''; ?>">
            <svg width="14" height="14" fill="none" viewBox="0 0 16 16"><path d="M3 4l4 4-4 4M9 12h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            SQL Editor
        </a>

        <?php if ( ! empty( $all_tables ) ) : ?>
            <div class="gig-nav-label" style="margin-top:16px;">Tabelas</div>
            <?php foreach ( $all_tables as $tbl ) : ?>
                <a href="<?php echo esc_url( gig_tab_url('records', ['gig_table' => $tbl->table_key]) ); ?>"
                   class="gig-nav-item <?php echo ($gig_tab === 'records' && $gig_table === $tbl->table_key) ? 'active' : ''; ?>">
                    <svg width="13" height="13" fill="none" viewBox="0 0 16 16"><rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M1 7h14M6 3v10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    <?php echo esc_html( $tbl->table_label ); ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="gig-sidebar-footer">
        v<?php echo GIGANTIC_CRUD_VERSION; ?> · <?php echo count($all_tables); ?> table<?php echo count($all_tables) !== 1 ? 's' : ''; ?>
    </div>
</aside>