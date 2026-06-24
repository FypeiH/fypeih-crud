<!-- Topbar -->
    <div class="fyp-topbar">
        <div class="fyp-topbar-title">
            <?php
            if ( $fyp_tab === 'tables' )       echo 'Gestão de Tabelas';
            elseif ( $fyp_tab === 'sql' )       echo 'SQL Editor';
            elseif ( $fyp_tab === 'records' && $active_meta ) echo esc_html( $active_meta->table_label );
            else                                echo 'Fypeih CRUD';
            ?>
        </div>
        <div class="fyp-topbar-actions">
            <?php if ( $fyp_tab === 'tables' ) : ?>
                <button class="fyp-btn fyp-btn-primary" onclick="fypOpenCreateTable()">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Nova Tabela
                </button>
            <?php elseif ( $fyp_tab === 'records' && $active_meta ) : ?>
                <a href="<?php echo esc_url( fyp_tab_url('tables') ); ?>" class="fyp-btn fyp-btn-ghost">
                    ← Tabelas
                </a>
            <?php endif; ?>
        </div>
    </div>