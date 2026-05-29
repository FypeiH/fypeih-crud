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