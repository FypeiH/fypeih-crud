<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require GIGANTIC_CRUD_PATH . 'templates/partials/setup.php';
?>

<div class="wrap gig-crud-admin">
    <div class="gig-shell">

        <?php require GIGANTIC_CRUD_PATH . 'templates/partials/sidebar.php'; ?>

        <main class="gig-main">

            <?php require GIGANTIC_CRUD_PATH . 'templates/partials/topbar.php'; ?>

            <?php require GIGANTIC_CRUD_PATH . 'templates/partials/notices.php'; ?>

            <!-- Content -->
            <div class="gig-content">
                <?php
                    if ( $gig_tab === 'tables' ) {
                        require GIGANTIC_CRUD_PATH . 'templates/partials/tables-tab.php';
                    } elseif ( $gig_tab === 'records' ) {
                        require GIGANTIC_CRUD_PATH . 'templates/partials/records-tab.php';
                    } elseif ( $gig_tab === 'sql' ) {
                        require GIGANTIC_CRUD_PATH . 'templates/partials/sql-tab.php';
                    }
                ?>
            </div>
        </main>
    </div>
</div>

<?php require GIGANTIC_CRUD_PATH . 'templates/partials/table-modal.php'; ?>