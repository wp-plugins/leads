<?php

/* INCLUDE FILE WHERE MAIN USERFIELDS ARE DEFINED */
include_once('wpl.m.userfields.php');

if (isset($_GET['page'])&&($_GET['page']=='lp_global_settings'&&$_GET['page']=='lp_global_settings'))
{
	add_action('admin_init','wpl_manage_lead_enqueue');
	function wpl_manage_lead_enqueue()
	{		
		wp_enqueue_style('wpl_manage_lead_css', WPL_URL . 'css/admin-global-settings.css');	
	}
}

/* REMOVE DEFAULT METABOXES */
add_filter('default_hidden_meta_boxes', 'wplead_hide_metaboxes', 10, 2);
function wplead_hide_metaboxes($hidden, $screen) 
{

	global $post;
	if ( isset($post) && $post->post_type == 'wp-lead' )
	{
		//print_r($hidden);exit;
		$hidden = array(
			'postexcerpt',
			'slugdiv',
			'postcustom',
			'trackbacksdiv', 
			'commentstatusdiv', 
			'commentsdiv', 
			'authordiv', 
			'revisionsdiv',
			'wpseo_meta',
			'wp-advertisement-dropper-post',
			'postdivrich'
		);
		
	}
	return $hidden;
}

/* REMOVE WYSIWYG */
add_filter( 'user_can_richedit', 'wplead_disable_for_cpt' );
function wplead_disable_for_cpt( $default ) {
    global $post;
    if ( $post->post_type == 'wp-lead' )
	{
      // echo 1; exit;
	   return false;
	}
    return $default;
}



function wp_leads_get_search_keywords($url = '')
{
	// Get the referrer
	//$referrer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';

	// Parse the referrer URL
   
  	$parsed_url = parse_url($url);
  	$host = $parsed_url['host']; // base url
    $se_match = array("google", "yahoo", "bing");

		foreach($se_match as $val) {
		  if (preg_match("/" . $val . "/", $url)){
		  	$is_search_engine = stripslashes($bl);
		  }
		}

	$query_str = (!empty($parsed_url['query'])) ? $parsed_url['query'] : '';
	$query_str = (empty($query_str) && !empty($parsed_url['fragment'])) ? $parsed_url['fragment'] : $query_str;

	// Parse the query string into a query array
	parse_str($query_str, $query);
	$empty_keywords = "Empty Keywords, User is probably logged into " . $is_search_engine;
	// Check some major search engines to get the correct query var
	$search_engines = array(
		'q' => 'alltheweb|aol|ask|ask|bing|google',
		'p' => 'yahoo',
		'wd' => 'baidu'
	);
	foreach ($search_engines as $query_var => $se)
	{
		$se = trim($se);
		preg_match('/(' . $se . ')\./', $host, $matches);
		if (!empty($matches[1]) && !empty($query[$query_var])) {
			return "From". $is_search_engine ." ". $query[$query_var];
		} else {
			return "From". $is_search_engine ." ". $empty_keywords;
		}
	}
	// return false;
}
//echo wp_leads_get_search_keywords('http://www.google.co.th/url?sa=t&rct=j&q=keywordsssss&esrc=s&source=web&cd=4&ved=0CE8QFjAD&url=http%3A%2F%2Fwww.inboundnow.com%2Fhow-to-properly-set-up-wordpress-301-redirects%2F&ei=FMHDUZPqBMztiAfi_YCoBA&usg=AFQjCNFuh3aH04u2Z4xXl2XNb3emE95p5Q&sig2=yrdyyZz83KfGte6SNZL7gA&bvm=bv.48293060,d.aGc');
/* ADD GRAVATAR METABOX TO SIDEBAR */
add_action('add_meta_boxes', 'wplead_display_gravatar_metabox');
function wplead_display_gravatar_metabox() {
	global $post;
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	add_meta_box( 
	'lp-gravatar-sidebar-preview', 
	__( "Quick Stats", 'wplead_metabox_gravatar_preview' ),
	'wplead_gravatar_metabox',
	'wp-lead' , 
	'side', 
	'high' );
}

function leads_time_diff($date1, $date2) {
$time_diff = array();
$diff = abs(strtotime($date2) - strtotime($date1));
$years = floor($diff / (365*60*60*24));
$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
$minutes = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60) / 60);
//$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60));

$time_diff['years'] = $years;
$time_diff['y-text'] = ($years > 1) ? "Years" : "Year";
$time_diff['months'] = $months;
$time_diff['m-text'] = ($months > 1) ? "Months" : "Month";
$time_diff['days'] = $days;
$time_diff['d-text'] = ($days > 1) ? "Days" : "Day";
$time_diff['hours'] = $hours;
$time_diff['h-text'] = ($hours > 1) ? "Hours" : "Hour";
$time_diff['minutes'] = $minutes;
$time_diff['mm-text'] = ($minutes > 1) ? "Minutes" : "Minute"; 

return $time_diff; 
}

function wplead_gravatar_metabox() {
	global $post;
	global $wpdb;
	//print_r($post);
	$lead_creation = $post->post_date;
	//define last touch point
	$query = 'SELECT * FROM '.$wpdb->prefix.'lead_tracking WHERE lead_id = "'.$post->ID.'" AND nature="conversion" ORDER BY id DESC LIMIT 1';
	$result = mysql_query($query);
	if (!$result){ echo $sql; echo mysql_error(); exit; }

	$array = mysql_fetch_array($result);
	
		$date1 = new DateTime($array['date']);
		$final_date1 = $date1->format('Y-m-d G:i:s');
		$date2 = new DateTime(date('Y-m-d G:i:s'));
		$final_date2 = $date2->format('Y-m-d G:i:s');

		$date_obj = leads_time_diff($lead_creation, $final_date2);
		$wordpress_timezone = get_option('gmt_offset');
		$years = $date_obj['years'];
		$months = $date_obj['months'];
		$days = $date_obj['days'];
		$hours = $date_obj['hours'] + $wordpress_timezone;
		$minutes = $date_obj['minutes'];
		$year_text = $date_obj['y-text'];
		$month_text = $date_obj['m-text'];
		$day_text = $date_obj['d-text'];
		$hours_text = $date_obj['h-text'];
		$minute_text = $date_obj['mm-text']; 

		$email = get_post_meta( $post->ID , 'wpleads_email_address', true );
		$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
		$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
		$conversions_count = get_post_meta($post->ID,'wpl-lead-conversion-count', true);
		$page_view_count = get_post_meta($post->ID,'wpl-lead-page-view-count', true);
	?>
	<div>
		<div class="inside" style='margin-left:-8px;text-align:center;'> 
			<div id="quick-stats-box">
			<div id="page_view_total">Total Page Views <span id="p-view-total"><?php echo $page_view_count; ?></span></div>
			<div id="conversion_count_total"># of Conversions <span id="conversion-total"><?php echo $conversions_count; ?></span></div>
			<br><br>
	
			<div id="last_touch_point">Time Since Last Conversion 
				<span id="touch-point">
					
					<?php

					echo "<span class='touchpoint-year'><span class='touchpoint-value'>" . $years . "</span> ".$year_text." </span><span class='touchpoint-month'><span class='touchpoint-value'>" . $months."</span> ".$month_text." </span><span class='touchpoint-day'><span class='touchpoint-value'>".$days."</span> ".$day_text." </span><span class='touchpoint-hour'><span class='touchpoint-value'>".$hours."</span> ".$hours_text." </span><span class='touchpoint-minute'><span class='touchpoint-value'>".$minutes."</span> ".$minute_text."</span> Ago"; 
					?>
				</span>
			</div>

			<div id="time-since-last-visit"></div>
			<div id="lead-score"></div><!-- Custom Before Quick stats and After Hook here for custom fields shown -->
			</div>
				
		</div>	
	</div>
	<?php
}	


/* ADD IP ADDRESS METABOX TO SIDEBAR */
add_action('add_meta_boxes', 'wplead_display_ip_address_metabox');
function wplead_display_ip_address_metabox() {
	global $post;
	add_meta_box( 
	'lp-ip-address-sidebar-preview', 
	__( 'Last Conversion Activity Location', 'wplead_metabox_ip_address_preview' ),
	'wplead_ip_address_metabox',
	'wp-lead' , 
	'side', 
	'low' );
}

function wplead_ip_address_metabox() {
	global $post;

	$ip_address = get_post_meta( $post->ID , 'wpleads_ip_address', true );
	$geo_array = unserialize(wpleads_remote_connect('http://www.geoplugin.net/php.gp?ip='.$ip_address));
	$city = get_post_meta($post->ID, 'wpleads_city', true);		
	$state = get_post_meta($post->ID, 'wpleads_region_name', true);	
	//print_r($geo_array);
	$latitude = $geo_array['geoplugin_latitude'];
	$longitude = $geo_array['geoplugin_longitude'];
	
	?>
	<div >
		<div class="inside" style='margin-left:-8px;text-align:left;'> 
			<div id='last-conversion-box'>
	
						<div id='lead-geo-data-area'>
							
						<?php
						if (is_array($geo_array))
						{
							unset($geo_array['geoplugin_status']);
							unset($geo_array['geoplugin_credit']);
							unset($geo_array['geoplugin_request']);
							unset($geo_array['geoplugin_currencyConverter']);
							unset($geo_array['geoplugin_currencySymbol_UTF8']);
							unset($geo_array['geoplugin_currencySymbol']);
							unset($geo_array['geoplugin_dmaCode']);
							if (isset($geo_array['geoplugin_city']) && $geo_array['geoplugin_city'] != ""){
							echo "<div class='lead-geo-field'><span class='geo-label'>City:</span>" . $geo_array['geoplugin_city'] . "</div>"; }
							if (isset($geo_array['geoplugin_regionName']) && $geo_array['geoplugin_regionName'] != ""){
							echo "<div class='lead-geo-field'><span class='geo-label'>State:</span>" . $geo_array['geoplugin_regionName'] . "</div>";
							}
							if (isset($geo_array['geoplugin_areaCode']) && $geo_array['geoplugin_areaCode'] != ""){
							echo "<div class='lead-geo-field'><span class='geo-label'>Area Code:</span>" . $geo_array['geoplugin_areaCode'] . "</div>";
							}
							if (isset($geo_array['geoplugin_countryName']) && $geo_array['geoplugin_countryName'] != ""){
							echo "<div class='lead-geo-field'><span class='geo-label'>Country:</span>" . $geo_array['geoplugin_countryName'] . "</div>";
							}
							if (isset($geo_array['geoplugin_regionName']) && $geo_array['geoplugin_regionName'] != ""){
							echo "<div class='lead-geo-field'><span class='geo-label'>IP Address:</span>" . $ip_address . "</div>";
							}
							/*
							foreach ($geo_array as $key=>$val)
							{
								$key = str_replace('geoplugin_','',$key);
								echo "<tr class='lp-geo-data'>";
								echo "<td class='lp-geo-key'><em><small>$key</small></em></td>";
								echo "<td class='lp-geo-val'><em><small>$val</small></em></td>";
								echo "</tr>";
							} */
						}
						if (($latitude != 0) && ($longitude != 0)){ 
						echo '<a class="maps-link" href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$latitude.','.$longitude.'&z=12" target="_blank">View Map</a>';	
						echo '<div id="lead-google-map">
								<iframe width="278" height="276" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q='.$latitude.','.$longitude.'&amp;aq=&amp;output=embed&amp;z=11"></iframe>
								</div>'; } else {
									echo "<h2>No Geo data collected</h2>";
								}
						echo '</div></div></div></div>';			
		
	}

/* Bottom SIDEBAR temporarily off 
add_action('add_meta_boxes', 'wplead_display_geo_map_metabox');
function wplead_display_geo_map_metabox() {
	global $post;
	add_meta_box( 
	'lp-geo-map-sidebar', 
	__( ' ', 'wplead_metabox_geo_map_preview' ),
	'wplead_geo_map_metabox',
	'wp-lead' , 
	'side', 
	'low' );
}

function wplead_geo_map_metabox() {
	global $post;
	
	$city = get_post_meta($post->ID, 'wpleads_city', true);		
	$state = get_post_meta($post->ID, 'wpleads_region_name', true);	

	?>
	<div>
		<div class="inside" style='margin-left:-8px;text-align:left;'> 
			<iframe width="259" height="276" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q=<?php echo $city.' '.$state; ?>&amp;aq=&amp;output=embed"></iframe><br /><small><a href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=<?php echo $city.' '.$state; ?>" target='_blank' style="color:#0000FF;text-align:left">View Larger Map</a></small>
		</div>	
	</div>
	<?php
}
*/
 
/* Top Metabox */
add_action( 'edit_form_after_title', 'wp_leads_header_area' );
add_action( 'save_post', 'wp_leads_save_header_area' );

function wp_leads_header_area()
{
   global $post;
	
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name', true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	$lead_status = 'wp_lead_status';
    if ( empty ( $post ) || 'wp-lead' !== get_post_type( $GLOBALS['post'] ) )
        return;

    if ( ! $content = get_post_meta( $post->ID , 'wpleads_first_name',true ) )
        $content = '';

    if ( ! $status_content = get_post_meta( $post->ID, $lead_status, TRUE ) )
        $status_content = '';
    echo "<div id='lead-top-area'>";
    echo "<div id='lead-header'><h1>".$first_name.' '.$last_name. "</h1></div>";

$values = get_post_custom( $post->ID );  
$selected = isset( $values['wp_lead_status'] ) ? esc_attr( $values['wp_lead_status'][0] ) : "";  
    ?>  
	<div id='lead-status'>
        <label for="wp_lead_status">Lead Status:</label>  
        <select name="wp_lead_status" id="wp_lead_status">
        	<option value="Read" <?php selected( $selected, 'Read' ); ?>>Read/Viewed</option>
        	<option value="New Lead" <?php selected( $selected, 'New Lead' ); ?>>New Lead</option>
            <option value="Contacted" <?php selected( $selected, 'Contacted' ); ?>>Contacted</option>
            <option value="Active" <?php selected( $selected, 'Active' ); ?>>Active</option>   
            <option value="Lost" <?php selected( $selected, 'Lost' ); ?>>Disqualified/Lost</option> 
            <option value="Customer" <?php selected( $selected, 'Customer' ); ?>>Customer</option>
            <option value="Archive" <?php selected( $selected, 'Archive' ); ?>>Archive</option>    
            <!-- Action hook here for custom lead status addon -->
        </select>  
    </div>
    <span id="current-lead-status" style="display:none;"><?php echo get_post_meta( $post->ID, $lead_status, TRUE );?></span>
</div>
    <?php 
}
function wp_leads_save_header_area( $post_id )
{
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;

    $key = 'wp_lead_status';

    if ( isset ( $_POST[ $key ] ) )
        return update_post_meta( $post_id, $key, $_POST[ $key ] );

	//echo 1; exit;
    delete_post_meta( $post_id, $key );
}

/* ADD MAIN METABOX */
//Add select template meta box
add_action('add_meta_boxes', 'wplead_add_metabox_main');
function wplead_add_metabox_main() {
	global $post;
	
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	add_meta_box(
		'wplead_metabox_main', // $id
		__( 'Lead Overview', 'wpleads' ),
		'wpleads_display_metabox_main', // $callback
		'wp-lead', // $page
		'normal', // $context
		'high'); // $priority 
}

// Render select template box
function wpleads_display_metabox_main() {
	//echo 1; exit;
	global $post; 
	global $wpleads_user_fields;
	
	//define tabs
	$tabs[] = array('id'=>'wpleads_lead_tab_main','label'=>'Lead Information');
	$tabs[] = array('id'=>'wpleads_lead_tab_conversions','label'=>'Conversions');
	$tabs[] = array('id'=>'wpleads_lead_tab_raw_form_data','label'=>'Raw Form Data');	
	
	$tabs = apply_filters('wpl_lead_tabs',$tabs);
	
	//define open tab
	$active_tab = 'wpleads_lead_tab_main'; 
	if (isset($_REQUEST['open-tab']))
	{
		$active_tab = $_REQUEST['open-tab'];
	}

		
	//print jquery for tab switching
	wpl_manage_lead_js($tabs);
	
	foreach ($wpleads_user_fields as $key=>$field)
	{
			$wpleads_user_fields[$key]['value'] = get_post_meta( $post->ID , $wpleads_user_fields[$key]['name'] ,true );
	}
	
	$wpleads_user_fields = apply_filters('wpleads_user_fields',$wpleads_user_fields);
	
	// Use nonce for verification
	echo "<input type='hidden' name='wplead_custom_fields_nonce' value='".wp_create_nonce('lp-nonce')."' />";
	echo "<input type='hidden' name='open-tab' id='id-open-tab' value='{$active_tab}'>";
	?>
	<div class="metabox-holder split-test-ui">
		<div class="meta-box-sortables ui-sortable">
		<h2 id="lp-st-tabs" class="nav-tab-wrapper">	
			<?php
			foreach ($tabs as $key=>$array)
			{
				?>
				<a  id='tabs-<?php echo $array['id']; ?>' class="wpl-nav-tab nav-tab nav-tab-special<?php echo $active_tab == $array['id'] ? '-active' : '-inactive'; ?>"><?php echo $array['label']; ?></a> 
				<?php
			}
			?>
		</h2>		
		<div class="wpl-tab-display" id='wpleads_lead_tab_main'>
			<div id='toggle-lead-fields'><a class="preview button" href="#" id="show-hidden-fields">Show Hidden/Empty Fields</a></div>
			<?php

			$email = get_post_meta( $post->ID , 'wpleads_email_address', true );
			$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
			$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
			$size = 150;
			$size2 = 36;
			$default = WPL_URL . '/images/gravatar_default_150.jpg';

			$gravatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
			$gravatar2 = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size2;
			$profile_image = apply_filters('wpleads_profile_image',$gravatar);
			?>
			<div id="lead_image">
				<div id="lead_name_overlay"><?php echo $first_name . " " . $last_name;?></div>
					<?php						
						echo'<img src="'.$gravatar.'"  title="'.$first_name.' '.$last_name.'"></a>';						
					?>
			</div>
			<style type="text/css">
			.icon32-posts-wp-lead {background-image: url("<?php echo $gravatar2;?>") !important;}</style>
				
			<?php
			// Custom Before and After Hook here for custom fields shown on main view page
			//print_r($wpleads_user_fields);exit;
			wpleads_render_setting($wpleads_user_fields);

			?>
		</div><!-- end wpleads_metabox_main AKA Tab 1-->
		<div class="wpl-tab-display" id="wpleads_lead_tab_conversions" style="display: <?php if ($active_tab == 'wpleads_lead_tab_conversions') { echo 'block;'; } else { echo 'none;'; } ?>">
			<div id="conversions-data-display">
			<?php 
			$test_array = '{"items":[{"id":"1","current_page":"http://lp.dev/go/test-page/?lp-variation-id=1","timestamp":"2013-5-6 17:8:6","referrer":"http://lp.dev/go/test-page/","original_referrer":"http://lp.dev/go/test-page/"},{"id":2,"current_page":"http://lp.dev/go/test-page/?lp-variation-id=0&codekitCB=393638936.159101","timestamp":"2013-5-6 17:8:56","referrer":"http://lp.dev/go/test-page/"},{"id":3,"current_page":"http://lp.dev/go/test-page/?lp-variation-id=1","timestamp":"2013-5-6 17:9:0","referrer":"http://lp.dev/go/test-page/"}]}';	
			$php_array = json_decode($test_array, true);
				foreach($php_array as  $key=>$val)
				{
					$final_page_count = count($val);
				}
			//print_r($php_array); 
			

			//first add to varation list if not present.
			$conversions_data = get_post_meta($post->ID,'wpleads_conversion_data', true);
			$times = get_post_meta($post->ID,'times', true);
			$conversions = get_post_meta($post->ID,'wpl-lead-conversions', true);
			$conversions_count = get_post_meta($post->ID,'wpl-lead-conversion-count', true);
			$page_view_count = get_post_meta($post->ID,'wpl-lead-page-view-count', true);
			$raw2 =get_post_meta($post->ID,'wpl-lead-conversions', true);
			$raw = get_post_meta($post->ID,'wpleads_conversion_data', true);
			$events = get_post_meta($post->ID,'tracking_event', true);
			$events_triggered = get_post_meta( $post->ID, 'events_triggered', TRUE );
			//echo $events_triggered;
			//echo $raw;
			//echo "count" . $page_view_count;
			//echo $raw2;
			//print_r($conversions_data);
			$the_array = json_decode($conversions_data, true);
			// echo "First id : ". $the_array[1]['id'] . "!"; // Get specific value
			if ($conversions_data) {
			$count = 1;
			foreach($the_array as  $key=>$val)
				{
					$id = $the_array[$count]['id'];
					$title = get_the_title($id);
					$display_location = get_permalink($id);
					$date_raw = new DateTime($the_array[$count]['datetime']);
					//date_format($date, 'F jS, Y \a\t g:ia (l)')
					$date_of_conversion = $date_raw->format('F jS, Y \a\t g:ia (l)');
					//echo $count . ": ". $the_array[$count]['datetime'] . "!<br>";
					echo "<div class='recent-conversion-item'>".$count.". <a href='{$display_location}' id='lead-session-".$count."' rel='".$count."' target='_blank'>{$title}</a><span class='conversion-date'>".$date_of_conversion."</span><span class='view-this-lead-session' rel='".$count."'> <a rel='".$count."' href='#view-session-".$count."'>(view visit path)</a></span></div>";
						foreach ($val as $key => $value) {
							//echo $key . "=" . $value;
						}
					$count++;
				}
			} else {
				echo "<span id='wpl-message-none'>No conversions found!</span>";
			}	
		
			?>
			
			
			</div> <!-- end #raw-data-display AKA Tab 2 -->			
		</div>

		<div class="wpl-tab-display" id="wpleads_lead_tab_raw_form_data" style="display:  <?php if ($active_tab == 'wpleads_lead_tab_raw_form_data') { echo 'block;'; } else { echo 'none;'; } ?>;">
			<div id="raw-data-display">			
			<?php

			//first add to varation list if not present.
			$raw_data = get_post_meta($post->ID,'wpl-lead-raw-post-data', true);
			
			if ($raw_data)
			{
				$raw_data = json_decode($raw_data, true);
				echo "<h2>Form Inputs with Values</h2>";
				echo "<span id='click-to-map'></span>";
				 echo "<div id='wpl-raw-form-data-table'>";
				foreach($raw_data as  $key=>$value)
				{
					?>
					<div class="wpl-raw-data-tr">
						<span class="wpl-raw-data-td-label">
							<?php echo "Input name: <span class='lead-key-normal'>". $key . "</span> &rarr; values:"; ?>
						</span>
						<span class="wpl-raw-data-td-value">
							<?php
							if (is_array($value))
							{
								$value = array_filter($value);
								$num_loop = 1;
								foreach($value as $k=>$v)
								{
									echo "<span class='".$key. "-". $num_loop." possible-map-value'>".$v."</span>";
									$num_loop++;
								}
							}
							else
							{
								echo "<span class='".$key."-1 possible-map-value'>".$value."</span>";
							}
							?>
						</span>
						<span class="map-raw-field"><span class="map-this-text">Map this field to lead</span><span style="display:none;" class='lead_map_select'><select name="NOA" class="field_map_select"></select></span><span class="apply-map button button-primary" style="display:none;">Apply</span></span>
					</div>
				<?php 

				}
				echo "<div id='raw-array'>";
				echo "<h2>Raw Form Data Array</h2>";
				echo "<pre>";
				print_r($raw_data);
				echo "</pre>";
				echo "</div>";
				echo "</div>"; 
			}
			else
			{					
				echo "<span id='wpl-message-none'>No raw data found!</span>";			
			}
		
			?>			
			
			</div> <!-- end #raw-data-display AKA Tab 3 -->			
		</div>
		
		<?php
		do_action('wpl_print_lead_tab_sections');
		?>
		
		</div><!-- end .meta-box-sortables -->
	</div><!-- end .metabox-holder -->
	<?php
}

function wpl_manage_lead_js($tabs)
{		
		
	if (isset($_GET['tab']))
	{
		$default_id = $_GET['tab'];
	}
	else
	{
		$default_id ='main';
	}
		
	?>
	<script type='text/javascript'>
	jQuery(document).ready(function() 
	{		
		jQuery('.wpl-nav-tab').live('click', function() {
		
			var this_id = this.id.replace('tabs-','');
			//alert(this_id);
			jQuery('.wpl-tab-display').css('display','none');
			jQuery('#'+this_id).css('display','block');
			jQuery('.wpl-nav-tab').removeClass('nav-tab-special-active');
			jQuery('.wpl-nav-tab').addClass('nav-tab-special-inactive');
			jQuery('#tabs-'+this_id).addClass('nav-tab-special-active');						
			jQuery('#id-open-tab').val(this_id);
		});
	});			
	</script>
	<?php
}

add_action('save_post', 'wpleads_save_user_fields');
function wpleads_save_user_fields($post_id) {
	global $wpleads_user_fields;
	global $post;
	
	if (!isset($post)||isset($_POST['split_test']))
		return;
		
	if ($post->post_type=='revision' ||  'trash' == get_post_status( $post_id ))
	{
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||$_POST['post_type']=='revision')
	{
		return;
	}
		
	if ($post->post_type=='wp-lead')
	{
		//print_r($$wpleads_user_fields);exit;
		//echo $_POST['lp-selected-template'];
		foreach ($wpleads_user_fields as $key=>$field)
		{	

			$old = get_post_meta($post_id, $field['name'], true);				
			if (isset($_POST[$field['name']])) 
			{
				$new = $_POST[$field['name']];	
			
				if (is_array($new))
				{
					//echo $field['name'];exit;
					array_filter($new);
					$new = implode(';',$new);
					update_post_meta($post_id, $field['name'], $new);
				}
				else if (isset($new) && $new != $old ) {
					update_post_meta($post_id, $field['name'], $new);
				}
				else if ('' == $new && $old) {
					//echo "here";exit;
					delete_post_meta($post_id, $field['name'], $old);
				}
			}
		}	

		
	}
}


/* ADD CONVERSIONS METABOX */

//Add select template meta box
add_action('add_meta_boxes', 'wplead_add_metabox_conversion');
function wplead_add_metabox_conversion() {
	global $post;
	
	add_meta_box(
		'wplead_metabox_conversion', // $id
		__( 'Lead History' , 'wplead_metabox_conversion' ),
		'wpleads_display_metabox_conversion', // $callback
		'wp-lead', // $page
		'normal', // $context
		'high'); // $priority 
}

// Render select template box
function wpleads_display_metabox_conversion() {
	global $post; 
	global $wpdb; 
	
	$query = 'SELECT * FROM '.$wpdb->prefix.'lead_tracking WHERE lead_id = "'.$post->ID.'" AND nature="1" ORDER BY id DESC';
	$result = mysql_query($query);
	if (!$result){ echo $sql; echo mysql_error(); exit; }

	$num_conversion = mysql_num_rows($result);
	if (empty($num_conversion)) {
		echo "<h2 style='background:transparent;'>No Conversions Tracked. This person could have javascript disabled or you have a javascript error on your site.</h2>";
	}
	$array_page_view_total = array();
	while ($array = mysql_fetch_array($result))
	{
		//echo "here";
		
		$old = null;
		$date = date_create($array['date']);
		$data = json_decode( $array['data'] , true);
		
		//echo "<br>";
		$date1 = new DateTime($array['date']);
		$final_date1 = $date1->format('Y-m-d G:i:s');
		$date2 = new DateTime(date('Y-m-d G:i:s'));
		$final_date2 = $date2->format('Y-m-d G:i:s');
		$date_obj = leads_time_diff($final_date1, $final_date2);
		$years = $date_obj['years'];
		$months = $date_obj['months'];
		$days = $date_obj['days'];
		$hours = $date_obj['hours'];
		$minutes = $date_obj['minutes'];

		$year_text = $date_obj['y-text'];
		$month_text = $date_obj['m-text'];
		$day_text = $date_obj['d-text'];
		$hours_text = $date_obj['h-text'];
		$minute_text = $date_obj['mm-text']; 
		$data = $data['items'];
		//print_r($data);exit;
	 
		echo '<a class="session-anchor" id="view-session-'.$num_conversion.'""></a><div id="conversion-tracking" class="wpleads-conversion-tracking-table" summary="Conversion Tracking">
			
			<div class="conversion-tracking-header">
					<h2><strong>Visit '.$num_conversion.'</strong> on <span class="shown_date">'.date_format($date, 'F jS, Y \a\t g:ia (l)').'</span><span class="toggle-conversion-list">-</span></h2> <span class="hidden_date date_'.$num_conversion.'">'.date_format($date, 'F jS, Y \a\t g:ia').'</span>
			</div>';
		echo '<div class="conversion-session-view session_id_'.$num_conversion.'">
			<div class="session-stats">
			<span class="session-stats-header">Session Stats</span>
			
			<span id="session-time-since"><span class="touchpoint-year"><span class="touchpoint-value">' . $years . '</span> '.$year_text.' </span><span class="touchpoint-month"><span class="touchpoint-value">' . $months.'</span> '.$month_text.' </span><span class="touchpoint-day"><span class="touchpoint-value">'.$days.'</span> '.$day_text.' </span><span class="touchpoint-hour"><span class="touchpoint-value">'.$hours.'</span> '.$hours_text.' </span><span class="touchpoint-minute"><span class="touchpoint-value">'.$minutes.'</span> '.$minute_text.'</span> Ago</span>
				<span class="session-head">Event Freshness</span>
				<div id="session-pageviews">
				<span id="pages-view-in-session">10</span>
				<span class="session-head page-view-sess">Pages Viewed in Session</span>
				</div>
			</div>';
			$num_conversion--;	
		
		//print_r($data);exit;
		echo "<div class='leads-visit-list'>";
		$i = 0;
		foreach ($data as $key => $value)
		{		
			
			$array_page_view_total[] = $i;
			($key==0 && $value['referrer'] ) ?	$display_location = $value['referrer'] : $display_location = $value['current_page'];			
			if ( isset($old) && $display_location == $old)	{	continue; } else { $old = $display_location; $i++;	 }			
			
			//echo $value['referrer'];
			//echo "<br>1";
			//echo $value['current_page'];
			//echo "<br>2";
			//echo $display_location;exit;
			if (count($data)==1)
			{
				if ($value['referrer'])
				{
				?>
				<div class="lp-page-view-item">
					
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $value['referrer']; ?>' title='<?php echo $value['referrer']; ?>' target='_blank'><?php echo $value['referrer']; ?></a>
									
				</div>
				<?php
				}
				?>
				<div class="lp-page-view-item">
					
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $value['current_page'] ?>' title='<?php echo $value['current_page'] ?>' target='_blank'><?php echo $value['current_page']; ?></a>
									
				</div>
				<?php
			}
			else
			{
			?>
				<div class="lp-page-view-item">
					<div class='lp-next-path'></div>
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $display_location; ?>' title='<?php echo $display_location; ?>' target='_blank'><?php echo $display_location; ?></a>
									
				</div>
			<?php
			}
		}
		?>
		
		<div class="lp-page-conversion-item">
	
				<div id='end-conversion-point'>
				<?php
				if (strstr($display_location,'?'))
				{
					$display_location = explode('?',$display_location);
					$display_location = $display_location[0];
				}
				
				$page_id = wpl_url_to_postid($display_location);
				//echo $page_id;
				$title = get_the_title(intval($page_id));
				?>
				<span>Converted on:</span> <a href='<?php echo $display_location;?>' target='_blank'><?php echo $title; ?></a></span>
				</div>
			
		</div>
		</div><!-- .leads-visit-list end -->
		</div><!-- end .conversion-session-view -->
		</div><!-- end #conversion-tracking -->
		
		<?php
	}
	//update_option($post->ID,'wpl-lead-conversions',implode(',',$conversions));
	// echo count($array_page_view_total);
}

 ?>