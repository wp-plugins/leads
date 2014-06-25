<?php


if ( !class_exists('Inbound_Akismet') ) {

	class Inbound_Akismet {

		function __construct() {
			self::load_hooks();
		}

		private function load_hooks() {
			/* Load hooks if akismet filtering is enabled */
			if (get_option('inbound_forms_enable_akismet' , '1' )) {
				add_action( 'inbound_store_lead_pre' , array( __CLASS__ , 'check_is_spam' ) );
			}
		}
		
		public static function check_is_spam( $lead_data ) {
			$api_key = Inbound_Akismet::get_api_key();

			if (!$api_key) {
				return;
			}

			$params = Inbound_Akismet::prepare_params( $lead_data );
			Inbound_Akismet::api_check( $params );
				
		}
		
		public static function api_check( $params ) {
			global $akismet_api_host, $akismet_api_port;

			/* bail if no content to check against akismet */
			if (!isset($params['comment_content'])) {
				return;
			}

			$spam = false;
			$query_string = '';

			foreach ( $params as $key => $data ) {
				$query_string .= $key . '=' . urlencode( wp_unslash( (string) $data ) ) . '&';
			}

			if ( is_callable( array( 'Akismet', 'http_post' ) ) ) { // Akismet v3.0+
				$response = Akismet::http_post( $query_string, 'comment-check' );
			} else {
				$response = akismet_http_post( $query_string, $akismet_api_host,
					'/1.1/comment-check', $akismet_api_port );
			}

			if ( 'true' == $response[1] ) {
				error_log( 'spam' );
				/* is spam bot kill store command */
				exit;
			} 
			
			error_log('not spam');
				
		}
		
		/* Get Akismet API key */
		public static function get_api_key() {

			if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) { // Akismet v3.0+
				return (bool) Akismet::get_api_key();
			}

			if ( function_exists( 'akismet_get_key' ) ) {
				return (bool) akismet_get_key();
			}

			return false;
		}

		/* Extract lead data and prepare params for akismet filtering */
		public static function prepare_params( $lead_data ) {			

			$first_name = (isset($lead_data['wpleads_first_name'])) ? $lead_data['wpleads_first_name'] : '';
			$last_name = (isset($lead_data['wpleads_last_name'])) ? $lead_data['wpleads_last_name'] : '';
			$email_address = (isset($lead_data['wpleads_email_address'])) ? $lead_data['wpleads_email_address'] : '';

			$content = Inbound_Akismet::detect_content( $lead_data['form_input_values'] );
	
		
			$params = array(
				'comment_author' => $first_name . ' ' . $last_name,
				'comment_author_email' => $email_address,
				'comment_content' => $content
			);
			
			$params['blog'] = get_option( 'home' );
			$params['blog_lang'] = get_locale();
			$params['blog_charset'] = get_option( 'blog_charset' );
			$params['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$params['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$params['referrer'] = $_SERVER['HTTP_REFERER'];
			$params['permalink'] = $_SERVER['HTTP_REFERER'];
			$params['comment_type'] = 'contact-form';
		
			return $params;
		}
		
		public static function detect_content( $form_submit_values ) {
			$form_submit_values = json_decode( stripslashes($form_submit_values) , true );

			/* If notes is mapped to the form then use the 'wpleads_notes' map key */
			if (isset($form_submit_values['wpleads_notes'])) {
				return $form_submit_values['wpleads_notes'];
			}
			
			/* detect multi-line content in form submission */
			foreach ( $form_submit_values as $key => $value ) {
				if ( substr_count( $value, "\n" ) > 1 ) {
					error_log( $key );
					return $value;
				}
			}
			
			return ''; 
		}
		
	}

	/* Load Email Templates Post Type Pre Init */
	add_action('init' , function() {
		$GLOBALS['Inbound_Akismet'] = new Inbound_Akismet();
	} );
}