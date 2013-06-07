<?php
/* 
Plugin Name: WordPress Leads
Plugin URI: http://www.inboundnow.com/landing-pages/downloads/lead-management/
Description: Wordpress Lead Manager provides CRM (Customer Relationship Management) applications for WordPress Landing Page plugin. Lead Manager Plugin provides a record management interface for viewing, editing, and exporting lead data collected by Landing Page Plugin. 
Author: Hudson Atwell(@atwellpub), David Wells (@inboundnow)
Version: 1.0.0.1
Author URI: http://www.inboundnow.com/landing-pages/
*/



$lead_manager_license_status = get_option('lp_license_status-lead-manager');

if ($lead_manager_license_status=='valid'||$debug=1)
{
	define('WPL_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
	define('WPL_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
	define('WPL_CORE', plugin_basename( __FILE__ ) );
	
	include_once('modules/wpl.m.post-type.php'); 
	include_once('modules/wpl.m.ajax-setup.php'); 
	include_once('modules/wpl.m.form-integrations.php'); 
	include_once('functions/wpl.f.global.php'); 
	
}
else
{
	function wpleads_admin_notice() 
	{
		_e('<div class="updated">
			   <p><b>Lead Manager Extension</b> requires license activation! Please head to <a href="'.admin_url().'edit.php?post_type=landing-page&page=lp_global_settings&tab=lp-license-keys">\'Landing Pages -> Global Settings -> License Keys\' to activate your copy!</a> </p>
			</div>','wpleads');
	}
	add_action('admin_notices', 'wpleads_admin_notice');
}

if (is_admin()) 
{
	load_plugin_textdomain('wpleads',false,dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
	/*SETUP NAVIGATION AND DISPLAY ELEMENTS
	$tab_slug = 'lp-license-keys';
	$lp_global_settings[$tab_slug]['label'] = 'License Keys';	
	
	$lp_global_settings[$tab_slug]['options'][] = lp_add_option($tab_slug,"license-key","lead-manager","","Lead Manager","Head to http://www.inboundnow.com/landing-pages/account/ to retrieve your license key for Lead Manager for Landing Pages", $options=null);
	
	
	$edd_updater = new LP_EXTENSION_UPDATER( LANDINGPAGES_STORE_URL, __FILE__, array( 
		'version' 	=> '1.0.1.1', 				// current version number
		'license' 	=> trim(get_option( 'lp-license-keys-lead-manager' )), // license key (used get_option above to retrieve from DB)
		'item_name' => 'lead-manager',	// permalink name of this plugin on inboundnow.com/landing-pages/ store.
		'nature' 	=> 'extension'  // nature of update request
	));
	/*SETUP END*/
	
	register_activation_hook(__FILE__, 'wpleads_activate');
	include_once('modules/wpl.m.activate.php'); 
	include_once('modules/wpl.m.metaboxes.php'); 
	include_once('functions/wpl.f.admin.php'); 	
	include_once('modules/wpl.m.global-settings.php'); 
	
	
}


add_action('wp_enqueue_scripts', 'wpleads_enqueuescripts_header');
function wpleads_enqueuescripts_header()
{
	$current_page = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$post_id = wpl_url_to_postid($current_page);
	(isset($_SERVER['HTTP_REFERER'])) ? $referrer = $_SERVER['HTTP_REFERER'] : $referrer ='direct access';	
	(isset($_SERVER['REMOTE_ADDR'])) ? $ip_address = $_SERVER['REMOTE_ADDR'] : $ip_address = '0.0.0.0.0';
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-cookie', WPL_URL . '/js/jquery.cookie.js', array( 'jquery' ));
	

	wp_enqueue_script( 'funnel-tracking' , WPL_URL . '/js/wpl.funnel-tracking.js', array( 'jquery','jquery-cookie'));
	wp_enqueue_script( 'wpl-nonconversion-tracking' , WPL_URL . '/js/wpl.nonconversion-tracking.js', array( 'jquery','jquery-cookie','funnel-tracking'));
	wp_localize_script( 'wpl-nonconversion-tracking' , 'wplnct', array( 'admin_url' => admin_url( 'admin-ajax.php' ) ));
	wp_enqueue_script('form-population', WPL_URL . '/js/wpl.form-population.js', array( 'jquery','jquery-cookie'));	
	
	$form_ids = get_option( 'wpl-main-tracking-ids' , 1);
	
	if ($form_ids)
	{
		wp_enqueue_script('wpl-assign-class', WPL_URL . '/js/wpl.assign-class.js', array( 'jquery'));	
		wp_localize_script( 'wpl-assign-class', 'wpleads', array( 'form_ids' => $form_ids ) );
	}
}

add_action('admin_enqueue_scripts', 'wpleads_admin_enqueuescripts');
function wpleads_admin_enqueuescripts($hook)
{
	global $post;


	if ((isset($_GET['post_type'])&&$_GET['post_type']=='wp-lead')||(isset($post->post_type)&&$post->post_type=='wp-lead'))
	{
		//echo $_GET['post_type'];exit; 
		if ( $hook == 'post.php' ) 
		{
			wp_enqueue_script('wpleads-edit', WPL_URL.'/js/wpl.admin.edit.js', array('jquery'));
			wp_localize_script( 'wpleads-edit', 'wp_lead_map', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'wp_lead_map_nonce' => wp_create_nonce('wp-lead-map-nonce') ) );
		}
		
		
		//Tool tip js
		wp_enqueue_script('jquery-qtip', WPL_URL . '/js/jquery-qtip/jquery.qtip.min.js');
		wp_enqueue_script('wpl-load-qtip', WPL_URL . '/js/jquery-qtip/load.qtip.js');
		wp_enqueue_style('wpleads-admin-css', WPL_URL.'/css/wpl.admin.css');
		
				
		// Leads list management js
		wp_enqueue_script('wpleads-list', WPL_URL . '/js/wpl.leads-list.js');
		wp_enqueue_style('wpleads-list-css', WPL_URL.'/css/wpl.leads-list.css');

	
		
		if ( $hook == 'post-new.php' ) 
		{
				wp_enqueue_script('wpleads-create-new-lander', WPL_URL . '/js/wpl.add-new.js');
		}		
	}
}

//if Landing Pages plugin not active setup independant tracking else intgrate into Landing Pages Tracking.
if (!@function_exists('lp_check_active'))
{
	//echo 1; exit;
	add_action('wp_footer','wpl_register_ajax');
	function wpl_register_ajax() 
	{
		global $post;
		include_once(WPL_PATH . '/js/wpl.leads-tracking.js.php');
	}
	
	//add additional tracking to Stand Alone.
	add_action( 'wpl_store_lead_post', 'wpleads_hook_store_lead_post' );
}
else
{
	//echo 2; exit;
	//add additional tracking into Landing Pages
	add_action( 'lp_store_lead_post', 'wpleads_hook_store_lead_post' );	
	
	add_action( 'lp-lead-collection-add-js-pre', 'wpleads_hook_js_pre' );
	function wpleads_hook_js_pre()
	{
		echo "var data_block = jQuery.parseJSON(jQuery.cookie('user_data_json'));
			//console.log(data_block);			
			//alert('here');
			var email;
			var firstname;
			var lastname;
			var json = JSON.stringify(data_block);
			//alert(json);
		";
	}

	add_action( 'lp-lead-collection-add-ajax-data', 'wpleads_hook_data' );
	function wpleads_hook_data()
	{
		echo ",
			json: json";
				
	}
}

// Enable some developer options on the front-end for testing and debugging purposes
add_action( 'get_header', 'wp_leads_dev_debug_mode' );
function wp_leads_dev_debug_mode() {

	// Only proceed if URL querystring cotnains "devmode=true"
	if ( isset($_GET['devmode']) && 'true' == $_GET['devmode'] ) {

		// Output visitor sessions history if URL querystring contains 'show=session'
		if ( isset($_GET['show']) && 'session' == $_GET['show'] )
			// Print out sessions data

		// Output current wp-lead cookie contents if URL querystring contains 'show=cookies'
		if ( isset($_GET['show']) && 'cookies' == $_GET['show'] )
			// Print out cookies

		// Clear Session History and dump us back at the homepage if URL querystring contains 'session-clear=reset'
		if ( isset($_GET['session-clear']) && 'reset' == $_GET['session-clear'] ) {
			// unset( $_SESSION['user_history'] );
			wp_redirect( site_url() );
			exit;
		}

	}

}

if (is_admin())
{
	
	/**********************************************************/
	/******************CREATE SETTINGS SUBMENU*****************/
	add_action('admin_menu', 'wpleads_add_menu');	
	function wpleads_add_menu()
	{
		//echo 1; exit;
		if (current_user_can('manage_options'))
		{			
			add_submenu_page('edit.php?post_type=wp-lead', 'Global Settings', 'Global Settings', 'manage_options', 'wpleads_global_settings','wpleads_display_global_settings');
			
		}
	}
	
	add_action('lp_lead_table_data_is_details_column','wpleads_add_user_edit_button');
	function wpleads_add_user_edit_button($item)
	{
		$image = WPL_URL.'/images/icons/edit_user.png';
		echo '&nbsp;&nbsp;<a href="'.get_admin_url().'post.php?post='.$item['ID'].'&action=edit" target="_blank"><img src="'.$image.'" title="Edit User"></a>';
	}
	
	add_action('lp_module_lead_splash_post','wpleads_add_user_conversion_data_to_splash');
	function wpleads_add_user_conversion_data_to_splash($data)
	{
		$conversion_data = $data['lead_custom_fields']['wpleads_conversion_data'];

		echo "<h3  class='lp-lead-splash-h3'>Recent Conversions:</h3>"; 
		echo "<table>";
		echo "<tr>";
					echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-0'>#</td>";
					echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-1'>Location</td>";
					echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-2'>Datetime</td>";
					echo "<td class='lp-lead-splash-td' 'id='lp-lead-splash-3'>First-time?</td>";				
		echo "<tr>";
		foreach ($conversion_data as $key=>$value)
		{
			$i = $key+1;
			//print_r($conversion_data);
			$value = json_decode($value, true);
			//print_r($value);
			foreach ($value as $k=>$row)
			{
				
				
				echo "<tr>";
					echo "<td>";					
						echo "[$i]";						
						//echo $row['id'];
						//print_r($row);exit;
					echo "</td>";
					echo "<td>";
						echo "<a href='".get_permalink($row['id'])."' target='_blank'>".get_the_title(intval($row['id']))."</a>";
					echo "</td>";
					echo "<td>";					
						echo $row['datetime'];
					echo "</td>";
					echo "<td>";					
						if ($row['first_time']==1)
						{
							echo "yes";
						}
					echo "</td>";				
				echo "<tr>";
				$i++;
			}
		}
		
		echo "</table>";
	}
}
