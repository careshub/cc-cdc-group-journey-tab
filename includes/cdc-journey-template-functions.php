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

/*
 * Shortcodes used on the journey pages for the above-content and below-content wrapper.
 */
function cdc_dch_top() {
?>
<style type="text/css">
.popup {z-index:10; width:700px; height:600px; background-color:#d3d3d3;border:solid 5px #bebebe; margin:0 auto;padding:15px;display:none;
  position:fixed;top:50%; left:50%; margin-left:-350px; margin-top:-300px;}
.closex {float:right;cursor:pointer;color:#0645AD;}
.closex:hover { TEXT-DECORATION: underline; font-weight:bold; }
.overlay {
    display: none; /* ensures it’s invisible until it’s called */
    position: absolute; /* makes the div go into a position that’s absolute to the browser viewing area */
    left: 35%; /* positions the div half way horizontally */
    top: 75%; /* positions the div half way vertically */
    padding: 25px;
    border: 2px solid black;
    background-color: #ffffff;
    width: 30%;
    height: 30%;
    z-index: 100; /* makes the div the top layer, so it’ll lay on top of the other content */
}
#fade {
    display: none;  /* ensures it’s invisible until it’s called */
    position: fixed;  /* makes the div go into a position that’s absolute to the browser viewing area */
    left: 0%; /* makes the div span all the way across the viewing area */
    top: 0%; /* makes the div span all the way across the viewing area */
    background-color: black;
    -moz-opacity: 0.7; /* makes the div transparent, so you have a cool overlay effect */
    opacity: .70;
    filter: alpha(opacity=70);
    width: 100%;
    height: 100%;
    z-index: 90; /* makes the div the second most top layer, so it’ll lay on top of everything else EXCEPT for divs with a higher z-index (meaning the #overlay ruleset) */
}
.gf_progressbar_title {display:none;}
#comments {display:none;}
</style>

<div id="overlay1" class="overlay">
    <div class="closex"  onclick="javascript:closediv('1');return false;">[X] close</div>
    <div>
        <h2>Reflection Point One - Resources</h2>
        <p>
            <ul>
                <li><a href="<?php echo content_url( 'uploads/2013/06/guidingprinciples.pdf' ); ?>" target="_blank">Guiding Principles</a></li>
                <li><a href="<?php echo content_url( 'uploads/2013/06/practices.pdf' ); ?>" target="_blank">Recommended Practices for Enhancing Community Health Improvement</a></li>
            </ul>
        </p>
    </div>
    <input type="button" value="Next" id="next1" onclick="nextPg('6','1')" />
</div>
<div id="overlay2" class="overlay">
    <div class="closex"  onclick="javascript:closediv('2');return false;">[X] close</div>
    <div>
        <h2>Reflection Point Two - Resources</h2>
        <p>
            <ul>
                <li>Resource 1</li>
                <li>Resource 2</li>
            </ul>
        </p>
    </div>
    <input type="button" value="Next" id="next2" onclick="nextPg('9','2')" />
</div>
<div id="overlay3" class="overlay">
    <div class="closex"  onclick="javascript:closediv('3');return false;">[X] close</div>
    <div>
        <h2>Reflection Point Three - Resources</h2>
        <p>
            <ul>
                <li>Resource 1</li>
                <li>Resource 2</li>
            </ul>
        </p>
    </div>
    <input type="button" value="Next" id="next3" onclick="nextPg('11','3')" />
</div>
<div id="fade"></div>

<script type="text/javascript">
    var $j = jQuery.noConflict();
    var baseurl = window.location.host;
    var formid = 9;
    if (baseurl == "dev.communitycommons.org") {
        formid = 10;
    }

    function navpg(x){
        if (x.value=='/groups/cdc-dch/') {
            location.href=x.value;
        } else {
            $j("#gform_target_page_number_" + formid).val(x.value); $j("#gform_" + formid).trigger("submit",[true]);
            $j("#steps").val(x.value);
        }
    }
    function navpg2(x){
        if (x=='/groups/cdc-dch/') {
            location.href=x;
        } else {
            $j("#gform_target_page_number_" + formid).val(x); $j("#gform_" + formid).trigger("submit",[true]);
            $j("#steps").val(x);
        }
    }
    function nextPg(x,y) {
        $j("#gform_target_page_number_" + formid).val(x); $j("#gform_" + formid).trigger("submit",[true]);
        $j("#steps").val(x);
        closediv(y);
    }

    $j(document).bind('gform_post_render', function(event, form_id, current_page){
        //alert("Mike's test - PAGE:" + current_page);

        if (current_page == 5)
        {
            $j("#steps").val(4);
            $j('input[name=input_56]').change(function() {
                var value9 = $j('input[name=input_9]:checked').val();
                var value20 = $j('input[name=input_20]:checked').val();
                var value108 = $j('input[name=input_108]:checked').val();
                var value19 = $j('input[name=input_19]:checked').val();
                var value18 = $j('input[name=input_18]:checked').val();
                var value17 = $j('input[name=input_17]:checked').val();
                var value16 = $j('input[name=input_16]:checked').val();
                var value15 = $j('input[name=input_15]:checked').val();
                var value14 = $j('input[name=input_14]:checked').val();
                var value13 = $j('input[name=input_13]:checked').val();
                var value12 = $j('input[name=input_12]:checked').val();
                var value56 = $j('input[name=input_56]:checked').val();
                if (value9 < 3 || value20 < 3 || value108 < 3 || value19 < 3 || value18 < 3 || value17 < 3 || value16 < 3 || value15 < 3 || value14 < 3 || value13 < 3 || value12 < 3 || value56 < 3) {
                    $j("#overlay1").fadeIn();
                    $j("#fade").fadeIn();
                }
            });

        } else if (current_page == 8) {
            $j("#steps").val(7);
            $j('input[name=input_29]').change(function() {
                var value28 = $j('input[name=input_28]:checked').val();
                var value32 = $j('input[name=input_32]:checked').val();
                var value31 = $j('input[name=input_31]:checked').val();
                var value30 = $j('input[name=input_30]:checked').val();
                var value29 = $j('input[name=input_29]:checked').val();
                if (value28 < 3 || value32 < 3 || value31 < 3 || value30 < 3 || value29 < 3) {
                    //$j("#overlay2").fadeIn();
                    //$j("#fade").fadeIn();
                }
            });
        } else if (current_page == 10) {
            $j("#steps").val(9);
            $j('input[name=input_64]').change(function() {
                var value59 = $j('input[name=input_59]:checked').val();
                var value61 = $j('input[name=input_61]:checked').val();
                var value62 = $j('input[name=input_62]:checked').val();
                var value63 = $j('input[name=input_63]:checked').val();
                var value64 = $j('input[name=input_64]:checked').val();
                if (value59 < 3 || value61 < 3 || value62 < 3 || value63 < 3 || value64 < 3) {
                    //$j("#overlay3").fadeIn();
                    //$j("#fade").fadeIn();
                }
            });
        } else {
            $j("#steps").val(current_page);
        }



    });

    $j(document).ready(function()
    {
        if (!getUrlVars()["navpg"]) {

        } else {
            var pg = getUrlVars()["navpg"];

            navpg2(pg);
        }
        $j('#tacticbtn').click(function () {
            window.open('https://www.communitiestransforming.org/');
        });
    });

    function getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    function closediv(y) {
        $j("#overlay" + y).fadeOut();
        $j("#fade").fadeOut();
    }


</script>
<?php



    $pt1 = '<div style="width:800px; text-align:left;position:relative;top:-20px;"><img src="http://www.communitycommons.org/wp-content/uploads/2013/08/banner2.jpg" alt="Community Health Improvement Journey" style="box-shadow: 0px 0px 0px #ffffff;"></div>';

    return $pt1;

}

add_shortcode( 'cdc_dch_top', 'cdc_dch_top' );

function cdc_dch_bot() {
    $a = array(
        'Group Home' => '/groups/cdc-dch/',
        'Community Health Improvement Process Overview' => 1,
        'Step 1: Health Equity' => 2,
        'Step 2. Strong Coalitions' => 3,
        'Step 3. Define Community' => 4,
        'Step 4. Explore Community Needs' => 6,
        'Step 5. Setting Priorities' => 7,
        'Step 6. Intervention Selection' => 9,
        'Step 7. Align Community Assets' => 11,
        'Step 8. Implement Interventions' => 12,
        'Step 9. Evaluation and Monitoring' => 13,
        'Name your Journey' => 14,
        'Journey Summary' => 15
    );

    $pt1 = '<div style="float:right;text-align:left;position:relative;top:-60px;">Navigate to:<br /><select style="font-family:Calibri,Arial;" onchange="javascript:navpg(this);" id="steps">';
    $pt2 = '';
    foreach($a as $option => $val) {
        $pt2 = $pt2 . "<option value='".$val."'>".$option."</option>";
    }
    $pt3 = '</select><br /><a href="/cdc-dch-journey-feedback/" target="_blank" class="button" style="margin-top:10px;">Send us your feedback!</a></div>';
    $mb = $pt1.$pt2.$pt3;
    return $mb;
}

add_shortcode( 'cdc_dch_bot', 'cdc_dch_bot' );