<?php

add_action('admin_init', 'wpleads_rebuild_permalinks');
function wpleads_rebuild_permalinks()
{
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

add_action('init', 'wpleads_register');
function wpleads_register() {
	//echo $slug;exit;
    $labels = array(
        'name' => _x('Leads', 'post type general name'),
        'singular_name' => _x('Lead', 'post type singular name'),
        'add_new' => _x('Add New', 'Lead'),
        'add_new_item' => __('Add New Lead'),
        'edit_item' => __('Edit Lead'),
        'new_item' => __('New Leads'),
        'view_item' => __('View Leads'),
        'search_items' => __('Search Leads'),
        'not_found' =>  __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'menu_icon' => WPL_URL . '/images/leads.png',
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('custom-fields','thumbnail')
      );

    register_post_type( 'wp-lead' , $args );
	//flush_rewrite_rules( false );

}

/*********PREPARE COLUMNS FOR LEADS***************/

if (is_admin())
{
	// Change the columns for the edit CPT screen
	add_filter( "manage_wp-lead_posts_columns", "wpleads_change_columns" );
	function wpleads_change_columns( $cols ) {
		$cols = array(
			"cb" => "<input type=\"checkbox\" />",
			"lead-picture" => "Lead",
			"first-name" => "First Name",
			"last-name" => "Last Name",
			"title" => "Email",	
			"status" => "Status",
			
			// "company" => "Company", Custom Column
			'conversion-count' => "Conversion Count",
			"page-views" => "Total Page Views",
			"date" => "Date"
		);
		return $cols;
	}


	add_action( "manage_posts_custom_column", "wpleads_custom_columns", 10, 2 );
	function wpleads_custom_columns( $column, $post_id ) 
	{
		switch ( $column ) {
			case "lead-picture":
			$email = get_post_meta( $post_id , 'wpleads_email_address', true );
			$size = 50;
			$default = WPL_URL . '/images/gravatar_default_50.jpg';

			$gravatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
	
			$profile_image = apply_filters('wpleads_profile_image',$gravatar);
		
			  echo'<img class="lead-grav-img" src="'.$gravatar.'">';
			  break;
			case "first-name":
			  $first_name = get_post_meta( $post_id, 'wpleads_first_name', true);
			  if (get_post_meta( $post_id, 'wpleads_first_name', true) == "") {
			  	$first_name = 'N/A';
			  }
			  echo $first_name;
			  break;
			case "last-name":
			  $last_name = get_post_meta( $post_id, 'wpleads_last_name', true);
			   if (get_post_meta( $post_id, 'wpleads_last_name', true) == "") {
			  	$last_name = 'N/A';
			  }
			  echo $last_name;
			  break;
			case "status":
			  $lead_status = get_post_meta( $post_id, 'wp_lead_status', true);
			  echo $lead_status;
			  break; 
			  case "conversion-count":
			  $conversion_count = get_post_meta( $post_id, 'wpl-lead-conversion-count', true);
			  echo $conversion_count;
			  break;  
			case "page-views":
			  $page_views = get_post_meta( $post_id, 'wpl-lead-page-view-count', true);
			  echo $page_views;
			  break;    
			case "company":
			  $company = get_post_meta( $post_id, 'wpleads_company_name', true);
			  echo $company;
			  break;
		}
	}

		
	// Make these columns sortable
	add_filter( "manage_edit-wp-lead_sortable_columns", "wpleads_sortable_columns" );
	function wpleads_sortable_columns($columns) {

		$columns['first-name'] = 'first-name';        
        $columns['last-name'] = 'last-name';
        $columns['status'] = 'status';         
        $columns['company'] = 'company';          
		 
		return $columns;
	}
	
	add_filter( 'post_row_actions', 'wpleads_remove_row_actions', 10, 2 );
	function wpleads_remove_row_actions( $actions, $post )
	{
		if( $post->post_type == 'wp-lead' && isset($actions['edit']) ) 
		{
			$actions['edit'] = str_replace('Edit','View',$actions['edit']);
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}
	
	add_action('admin_footer-edit.php', 'wpleads_bulk_admin_footer');

// Sort by lead status... coming soon


// Mark lead as viewed 
	add_action( 'wp_ajax_nopriv_wp_leads_mark_as_read_save', 'wp_leads_mark_as_read_save' );
		add_action( 'wp_ajax_wp_leads_mark_as_read_save', 'wp_leads_mark_as_read_save' );
		function wp_leads_mark_as_read_save()
		{
			global $wpdb;
			//echo "here";
			// Grab form values
			$newrules = $_POST['j_rules'];
			//echo $newrules;
			
			$post_id = mysql_real_escape_string($_POST['page_id']);

			add_post_meta( $post_id, 'wp_lead_status', 'Read', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
			header('HTTP/1.1 200 OK');
		}
// Undo mark lead as viewed
	add_action( 'wp_ajax_nopriv_wp_leads_mark_as_read_undo', 'wp_leads_mark_as_read_undo' );
		add_action( 'wp_ajax_wp_leads_mark_as_read_undo', 'wp_leads_mark_as_read_undo' );
		function wp_leads_mark_as_read_undo()
		{
			global $wpdb;
			//echo "here";
			// Grab form values
			$newrules = "New Lead";
			//echo $newrules;
			
			$post_id = mysql_real_escape_string($_POST['page_id']);

			add_post_meta( $post_id, 'wp_lead_status', 'New Lead', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
			header('HTTP/1.1 200 OK');
		}		

// Ajax Save Raw Form Field Mapping
	add_action( 'wp_ajax_nopriv_wp_leads_raw_form_map_save', 'wp_leads_raw_form_map_save' );
	add_action( 'wp_ajax_wp_leads_raw_form_map_save', 'wp_leads_raw_form_map_save' );
		function wp_leads_raw_form_map_save()
		{
			global $wpdb;
			if ( !wp_verify_nonce( $_POST['nonce'], "wp-lead-map-nonce")) {
       			 exit("Wrong nonce");
    		}
			// Grab form values
			$mapped_field = $_POST['mapped_field'];
			$meta_val = $_POST['meta_val'];
			$post_id = mysql_real_escape_string($_POST['page_id']);

			add_post_meta( $post_id, $meta_val, $mapped_field, true ) or update_post_meta( $post_id, $meta_val, $mapped_field );
			header('HTTP/1.1 200 OK');
		}
// Ajax Auto mark lead as read on first page view
	add_action( 'wp_ajax_nopriv_wp_leads_auto_mark_as_read', 'wp_leads_auto_mark_as_read' );
	add_action( 'wp_ajax_wp_leads_auto_mark_as_read', 'wp_leads_auto_mark_as_read' );
		function wp_leads_auto_mark_as_read()
		{
			global $wpdb;
			if ( !wp_verify_nonce( $_POST['nonce'], "wp-lead-map-nonce")) {
       			 exit("Wrong nonce");
    		}
			//$mapped_field = $_POST['mapped_field'];
			$newrules = "Read";
			$post_id = mysql_real_escape_string($_POST['page_id']);

			add_post_meta( $post_id, 'wp_lead_status', 'Read', true ) or update_post_meta( $post_id, 'wp_lead_status', $newrules );
			header('HTTP/1.1 200 OK');
		}


	function wpleads_bulk_admin_footer() {
	  global $post_type;
	  if($post_type == 'wp-lead') {
		?>
		<script type="text/javascript">
		  jQuery(document).ready(function() {
			jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-csv').text('<?php _e('Export CSV')?>').appendTo("select[name='action2']");
			
			jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action']");
			jQuery('<option>').val('export-xml').text('<?php _e('Export XML')?>').appendTo("select[name='action2']");

			
			jQuery('select[name=action]').change(function(){
				if (jQuery(this).val().contains('export-')){
					jQuery('#posts-filter').prop('target','_blank');					
				}
				else
				{
					jQuery('#posts-filter').prop('target','self');
				}
			});
			
		  });
		</script>
		<?php
	  }
	}
	
	add_action('load-edit.php', 'wpleads_bulk_action');

	function wpleads_bulk_action() {
		// ...
		if (isset($_REQUEST['post_type'])&&$_REQUEST['post_type']=='wp-lead'&&isset($_REQUEST['post']))
		{
			//print_r($_REQUEST); 
			// 1. get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();
			  
		  
			if ( !current_user_can('manage_options') ) {
				die();
			}
			
			$post_ids = array_map('intval', $_REQUEST['post']);
			
			switch($action) {
				case 'export-csv':
					$exported = 0;
					
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header('Content-Description: File Transfer');
					header("Content-type: text/csv");
					header("Content-Disposition: attachment; filename=leads-export-csv-".date("m.d.y").".csv");
					header("Expires: 0");
					header("Pragma: public");
					 
					$fh = @fopen( 'php://output', 'w' );	
							
					foreach( $post_ids as $post_id ) {		
						$this_lead_data = get_post_custom($post_id);
						
						foreach ($this_lead_data as $key => $val)
						{
							//if (!strstr($key,'wpleads_'))
							//{
							//}
							
							if (is_array($val))
							{
								$this_lead_data[$key] = implode(',',$val);
							}
						}			
						
						// Add a header row if it hasn't been added yet
						if ( !$headerDisplayed ) {
							// Use the keys from $data as the titles
							fputcsv($fh, array_keys($this_lead_data));
							$headerDisplayed = true;
						}
						
						


						fputcsv($fh, $this_lead_data);						
						
						$exported++;
					}
					
					// Close the file
					fclose($fh);
					
					// Make sure nothing else is sent, our file is done
					exit;
				  
					// build the redirect url						
					$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
				break;
				case 'export-xml':				
					echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";	
					foreach( $post_ids as $post_id ) {		
						$this_lead_data = get_post_custom($post_id);
						
						foreach ($this_lead_data as $key => $val)
						{			
							
							if (is_array($val))
							{
								$this_lead_data[$key] = implode(',',$val);
							}
						}
						
						unset($this_lead_data['_edit_lock']);
						unset($this_lead_data['_yoast_wpseo_linkdex']);
						
						$xml = wpleads_generate_valid_xml_from_array($this_lead_data);
						echo $xml;						
					}
					echo 
					// Make sure nothing else is sent, our file is done
					exit;
					  
					// build the redirect url						
					$sendback = add_query_arg( array('exported' => $exported , 'post_type' => 'wp-lead', 'ids' => join(',', $post_ids) ), $sendback );
				break;				
				default: return;
			}
			  
			// 4. Redirect client
			wp_redirect($sendback);
			exit();
		}
	}
	
	add_action('admin_notices', 'wpleads_bulk_admin_notices');
	function wpleads_bulk_admin_notices() {
	  global $post_type, $pagenow;
	  if($pagenow == 'edit.php' && $post_type == 'wp-lead' && 
		 isset($_REQUEST['exported']) && (int) $_REQUEST['exported']) {
		$message = sprintf( _n( 'Lead exported.', '%s lead exported.', $_REQUEST['exported'] ), number_format_i18n( $_REQUEST['exported'] ) );
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	  }
	}
	
	function wpleads_generate_xml_from_array($array, $node_name) {
		$xml = '';

		if (is_array($array) || is_object($array)) {
			foreach ($array as $key=>$value) {
				if (is_numeric($key)) {
					$key = $node_name;
				}

				$xml .= '		<' . $key . '>' . "\n			" . wpleads_generate_xml_from_array($value, $node_name) . '		</' . $key . '>' . "\n";
			}
		} else {
			$xml = htmlspecialchars($array, ENT_QUOTES) . "\n";
		}

		return $xml;
	}

	function wpleads_generate_valid_xml_from_array($array, $node_block='lead_data', $node_name='lead_data') {		

		$xml = "";
		$xml .= '	<' . $node_block . '>' . "\n";
		$xml .= "".wpleads_generate_xml_from_array($array, $node_name);
		$xml .= '	</' . $node_block . '>' . "\n";

		return $xml;
	}
	
}
?>