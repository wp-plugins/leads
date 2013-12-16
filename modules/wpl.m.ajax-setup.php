<?php

/* This file no longer stores leads. Those functions live in /shared/tracking/ */

add_action('wp_ajax_wpl_track_user', 'wpl_track_user_callback');
add_action('wp_ajax_nopriv_wpl_track_user', 'wpl_track_user_callback');
// Tracks known leads
function wpl_track_user_callback()
{
	global $wpdb;
	(isset(	$_POST['wp_lead_id'] )) ? $lead_id = $_POST['wp_lead_id'] : $lead_id = '';
	(isset(	$_POST['nature'] )) ? $nature = $_POST['nature'] : $nature = 'non-conversion'; // what is nature?
	(isset(	$_POST['json'] )) ? $json = addslashes($_POST['json']) : $json = 0;
	(isset(	$_POST['wp_lead_uid'] )) ? $wp_lead_uid = $_POST['wp_lead_uid'] : $wp_lead_uid = 0;
	(isset(	$_POST['page_id'] )) ? $page_id = $_POST['page_id'] : $page_id = 0;
	(isset(	$_POST['current_url'] )) ? $current_url = $_POST['current_url'] : $current_url = 'notfound';

	// NEW Tracking
	if(isset($_POST['wp_lead_id'])) {
		wp_leads_update_page_view_obj($lead_id, $page_id, $current_url);
	}

	/* Old non logged in tracking. This updates tracking table. We might need the table in future. */
	$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
	$wordpress_date_time = date("Y-m-d G:i:s", $time);

	$query = "SELECT * FROM ".$wpdb->prefix."lead_tracking WHERE DATE(date) = DATE('{$wordpress_date_time}') AND tracking_id='{$wp_lead_uid}' AND nature='non-conversion'";
	$result = mysql_query($query);
	if (!$result){ echo $query; echo mysql_error(); exit; }

	if (mysql_num_rows($result)>0)
	{
		$row = mysql_fetch_array($result);
		$row_id = $row['id'];

		$query = "UPDATE ".$wpdb->prefix."lead_tracking SET date='{$wordpress_date_time}' , data = '{$json}' , nature = 'non-conversion' WHERE id='{$row_id}' AND DATE(date) = DATE('{$wordpress_date_time}') ORDER BY `id` DESC LIMIT 1";

		$result = mysql_query($query);
		if (!$result){ echo $query; echo mysql_error(); exit; }
	}
	else
	{
		$query = 'INSERT INTO '.$wpdb->prefix.'lead_tracking
				(lead_id,tracking_id,date,data,nature) VALUES
				("'.$lead_id.'" , "'.$wp_lead_uid.'" , "'.$wordpress_date_time.'" , "'.$json.'" , "non-conversion")';

		$result = mysql_query($query);
		if (!$result){ echo $query; echo mysql_error(); exit; }

		$row_id = mysql_insert_id();
	}

	echo $row_id;
	die();

}

/**
 * wp_leads_update_page_view_obj updates page_views meta for known leads
 * @param  string $lead_id     [description]
 * @param  string $page_id     [description]
 */
function wp_leads_update_page_view_obj($lead_id, $page_id, $current_url) {

		if($page_id === "" || empty($page_id)){
			return;
		}
		$current_page_view_count = get_post_meta($lead_id,'wpl-lead-page-view-count', true);

		$increment_page_views = $current_page_view_count + 1;

		update_post_meta($lead_id,'wpl-lead-page-view-count', $increment_page_views); // update count

		$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
		$wordpress_date_time = date("Y-m-d G:i:s T", $time);

		$page_view_data = get_post_meta( $lead_id, 'page_views', TRUE );
		//echo $page_id; // for debug

		// If page_view meta exists do this
		if ($page_view_data) {
			$current_count = 0; // default
			$timeout = 30;  // 30 Timeout analytics tracking for same page timestamps
			$page_view_data = json_decode($page_view_data,true);

				// increment view count on page
				if(isset($page_view_data[$page_id])){
				$current_count = count($page_view_data[$page_id]);
				$last_view = $page_view_data[$page_id][$current_count];
				$timeout = abs(strtotime($last_view) - strtotime($wordpress_date_time));
				}

			// If page hasn't been viewed in past 30 seconds. Log it
			if ($timeout >= 30) {
			$page_view_data[$page_id][$current_count + 1] = $wordpress_date_time;
			$page_view_data = json_encode($page_view_data);
			update_post_meta( $lead_id, 'page_views', $page_view_data );
			}

		} else {
		// Create page_view meta if it doesn't exist
			$page_view_data = array();
			$page_view_data[$page_id][0] = $wordpress_date_time;
			$page_view_data = json_encode($page_view_data);
			update_post_meta( $lead_id, 'page_views', $page_view_data );
		}
}

add_action('wp_ajax_wpl_check_lists', 'wpl_check_lists_callback');
add_action('wp_ajax_nopriv_wpl_check_lists', 'wpl_check_lists_callback');

function wpl_check_lists_callback()
{
$wp_lead_id = $_POST['wp_lead_id'];
if (isset( $_POST['wp_lead_id'])&&!empty( $_POST['wp_lead_id']))
	{
		wp_leads_get_current_lists($wp_lead_id);
	}
}

/**
 * Sets cookie with current lists the lead is a part of
 * @param  string $lead_id - lead CPT id
 * @return sets cookie of lists lead belongs to
 */
function wp_leads_get_current_lists($lead_id){
		// Set List Cookies if lead is in lists.
		$terms = get_the_terms( $lead_id, 'wplead_list_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {

			$lead_list = array();
			$count = 0;
			foreach ( $terms as $term ) {
				$lead_list[] = $term->term_id;
				$count++;
			}

			//$test = serialize($lead_list);
			$list_array = json_encode(array( 'ids' => $lead_list )); ;

			setcookie('wp_lead_list' , $list_array, time() + (20 * 365 * 24 * 60 * 60),'/');
			//setcookie('check_lead_list' , true, time() + ( 24 * 60 * 60),'/');
		}
}

function wp_leads_get_meta_data($lead_id){
	// function for grabbing any metadata from the backend and displaying to visitor.
	// meta values etc.
}

// This function might need to just build the page_view meta on the lead and be in /shared/
function wpleads_hook_store_lead_post($data)
{
	//setcookie('this_running', "EYP",time()+3600,"/"); // test if firing
	//print_r($data);
	if ($data['lead_id'])
	{
		global $wpdb;

		$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
		$wordpress_date_time = date("Y-m-d G:i:s", $time);

		 // note: Grab page view obj instead of trackObj
		(isset(	$_POST['json'] )) ? $json = $_POST['json'] : $json = 0;
		(isset(	$_POST['page_view_count'] )) ? $view_count = $_POST['page_view_count'] : $view_count = 0;

		$json = stripslashes($json);
		$json = json_decode($json, true);
		$json[0]['converted_page'] = array( 'page_id'=> $_POST['lp_id'] , 'datetime' => $wordpress_date_time );
		$json = json_encode($json);

		//$query = 'INSERT INTO '.$wpdb->prefix.'lead_tracking
		//		(lead_id,tracking_id,date,data,nature) VALUES
		//		("'.$data['lead_id'].'" , "'.$data['wp_lead_uid'].'" , "'.$wordpress_date_time.'" , "'.$json.'" , "conversion")';

		$query = "SELECT * FROM ".$wpdb->prefix."lead_tracking WHERE tracking_id='".$data['wp_lead_uid']."' ORDER BY id DESC LIMIT 1";
		$result = mysql_query($query);
		if (!$result){ echo $query; echo mysql_error(); exit; }

		if (mysql_num_rows($result)>0)
		{
			$row = mysql_fetch_array($result);
			$row_id = $row['id'];

			$query = "UPDATE ".$wpdb->prefix."lead_tracking SET date='{$wordpress_date_time}' , data = '{$json}' , lead_id= '{$data['lead_id']}' ,  nature = 'conversion' WHERE id='{$row_id}' ";

			$result = mysql_query($query);
			if (!$result){ echo $query; echo mysql_error(); exit; }
		}
		else
		{
			$query = "INSERT INTO ".$wpdb->prefix."lead_tracking
					(lead_id,tracking_id,date,data,nature) VALUES
					('".$data['lead_id']."' , '".$data['wp_lead_uid']."' , '".$wordpress_date_time."' , '".$json."' , 'conversion')";

			$result = mysql_query($query);
			if (!$result){ echo $query; echo mysql_error(); exit; }

			$row_id = mysql_insert_id();
		}

		setcookie('user_data_json', "",time()+3600,"/"); // clear page view data



	}
}