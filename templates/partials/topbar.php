<!-- Topbar -->
    <div class="gig-topbar">
        <div class="gig-topbar-title">
            <?php
            if ( $gig_tab === 'tables' )       echo 'Gestão de Tabelas';
            elseif ( $gig_tab === 'sql' )       echo 'SQL Editor';
            elseif ( $gig_tab === 'records' && $active_meta ) echo esc_html( $active_meta->table_label );
            else                                echo 'Gigantic CRUD';
            ?>
        </div>
        <div class="gig-topbar-actions">
            <?php if ( $gig_tab === 'tables' ) : ?>
                <button class="gig-btn gig-btn-primary" onclick="gigOpenCreateTable()">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Nova Tabela
                </button>
            <?php elseif ( $gig_tab === 'records' && $active_meta ) : ?>
                <a href="<?php echo esc_url( gig_tab_url('tables') ); ?>" class="gig-btn gig-btn-ghost">
                    ← Tabelas
                </a>
            <?php endif; ?>
        </div>
    </div>