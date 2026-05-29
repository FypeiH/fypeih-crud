<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ----------------------------------------------------------------
   Resolve active tab, active table, messages
---------------------------------------------------------------- */
$gig_tab   = sanitize_key( $_GET['gig_tab']   ?? 'tables' );
$gig_table = sanitize_key( $_GET['gig_table'] ?? '' );
$gig_msg   = sanitize_key( $_GET['gig_msg']   ?? '' );
$all_tables = gig_crud_get_all_meta_tables();
$uid        = get_current_user_id();

/* Active table meta */
$active_meta   = $gig_table ? gig_crud_get_meta_table( $gig_table ) : null;
$active_schema = $active_meta ? json_decode( $active_meta->schema_json, true ) : [];

/* Edit record */
$edit_record = null;
if ( $gig_tab === 'records' && isset( $_GET['gig_edit_record'] ) && $gig_table ) {
    $edit_record = gig_crud_get_record( $gig_table, absint( $_GET['gig_edit_record'] ) );
}

/* Edit table schema */
$edit_table_key = sanitize_key( $_GET['gig_edit_table'] ?? '' );
$edit_table_meta = $edit_table_key ? gig_crud_get_meta_table( $edit_table_key ) : null;

/* SQL results */
$sql_result = get_transient( 'gig_crud_sql_result_' . $uid );
$sql_last   = get_transient( 'gig_crud_sql_last_'   . $uid );
if ( $gig_tab === 'sql' ) {
    delete_transient( 'gig_crud_sql_result_' . $uid );
    delete_transient( 'gig_crud_sql_last_'   . $uid );
}

/* Nav shortcuts */
$base_url = admin_url( 'admin.php?page=' . GIGANTIC_CRUD_SLUG );

function gig_tab_url( $tab, $extra = [] ) {
    global $base_url;
    return add_query_arg( array_merge( [ 'gig_tab' => $tab ], $extra ), $base_url );
}

/* Messages map */
$messages = [
    'table_saved'   => [ 'success', 'Tabela guardada com sucesso.' ],
    'table_deleted' => [ 'success', 'Tabela eliminada com sucesso.' ],
    'bulk_deleted'  => [ 'success', 'Seleção eliminada com sucesso.' ],
    'record_saved'  => [ 'success', 'Registo criado com sucesso.' ],
    'record_updated'=> [ 'success', 'Registo atualizado com sucesso.' ],
    'record_deleted'=> [ 'success', 'Registo eliminado com sucesso.' ],
    'sql_run'       => [ 'success', 'Query executada com sucesso.' ],
];
?>
<!– Gigantic CRUD Manager — redesign v2 –>
<div class="wrap gig-crud-admin">
<div class="gig-shell">

<!-- ================================================================
     SIDEBAR
================================================================ -->
<aside class="gig-sidebar">
    <div class="gig-sidebar-logo">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <rect width="28" height="28" rx="6" fill="#c8f135"/>
            <path d="M7 10h14M7 14h10M7 18h12" stroke="#0a0a0b" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <div class="gig-sidebar-logo-text">
            CRUD Manager
            <span>Gigantic Digital</span>
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

<!-- ================================================================
     MAIN AREA
================================================================ -->
<main class="gig-main">

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

    <!-- Message -->
    <?php if ( $gig_msg && isset( $messages[$gig_msg] ) ) :
        [$mtype, $mtext] = $messages[$gig_msg]; ?>
        <div class="gig-notice gig-notice-<?php echo $mtype === 'success' ? 'success' : 'error'; ?>" style="margin:16px 28px 0;">
            <?php if ($mtype==='success') : ?>
                <svg width="15" height="15" fill="none" viewBox="0 0 16 16"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php endif; ?>
            <?php echo esc_html( $mtext ); ?>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="gig-content">

<?php /* ============================================================
       TAB: TABLES
============================================================ */ ?>
<?php if ( $gig_tab === 'tables' ) : ?>

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

<?php endif; /* tables */ ?>

<?php /* ============================================================
       TAB: RECORDS
============================================================ */ ?>
<?php if ( $gig_tab === 'records' ) :
    if ( ! $active_meta ) : ?>
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
                                    <input type="text" name="rec[<?php echo esc_attr($f['name']); ?>]"
                                        class="gig-input"
                                        <?php echo !empty($f['required']) ? 'required' : ''; ?>
                                        value="<?php $v = $edit_record->{$f['name']} ?? ''; echo esc_attr($v); ?>">
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
                                        <input type="text" name="rec[<?php echo esc_attr($f['name']); ?>]"
                                               form="gig-add-record-form"
                                               class="gig-input gig-input-sm"
                                               placeholder="<?php echo esc_attr($f['name']); ?>"
                                               <?php echo !empty($f['required']) ? 'required' : ''; ?>>
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
<?php endif; /* records */ ?>

<?php /* ============================================================
       TAB: SQL
============================================================ */ ?>
<?php if ( $gig_tab === 'sql' ) : ?>
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
<?php endif; /* sql */ ?>

    </div><!-- .gig-content -->
</main><!-- .gig-main -->
</div><!-- .gig-shell -->
</div><!-- .wrap -->

<!-- ================================================================
     MODAL: CREATE / EDIT TABLE
================================================================ -->
<div class="gig-modal-backdrop" id="gig-table-modal">
    <div class="gig-modal" style="max-width:960px;">
        <div class="gig-modal-header">
            <span class="gig-modal-title" id="gig-modal-title">Nova Tabela</span>
            <button type="button" class="gig-modal-close" onclick="gigCloseModal()">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="gig-table-form" method="post">
            <?php wp_nonce_field('gig_crud_save_table'); ?>
            <input type="hidden" name="gig_action" value="save_table">

            <div class="gig-modal-body">
                <div class="gig-form-row cols-2" style="margin-bottom:20px;">
                    <div class="gig-field">
                        <label class="gig-label">Nome da Tabela *</label>
                        <input type="text" id="gig-table-label" name="table_label" class="gig-input" required placeholder="ex: Contactos">
                    </div>
                    <div class="gig-field">
                        <label class="gig-label">Chave / Identificador *</label>
                        <input type="text" id="gig-table-key" name="table_key" class="gig-input" required placeholder="ex: contactos" pattern="[a-z0-9_]+" title="Apenas letras minúsculas, números e underscores">
                        <span style="font-size:10px;color:var(--gig-text-3);margin-top:4px;">Apenas a-z, 0-9, _</span>
                    </div>
                </div>

                <div style="margin-bottom:10px;">
                    <label class="gig-label">Campos da Tabela</label>
                </div>

                <!-- Field builder header -->
                <div class="gig-field-row-header">
                    <span>Nome do campo</span>
                    <span>Tipo</span>
                    <span>Tamanho</span>
                    <span style="text-align:center;">NULL ok</span>
                    <span style="text-align:center;">Required</span>
                    <span style="text-align:center;">Auto Inc</span>
                    <span style="text-align:center;">Chave P.</span>
                    <span style="text-align:center;">Chave E.</span>
                    <span></span>
                </div>

                <div class="gig-field-builder" id="gig-field-builder"></div>

                <button type="button" class="gig-btn gig-btn-ghost" onclick="gigAddFieldRow()" style="margin-top:10px;">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Adicionar campo
                </button>

                <input type="hidden" name="schema_json" id="gig-schema-json">
            </div>

            <div class="gig-modal-footer">
                <button type="button" class="gig-btn gig-btn-ghost" onclick="gigCloseModal()">Cancelar</button>
                <button type="submit" class="gig-btn gig-btn-primary" onclick="gigBuildSchema()">Guardar Tabela</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<script>
(function(){

/* ---------- Checkbox bulk selection ---------- */
function bindCheckboxes() {
    document.querySelectorAll('.gig-checkbox-all').forEach(function(master) {
        var scope = master.dataset.scope;
        master.addEventListener('change', function() {
            var all = document.querySelectorAll('.gig-row-check[data-scope="'+scope+'"]');
            all.forEach(function(cb) {
                cb.checked = master.checked;
                syncCheckStyle(cb);
            });
            syncMasterStyle(master);
            updateBulkBar(scope);
        });
        syncMasterStyle(master);
    });

    document.querySelectorAll('.gig-row-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            syncCheckStyle(cb);
            var scope = cb.dataset.scope;
            updateBulkBar(scope);
        });
    });
}

function syncCheckStyle(cb) {
    if ( cb.checked ) {
        cb.style.background = 'var(--gig-accent)';
        cb.style.borderColor = 'var(--gig-accent)';
    } else {
        cb.style.background = 'var(--gig-bg)';
        cb.style.borderColor = 'var(--gig-border-2)';
    }
}

function syncMasterStyle(master) {
    var scope = master.dataset.scope;
    var total   = document.querySelectorAll('.gig-row-check[data-scope="'+scope+'"]').length;
    var checked = document.querySelectorAll('.gig-row-check[data-scope="'+scope+'"]:checked').length;
    master.indeterminate = checked > 0 && checked < total;
    master.checked       = total > 0 && checked === total;
    syncCheckStyle(master);
}

function updateBulkBar(scope) {
    var count  = document.querySelectorAll('.gig-row-check[data-scope="'+scope+'"]:checked').length;
    var barId  = scope === 'tables' ? 'gig-tables-bulk-bar' : 'gig-records-bulk-bar';
    var cntId  = scope === 'tables' ? 'gig-tables-bulk-count' : 'gig-records-bulk-count';
    var bar    = document.getElementById(barId);
    var cntEl  = document.getElementById(cntId);
    if (!bar) return;
    bar.classList.toggle('visible', count > 0);
    if (cntEl) cntEl.textContent = count + (scope==='tables' ? ' tabela(s) selecionadas' : ' registo(s) selecionados');
    var master = document.querySelector('.gig-checkbox-all[data-scope="'+scope+'"]');
    if (master) syncMasterStyle(master);
}

/* ---------- Modal ---------- */
window.gigOpenCreateTable = function() {
    document.getElementById('gig-modal-title').textContent = 'Nova Tabela';
    document.getElementById('gig-table-label').value = '';
    document.getElementById('gig-table-key').value  = '';
    document.getElementById('gig-table-key').readOnly = false;
    document.getElementById('gig-field-builder').innerHTML = '';
    gigAddFieldRow({ name:'id', type:'MEDIUMINT', size:'9', required:true, auto_increment:true, primary_key:true });
    gigAddFieldRow();
    document.getElementById('gig-table-modal').classList.add('open');
};

window.gigOpenEditTable = function(key) {
    // Fetch table data via meta tables PHP array
    var tables = <?php echo wp_json_encode( array_map(function($t){ return ['key'=>$t->table_key,'label'=>$t->table_label,'schema'=>json_decode($t->schema_json,true)]; }, $all_tables) ); ?>;
    var found  = tables.find(function(t){ return t.key === key; });
    if (!found) return;

    document.getElementById('gig-modal-title').textContent = 'Editar Tabela: ' + found.label;
    document.getElementById('gig-table-label').value = found.label;
    document.getElementById('gig-table-key').value   = found.key;
    document.getElementById('gig-table-key').readOnly = true;
    document.getElementById('gig-field-builder').innerHTML = '';
    (found.schema || []).forEach(function(f){ gigAddFieldRow(f); });
    document.getElementById('gig-table-modal').classList.add('open');
};

window.gigCloseModal = function() {
    document.getElementById('gig-table-modal').classList.remove('open');
};

document.getElementById('gig-table-modal').addEventListener('click', function(e){
    if (e.target === this) gigCloseModal();
});

/* ---------- Field builder ---------- */
var typeOptions = ['VARCHAR','TEXT','LONGTEXT','INT','MEDIUMINT','BIGINT','TINYINT','DECIMAL','FLOAT','DATETIME','DATE','BOOLEAN'];

window.gigAddFieldRow = function(defaults) {
    defaults = defaults || {};
    var d = document.getElementById('gig-field-builder');
    var row = document.createElement('div');
    row.className = 'gig-field-row';

    function mk(tag, attrs, text) {
        var el = document.createElement(tag);
        Object.keys(attrs||{}).forEach(function(k){ el.setAttribute(k, attrs[k]); });
        if (text !== undefined) el.textContent = text;
        return el;
    }

    // Name
    var nameIn = mk('input', {type:'text', class:'gig-input gig-input-sm', placeholder:'campo_nome'});
    if (defaults.name) nameIn.value = defaults.name;
    if (defaults.auto_increment) nameIn.readOnly = true;
    row.appendChild(nameIn);

    // Type
    var typeS = mk('select', {class:'gig-input gig-input-sm'});
    typeOptions.forEach(function(t){
        var o = mk('option', {value:t}, t);
        if (defaults.type && defaults.type.toUpperCase() === t) o.selected = true;
        typeS.appendChild(o);
    });
    if (defaults.auto_increment) typeS.disabled = true;
    row.appendChild(typeS);

    // Size
    var sizeIn = mk('input', {type:'number', class:'gig-input gig-input-sm', placeholder:'–', min:'0', max:'65535'});
    if (defaults.size) sizeIn.value = defaults.size;
    row.appendChild(sizeIn);

    // Checkboxes helper
    function mkCb(checked, locked) {
        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;justify-content:center;align-items:center;';
        var cb = mk('input', {type:'checkbox', class:'gig-checkbox-field', style:'appearance:none;width:14px;height:14px;border:1px solid var(--gig-border-2);border-radius:3px;background:var(--gig-bg);cursor:pointer;'});
        if (checked) { cb.checked = true; cb.style.background='var(--gig-accent)'; cb.style.borderColor='var(--gig-accent)'; }
        if (locked) cb.disabled = true;
        cb.addEventListener('change', function(){
            cb.style.background   = cb.checked ? 'var(--gig-accent)' : 'var(--gig-bg)';
            cb.style.borderColor  = cb.checked ? 'var(--gig-accent)' : 'var(--gig-border-2)';
        });
        wrap.appendChild(cb);
        return { wrap, cb };
    }

    var nullable = mkCb(!defaults.required && !defaults.auto_increment, false);
    var required = mkCb(!!defaults.required || !!defaults.auto_increment, !!defaults.auto_increment);
    var autoinc  = mkCb(!!defaults.auto_increment, !!defaults.auto_increment);
    var primaryk = mkCb(!!defaults.primary_key, !!defaults.auto_increment);
    var foreignk = mkCb(!!defaults.foreign_key, false);

    row.appendChild(nullable.wrap);
    row.appendChild(required.wrap);
    row.appendChild(autoinc.wrap);
    row.appendChild(primaryk.wrap);
    row.appendChild(foreignk.wrap);

    // Delete button
    var del = document.createElement('button');
    del.type = 'button';
    del.className = 'gig-btn gig-btn-danger gig-btn-icon';
    del.style.padding = '5px 7px';
    del.innerHTML = '<svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
    if (defaults.auto_increment) del.disabled = true;
    del.addEventListener('click', function(){ d.removeChild(row); });
    row.appendChild(del);

    // Store references for schema build
    row._nameIn  = nameIn;
    row._typeS   = typeS;
    row._sizeIn  = sizeIn;
    row._nullable  = nullable.cb;
    row._required  = required.cb;
    row._autoinc   = autoinc.cb;
    row._primaryk  = primaryk.cb;
    row._foreignk  = foreignk.cb;

    d.appendChild(row);
};

// Auto-slug table key from label
document.getElementById('gig-table-label').addEventListener('input', function(){
    var keyEl = document.getElementById('gig-table-key');
    if (!keyEl.readOnly) {
        keyEl.value = this.value.toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'');
    }
});

window.gigBuildSchema = function() {
    var rows   = document.querySelectorAll('#gig-field-builder .gig-field-row');
    var schema = [];
    rows.forEach(function(row){
        if (!row._nameIn) return;
        var name = row._nameIn.value.trim();
        if (!name) return;
        schema.push({
            name:          name,
            type:          row._typeS.value,
            size:          row._sizeIn.value || '',
            required:      row._required.checked,
            auto_increment:row._autoinc.checked,
            primary_key:   row._primaryk.checked,
            foreign_key:   row._foreignk.checked,
            nullable:      row._nullable.checked,
        });
    });
    document.getElementById('gig-schema-json').value = JSON.stringify(schema);
    return true;
};

/* ---- Init ---- */
bindCheckboxes();

})();
</script>
