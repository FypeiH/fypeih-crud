<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Gigantic_CRUD_Field_Tag extends \Elementor\Core\DynamicTags\Tag {

    public function get_name() {
        return 'gig-crud-field';
    }

    public function get_title() {
        return 'Gigantic CRUD Field';
    }

    public function get_group() {
        return 'gigantic-crud';
    }

    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    /* ------------------------------------------------------------------
       Helpers
    ------------------------------------------------------------------ */

    /**
     * Devolve todas as tabelas como array para uso nos SELECTs do Elementor.
     * Formato: [ '' => '— Seleciona tabela —', 'key' => 'Label', ... ]
     */
    private function get_tables_options(): array {
        $options = [ '' => '— Seleciona tabela —' ];

        $tables = gig_crud_get_all_meta_tables();
        if ( ! empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $options[ $table->table_key ] = $table->table_label;
            }
        }

        return $options;
    }

    /**
     * Devolve os campos de uma tabela como array para uso no SELECT do Elementor.
     * Formato: [ '' => '— Seleciona campo —', 'nome' => 'nome', ... ]
     *
     * Se $table_key estiver vazio devolve apenas o placeholder.
     */
    private function get_fields_options( string $table_key ): array {
        $options = [ '' => '— Seleciona campo —' ];

        if ( ! $table_key ) {
            return $options;
        }

        $meta = gig_crud_get_meta_table( $table_key );
        if ( ! $meta || empty( $meta->schema_json ) ) {
            return $options;
        }

        $schema = json_decode( $meta->schema_json, true );
        if ( ! is_array( $schema ) ) {
            return $options;
        }

        foreach ( $schema as $field ) {
            $name = $field['name'] ?? '';
            if ( $name === '' ) continue;
            // Label capitalizado, valor é o nome real do campo na DB
            $options[ $name ] = ucfirst( $name );
        }

        return $options;
    }

    /**
     * Devolve todos os registos de uma tabela como array para o SELECT.
     * Formato: [ '' => '— Seleciona registo —', 1 => 'ID 1', ... ]
     *
     * Tenta usar o primeiro campo VARCHAR/TEXT como label legível.
     */
    private function get_records_options( string $table_key ): array {
        $options = [ '' => '— Seleciona registo —' ];

        if ( ! $table_key ) {
            return $options;
        }

        // Descobrir o primeiro campo de texto para usar como label
        $label_field = null;
        $meta = gig_crud_get_meta_table( $table_key );
        if ( $meta && ! empty( $meta->schema_json ) ) {
            $schema = json_decode( $meta->schema_json, true );
            if ( is_array( $schema ) ) {
                $text_types = [ 'VARCHAR', 'TEXT', 'LONGTEXT' ];
                foreach ( $schema as $field ) {
                    $type = strtoupper( $field['type'] ?? '' );
                    if ( in_array( $type, $text_types, true ) && ! empty( $field['name'] ) && $field['name'] !== 'id' ) {
                        $label_field = $field['name'];
                        break;
                    }
                }
            }
        }

        $records = gig_crud_get_records( $table_key );
        if ( ! empty( $records ) ) {
            foreach ( $records as $record ) {
                $id    = $record->id ?? '';
                $label = $label_field && isset( $record->{ $label_field } )
                    ? 'ID ' . $id . ' — ' . wp_trim_words( $record->{ $label_field }, 6, '…' )
                    : 'ID ' . $id;
                $options[ $id ] = $label;
            }
        }

        return $options;
    }

    /* ------------------------------------------------------------------
       Controls
    ------------------------------------------------------------------ */

    protected function register_controls() {

        // Lê a tabela já guardada neste widget para pré-popular os campos
        $saved_table_key = (string) ( $this->get_settings( 'table_key' ) ?? '' );

        // --- TABELA ---
        $this->add_control(
            'table_key',
            [
                'label'   => esc_html__( 'Tabela', 'gigantic-crud-manager' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_tables_options(),
                'default' => '',
            ]
        );

        // --- REGISTO (dropdown dinâmica) ---
        $this->add_control(
            'record_id',
            [
                'label'       => esc_html__( 'Registo', 'gigantic-crud-manager' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $this->get_records_options( $saved_table_key ),
                'default'     => '',
                'description' => esc_html__( 'Guarda a tabela primeiro para ver os registos disponíveis.', 'gigantic-crud-manager' ),
            ]
        );

        // --- CAMPO ---
        $this->add_control(
            'field_name',
            [
                'label'   => esc_html__( 'Campo', 'gigantic-crud-manager' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_fields_options( $saved_table_key ),
                'default' => '',
            ]
        );

        // --- SEPARADOR: opções avançadas ---
        $this->add_control(
            'advanced_heading',
            [
                'label'     => esc_html__( 'Avançado', 'gigantic-crud-manager' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // --- FALLBACK ---
        $this->add_control(
            'fallback',
            [
                'label'       => esc_html__( 'Valor por omissão', 'gigantic-crud-manager' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Texto quando não há valor', 'gigantic-crud-manager' ),
            ]
        );
    }

    /* ------------------------------------------------------------------
       Render
    ------------------------------------------------------------------ */

    public function render() {
        $table_key = sanitize_key( $this->get_settings( 'table_key' ) );
        $record_id = absint( $this->get_settings( 'record_id' ) );
        $field     = sanitize_key( $this->get_settings( 'field_name' ) );
        $fallback  = wp_kses_post( $this->get_settings( 'fallback' ) );

        if ( ! $table_key || ! $record_id || ! $field ) {
            echo $fallback;
            return;
        }

        $record = gig_crud_get_record( $table_key, $record_id );

        if ( $record && isset( $record->{ $field } ) && $record->{ $field } !== '' ) {
            echo esc_html( $record->{ $field } );
        } else {
            echo $fallback;
        }
    }
}