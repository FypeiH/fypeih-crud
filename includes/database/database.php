<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once GIGANTIC_CRUD_PATH . 'includes/database/meta-tables.php';
require_once GIGANTIC_CRUD_PATH . 'includes/database/schema-sync.php';
require_once GIGANTIC_CRUD_PATH . 'includes/database/records.php';
require_once GIGANTIC_CRUD_PATH . 'includes/database/sql-runner.php';

/* =====================================================================
   ACTIVATION
===================================================================== */

function gig_crud_create_table() {
    gig_crud_create_meta_table();
}
