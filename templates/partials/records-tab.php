<?php if ( ! $active_meta ) : ?>
        <div class="gig-card">
            <div class="gig-empty">
                <div class="gig-empty-title">Seleciona uma tabela</div>
                <div class="gig-empty-desc">Escolhe uma tabela na barra lateral para ver os seus registos.</div>
            </div>
        </div>
    <?php else :
        $records = gig_crud_get_records( $gig_table );
        // Non-meta columns
        $data_cols = array_filter($active_schema, fn($f) => !in_array($f['name'], ['id','data_criacao','created_at']));
    ?>
        <?php if ( $edit_record ) : ?>
            <!-- EDIT RECORD CARD -->
            <div class="gig-card" style="margin-bottom:20px;">
                <div class="gig-card-header">
                    <span class="gig-card-title">Editar Registo #<?php echo esc_html($edit_record->id); ?></span>
                    <a href="<?php echo esc_url( gig_tab_url('records',['gig_table'=>$gig_table]) ); ?>" class="gig-btn gig-btn-ghost">Cancelar</a>
                </div>
                <div class="gig-card-body">
                    <form method="post">
                        <?php wp_nonce_field('gig_crud_save_record'); ?>
                        <input type="hidden" name="gig_action" value="update_record">
                        <input type="hidden" name="table_key" value="<?php echo esc_attr($gig_table); ?>">
                        <input type="hidden" name="rec_id" value="<?php echo esc_attr($edit_record->id); ?>">
                        <div class="gig-form-row cols-3">
                            <?php foreach ( $active_schema as $f ) :
                                if ( !empty($f['auto_increment']) || $f['name'] === 'id' ) continue; ?>
                                <div class="gig-field">
                                    <label class="gig-label"><?php echo esc_html($f['name']); ?><?php echo !empty($f['required']) ? ' *' : ''; ?></label>
                                    <?php
                                        $v = $edit_record->{$f['name']} ?? '';
                                        echo gig_crud_render_field_input( $f, 'rec[' . esc_attr($f['name']) . ']', $v );
                                        ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top:8px;">
                            <button type="submit" class="gig-btn gig-btn-primary">Guardar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- RECORDS TABLE -->
        <div class="gig-card">
            <form method="post" id="gig-records-form">
                <?php wp_nonce_field('gig_crud_bulk_records'); ?>
                <input type="hidden" name="gig_action" value="bulk_delete_records">
                <input type="hidden" name="table_key" value="<?php echo esc_attr($gig_table); ?>">

                <!-- Bulk bar -->
                <div class="gig-bulk-bar" id="gig-records-bulk-bar">
                    <span class="gig-bulk-count" id="gig-records-bulk-count">0 selecionados</span>
                    <div class="gig-bulk-sep"></div>
                    <button type="submit" class="gig-btn gig-btn-danger"
                        onclick="return confirm('Apagar os registos selecionados?')">
                        <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M2 3h8M5 5v4M7 5v4M3 3l.5 7h5L9 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Apagar seleção
                    </button>
                </div>

                <div class="gig-table-wrap">
                    <table class="gig-table">
                        <thead>
                            <tr>
                                <th class="check-col">
                                    <input type="checkbox" class="gig-checkbox-all" data-scope="records"
                                        style="appearance:none;width:14px;height:14px;border:1px solid var(--gig-border-2);border-radius:3px;background:var(--gig-bg);cursor:pointer;">
                                </th>
                                <?php foreach ( $active_schema as $f ) : ?>
                                    <th><?php echo esc_html($f['name']); ?></th>
                                <?php endforeach; ?>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- ADD ROW (inline) -->
                            <tr class="gig-add-row" id="gig-add-row">
                                <td></td>
                                <?php
                                // We build a small inline add form per cell
                                // Use a shared hidden form below
                                foreach ( $active_schema as $f ) :
                                    if ( !empty($f['auto_increment']) || $f['name'] === 'id' ) :
                                ?>
                                    <td><span class="gig-mono gig-muted" style="font-size:11px;">auto</span></td>
                                <?php else : ?>
                                    <td>
                                        <?php echo gig_crud_render_field_input( $f, 'rec[' . esc_attr($f['name']) . ']', '', 'gig-add-record-form' ); ?>
                                    </td>
                                <?php endif; endforeach; ?>
                                <td>
                                    <button type="submit" form="gig-add-record-form" class="gig-btn gig-btn-primary" style="padding:5px 12px;font-size:10px;">
                                        + Adicionar
                                    </button>
                                </td>
                            </tr>

                            <?php if ( empty($records) ) : ?>
                                <tr>
                                    <td colspan="<?php echo count($active_schema) + 2; ?>">
                                        <div class="gig-empty" style="padding:30px;">
                                            <div class="gig-empty-title">Sem registos</div>
                                            <div class="gig-empty-desc">Usa a linha acima para adicionar o primeiro registo.</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ( $records as $rec ) : ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="rec_ids[]" value="<?php echo esc_attr($rec->id); ?>"
                                                class="gig-row-check" data-scope="records"
                                                style="appearance:none;width:14px;height:14px;border:1px solid var(--gig-border-2);border-radius:3px;background:var(--gig-bg);cursor:pointer;">
                                        </td>
                                        <?php foreach ( $active_schema as $f ) : ?>
                                            <td><?php echo esc_html( $rec->{$f['name']} ?? '' ); ?></td>
                                        <?php endforeach; ?>
                                        <td>
                                            <div class="gig-gap-6">
                                                <a href="<?php echo esc_url( add_query_arg(['page'=>GIGANTIC_CRUD_SLUG,'gig_tab'=>'records','gig_table'=>$gig_table,'gig_edit_record'=>$rec->id], admin_url('admin.php')) ); ?>"
                                                   class="gig-btn gig-btn-ghost" style="padding:5px 10px;">
                                                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M8.5 1.5L10.5 3.5L4 10H2V8L8.5 1.5Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                                                </a>
                                                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg(['page'=>GIGANTIC_CRUD_SLUG,'gig_tab'=>'records','gig_table'=>$gig_table,'gig_delete_record'=>$rec->id], admin_url('admin.php')), 'gig_crud_delete_record') ); ?>"
                                                   class="gig-btn gig-btn-danger" style="padding:5px 10px;"
                                                   onclick="return confirm('Apagar este registo?')">
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

        <!-- Hidden add form -->
        <form id="gig-add-record-form" method="post" style="display:none;">
            <?php wp_nonce_field('gig_crud_save_record'); ?>
            <input type="hidden" name="gig_action" value="insert_record">
            <input type="hidden" name="table_key" value="<?php echo esc_attr($gig_table); ?>">
        </form>

    <?php endif; ?>
