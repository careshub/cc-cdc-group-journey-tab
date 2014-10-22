<?php
/**
 * @package   CC Add CDC Digital Journey Tab
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

// Is this the correct group for this site?
function cdc_get_target_group_id() {
	$home_url = get_home_url();
	switch ( $home_url ) {
		case 'http://commonsdev.local':
			$target_group_id = 538;
			break;
		case 'http://www.communitycommons.org':
		case 'http://staging.communitycommons.org':
			$target_group_id = 36;
			break;
		case 'http://dev.communitycommons.org':
			$target_group_id = 30;
			break;
		default:
			$target_group_id = -1;
			break;
	}
	return $target_group_id;
}

// What's our target bp-doc tag taxonomy term id?
// This determines which 
function cdc_get_target_tag_id() {
	$home_url = get_home_url();
	switch ( $home_url ) {
		case 'http://commonsdev.local':
			$target_tag_id = 12883;
			break;
		case 'http://www.communitycommons.org':
			$target_tag_id = 58232;
			break;
		case 'http://staging.communitycommons.org':
			$target_tag_id = 57853;
			break;
		default:
			$target_tag_id = -1;
			break;
	}
	return $target_tag_id;
}

//What's the id of the source pages for the "journey" and resources subnav?
function cdc_get_journey_source_page_id() {
	$home_url = get_home_url();
	switch ( $home_url ) {
		case 'http://commonsdev.local':
			$target_page_id = 19385;
			break;
		case 'http://www.communitycommons.org':
			$target_page_id = 35054;
			break;
		case 'http://staging.communitycommons.org':
			$target_page_id = 31995;
			break;
		default:
			$target_page_id = -1;
			break;
	}
	return $target_page_id;
}

function cdc_get_resources_source_page_id() {
	$home_url = get_home_url();
	switch ( $home_url ) {
		case 'http://commonsdev.local':
			$target_page_id = 19388;
			break;
		case 'http://www.communitycommons.org':
			$target_page_id = 35217;
			break;
		case 'http://staging.communitycommons.org':
			$target_page_id = 31993;
			break;
		default:
			$target_page_id = -1;
			break;
	}
	return $target_page_id;
}
