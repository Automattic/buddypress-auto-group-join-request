<?php
/**
 * Integration tests bootstrap file.
 *
 * @package Automattic\BuddyPressAutoGroupJoinRequest\Tests\Integration
 */

use Yoast\WPTestUtils\WPIntegration;

// Composer autoloader.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Load the Yoast WP integration helper functions.
require_once dirname( __DIR__, 2 ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

// Get the path to the WordPress test library.
$_tests_dir = WPIntegration\get_path_to_wp_test_dir();

if ( empty( $_tests_dir ) ) {
	echo 'ERROR: Could not find WordPress test library directory.' . PHP_EOL;
	echo 'Make sure wp-env is running: npm run wp-env start' . PHP_EOL;
	exit( 1 );
}

// Get access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// BuddyPress only turns on its core components by default; the plugin needs groups and profile fields.
tests_add_filter(
	'bp_active_components',
	static function ( $components ) {
		return array_merge(
			(array) $components,
			array(
				'groups'   => 1,
				'xprofile' => 1,
			)
		);
	}
);

tests_add_filter(
	'muplugins_loaded',
	static function () {
		// wp-env installs BuddyPress alongside this plugin (see .wp-env.json).
		require WP_PLUGIN_DIR . '/buddypress/bp-loader.php';
		require dirname( __DIR__, 2 ) . '/buddypress-auto-group-join-request.php';
	}
);

// Make sure the Composer autoload file has been generated.
WPIntegration\check_composer_autoload_exists();

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

/*
 * The WordPress test installer drops every table, BuddyPress's included, and
 * recreates only core's, so create BuddyPress's tables for the active components.
 */
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once buddypress()->plugin_dir . 'bp-core/admin/bp-core-admin-schema.php';
bp_core_install();

/*
 * BuddyPress adds its emails' unsubscribe salt when it installs the emails, which
 * bp_core_install() doesn't do. Without it, notifying a group admin of a join request
 * passes null to hash_hmac(), which is deprecated.
 */
bp_update_option( 'bp-emails-unsubscribe-salt', base64_encode( wp_generate_password( 64, true, true ) ) );

/*
 * Register the custom autoloader to overload the PHPUnit MockObject classes when running on PHP 8.
 *
 * This function has to be called _last_, after the WP test bootstrap to make sure it registers
 * itself in FRONT of the Composer autoload (which also prepends itself to the autoload queue).
 */
WPIntegration\register_mockobject_autoloader();
