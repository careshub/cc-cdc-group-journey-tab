<?php 
/**
 * @package   CC Add CDC Digital Journey Tab
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

if ( class_exists( 'BP_Group_Extension' ) ) {

	class CDC_Digital_Journey_Group_Extension extends BP_Group_Extension {
		/**
		* Your __construct() method will contain configuration options for 
		* your extension, and will pass them to parent::init()
		*/
		function __construct() {

				$args = array(
				'slug' => 'digital-journey',
				'name' => 'Journey',
				'access' => 'member', // @since BuddyPress 2.1.
				'show_tab' => $this->enabled_for_group( bp_get_current_group_id() ) ? 'member' : 'noone', // @since BuddyPress 2.1
				'nav_item_position' => 31,
           		'screens' => array(
	                'edit' => array(
	                    'enabled' => false,
	                ),
	                'create' => array(
	                    'enabled' => false,
	                ),
	                'admin' => array(
	                    'enabled' => false,
	                ),
	            ), 
				// 'nav_item_name' => bcg_get_tab_label(),
				);        
	        	parent::init( $args );

		}

		/**
		* display() contains the markup that will be displayed on the main 
		* plugin tab
		*/
		function display() {
			?>
			<div id="subnav" class="item-list-tabs no-ajax">
				<ul class="nav-tabs">
					<li <?php if( $this->is_home() ) { echo 'class="current"'; } ?>><a href="<?php echo $this->get_home_url();?>">Digital Journey</a></li>
					<li <?php if( $this->is_resources() ) { echo 'class="current"'; } ?>><a href="<?php echo $this->get_resources_url();?>">Resources</a></li>
					<li <?php if( $this->is_backpack() ) { echo 'class="current"'; } ?>><a href="<?php echo $this->get_backpack_url();?>">Backpack</a></li>
				</ul>
			</div>
			<?php
			if ( $this->is_home() ) {
				// echo '<p><a href="http://www.communitycommons.org/cdc-dch-v2/" class="button">Start a new Journey</a></p>';

				$main_tab_content = get_post( cdc_get_journey_source_page_id(), 'OBJECT' );

				echo apply_filters( 'the_content', $main_tab_content->post_content );
				edit_post_link( "Edit this page&rsquo;s content", '<p>', '</p>', cdc_get_journey_source_page_id() );

			} else if ( $this->is_resources() ) {
				// echo "This is the resources page";
				$resources_tab_content = get_post( cdc_get_resources_source_page_id(), 'OBJECT' );

				echo apply_filters( 'the_content', $resources_tab_content->post_content );
				edit_post_link( "Edit this page&rsquo;s content", '<p>', '</p>', cdc_get_resources_source_page_id() );

			} else if ( $this->is_backpack() ) {
				// echo "This is the resources page";
				cdc_backpack_content();
			}
			// global $bp;
			// echo "<pre>";
			// print_r($bp);
			// echo "</pre>";
		}

		/**
		* settings_screen() is the catch-all method for displaying the content 
		* of the edit, create, and Dashboard admin panels
		*/
		function settings_screen( $group_id = null ) {
			//Not used
			return false;
		}

		/**
		* settings_sceren_save() contains the catch-all logic for saving 
		* settings from the edit, create, and Dashboard admin panels
		*/
		function settings_screen_save( $group_id = null ) {
			//Not used
			return false;
		}

		function enabled_for_group( $group_id ) {
			//This should only be enabled for the CDC group, which has a group ID of 
			$setting = ( $group_id == cdc_get_target_group_id() ) ? true : false;
			return apply_filters('cdc_digital_journey_enabled_for_group', $setting);
		}


		//Helpers
		function is_current_component(){
		    $bp = buddypress();
		    if ( bp_is_current_component( $bp->groups->slug ) && bp_is_current_action( 'digital-journey' ) )
		        return true;
		    
		    return false;
		}
		function is_home(){
		    $bp = buddypress();
		    if ($this->is_current_component() && empty( $bp->action_variables[0] ) )
		         return true;
		}
		function is_resources(){
		    if ( $this->is_current_component() && bp_is_action_variable( 'resources', 0 ) )
		         return true;
		}
		function is_backpack(){
		    if ( $this->is_current_component() && bp_is_action_variable( 'backpack', 0 ) )
		         return true;
		}
		function get_home_url(){
			return apply_filters('cdc_dj_home_url',  bp_get_group_permalink( groups_get_current_group() ) . 'digital-journey/');
		}
		function get_resources_url(){
			return apply_filters('cdc_dj_resources_url',  bp_get_group_permalink( groups_get_current_group() ) . 'digital-journey/resources');
		}
		function get_backpack_url(){
			return apply_filters('cdc_dj_backpack_url',  bp_get_group_permalink( groups_get_current_group() ) . 'digital-journey/backpack');
		}

	}
bp_register_group_extension( 'CDC_Digital_Journey_Group_Extension' );
} // if ( class_exists( 'BP_Group_Extension' ) )