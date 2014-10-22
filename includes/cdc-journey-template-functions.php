<?php
/**
 * @package   CC Add CDC Digital Journey Tab
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

/**
* The CDC "backpack" is a pane in the journey tab where we assemble the following:
* 	• docs the user has associated with the group
* 	• maps & reports the user has associated with the group
* 	• Group docs that the user has bookmarked
*/
function cdc_backpack_content() {
	echo cdc_get_backpack_content();
}
    function cdc_get_backpack_content() {
    	$group_id = bp_get_current_group_id();
    	$user_id = get_current_user_id();

    	if ( ! $group_id || ! $user_id )
    		return false;

    	// Get the user's bp_docs
    	$docs_args = array( 'group_id' =>  $group_id, 'author_id' => $user_id, 'status' => 'publish',  );
    	echo '<h3>Library Items</h3>';

        if ( bp_docs_has_docs( $docs_args ) ) :
            echo '<ul id="library-items" class="item-list">';
            while ( bp_docs_has_docs() ) : 
                bp_docs_the_doc();
                ?>
                    <li>
                    	<div class="item">
                    		<div class="item-title">
    		                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    		                    <span class="date"> | <?php the_date(); ?> </span>
    	                    </div>
    	                    <div class="item-desc">
    	                    	<p><?php the_excerpt(); ?></p>
    						</div>
                        </div>
                    </li>
                <?php
            endwhile;
            echo '</ul>';
        else:
        	echo "No library items to display.";
        endif;


        // Get the user's Maps & Reports
        $items_to_fetch = array( 'map', 'report' );
        foreach ($items_to_fetch as $item_type) {
        	echo '<h3>Saved ' . ucfirst( $item_type ) . 's</h3>';
            $displayed_an_item = false;

        	$results = '';
        	// If we can't fetch the items, or none exist, skip.
            if ( function_exists('commons_library_pane_get_saved_maps_reports_for_user') )
                $results = commons_library_pane_get_saved_maps_reports_for_user( $user_id, $item_type );

        	echo '<ul id="saved-' . $item_type . '" class="item-list">';
	    	foreach ( $results as $item ) {
    			if ( stripos( $item['sharing'], (string) $group_id ) === false )
					continue;

	    		cdc_saved_map_report_output( $item, $group_id );
                // If we actually displayed a result, note it.
                $displayed_an_item = true;
	    	}

            if ( ! $displayed_an_item )
                echo '<li><div class="item-desc">No saved ' . $item_type . 's to display.</div></li>';

            echo '</ul>';
        } // End foreach $items_to_fetch

        // Get the user's bookmarked bp_docs
        $favorites = get_user_meta( $user_id, 'cdc_backpack_favorites', true);
        echo '<h3>Bookmarked Library Items</h3>';

        if ( ! empty( $favorites ) ) {
        	$bookmarks_args = array( 'post__in' => $favorites, 'post_type' => 'bp_doc' );
            $bookmarks = new WP_Query( $bookmarks_args );
            // print_r($bookmarks);

            if ( $bookmarks->have_posts() ) {
                echo '<ul id="bookmarked-items" class="item-list">';
                while ( $bookmarks->have_posts() ) {
                    $bookmarks->the_post();
                    ?>
                    <li>
                        <div class="item">
                            <div class="item-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <span class="date"> | <?php echo get_the_date(); ?> </span>
                            </div>
                            <div class="item-desc">
                                <p><?php the_excerpt(); ?></p>
                            </div>
                        </div>
                    </li>
                    <?php
                }
                echo '</ul>';
            }

        } else {
        	echo "No bookmarked library items to display.";
        }
    }

function cdc_saved_map_report_output( $item, $group_id ) {
	// Only show this item if it is shared to the group
	$sharing = $item['sharing'];
	$item_description = !empty($item['description']) ? apply_filters('the_content', $item['description'] ) : '';
	$date = date("F d, Y", strtotime( $item['savedate'] ));

	$group_id_arg = $group_id ? '&groupid=' . $group_id : ''; 
	?>

	<li class="" id="saved-map-<?php echo $item['id']; ?>">
		<!-- <pre>
		<?php //print_r($item) ?>
		</pre> -->
		<div class="item">
		    <div class="item-title"><a href="<?php echo $item['link'] . $group_id_arg; ?>"><?php echo $item['title']; ?></a>&emsp;<span class="date">|&emsp;<?php echo $date; ?></span> <br /><?php 
		    ?></div>
		    <div class="item-desc"><?php echo $item_description; ?></div>
		</div>
	</li>
<?php
}