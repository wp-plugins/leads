<?php
/*
Plugin Name: Leads
Plugin URI: http://www.inboundnow.com/leads/
Description: Track website visitor activity, manage incoming leads, and send collected emails to your email service provider.
Author: Inbound Now
Version: 1.8.1
Author URI: http://www.inboundnow.com/
Text Domain: leads
Domain Path: lang
*/

if ( ! class_exists( 'Inbound_Leads_Plugin' ) ) {

	final class Inbound_Leads_Plugin {


		public function __construct() {
			self::define_constants();
			self::includes();
			self::load_shared_files();
			self::load_text_domain_init();
		}

		/**
		*  Setup plugin constants
		*/
		private static function define_constants() {
			define('WPL_CURRENT_VERSION', '1.8.1' );
			define('WPL_URLPATH',  plugins_url( '/', __FILE__ ) );
			define('WPL_PATH', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) . '/' );
			define('WPL_CORE', plugin_basename( __FILE__ ) );
			define('WPL_SLUG', plugin_basename( dirname(__FILE__) ) );
			define('WPL_FILE',  __FILE__ );
			define('WPL_STORE_URL', 'http://www.inboundnow.com' );
			$uploads = wp_upload_dir();
			define('WPL_UPLOADS_PATH', $uploads['basedir'].'/leads/' );
			define('WPL_UPLOADS_URLPATH', $uploads['baseurl'].'/leads/' );
		}

		/**
		*  Include required files
		*/
		private static function includes() {

			if ( is_admin() ) {

				/* Admin Includes */
				include_once( WPL_PATH . 'classes/class.activation.php');
				include_once( WPL_PATH . 'classes/class.activation.upgrade-routines.php');
				include_once( WPL_PATH . 'classes/class.post-type.wp-lead.php');
				include_once( WPL_PATH . 'classes/class.metaboxes.wp-lead.php');
				include_once( WPL_PATH . 'classes/class.lead-management.php');
				include_once( WPL_PATH . 'classes/class.form-integrations.php');
				include_once( WPL_PATH . 'classes/class.settings.php');
				include_once( WPL_PATH . 'classes/class.dashboard.php');
				include_once( WPL_PATH . 'classes/class.tracking.php');
				include_once( WPL_PATH . 'classes/class.admin-notices.php');
				include_once( WPL_PATH . 'classes/class.branching.php');
				include_once( WPL_PATH . 'classes/class.login.php');

			} else {
				/* Frontend Includes */
				include_once( WPL_PATH . 'classes/class.post-type.wp-lead.php');
				include_once( WPL_PATH . 'classes/class.form-integrations.php');
				include_once( WPL_PATH . 'classes/class.login.php');
				include_once( WPL_PATH . 'modules/module.enqueue-frontend.php');
				include_once( WPL_PATH . 'classes/class.tracking.php');

			}

		}

		/**
		*  Load Shared Files
		*/
		private static function load_shared_files() {
            if (defined('INBOUND_PRO_PATH')) {
                include_once( INBOUND_PRO_PATH . 'core/shared/classes/class.load-shared.php' );
            } else {
                include_once( WPL_PATH . 'shared/classes/class.load-shared.php');
            }
            add_action( 'plugins_loaded', array( 'Inbound_Load_Shared' , 'init') , 1 );
		}


		/**
		* Hook method to load correct text domain
		*/
		private static function load_text_domain_init() {
			add_action( 'init' , array( __CLASS__ , 'load_text_domain' ) );
		}

		/**
		*   Loads the text domain
		*/
		public static function load_text_domain() {
			load_plugin_textdomain( 'leads' , false , WPL_SLUG . '/assets/lang/' );
		}

		/* START PHP VERSION CHECKS */
		/**
		 * Admin notices, collected and displayed on proper action
		 *
		 * @var array
		 */
		public static $notices = array();

		/**
		 * Whether the current PHP version meets the minimum requirements
		 *
		 * @return bool
		 */
		public static function is_valid_php_version() {
			return version_compare( PHP_VERSION, '5.3', '>=' );
		}

		/**
		 * Invoked when the PHP version check fails. Load up the translations and
		 * add the error message to the admin notices
		 */
		static function fail_php_version() {
			//add_action( 'plugins_loaded', array( __CLASS__, 'load_text_domain_init' ) );
			$plugin_url = admin_url( 'plugins.php' );
			self::notice( __( 'Leads requires PHP version 5.3+ to run. Your version '.PHP_VERSION.' is not high enough.<br><u>Please contact your hosting provider</u> to upgrade your PHP Version.<br>The plugin is NOT Running. You can disable this warning message by <a href="'.$plugin_url.'">deactivating the plugin</a>', 'leads' ) );
		}

		/**
		 * Handle notice messages according to the appropriate context (WP-CLI or the WP Admin)
		 *
		 * @param string $message
		 * @param bool $is_error
		 * @return void
		 */
		public static function notice( $message, $is_error = true ) {
			if ( defined( 'WP_CLI' ) ) {
				$message = strip_tags( $message );
				if ( $is_error ) {
					WP_CLI::warning( $message );
				} else {
					WP_CLI::success( $message );
				}
			} else {
				// Trigger admin notices
				add_action( 'all_admin_notices', array( __CLASS__, 'admin_notices' ) );

				self::$notices[] = compact( 'message', 'is_error' );
			}
		}

		/**
		 * Show an error or other message in the WP Admin
		 *
		 * @action all_admin_notices
		 * @return void
		 */
		public static function admin_notices() {
			foreach ( self::$notices as $notice ) {
				$class_name   = empty( $notice['is_error'] ) ? 'updated' : 'error';
				$html_message = sprintf( '<div class="%s">%s</div>', esc_attr( $class_name ), wpautop( $notice['message'] ) );
				echo wp_kses_post( $html_message );
			}
		}
		/* End PHP VERSION CHECKS */

	}

	/* Initiate Plugin */
	if ( Inbound_Leads_Plugin::is_valid_php_version() ) {
		// Get Inbound Now Running
		$GLOBALS['Inbound_Leads_Plugin'] = new Inbound_Leads_Plugin;
	} else {
		// Show Failure message
		Inbound_Leads_Plugin::fail_php_version();
	}

	/* method to see if leads is active. Legacy */
	function wpleads_check_active() {
		return true;
	}

}