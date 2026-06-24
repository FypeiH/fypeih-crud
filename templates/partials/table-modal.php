<div class="fyp-modal-backdrop" id="fyp-table-modal">
    <div class="fyp-modal" style="max-width:960px;">
        <div class="fyp-modal-header">
            <span class="fyp-modal-title" id="fyp-modal-title">Nova Tabela</span>
            <button type="button" class="fyp-modal-close" onclick="fypCloseModal()">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <form id="fyp-table-form" method="post">
            <?php wp_nonce_field('fyp_crud_save_table'); ?>
            <input type="hidden" name="fyp_action" value="save_table">

            <div class="fyp-modal-body">
                <div class="fyp-form-row cols-2" style="margin-bottom:20px;">
                    <div class="fyp-field">
                        <label class="fyp-label">Nome da Tabela *</label>
                        <input type="text" id="fyp-table-label" name="table_label" class="fyp-input" required placeholder="ex: Contactos">
                    </div>
                    <div class="fyp-field">
                        <label class="fyp-label">Chave / Identificador *</label>
                        <input type="text" id="fyp-table-key" name="table_key" class="fyp-input" required placeholder="ex: contactos" pattern="[a-z0-9_]+" title="Apenas letras minúsculas, números e underscores">
                        <span style="font-size:10px;color:var(--fyp-text-3);margin-top:4px;">Apenas a-z, 0-9, _</span>
                    </div>
                </div>

                <div style="margin-bottom:10px;">
                    <label class="fyp-label">Campos da Tabela</label>
                </div>

                <!-- Field builder header -->
                <div class="fyp-field-row-header">
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

                <div class="fyp-field-builder" id="fyp-field-builder"></div>

                <button type="button" class="fyp-btn fyp-btn-ghost" onclick="fypAddFieldRow()" style="margin-top:10px;">
                    <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Adicionar campo
                </button>

                <input type="hidden" name="schema_json" id="fyp-schema-json">
            </div>

            <div class="fyp-modal-footer">
                <button type="button" class="fyp-btn fyp-btn-ghost" onclick="fypCloseModal()">Cancelar</button>
                <button type="submit" class="fyp-btn fyp-btn-primary" onclick="fypBuildSchema()">Guardar Tabela</button>
            </div>
        </form>
    </div>
</div>