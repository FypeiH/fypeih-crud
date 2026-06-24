<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renderiza o input correto consoante o tipo do campo.
 *
 * @param array  $field      — definição do campo (name, type, required, ...)
 * @param string $input_name — atributo name do input
 * @param mixed  $value      — valor atual
 * @param string $form_id    — atributo form (para inputs fora do form, ex: add row)
 */
function fyp_crud_render_field_input( array $field, string $input_name, $value = '', string $form_id = '' ): string {

    $type     = strtoupper( $field['type'] ?? 'VARCHAR' );
    $required = ! empty( $field['required'] ) ? 'required' : '';
    $form_attr= $form_id ? 'form="' . esc_attr( $form_id ) . '"' : '';
    $name_attr= 'name="' . esc_attr( $input_name ) . '"';
    $val      = esc_attr( $value );

    if ( $type === 'IMAGE' ) {
        return fyp_crud_render_image_input( $input_name, $value, $form_id, $required );
    }

    if ( $type === 'TEXT' || $type === 'LONGTEXT' ) {
        return '<textarea ' . $name_attr . ' ' . $form_attr . ' class="fyp-input fyp-input-sm" rows="2" ' . $required . '>' . esc_textarea( $value ) . '</textarea>';
    }

    if ( $type === 'BOOLEAN' || $type === 'TINYINT' ) {
        $checked = $value ? 'checked' : '';
        return '<input type="checkbox" ' . $name_attr . ' ' . $form_attr . ' value="1" ' . $checked . ' ' . $required . ' style="width:16px;height:16px;cursor:pointer;">';
    }

    if ( $type === 'INT' || $type === 'MEDIUMINT' || $type === 'BIGINT' || $type === 'DECIMAL' || $type === 'FLOAT' ) {
        return '<input type="number" ' . $name_attr . ' ' . $form_attr . ' class="fyp-input fyp-input-sm" value="' . $val . '" ' . $required . '>';
    }

    if ( $type === 'DATE' ) {
        return '<input type="date" ' . $name_attr . ' ' . $form_attr . ' class="fyp-input fyp-input-sm" value="' . $val . '" ' . $required . '>';
    }

    if ( $type === 'DATETIME' ) {
        return '<input type="datetime-local" ' . $name_attr . ' ' . $form_attr . ' class="fyp-input fyp-input-sm" value="' . esc_attr( str_replace( ' ', 'T', $value ) ) . '" ' . $required . '>';
    }

    // Default — VARCHAR e outros
    return '<input type="text" ' . $name_attr . ' ' . $form_attr . ' class="fyp-input fyp-input-sm" value="' . $val . '" ' . $required . '>';
}

/**
 * Renderiza o campo de imagem com preview e botão do Media Uploader.
 */
function fyp_crud_render_image_input( string $input_name, $value, string $form_id, string $required ): string {
    $form_attr  = $form_id ? 'form="' . esc_attr( $form_id ) . '"' : '';
    $attachment_id = absint( $value );
    $preview_html  = '';

    if ( $attachment_id ) {
        $img = wp_get_attachment_image( $attachment_id, 'thumbnail', false, [
            'style' => 'max-width:80px;max-height:60px;border-radius:4px;object-fit:cover;',
        ] );
        $preview_html = $img ?: '';
    }

    ob_start();
    ?>
    <div class="fyp-media-field">
        <input type="hidden"
               name="<?php echo esc_attr( $input_name ); ?>"
               <?php echo $form_attr; ?>
               class="fyp-media-input"
               value="<?php echo esc_attr( $value ); ?>"
               <?php echo $required; ?>>

        <div class="fyp-media-preview"><?php echo $preview_html; ?></div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <button type="button" class="fyp-btn fyp-btn-ghost fyp-media-btn-open" style="padding:5px 10px;font-size:10px;">
                <svg width="11" height="11" fill="none" viewBox="0 0 12 12"><rect x="1" y="1" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><circle cx="4" cy="4" r="1" stroke="currentColor" stroke-width="1.1"/><path d="M1 8l3-3 2 2 2-2 3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?php echo $attachment_id ? 'Alterar' : 'Escolher'; ?>
            </button>
            <button type="button" class="fyp-btn fyp-btn-danger fyp-media-btn-clear"
                    style="padding:4px 10px;font-size:10px;<?php echo ! $attachment_id ? 'display:none;' : ''; ?>">
                Remover
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}