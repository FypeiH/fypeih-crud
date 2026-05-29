<div class="gig-card">
        <!-- Bulk action form -->
        <form method="post" id="gig-tables-form">
            <?php wp_nonce_field('gig_crud_bulk_tables'); ?>
            <input type="hidden" name="gig_action" value="bulk_delete_tables">

            <!-- Bulk bar -->
            <div class="gig-bulk-bar" id="gig-tables-bulk-bar">
                <span class="gig-bulk-count" id="gig-tables-bulk-count">0 selecionadas</span>
                <div class="gig-bulk-sep"></div>
                <button type="submit" class="gig-btn gig-btn-danger gig-btn-sm"
                    onclick="return confirm('Tens a certeza? Esta ação é irreversível.')">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M2 3h8M5 5v4M7 5v4M3 3l.5 7h5L9 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Apagar seleção
                </button>
            </div>

            <div class="gig-table-wrap">
                <table class="gig-table">
                    <thead>
                        <tr>
                            <th class="check-col">
                                <input type="checkbox" class="gig-checkbox-all" data-scope="tables"
                                    style="appearance:none;width:14px;height:14px;border:1px solid var(--gig-border-2);border-radius:3px;background:var(--gig-bg);cursor:pointer;vertical-align:middle">
                            </th>
                            <th>Nome</th>
                            <th>Chave</th>
                            <th>Campos</th>
                            <th>Criada a</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty($all_tables) ) : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="gig-empty">
                                        <svg class="gig-empty-icon" width="40" height="40" fill="none" viewBox="0 0 40 40"><rect x="4" y="8" width="32" height="24" rx="3" stroke="currentColor" stroke-width="2"/><path d="M4 16h32M14 8v24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        <div class="gig-empty-title">Nenhuma tabela criada</div>
                                        <div class="gig-empty-desc">Clica em "Nova Tabela" para criares a tua primeira tabela personalizada.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $all_tables as $tbl ) :
                                $sch = json_decode( $tbl->schema_json, true );
                                $num_fields = is_array($sch) ? count($sch) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="table_keys[]" value="<?php echo esc_attr($tbl->table_key); ?>"
                                            class="gig-row-check" data-scope="tables"
                                            style="appearance:none;width:14px;height:14px;border:1px solid var(--gig-border-2);border-radius:3px;background:var(--gig-bg);cursor:pointer;vertical-align:middle">
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( gig_tab_url('records', ['gig_table'=>$tbl->table_key]) ); ?>"
                                           style="color:var(--gig-accent);text-decoration:none;font-weight:500;">
                                            <?php echo esc_html($tbl->table_label); ?>
                                        </a>
                                    </td>
                                    <td><span class="gig-mono gig-muted"><?php echo esc_html($tbl->table_key); ?></span></td>
                                    <td><span class="gig-badge gig-badge-grey"><?php echo $num_fields; ?> campos</span></td>
                                    <td><span class="gig-mono gig-muted" style="font-size:11px;"><?php echo esc_html( date('d/m/Y', strtotime($tbl->created_at)) ); ?></span></td>
                                    <td>
                                        <div class="gig-gap-6">
                                            <button type="button" class="gig-btn gig-btn-ghost" style="padding:5px 10px;"
                                                onclick="gigOpenEditTable('<?php echo esc_js($tbl->table_key); ?>')">
                                                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M8.5 1.5L10.5 3.5L4 10H2V8L8.5 1.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                                                Editar
                                            </button>
                                            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg(['page'=>GIGANTIC_CRUD_SLUG,'gig_tab'=>'tables','gig_delete_table'=>$tbl->table_key], admin_url('admin.php')), 'gig_crud_delete_table') ); ?>"
                                               class="gig-btn gig-btn-danger" style="padding:5px 10px;"
                                               onclick="return confirm('Apagar a tabela e todos os seus dados?')">
                                                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M2 3h8M5 5v4M7 5v4M3 3l.5 7h5L9 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
