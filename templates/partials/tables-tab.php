<?php foreach ( $tab_groups as $source => $tables ) :
    $source_label = $tables[0]->source_label ?? ucfirst( $source );
    $badge_class  = $source_badge_class[ $source ] ?? 'fyp-badge-grey';
?>
    <div class="fyp-card" style="margin-bottom: 20px;">
        <div class="fyp-card-header">
            <span class="fyp-card-title" style="display:flex;align-items:center;gap:8px;">
                <?php echo fyp_crud_source_icon( $source ); ?>
                <?php echo esc_html( $source_label ); ?>
                <span class="fyp-badge <?php echo $badge_class; ?>"><?php echo count($tables); ?></span>
            </span>
            <?php if ( $source === 'fypeih' ) : ?>
                <button class="fyp-btn fyp-btn-primary" onclick="fypOpenCreateTable()">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Nova Tabela
                </button>
            <?php endif; ?>
        </div>

        <form method="post" id="fyp-tables-form-<?php echo esc_attr($source); ?>">
            <?php wp_nonce_field('fyp_crud_bulk_tables'); ?>
            <input type="hidden" name="fyp_action" value="bulk_delete_tables">

            <?php if ( $source === 'fypeih' ) : ?>
            <div class="fyp-bulk-bar" id="fyp-tables-bulk-bar-<?php echo esc_attr($source); ?>">
                <span class="fyp-bulk-count" id="fyp-tables-bulk-count-<?php echo esc_attr($source); ?>">0 selecionadas</span>
                <div class="fyp-bulk-sep"></div>
                <button type="submit" class="fyp-btn fyp-btn-danger"
                    onclick="return confirm('Tens a certeza? Esta ação é irreversível.')">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M2 3h8M5 5v4M7 5v4M3 3l.5 7h5L9 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Apagar seleção
                </button>
            </div>
            <?php endif; ?>

            <div class="fyp-table-wrap">
                <table class="fyp-table">
                    <thead>
                        <tr>
                            <?php if ( $source === 'fypeih' ) : ?>
                                <th class="check-col">
                                    <input type="checkbox" class="fyp-checkbox-all" data-scope="tables-<?php echo esc_attr($source); ?>"
                                        style="appearance:none;width:14px;height:14px;border:1px solid var(--fyp-border-2);border-radius:3px;background:var(--fyp-bg);cursor:pointer;vertical-align:middle">
                                </th>
                            <?php endif; ?>
                            <th>Nome</th>
                            <th>Tabela na DB</th>
                            <th>Campos</th>
                            <th>Registos</th>
                            <?php if ( $source === 'fypeih' ) : ?>
                                <th>Criada a</th>
                            <?php endif; ?>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $tables as $tbl ) :
                            // Schema: geridas usam schema_json, externas usam DESCRIBE
                            if ( $tbl->managed ) {
                                $sch = json_decode( $tbl->schema_json, true );
                                $num_fields = is_array($sch) ? count($sch) : '—';
                            } else {
                                $ext_cols   = fyp_crud_get_external_columns( $tbl->table_name );
                                $num_fields = count( $ext_cols );
                            }
                        ?>
                            <tr>
                                <?php if ( $source === 'fypeih' ) : ?>
                                    <td>
                                        <input type="checkbox"
                                            name="table_keys[]"
                                            value="<?php echo esc_attr($tbl->table_key); ?>"
                                            class="fyp-row-check"
                                            data-scope="tables-<?php echo esc_attr($source); ?>"
                                            style="appearance:none;width:14px;height:14px;border:1px solid var(--fyp-border-2);border-radius:3px;background:var(--fyp-bg);cursor:pointer;vertical-align:middle">
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <a href="<?php echo esc_url( fyp_tab_url('records', ['fyp_table' => $tbl->table_key]) ); ?>"
                                       style="color:var(--fyp-accent);text-decoration:none;font-weight:500;">
                                        <?php echo esc_html( $tbl->table_label ); ?>
                                    </a>
                                </td>

                                <td><span class="fyp-mono fyp-muted" style="font-size:11px;"><?php echo esc_html( $tbl->table_name ); ?></span></td>

                                <td><span class="fyp-badge fyp-badge-grey"><?php echo $num_fields; ?> campos</span></td>

                                <td><span class="fyp-mono fyp-muted"><?php echo number_format( $tbl->row_count ); ?></span></td>

                                <?php if ( $source === 'fypeih' ) : ?>
                                    <td><span class="fyp-mono fyp-muted" style="font-size:11px;">
                                        <?php echo $tbl->created_at ? esc_html( date('d/m/Y', strtotime($tbl->created_at)) ) : '—'; ?>
                                    </span></td>
                                <?php endif; ?>

                                <td>
                                    <div class="fyp-gap-6">
                                        <!-- Ver registos (disponível para todas) -->
                                        <a href="<?php echo esc_url( fyp_tab_url('records', ['fyp_table' => $tbl->table_key]) ); ?>"
                                           class="fyp-btn fyp-btn-ghost" style="padding:5px 10px;">
                                            <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M1 6s2-4 5-4 5 4 5 4-2 4-5 4-5-4-5-4z" stroke="currentColor" stroke-width="1.3"/><circle cx="6" cy="6" r="1.5" stroke="currentColor" stroke-width="1.3"/></svg>
                                            Ver
                                        </a>

                                        <?php if ( $tbl->managed ) : ?>
                                            <!-- Editar schema (só Fypeih) -->
                                            <button type="button" class="fyp-btn fyp-btn-ghost" style="padding:5px 10px;"
                                                onclick="fypOpenEditTable('<?php echo esc_js($tbl->table_key); ?>')">
                                                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M8.5 1.5L10.5 3.5L4 10H2V8L8.5 1.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                                                Editar
                                            </button>
                                            <!-- Apagar (só Fypeih) -->
                                            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg(['page'=>FYPEIH_CRUD_SLUG,'fyp_tab'=>'tables','fyp_delete_table'=>$tbl->table_key], admin_url('admin.php')), 'fyp_crud_delete_table') ); ?>"
                                               class="fyp-btn fyp-btn-danger" style="padding:5px 10px;"
                                               onclick="return confirm('Apagar a tabela e todos os seus dados?')">
                                                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M2 3h8M5 5v4M7 5v4M3 3l.5 7h5L9 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        <?php else : ?>
                                            <!-- Importar para Fypeih (tabelas externas) -->
                                            <a href="<?php echo esc_url( add_query_arg(['page'=>FYPEIH_CRUD_SLUG,'fyp_tab'=>'tables','fyp_import'=>$tbl->table_key], admin_url('admin.php')) ); ?>"
                                               class="fyp-btn fyp-btn-ghost" style="padding:5px 10px;color:var(--fyp-accent);"
                                               title="Importar para o Fypeih CRUD para gestão completa">
                                                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v7M3 5l3 3 3-3M2 10h8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Importar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
<?php endforeach; ?>