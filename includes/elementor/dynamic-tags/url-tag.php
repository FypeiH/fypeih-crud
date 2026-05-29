<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Gigantic_CRUD_URL_Tag extends \Elementor\Core\DynamicTags\Tag {

    public function get_name()  { return 'gig-crud-url'; }
    public function get_title() { return 'Gigantic CRUD URL'; }
    public function get_group() { return 'gigantic-crud'; }

    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
    }

    private function get_tables_options(): array {
        $options = [ '' => '— Seleciona tabela —' ];
        foreach ( gig_crud_get_all_meta_tables() as $t ) {
            $options[ $t->table_key ] = $t->table_label;
        }
        return $options;
    }

    private function get_fields_options( string $table_key ): array {
        $options = [ '' => '— Seleciona campo —' ];
        if ( ! $table_key ) return $options;
        $meta = gig_crud_get_meta_table( $table_key );
        if ( ! $meta ) return $options;
        $schema = json_decode( $meta->schema_json, true );
        if ( ! is_array( $schema ) ) return $options;
        foreach ( $schema as $f ) {
            if ( ! empty( $f['name'] ) ) $options[ $f['name'] ] = ucfirst( $f['name'] );
        }
        return $options;
    }

    protected function register_controls() {
        $saved_table = (string) ( $this->get_settings( 'table_key' ) ?? '' );

        $this->add_control( 'table_key', [
            'label'   => 'Tabela',
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_tables_options(),
            'default' => '',
        ]);

        $this->add_control( 'record_id', [
            'label'   => 'ID do Registo',
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => '',
        ]);

        $this->add_control( 'field_name', [
            'label'   => 'Campo com o URL',
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_fields_options( $saved_table ),
            'default' => '',
        ]);
    }

    public function render() {
        $table_key = sanitize_key( $this->get_settings( 'table_key' ) );
        $record_id = absint( $this->get_settings( 'record_id' ) );
        $field     = sanitize_key( $this->get_settings( 'field_name' ) );

        if ( ! $table_key || ! $record_id || ! $field ) return;

        $record = gig_crud_get_record( $table_key, $record_id );
        if ( $record && isset( $record->{ $field } ) ) {
            echo esc_url( $record->{ $field } );
        }
    }
}