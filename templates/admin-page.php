<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require FYPEIH_CRUD_PATH . 'templates/partials/setup.php';
?>

<div class="wrap fyp-crud-admin">
    <div class="fyp-shell">

        <?php require FYPEIH_CRUD_PATH . 'templates/partials/sidebar.php'; ?>

        <main class="fyp-main">

            <?php require FYPEIH_CRUD_PATH . 'templates/partials/topbar.php'; ?>

            <?php require FYPEIH_CRUD_PATH . 'templates/partials/notices.php'; ?>

            <!-- Content -->
            <div class="fyp-content">
                <?php
                    if ( $fyp_tab === 'tables' ) {
                        require FYPEIH_CRUD_PATH . 'templates/partials/tables-tab.php';
                    } elseif ( $fyp_tab === 'records' ) {
                        require FYPEIH_CRUD_PATH . 'templates/partials/records-tab.php';
                    } elseif ( $fyp_tab === 'sql' ) {
                        require FYPEIH_CRUD_PATH . 'templates/partials/sql-tab.php';
                    }
                ?>
            </div>
        </main>
    </div>
</div>

<?php require FYPEIH_CRUD_PATH . 'templates/partials/table-modal.php'; ?>