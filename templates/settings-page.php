<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$saved = false;

?>

<div class="wrap gr-wrap">

	<h1 class="gr-page-title">Definições — GiganticCRUD</h1>

	<?php if ( $saved ) : ?>
		<div class="gr-notice gr-notice-success">
			<div class="gr-notice-icon">✓</div>
			<div class="gr-notice-content">
				<strong>Sucesso</strong>
				<span>Definições guardadas.</span>
			</div>
		</div>
	<?php endif; ?>

	<div class="gr-widget" style="max-width:520px;">
		<div class="gr-widget-head">
			<h3>Database Settings</h3>
		</div>

		<div class="gr-widget-body">
			<form method="post">
				<?php wp_nonce_field( 'gig_settings_nonce' ); ?>

				<?php submit_button( 'Guardar', 'primary', 'gig_save_settings' ); ?>
			</form>
		</div>
	</div>

</div>