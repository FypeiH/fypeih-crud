<div class="gig-sql-editor">
        <div class="gig-sql-toolbar">
            <span class="gig-sql-toolbar-label">
                <svg width="13" height="13" fill="none" viewBox="0 0 14 14" style="vertical-align:middle;margin-right:6px;"><path d="M2 4l4 3-4 3M8 10h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                SQL Query
            </span>
            <button type="submit" form="gig-sql-form" class="gig-btn gig-btn-primary" style="gap:6px;">
                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M3 2l7 4-7 4V2Z" fill="currentColor"/></svg>
                Executar
            </button>
        </div>
        <form id="gig-sql-form" method="post">
            <?php wp_nonce_field('gig_crud_run_sql'); ?>
            <input type="hidden" name="gig_action" value="run_sql">
            <textarea name="sql_query" class="gig-sql-area" spellcheck="false"
                placeholder="-- Escreve o teu SQL aqui&#10;SELECT * FROM wp_gig_crud_meta;"><?php echo $sql_last ? esc_textarea($sql_last) : ''; ?></textarea>
        </form>

        <?php if ( $sql_result !== false ) : ?>
            <div class="gig-sql-results">
                <div class="gig-sql-results-toolbar">
                    <?php if ( $sql_result['error'] ) : ?>
                        <span class="gig-badge gig-badge-red">ERRO</span>
                        <span class="gig-mono" style="color:var(--gig-red);font-size:12px;"><?php echo esc_html($sql_result['error']); ?></span>
                    <?php elseif ( $sql_result['type'] === 'select' ) : ?>
                        <span class="gig-badge gig-badge-green">SELECT</span>
                        <span class="gig-sql-results-label"><?php echo count($sql_result['rows']); ?> linha(s) retornadas</span>
                    <?php else : ?>
                        <span class="gig-badge gig-badge-blue">EXEC</span>
                        <span class="gig-sql-results-label"><?php echo intval($sql_result['affected']); ?> linha(s) afetadas</span>
                    <?php endif; ?>
                </div>

                <?php if ( ! $sql_result['error'] && $sql_result['type'] === 'select' && ! empty($sql_result['rows']) ) : ?>
                    <div class="gig-table-wrap">
                        <table class="gig-table">
                            <thead>
                                <tr>
                                    <?php foreach ( array_keys($sql_result['rows'][0]) as $col ) : ?>
                                        <th><?php echo esc_html($col); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $sql_result['rows'] as $row ) : ?>
                                    <tr>
                                        <?php foreach ( $row as $cell ) : ?>
                                            <td class="gig-mono"><?php echo esc_html($cell); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ( ! $sql_result['error'] && $sql_result['type'] === 'select' && empty($sql_result['rows']) ) : ?>
                    <div class="gig-empty" style="padding:24px;">
                        <div class="gig-empty-title" style="font-size:13px;">Query devolveu 0 resultados</div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick reference -->
    <div class="gig-card" style="margin-top:20px;">
        <div class="gig-card-header">
            <span class="gig-card-title">Tabelas disponíveis</span>
        </div>
        <div class="gig-card-body">
            <?php global $wpdb; ?>
            <?php if ( empty($all_tables) ) : ?>
                <span class="gig-muted" style="font-size:13px;">Ainda não há tabelas criadas.</span>
            <?php else : ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ( $all_tables as $tbl ) : ?>
                        <span class="gig-badge gig-badge-grey gig-mono" style="font-size:11px;cursor:pointer;"
                            onclick="document.querySelector('.gig-sql-area').value += '\nSELECT * FROM <?php echo esc_js($wpdb->prefix . $tbl->table_key); ?>;'">
                            <?php echo esc_html($wpdb->prefix . $tbl->table_key); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>