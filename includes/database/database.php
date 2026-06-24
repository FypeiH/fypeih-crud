<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once FYPEIH_CRUD_PATH . 'includes/database/meta-tables.php';
require_once FYPEIH_CRUD_PATH . 'includes/database/schema-sync.php';
require_once FYPEIH_CRUD_PATH . 'includes/database/records.php';
require_once FYPEIH_CRUD_PATH . 'includes/database/sql-runner.php';
require_once FYPEIH_CRUD_PATH . 'includes/database/external-tables.php';

/* =====================================================================
   ACTIVATION
===================================================================== */

function fyp_crud_create_table() {
    fyp_crud_create_meta_table();
}
