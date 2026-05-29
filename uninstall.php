<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'gig_pagespeed_api_key' );
delete_option( 'gigantic_pagespeed_mobile_latest' );
delete_option( 'gigantic_pagespeed_desktop_latest' );

global $wpdb;

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE %s
		    OR option_name LIKE %s",
		'_transient_gigantic_psi_%',
		'_transient_timeout_gigantic_psi_%'
	)
);