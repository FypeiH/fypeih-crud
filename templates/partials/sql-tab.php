<div class="fyp-sql-editor">
        <div class="fyp-sql-toolbar">
            <span class="fyp-sql-toolbar-label">
                <svg width="13" height="13" fill="none" viewBox="0 0 14 14" style="vertical-align:middle;margin-right:6px;"><path d="M2 4l4 3-4 3M8 10h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                SQL Query
            </span>
            <button type="submit" form="fyp-sql-form" class="fyp-btn fyp-btn-primary" style="gap:6px;">
                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M3 2l7 4-7 4V2Z" fill="currentColor"/></svg>
                Executar
            </button>
        </div>
        <form id="fyp-sql-form" method="post">
            <?php wp_nonce_field('fyp_crud_run_sql'); ?>
            <input type="hidden" name="fyp_action" value="run_sql">
            <textarea name="sql_query" class="fyp-sql-area" spellcheck="false"
                placeholder="-- Escreve o teu SQL aqui&#10;SELECT * FROM wp_fyp_crud_meta;"><?php echo $sql_last ? esc_textarea($sql_last) : ''; ?></textarea>
        </form>

        <?php if ( $sql_result !== false ) : ?>
            <div class="fyp-sql-results">
                <div class="fyp-sql-results-toolbar">
                    <?php if ( $sql_result['error'] ) : ?>
                        <span class="fyp-badge fyp-badge-red">ERRO</span>
                        <span class="fyp-mono" style="color:var(--fyp-red);font-size:12px;"><?php echo esc_html($sql_result['error']); ?></span>
                    <?php elseif ( $sql_result['type'] === 'select' ) : ?>
                        <span class="fyp-badge fyp-badge-green">SELECT</span>
                        <span class="fyp-sql-results-label"><?php echo count($sql_result['rows']); ?> linha(s) retornadas</span>
                    <?php else : ?>
                        <span class="fyp-badge fyp-badge-blue">EXEC</span>
                        <span class="fyp-sql-results-label"><?php echo intval($sql_result['affected']); ?> linha(s) afetadas</span>
                    <?php endif; ?>
                </div>

                <?php if ( ! $sql_result['error'] && $sql_result['type'] === 'select' && ! empty($sql_result['rows']) ) : ?>
                    <div class="fyp-table-wrap">
                        <table class="fyp-table">
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
                                            <td class="fyp-mono"><?php echo esc_html($cell); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ( ! $sql_result['error'] && $sql_result['type'] === 'select' && empty($sql_result['rows']) ) : ?>
                    <div class="fyp-empty" style="padding:24px;">
                        <div class="fyp-empty-title" style="font-size:13px;">Query devolveu 0 resultados</div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick reference -->
    <div class="fyp-card" style="margin-top:20px;">
        <div class="fyp-card-header">
            <span class="fyp-card-title">Tabelas disponíveis</span>
        </div>
        <div class="fyp-card-body">
            <?php global $wpdb; ?>
            <?php if ( empty($all_tables) ) : ?>
                <span class="fyp-muted" style="font-size:13px;">Ainda não há tabelas criadas.</span>
            <?php else : ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php foreach ( $all_tables as $tbl ) : ?>
                        <span class="fyp-badge fyp-badge-grey fyp-mono" style="font-size:11px;cursor:pointer;"
                            onclick="document.querySelector('.fyp-sql-area').value += '\nSELECT * FROM <?php echo esc_js($wpdb->prefix . $tbl->table_key); ?>;'">
                            <?php echo esc_html($wpdb->prefix . $tbl->table_key); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>