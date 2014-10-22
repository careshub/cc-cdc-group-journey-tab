<?php
/**
 * @package   CC Add CDC Digital Journey Tab
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 *
 * @package CC Group Narratives
 * @author  David Cavins
 */
class CC_CDC_Journey {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'cdc-journey';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Add the ability to "favorite" docs created by group admins
		add_action( 'bp_docs_single_doc_meta', array( $this, 'add_favoriting_mechanism' ), 60 );

		// Handle AJAX favorite/unfavorite requests.
		add_action( 'wp_ajax_cdc_favorite_doc', array( $this, 'ajax_cdc_favorite_doc' ) );
		add_action( 'wp_ajax_cdc_favorite_doc', array( $this, 'ajax_cdc_favorite_doc' ) );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	
	/**
	* Add the ability to "favorite" docs created by group admins
	* @since 1.0.0
	*/
	public function add_favoriting_mechanism() {
		// Only continue if the user is logged in
		if ( ! $user_id = get_current_user_id() )
			return;

		// Fire only if the doc is associated with the CDC group and has the tag 'dch-backpack'
		$doc_id = get_the_ID();
		if ( cdc_get_target_group_id() != bp_docs_get_associated_group_id( $doc_id ) )
			return;

		// Don't rely on the tag name or slug being reliable. Use the tag ID.
		$target_tag_id = cdc_get_target_tag_id();
		$tag_found = false;
		$tags = wp_get_post_terms( $doc_id, 'bp_docs_tag' );
		foreach ( $tags as $tag ) {
			if ( $tag->term_id == $target_tag_id )
				$tag_found = true;
		}

		if ( ! $tag_found )
			return;

		// If we're still going, then the doc is a match and we should add the favorite/unfavorite button
		echo '<div class="cdc-favorite-button-container">';
			$this->favorite_button_output( $doc_id, $user_id );
		echo '</div>';

		$this->favorite_button_javascript( $doc_id );
	}

	/**
	* Build the html for the favorite button
	* @since 1.0.0
	*/
	public function favorite_button_output( $doc_id = 0, $user_id = 0 ) {
		$status = $this->has_user_favorited_this_doc( $doc_id, $user_id );

		if ( ! $status ) {
			echo '<a href="#" id="backpack-favorite" class="cdc-backpack-favorite button">Add this item to my backpack<a>';
		} else {
			echo '<a href="#" id="backpack-unfavorite" class="cdc-backpack-favorite button">Remove this item from my backpack<a>';
		}
	}

	/**
	* Script required to send/receive javascript requests
	* @since 1.0.0
	*/
	public function favorite_button_javascript( $doc_id ) {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var quick_click = false;

				jQuery('.cdc-favorite-button-container').on('click', 'a', function( e ){
					e.preventDefault();

					// Defeat quick clickers.
					if ( quick_click ) 
						return;

				    quick_click = true;
					var favorite = jQuery( this ).attr( 'id' );
					jQuery.post(
					    ajaxurl, 
					    {
					        'action': 'cdc_favorite_doc',
					        'favorite_action': favorite,
					        'doc_id': <?php echo json_encode( $doc_id ); ?>,
					        '_wpnonce': <?php echo json_encode( wp_create_nonce( "cdc-favorite" ) ); ?>,
					    }, 
					    function(response){
					    	quick_click = false;
					        jQuery( '.cdc-favorite-button-container' ).html( response );
					    }
					);				
				});

			});

		</script>
		<?php 
	}

	/**
	* Check to see if this doc is in the user's favorite meta array
	* @since 1.0.0
	*/
	public function has_user_favorited_this_doc( $doc_id = 0, $user_id ) {
		if ( ! $doc_id || ! $user_id)
			return false;

		$status = false;
		$favorites = get_user_meta( $user_id, 'cdc_backpack_favorites', true );

		foreach ($favorites as $favorite) {
			if ( $favorite == $doc_id )
				$status = true;
		}
		return $status;
	}

	public function ajax_cdc_favorite_doc() {
		// See if this is a valid request
		check_ajax_referer( 'cdc-favorite' );

		$doc_id = isset( $_POST['doc_id'] ) ? intval( $_POST['doc_id'] ) : 0;
		$action = isset( $_POST['favorite_action'] ) ? ( $_POST['favorite_action'] ) : 'backpack-favorite';
		$user_id = get_current_user_id();

		if ( ! ( $doc_id || $user_id ) )
			die( 'Not enough info to go on.' );

		$success = $this->modify_user_favorites( $user_id, $doc_id, $action );

		$this->favorite_button_output( $doc_id, $user_id );

		die();
	}

	public function modify_user_favorites( $user_id, $doc_id, $action ) {
		$favorites = get_user_meta( $user_id, 'cdc_backpack_favorites', true);

		if ( $action == 'backpack-favorite' ) {
			// Only add the term if there are no terms or the term isn't in the list.
			if ( ! is_array( $favorites ) || array_search( $doc_id, $favorites ) === false )
				$favorites[] = $doc_id;
		} else {
			$key_to_delete = array_search( $doc_id, $favorites );
			// Watch out for 0 being a valid return value!
			if ( $key_to_delete !== false )
				unset( $favorites[ $key_to_delete ] );
		}

		$success = update_user_meta( $user_id, 'cdc_backpack_favorites', $favorites );

		return $success;
	}

}