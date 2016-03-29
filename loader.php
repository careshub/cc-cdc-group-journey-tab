<?php
/*
Plugin Name: CC Add CDC Digital Journey Tab
Description: Adds "Digital Journey" access and other functionality
Version: 0.1
*/

/**
 * @package   CC Add CDC Digital Journey Tab
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

/**
* The class_exists() check is recommended, to prevent problems during upgrade
* or when the Groups component is disabled
*/

// Define a constant that can be checked to see if the component is installed or not.
define( 'CC_CDC_DIGITAL_JOURNEY_IS_INSTALLED', 1 );

// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'CC_CDC_DIGITAL_JOURNEY_VERSION', '.1' );

// Define a constant that we can use to construct file paths throughout the component
define( 'CC_CDC_DIGITAL_JOURNEY_PLUGIN_DIR', dirname( __FILE__ ) );

/* Define a constant that will hold the database version number that can be used for upgrading the DB
 *
 * NOTE: When table defintions change and you need to upgrade,
 * make sure that you increment this constant so that it runs the install function again.
 *
 * Also, if you have errors when testing the component for the first time, make sure that you check to
 * see if the table(s) got created. If not, you'll most likely need to increment this constant as
 * BP_EXAMPLE_DB_VERSION was written to the wp_usermeta table and the install function will not be
 * triggered again unless you increment the version to a number higher than stored in the meta data.
 */
define ( 'CC_CDC_DIGITAL_JOURNEY_DB_VERSION', '1' );

/* Only load the component if BuddyPress is loaded and initialized. */
function startup_cdc_digital_journey_tab() {
	require( dirname( __FILE__ ) . '/includes/cdc-journey-functions.php' );
	require( dirname( __FILE__ ) . '/includes/cdc-journey-template-functions.php' );
	require( dirname( __FILE__ ) . '/includes/class-cdc-journey.php' );

	add_action( 'bp_include', array( 'CC_CDC_Journey', 'get_instance' ), 22 );
}
add_action( 'bp_include', 'startup_cdc_digital_journey_tab' );