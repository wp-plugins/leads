<?php

add_action('wp_ajax_wpl_track_user', 'wpl_track_user_callback');
add_action('wp_ajax_nopriv_wpl_track_user', 'wpl_track_user_callback');

function wpl_track_user_callback() 
{
	global $wpdb;
	//echo "here";exit;
	(isset(	$_POST['lead_id'] )) ? $lead_id = $_POST['lead_id'] : $lead_id = '';
	(isset(	$_POST['nature'] )) ? $nature = $_POST['nature'] : $nature = 1;
	(isset(	$_POST['json'] )) ? $json = addslashes($_POST['json']) : $json = 0;
	(isset(	$_POST['wp_lead_uid'] )) ? $wp_lead_uid = $_POST['wp_lead_uid'] : $wp_lead_uid = 0;
	

	$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
	$wordpress_date_time =  date_i18n($timezone_format);
	
	$query = "SELECT * FROM ".$wpdb->prefix."lead_tracking WHERE DATE(date) = DATE('{$wordpress_date_time}') AND tracking_id='{$wp_lead_uid}' AND nature='non-conversion'";
	$result = mysql_query($query);				
	if (!$result){ echo $query; echo mysql_error(); exit; }
	
	if (mysql_num_rows($result)>0)
	{
		//echo "here";
		$row = mysql_fetch_array($result);
		$row_id = $row['id'];
		
		$query = "UPDATE ".$wpdb->prefix."lead_tracking SET date='{$wordpress_date_time}' , data = '{$json}' , nature = 'non-conversion' WHERE id='{$row_id}'";
		
		$result = mysql_query($query);				
		if (!$result){ echo $query; echo mysql_error(); exit; }
	}
	else
	{
		//echo "there";
		$query = 'INSERT INTO '.$wpdb->prefix.'lead_tracking
				(lead_id,tracking_id,date,data,nature) VALUES
				("'.$lead_id.'" , "'.$wp_lead_uid.'" , "'.$wordpress_date_time.'" , "'.$json.'" , 0)';
		
		$result = mysql_query($query);				
		if (!$result){ echo $query; echo mysql_error(); exit; }
		
		$row_id = mysql_insert_id();
	}
	
	echo $row_id;
	die();
}

add_action('wp_ajax_wpl_store_lead', 'wpl_store_lead_callback');
add_action('wp_ajax_nopriv_wpl_store_lead', 'wpl_store_lead_callback');

function wpl_store_lead_callback() 
{
	// Grab form values
	$title = $_POST['emailTo'];
	$content =	$_POST['first_name'];
	$wp_lead_uid = $_POST['wp_lead_uid'];
	$raw_post_values_json = $_POST['raw_post_values_json'];
	
	if (isset( $_POST['emailTo'])&&!empty( $_POST['emailTo'])&&strstr($_POST['emailTo'],'@'))
	{
		//echo 'here';
		global $user_ID, $wpdb;
		$wordpress_date_time = $timezone_format = _x('Y-m-d G:i:s T', 'timezone date format');
		$wordpress_date_time =  date_i18n($timezone_format);
		
		(isset(	$_POST['first_name'] )) ? $first_name = $_POST['first_name'] : $first_name = "";
		(isset(	$_POST['last_name'] )) ? $last_name = $_POST['last_name'] : $last_name = "";
		(isset(	$_SERVER['REMOTE_ADDR'] )) ? $ip_address = $_SERVER['REMOTE_ADDR'] : $ip_address = "undefined";
		(isset(	$_POST['nature'] )) ? $nature = $_POST['nature'] : $nature = 0;
		(isset(	$_POST['wp_lead_uid'] )) ? $wp_lead_id = $_POST['wp_lead_uid'] : $wp_lead_id = "null";
		(isset(	$_POST['lp_id'] )) ? $lp_id = $_POST['lp_id'] : $lp_id = 0;
		
		do_action('wpl_store_lead_pre');
		
		$query = $wpdb->prepare(
			'SELECT ID FROM ' . $wpdb->posts . '
			WHERE post_title = %s
			AND post_type = \'wp-lead\'',
			$_POST['emailTo']
		);
		$wpdb->query( $query );

		if ( $wpdb->num_rows ) {
			// If lead exists add data/append data to it
			$post_ID = $wpdb->get_var( $query );
			//echo "here";
			//echo $post_ID;
			$meta = get_post_meta( $post_ID, 'times', TRUE );			
			$meta++;
			
			if ($lp_id)
			{
				$conversion_data = get_post_meta( $post_ID, 'wpleads_conversion_data', TRUE );
				$conversion_data = json_decode($conversion_data,true);
				$conversion_data[$lp_id]['id'] = $lp_id;
				$conversion_data[$lp_id]['datetime'] = $wordpress_date_time;
				$conversion_data = json_encode($conversion_data);
			}
			
			update_post_meta( $post_ID, 'times', $meta );
			update_post_meta( $post_ID, 'wpleads_email_address', $title );
			
			if (!empty($user_ID))
				update_post_meta( $post_ID, 'wpleads_wordpress_user_id', $user_ID );				
			if (!empty($first_name))
				update_post_meta( $post_ID, 'wpleads_first_name', $first_name );
			if (!empty($last_name))
				update_post_meta( $post_ID, 'wpleads_last_name', $last_name );
			if (!empty($wp_lead_id))
				update_post_meta( $post_ID, 'wpleads_uid', $wp_lead_id );
				
			update_post_meta( $post_ID, 'wpleads_ip_address', $ip_address );
			update_post_meta( $post_ID, 'wpleads_conversion_data', $conversion_data );
			update_post_meta( $post_ID, 'wpleads_landing_page_'.$lp_id, 1 );
			
			do_action('wpleads_after_conversion_lead_update',$post_ID);
		
		} else { 
			// If lead doesn't exist create it
			$post = array(
				'post_title'		=> $title, 
				 //'post_content'		=> $json,
				'post_status'		=> 'publish',
				'post_type'		=> 'wp-lead',
				'post_author'		=> 1
			);
			
			//$post = add_filter('wpl_leads_post_vars',$post);
			
			if ($lp_id)
			{			
				$conversion_data[$lp_id]['id'] = $lp_id;
				$conversion_data[$lp_id]['datetime'] = $wordpress_date_time;
				$conversion_data[$lp_id]['first_time'] = 1;					
				$conversion_data = json_encode($conversion_data);
			}
			
			$post_ID = wp_insert_post($post);
			update_post_meta( $post_ID, 'wpleads_wordpress_user_id', $user_ID );
			update_post_meta( $post_ID, 'wpleads_email_address', $title );
			update_post_meta( $post_ID, 'wpleads_first_name', $first_name);
			update_post_meta( $post_ID, 'wpleads_last_name', $last_name);
			update_post_meta( $post_ID, 'wpleads_ip_address', $ip_address );
			update_post_meta( $post_ID, 'wpleads_uid', $wp_lead_id );
			update_post_meta( $post_ID, 'wpleads_conversion_data', $conversion_data );
			update_post_meta( $post_ID, 'wpleads_landing_page_'.$lp_id, 1 );
			
			$geo_array = unserialize(wpl_remote_connect('http://www.geoplugin.net/php.gp?ip='.$ip_address));
			
			
			(isset($geo_array['geoplugin_areaCode'])) ? update_post_meta( $post_ID, 'wpleads_areaCode', $geo_array['geoplugin_areaCode'] ) : null;
			
			
			(isset($geo_array['geoplugin_city'])) ? update_post_meta( $post_ID, 'wpleads_city', $geo_array['geoplugin_city'] ) : null;
			(isset($geo_array['geoplugin_regionName'])) ? update_post_meta( $post_ID, 'wpleads_region_name', $geo_array['geoplugin_regionName'] ) : null;
			(isset($geo_array['geoplugin_regionCode'])) ? update_post_meta( $post_ID, 'wpleads_region_code', $geo_array['geoplugin_regionCode'] ) : null;
			(isset($geo_array['geoplugin_countryName'])) ? update_post_meta( $post_ID, 'wpleads_country_name', $geo_array['geoplugin_countryName'] ) : null;
			(isset($geo_array['geoplugin_countryCode'])) ? update_post_meta( $post_ID, 'wpleads_country_code', $geo_array['geoplugin_countryCode'] ) : null;
			(isset($geo_array['geoplugin_latitude'])) ? update_post_meta( $post_ID, 'wpleads_latitude', $geo_array['geoplugin_latitude'] ) : null;
			(isset($geo_array['geoplugin_longitude'])) ? update_post_meta( $post_ID, 'wpleads_longitude', $geo_array['geoplugin_longitude'] ) : null;
			(isset($geo_array['geoplugin_currencyCode'])) ? update_post_meta( $post_ID, 'wpleads_currency_code', $geo_array['geoplugin_currencyCode'] ) : null;
			(isset($geo_array['geoplugin_currencySymbol_UTF8'])) ? update_post_meta( $post_ID, 'wpleads_currency_symbol', $geo_array['geoplugin_currencySymbol_UTF8'] ) : null;
			
			do_action('wpleads_after_conversion_lead_insert',$post_ID);
		
		}

		$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
		$wordpress_date_time =  date_i18n($timezone_format);
		$data['lp_id'] = $lp_id;
		$data['lead_id'] = $post_ID;
		$data['first_name'] = $first_name;
		$data['last_name'] = $last_name;
		$data['email'] = $title;
		$data['wp_lead_uid'] = $wp_lead_id;
		$data['raw_post_values_json'] = $raw_post_values_json;
		
		do_action('wpl_store_lead_post', $data );
		
		echo $post_ID;
		die();
	}
}


//function to store additonal lead conversion data - plugins into Wordpress Leads standalone and Landing Pages plugin.
function wpleads_hook_store_lead_post($data)
{
	//print_r($data);
	if ($data['lead_id'])
	{
		global $wpdb;
		(isset(	$_POST['nature'] )) ? $nature = $_POST['nature'] : $nature = 1;
		(isset(	$_POST['json'] )) ? $json = $_POST['json'] : $json = 0;
		(isset(	$_POST['page_view_count'] )) ? $view_count = $_POST['page_view_count'] : $view_count = 0;
		

		$timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
		$wordpress_date_time =  date_i18n($timezone_format);

		$query = 'INSERT INTO '.$wpdb->prefix.'lead_tracking
				(lead_id,tracking_id,date,data,nature) VALUES
				("'.$data['lead_id'].'" , "'.$data['wp_lead_uid'].'" , "'.$wordpress_date_time.'" , "'.$json.'" , "'.$nature.'")';
		
		$result = mysql_query($query);				
		if (!$result){ echo $query; echo mysql_error(); exit; }
		
		setcookie('user_data_json', "",time()+3600,"/");

		/* Store number of page views as meta */
		$current_page_view_count = get_post_meta($data['lead_id'],'wpl-lead-page-view-count', true);
		if ($current_page_view_count)
		{
			$add_count_views = $view_count;
		}
		else
		{					
			$current_page_view_count = 0;
			$add_count_views = $view_count;			
		}
		$increment_page_views = $current_page_view_count + $add_count_views;
		update_post_meta($data['lead_id'],'wpl-lead-page-view-count', $increment_page_views);
		/* End Store number of page views as meta */

		/* Store conversions as meta */
		$conversions = get_post_meta($data['lead_id'],'wpl-lead-conversions', true);

		if ($conversions)
		{
			$array_conversions = explode(',',$conversions);
			$count_of_conversions = count($array_conversions);
			// if (!in_array($data['lp_id'],$array_conversions)) {
				$array_conversions[] = $data['lp_id'];
				//$array_conversions[] = $data['lp_id'];
			//}
		}
		else
		{					
			$array_conversions[] = $data['lp_id'];
			$count_of_conversions = 0;			
		}
		
		update_post_meta($data['lead_id'],'wpl-lead-conversions', implode(',',$array_conversions));
		/* Store conversions count as meta */
		$increment_conversions = $count_of_conversions + 1;
		update_post_meta($data['lead_id'],'wpl-lead-conversion-count', $increment_conversions);
		
		//update raw post data json 
		$raw_post_data = get_post_meta($data['lead_id'],'wpl-lead-raw-post-data', true);
					
		$a1 = json_decode( $raw_post_data, true );
		$a2 = json_decode( stripslashes($data['raw_post_values_json']), true );
		
		foreach ($a2 as $key=>$value)
		{
			if (stristr($key,'company'))
			{
				update_post_meta( $post_ID, 'wpleads_company_name', $value );
			}					
			else if (stristr($key,'website'))
			{
				$websites = get_post_meta( $post_ID, 'wpleads_websites', $value );
				
				if(is_array($websites))
				{
					$array_websites = explode(';',$websites);
				}
				
				$array_websites[] = $value;
				$websites = implode(';',$array_websites);
				update_post_meta( $post_ID, 'wpleads_websites', $websites );
			}
		}
		
		if (is_array($a1))
		{
			$new_raw_post_data = array_merge_recursive( $a1, $a2 );
		}
		else
		{
			$new_raw_post_data = $a2;
		}
		//print_r($new_raw_post_data);exit;
		
		$new_raw_post_data = json_encode( $new_raw_post_data );
		update_post_meta( $data['lead_id'],'wpl-lead-raw-post-data', $new_raw_post_data );
		
	}
}