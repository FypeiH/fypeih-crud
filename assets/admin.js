(function(){

/* ---------- Checkbox bulk selection ---------- */
function bindCheckboxes() {
    document.querySelectorAll('.fyp-checkbox-all').forEach(function(master) {
        var scope = master.dataset.scope;
        master.addEventListener('change', function() {
            var all = document.querySelectorAll('.fyp-row-check[data-scope="'+scope+'"]');
            all.forEach(function(cb) {
                cb.checked = master.checked;
                syncCheckStyle(cb);
            });
            syncMasterStyle(master);
            updateBulkBar(scope);
        });
        syncMasterStyle(master);
    });

    document.querySelectorAll('.fyp-row-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            syncCheckStyle(cb);
            var scope = cb.dataset.scope;
            updateBulkBar(scope);
        });
    });
}

function syncCheckStyle(cb) {
    if ( cb.checked ) {
        cb.style.background = 'var(--fyp-accent)';
        cb.style.borderColor = 'var(--fyp-accent)';
    } else {
        cb.style.background = 'var(--fyp-bg)';
        cb.style.borderColor = 'var(--fyp-border-2)';
    }
}

function syncMasterStyle(master) {
    var scope = master.dataset.scope;
    var total   = document.querySelectorAll('.fyp-row-check[data-scope="'+scope+'"]').length;
    var checked = document.querySelectorAll('.fyp-row-check[data-scope="'+scope+'"]:checked').length;
    master.indeterminate = checked > 0 && checked < total;
    master.checked       = total > 0 && checked === total;
    syncCheckStyle(master);
}

function updateBulkBar(scope) {
    var count  = document.querySelectorAll('.fyp-row-check[data-scope="'+scope+'"]:checked').length;
    var barId  = scope === 'tables' ? 'fyp-tables-bulk-bar' : 'fyp-records-bulk-bar';
    var cntId  = scope === 'tables' ? 'fyp-tables-bulk-count' : 'fyp-records-bulk-count';
    var bar    = document.getElementById(barId);
    var cntEl  = document.getElementById(cntId);
    if (!bar) return;
    bar.classList.toggle('visible', count > 0);
    if (cntEl) cntEl.textContent = count + (scope==='tables' ? ' tabela(s) selecionadas' : ' registo(s) selecionados');
    var master = document.querySelector('.fyp-checkbox-all[data-scope="'+scope+'"]');
    if (master) syncMasterStyle(master);
}

/* ---------- Modal ---------- */
window.fypOpenCreateTable = function() {
    document.getElementById('fyp-modal-title').textContent = 'Nova Tabela';
    document.getElementById('fyp-table-label').value = '';
    document.getElementById('fyp-table-key').value  = '';
    document.getElementById('fyp-table-key').readOnly = false;
    document.getElementById('fyp-field-builder').innerHTML = '';
    fypAddFieldRow({ name:'id', type:'MEDIUMINT', size:'9', required:true, auto_increment:true, primary_key:true });
    fypAddFieldRow();
    document.getElementById('fyp-table-modal').classList.add('open');
};

window.fypOpenEditTable = function(key) {
    // Fetch table data via meta tables PHP array
    var tables = window.GigCrudAdmin?.tables || [];
    var found  = tables.find(function(t){ return t.key === key; });
    if (!found) return;

    document.getElementById('fyp-modal-title').textContent = 'Editar Tabela: ' + found.label;
    document.getElementById('fyp-table-label').value = found.label;
    document.getElementById('fyp-table-key').value   = found.key;
    document.getElementById('fyp-table-key').readOnly = true;
    document.getElementById('fyp-field-builder').innerHTML = '';
    (found.schema || []).forEach(function(f){ fypAddFieldRow(f); });
    document.getElementById('fyp-table-modal').classList.add('open');
};

window.fypCloseModal = function() {
    document.getElementById('fyp-table-modal').classList.remove('open');
};

var modal = document.getElementById('fyp-table-modal');

if (modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            fypCloseModal();
        }
    });
}

/* ---------- Field builder ---------- */
var typeOptions = ['VARCHAR','TEXT','LONGTEXT','INT','MEDIUMINT','BIGINT','TINYINT','DECIMAL','FLOAT','DATETIME','DATE','BOOLEAN','IMAGE'];

window.fypAddFieldRow = function(defaults) {
    defaults = defaults || {};
    var d = document.getElementById('fyp-field-builder');
    var row = document.createElement('div');
    row.className = 'fyp-field-row';

    function mk(tag, attrs, text) {
        var el = document.createElement(tag);
        Object.keys(attrs||{}).forEach(function(k){ el.setAttribute(k, attrs[k]); });
        if (text !== undefined) el.textContent = text;
        return el;
    }

    // Name
    var nameIn = mk('input', {type:'text', class:'fyp-input fyp-input-sm', placeholder:'campo_nome'});
    if (defaults.name) nameIn.value = defaults.name;
    if (defaults.auto_increment) nameIn.readOnly = true;
    row.appendChild(nameIn);

    // Type
    var typeS = mk('select', {class:'fyp-input fyp-input-sm'});
    typeOptions.forEach(function(t){
        var o = mk('option', {value:t}, t);
        if (defaults.type && defaults.type.toUpperCase() === t) o.selected = true;
        typeS.appendChild(o);
    });
    if (defaults.auto_increment) typeS.disabled = true;
    row.appendChild(typeS);

    // Size
    var sizeIn = mk('input', {type:'number', class:'fyp-input fyp-input-sm', placeholder:'–', min:'0', max:'65535'});
    if (defaults.size) sizeIn.value = defaults.size;
    row.appendChild(sizeIn);

    // Checkboxes helper
    function mkCb(checked, locked) {
        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;justify-content:center;align-items:center;';
        var cb = mk('input', {type:'checkbox', class:'fyp-checkbox-field', style:'appearance:none;width:14px;height:14px;border:1px solid var(--fyp-border-2);border-radius:3px;background:var(--fyp-bg);cursor:pointer;'});
        if (checked) { cb.checked = true; cb.style.background='var(--fyp-accent)'; cb.style.borderColor='var(--fyp-accent)'; }
        if (locked) cb.disabled = true;
        cb.addEventListener('change', function(){
            cb.style.background   = cb.checked ? 'var(--fyp-accent)' : 'var(--fyp-bg)';
            cb.style.borderColor  = cb.checked ? 'var(--fyp-accent)' : 'var(--fyp-border-2)';
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
    del.className = 'fyp-btn fyp-btn-danger fyp-btn-icon';
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
var tableLabel = document.getElementById('fyp-table-label');

if (tableLabel) {
    tableLabel.addEventListener('input', function() {
        var keyEl = document.getElementById('fyp-table-key');

        if (keyEl && !keyEl.readOnly) {
            keyEl.value = this.value.toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'');
        }
    });
}

window.fypBuildSchema = function() {
    var rows   = document.querySelectorAll('#fyp-field-builder .fyp-field-row');
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
    document.getElementById('fyp-schema-json').value = JSON.stringify(schema);
    return true;
};

/* ---- Init ---- */
bindCheckboxes();

})();


/* ================================================================
   MEDIA UPLOADER — campos do tipo image
================================================================ */
function fypInitMediaFields() {
    document.querySelectorAll('.fyp-media-field').forEach(function(wrap) {
        if (wrap.dataset.fypMediaInit) return; // já inicializado
        wrap.dataset.fypMediaInit = '1';

        var input   = wrap.querySelector('.fyp-media-input');
        var preview = wrap.querySelector('.fyp-media-preview');
        var btnOpen = wrap.querySelector('.fyp-media-btn-open');
        var btnClear= wrap.querySelector('.fyp-media-btn-clear');

        btnOpen.addEventListener('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
                title:    'Selecionar Imagem',
                button:   { text: 'Usar esta imagem' },
                multiple: false,
                library:  { type: 'image' },
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                input.value = attachment.id; // guarda o ID

                // Preview
                var url = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;
                preview.innerHTML = '<img src="' + url + '" style="max-width:80px;max-height:60px;border-radius:4px;object-fit:cover;">';
                btnClear.style.display = 'inline-flex';
            });

            frame.open();
        });

        btnClear.addEventListener('click', function(e) {
            e.preventDefault();
            input.value = '';
            preview.innerHTML = '';
            btnClear.style.display = 'none';
        });
    });
}

// Corre no load inicial e sempre que a tab de records for renderizada
document.addEventListener('DOMContentLoaded', fypInitMediaFields);
