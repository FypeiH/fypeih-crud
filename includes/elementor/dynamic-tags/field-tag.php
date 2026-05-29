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
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }

    protected function register_controls() {
        $this->add_control(
            'table_key',
            [
                'label' => 'Tabela',
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'record_id',
            [
                'label' => 'ID do Registo',
                'type' => \Elementor\Controls_Manager::NUMBER,
            ]
        );

        $this->add_control(
            'field_name',
            [
                'label' => 'Campo',
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );
    }

    public function render() {
        $table_key = sanitize_key( $this->get_settings( 'table_key' ) );
        $record_id = absint( $this->get_settings( 'record_id' ) );
        $field     = sanitize_key( $this->get_settings( 'field_name' ) );

        if ( ! $table_key || ! $record_id || ! $field ) {
            return;
        }

        $record = gig_crud_get_record( $table_key, $record_id );

        if ( $record && isset( $record->{$field} ) ) {
            echo esc_html( $record->{$field} );
        }
    }
}