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

function wplead_gravatar_metabox() {
	global $post;
	global $wpdb;
	
	//define last touch point
	$query = 'SELECT * FROM '.$wpdb->prefix.'lead_tracking WHERE lead_id = "'.$post->ID.'" AND nature="conversion" ORDER BY id DESC LIMIT 1';
	$result = mysql_query($query);
	if (!$result){ echo $sql; echo mysql_error(); exit; }

	$array = mysql_fetch_array($result);
	
		$date1 = new DateTime($array['date']);

		$date2 = new DateTime(date('Y-m-d G:i:s'));
		$interval = $date1->diff($date2);
		//print_r($date1);
		//echo $date1->date;
		$year = ($interval->y > 1) ? "Years" : "Year";
		$month = ($interval->m > 1) ? "Months" : "Month";
		$day = ($interval->d > 1) ? "Days" : "Day";
		$hours = ($interval->h > 1) ? "Hours" : "Hour";
		$minute = ($interval->i > 1) ? "Minutes" : "Minute"; 
	//echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days, ".$interval->h." hours, ".$interval->i." minutes, "; exit;
		
	$email = get_post_meta( $post->ID , 'wpleads_email_address', true );
	$first_name = get_post_meta( $post->ID , 'wpleads_first_name',true );
	$last_name = get_post_meta( $post->ID , 'wpleads_last_name', true );
	?>
	<div >
		<script type="text/javascript">
		jQuery(document).ready(function() {
		  var timesince = jQuery("#session-time-since").first().html();
		  jQuery(".timeago").html(timesince);
		});</script>
		<div class="inside" style='margin-left:-8px;text-align:center;'> 
			<div id="quick-stats-box">
			<div id="page_view_total">Total Page Views <span id="p-view-total"></span></div>
			<div id="conversion_count_total"># of Conversions <span id="conversion-total"></span></div>
			<br><br>
			
			<div id="last_touch_point">Time Since Last Conversion 
				<span id="touch-point">
					<span class='timeago' title='<?php echo $date1->date; ?>'></span>
					<?php /* Not Working *	$year = ($interval->y > 1) ? "Years" : "Year";
							$month = ($interval->m > 1) ? "Months" : "Month";
							$day = ($interval->d > 1) ? "Days" : "Day";
							$hours = ($interval->h > 1) ? "Hours" : "Hour";
							$minute = ($interval->i > 1) ? "Minutes" : "Minute"; ?>
					<?php

					echo "<span class='touchpoint-year'><span class='touchpoint-value'>" . $interval->y . "</span> ".$year." </span><span class='touchpoint-month'><span class='touchpoint-value'>" . $interval->m."</span> ".$month." </span><span class='touchpoint-day'><span class='touchpoint-value'>".$interval->d."</span> ".$day." </span><span class='touchpoint-hour'><span class='touchpoint-value'>".$interval->h."</span> ".$hours." </span><span class='touchpoint-minute'><span class='touchpoint-value'>".$interval->i."</span> ".$minute."</span> Ago"; */
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
							<a class="maps-link" href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=<?php echo $latitude.','.$longitude; ?>&z=12" target='_blank'>View Map</a>
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

							echo "<div class='lead-geo-field'><span class='geo-label'>City:</span>" . $geo_array['geoplugin_city'] . "</div>";
							echo "<div class='lead-geo-field'><span class='geo-label'>State:</span>" . $geo_array['geoplugin_regionName'] . "</div>";
							
							echo "<div class='lead-geo-field'><span class='geo-label'>Area Code:</span>" . $geo_array['geoplugin_areaCode'] . "</div>";
							echo "<div class='lead-geo-field'><span class='geo-label'>Country:</span>" . $geo_array['geoplugin_countryName'] . "</div>";
							echo "<div class='lead-geo-field'><span class='geo-label'>IP Address:</span>" . $ip_address . "</div>";
							
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
						?>
	
	<div id="lead-google-map">
			<!-- <iframe width="278" height="276" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q=<?php echo $city.' '.$state; ?>&amp;aq=&amp;output=embed&amp;z=5"></iframe><br /><small><a class="maps-link" href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=<?php echo $city.' '.$state; ?>" target='_blank'>View Larger Map</a></small> -->
			<iframe width="278" height="276" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;q=<?php echo $latitude.','.$longitude; ?>&amp;aq=&amp;output=embed&amp;z=11"></iframe>
	</div>
						</div>
			</div>
				
		</div>	
	</div>
	<?php
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
						
			//first add to varation list if not present.
			$conversions = get_post_meta($post->ID,'wpl-lead-conversions', true);
			
			if ($conversions)
			{
				echo "<h2>Recent Conversions</h2>";
				$conversions = explode(',',$conversions);
				//print_r($conversions);
				echo "<div id='recent-conversions-list'>";
				$num = 1;
				foreach($conversions as  $key=>$val)
				{
					$title = get_the_title($val);
					$display_location = get_permalink($val);
					echo "<div class='recent-conversion-item'>".$num.". <a href='{$display_location}' id='lead-session-".$num."' rel='".$num."' target='_blank'>{$title}</a><span class='conversion-date'></span><span class='view-this-lead-session' rel='".$num."'> <a rel='".$num."' href='#view-session-".$num."'>view visit details</a></span></div>";
					$num++;
				}
				echo "</div>";
			}
			else
			{					
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
	while ($array = mysql_fetch_array($result))
	{
		//echo "here";
		$old = null;
		$date = date_create($array['date']);
		$data = json_decode( $array['data'] , true);
		//echo $array['data'];
		//echo "<br>";
		$date1 = new DateTime($array['date']);
		$date2 = new DateTime(date('Y-m-d G:i:s'));
		$interval = $date1->diff($date2);
		$year = ($interval->y > 1) ? "Years" : "Year";
							$month = ($interval->m > 1) ? "Months" : "Month";
							$day = ($interval->d > 1) ? "Days" : "Day";
							$hours = ($interval->h > 1) ? "Hours" : "Hour";
							$minute = ($interval->i > 1) ? "Minutes" : "Minute"; 
		$data = $data['items'];
		//print_r($data);exit;
		echo '<a class="session-anchor" id="view-session-'.$num_conversion.'""></a><div id="conversion-tracking" class="wpleads-conversion-tracking-table" summary="Conversion Tracking">
			
			<div class="conversion-tracking-header">
					<h2><strong>Visit '.$num_conversion.'</strong> on <span class="shown_date">'.date_format($date, 'F jS, Y \a\t g:ia (l)').'</span><span class="toggle-conversion-list">-</span></h2> <span class="hidden_date date_'.$num_conversion.'">'.date_format($date, 'F jS, Y \a\t g:ia').'</span>
			</div>
			<div class="conversion-session-view session_id_'.$num_conversion.'">
			<div class="session-stats">
			<span class="session-stats-header">Session Stats</span>
			
			<span id="session-time-since"><span class="touchpoint-year"><span class="touchpoint-value">' . $interval->y . '</span> '.$year.' </span><span class="touchpoint-month"><span class="touchpoint-value">' . $interval->m.'</span> '.$month.' </span><span class="touchpoint-day"><span class="touchpoint-value">'.$interval->d.'</span> '.$day.' </span><span class="touchpoint-hour"><span class="touchpoint-value">'.$interval->h.'</span> '.$hours.' </span><span class="touchpoint-minute"><span class="touchpoint-value">'.$interval->i.'</span> '.$minute.'</span> Ago</span>
				<span class="session-head">Event Freshness</span>
				<div id="session-pageviews">
				<span id="pages-view-in-session">10</span>
				<span class="session-head page-view-sess">Pages Viewed in Session</span>
				</div>
			</div>';

			$num_conversion--;	
		
		//print_r($data);exit;
		$i = 0;
		foreach ($data as $key => $value)
		{		

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
					
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $value['referrer']; ?>' title='' target='_blank'><?php echo $value['referrer']; ?></a>
									
				</div>
				<?php
				}
				?>
				<div class="lp-page-view-item">
					
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $value['current_page'] ?>' title='' target='_blank'><?php echo $value['current_page']; ?></a>
									
				</div>
				<?php
			}
			else
			{
			?>
				<div class="lp-page-view-item">
					<div class='lp-next-path'></div>
						<span class='marker'><?php echo $i; ?></span> <a href='<?php echo $display_location; ?>' title='' target='_blank'><?php echo $display_location; ?></a>
									
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
		
		</div><!-- end .conversion-session-view -->
		</div><!-- end #conversion-tracking -->
		
		<?php
	}
	//update_option($post->ID,'wpl-lead-conversions',implode(',',$conversions));
}

 ?>