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

        ?>
        <p class="message info">Welcome to your Community Health Improvement Journey Backpack!  Here you will find everything you&rsquo;ve saved along your journey including resources, Target Intervention Areas, maps, CHNA reports, files you&rsquo;ve uploaded into the Hub Library, and Check-in &amp; Evaluate checklists you&rsquo;ve submitted.  Just click on any title below to view and open what you&rsquo;ve saved to your backpack.</p>
        <?php

        // Get the user's Maps & Reports
        $items_to_fetch = array( 'map', 'report' );
        foreach ($items_to_fetch as $item_type) {
            echo '<div class="backpack-section">';
            	$results = '';
            	// If we can't fetch the items, or none exist, skip.
                if ( function_exists('commons_group_library_pane_get_saved_maps_reports_for_user_in_group') ) {
                    $results = commons_group_library_pane_get_saved_maps_reports_for_user_in_group( $group_id, $user_id, $item_type );
                }
                echo '<h3><a href="#" class="toggle-link">Saved ' . ucfirst( $item_type ) . 's</a> <span class="count">' . count( $results ) . '</span></h3>';

            	echo '<ul id="saved-' . $item_type . '" class="item-list toggleable">';
                if ( $results ) {
        	    	foreach ( $results as $item ) {
            			cdc_saved_map_report_output( $item, $group_id );
        	    	}
                } else {
                    echo '<li><div class="item-desc">No saved ' . $item_type . 's to display.</div></li>';
                }
                echo '</ul>';
            echo '</div>';

        } // End foreach $items_to_fetch

        // Get the user's saved areas
        $areas = '';
        // If we can't fetch the items, or none exist, skip.
        if ( function_exists('commons_group_library_pane_get_saved_areas_for_user_in_group') ) {
            $areas = commons_group_library_pane_get_saved_areas_for_user_in_group( $group_id, $user_id );
        }
        echo '<div class="backpack-section">';
            echo '<h3><a href="#" class="toggle-link">Saved Areas</a> <span class="count">' . count( $areas ) . '</span></h3>';
            echo '<ul id="saved-areas" class="item-list toggleable">';
            if ( $areas ) {
                foreach ( $areas as $item ) {
                    // echo '<pre>'; var_dump($item); echo '</pre>';
                    cdc_saved_map_report_output( $item, $group_id );
                }
            } else {
                echo '<li><div class="item-desc">No saved areas to display.</div></li>';
            }
            echo '</ul>';
        echo '</div>';


        // Get the user's bp_docs
        $docs_args = array( 'group_id' =>  $group_id, 'author_id' => $user_id, 'status' => 'publish',  );
        echo '<div class="backpack-section">';
            if ( bp_docs_has_docs( $docs_args ) ) :
                echo '<h3><a href="#" class="toggle-link">My Files</a> <span class="count">' . bp_docs_get_total_docs_num() . '</span></h3>';
                echo '<ul id="library-items" class="item-list toggleable">';
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
            else:
                echo '<h3><a href="#" class="toggle-link">My Files</a> <span class="count">0</span></h3>';
                echo '<ul id="library-items" class="item-list toggleable">';
                echo '<li><div class="item-desc">No library items to display.</div></li>';
            endif;
            echo '</ul>';
        echo '</div>';



        // Get the user's bookmarked bp_docs
        echo '<div class="backpack-section">';
            $favorites = get_user_meta( $user_id, 'cdc_backpack_favorites', true);
            $favorites = ! empty( $favorites ) ? $favorites : array( 0 );
            $bookmarks_args = array( 'post__in' => $favorites, 'post_type' => 'bp_doc' );
            $bookmarks = new WP_Query( $bookmarks_args );

            $found_bookmarks = $bookmarks->found_posts ? $bookmarks->found_posts : 0;
            echo '<h3><a href="#" class="toggle-link">Saved Resources</a> <span class="count">' . $found_bookmarks . '</span></h3>';
            echo '<ul id="bookmarked-items" class="item-list toggleable">';

            if ( $bookmarks->have_posts() ) {
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
            } else {
            	echo '<li><div class="item-desc">No bookmarked library items to display.</div></li>';
            }
            echo '</ul>';
        echo '</div>';


        // Get the GF checklists that the user has submitted
        if ( class_exists('GFAPI') ) : ?>
        <div class="backpack-section">
            <h3><a href="#" class="toggle-link">Completed Evaluation Checklists</a></h3>
            <ul class='completed-checklists item-list toggleable'>
            <?php
            $showed_forms = false;
            // Prepare the search criteria for Gravity Forms calls
            $search_criteria["field_filters"][] = array( "key" => "created_by", value => $user_id );
            $forms = array( 'intro' => array(
                                'id' => 23,
                                'label' => 'Introduction: Check-In &amp; Evaluate',
                                'url' => 'introduction-check-in-and-evaluate',
                                ),
                            'assess' => array(
                                'id' => 24,
                                'label' => 'Assess Needs &amp; Resources',
                                'url' => 'assess-needs-and-resources-check-in-and-evaluate',
                                ),
                            'focus' => array(
                                'id' => 25,
                                'label' => 'Focus on What&rsquo;s Important',
                                'url' => 'focus-on-whats-important-check-and-evaluate',
                                ),
                            'choose' => array(
                                'id' => 26,
                                'label' => 'Choose Effective Policies and Programs',
                                'url' => 'choose-effective-policies-and-programs-check-in-evaluate',
                                ),
            );
            foreach ($forms as $key => $value) :
                if ( $entry = GFAPI::get_entries( $value['id'], $search_criteria) ) :
                    $date = date("F d, Y", strtotime( $entry[0]['date_created'] ));
                    ?>
                    <li>
                        <div class="item">
                            <div class="item-title">
                                <a href="<?php echo home_url( $value['url'] ); ?>"><?php echo $value['label']; ?></a>
                                <span class="date"> | <?php echo $date; ?> </span>
                            </div>
                        </div>
                    </li>
                    <?php
                    $showed_forms = true;
                endif;
            endforeach;
            if ( ! $showed_forms ) {
                echo '<li><div class="item-desc">No completed forms to show.</div></li>';
            } ?>
            </ul>
        </div>
        <?php
        endif; // class_exists('GFAPI')
        ?>
        <?php
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
		    <div class="item-title"><a href="<?php echo $item['link'] . $group_id_arg; ?>"><?php echo $item['title']; ?></a>&emsp;<span class="date">|&emsp;<?php echo $date; ?></span></div>
		    <div class="item-desc"><?php echo $item_description; ?></div>
		</div>
        <?php
        if ( $item['itemtype'] == 'area' ) {
            ?>
            <div class="action">
                <a href="<?php echo $item['demoreport']; ?>" class="button" style="margin-bottom:.5em;">Open Summary Report</a>
                <a href="<?php echo $item['chnareport']; ?>" class="button">Open CHNA Report</a>
            </div>
            <?php
        }
        ?>
	</li>
<?php
}