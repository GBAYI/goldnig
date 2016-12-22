<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Pluginlytics' ) ) {
	class Pluginlytics {

		public static function send_tracking_data( $api_url = '', $override = false ) {

			if ( ! apply_filters( 'pluginlytics_send_override', $override ) ) {
				// Send a maximum of once per week by default.
				$last_send = self::get_last_sent();
				if ( $last_send && $last_send > apply_filters( 'pluginlytics_last_send_interval', strtotime( '-1 week' ) ) ) {
					return;
				}
			}

			$api_url = apply_filters( 'pluginlytics_api_url', $api_url );

			if( empty( $api_url ) )
				return;

			$tracking_data   = self::get_tracking_data();
			$response = wp_remote_post( $api_url, array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array( 'user-agent' => 'pluginlytics/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
					'body'        => json_encode( $tracking_data ),
					'cookies'     => array()
				)
			);
			if ( ! is_wp_error( $response ) && '200' == wp_remote_retrieve_response_code( $response ) ) {
				update_option( 'pluginlytics_last_sent', time() );
			}
		}

		private static function get_last_sent() {
			return apply_filters( 'pluginlytics_last_sent', get_option( 'pluginlytics_last_sent', false ) );
		}

		private static function get_tracking_data() {
			$tracking_data = array();
			$tracking_data['url']                = home_url();
			$tracking_data['email']              = apply_filters( 'pluginlytics_admin_email', get_option( 'admin_email' ) );
			$tracking_data['theme']              = self::get_theme_info();
			$tracking_data['wp']                 = self::get_wordpress_info();
			$tracking_data['server']             = self::get_server_info();
			$tracking_data['active_plugins']     = self::get_all_plugins('active_plugins');
			$tracking_data['inactive_plugins']   = self::get_all_plugins('inactive_plugins');
			return apply_filters( 'pluginlytics_tracking_data', $tracking_data );
		}

		public static function get_theme_info() {
			$wp_version = get_bloginfo( 'version' );

			if ( version_compare( $wp_version, '3.4', '<' ) ) {
				$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
				$theme_name = $theme_data['Name'];
				$theme_version = $theme_data['Version'];
			} else {
				$theme_data = wp_get_theme();
				$theme_name = $theme_data->Name;
				$theme_version = $theme_data->Version;
			}
			$theme_child_theme = is_child_theme() ? 'Yes' : 'No';

			return array( 'name' => $theme_name, 'version' => $theme_version, 'child_theme' => $theme_child_theme );
		}

		private static function get_wordpress_info() {
			$wp_data = array();
			$wp_data['debug_mode'] = ( defined('WP_DEBUG') && WP_DEBUG ) ? 'Yes' : 'No';
			$wp_data['locale'] = get_locale();
			$wp_data['version'] = get_bloginfo( 'version' );
			$wp_data['multisite'] = is_multisite() ? 'Yes' : 'No';
			return $wp_data;
		}

		private static function get_server_info() {
			$server_data = array();

			if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
				$server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
			}

			if ( function_exists( 'phpversion' ) ) {
				$server_data['php_version'] = phpversion();
			}

			if ( function_exists( 'ini_get' ) ) {
				$server_data['php_time_limt'] = ini_get( 'max_execution_time' );
				$server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
				$server_data['php_suhosin'] = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
			}

			global $wpdb;
			$server_data['mysql_version'] = $wpdb->db_version();
			$server_data['php_max_upload_size'] = size_format( wp_max_upload_size() );
			$server_data['php_default_timezone'] = date_default_timezone_get();
			$server_data['php_soap'] = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
			$server_data['php_fsockopen'] = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
			$server_data['php_curl'] = function_exists( 'curl_init' ) ? 'Yes' : 'No';

			return $server_data;
		}

		private static function get_all_plugins( $filter = 'all' ) {
			// Ensure get_plugins function is loaded
			if( ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins        	 = get_plugins();
			$active_plugins_keys = get_option( 'active_plugins', array() );
			$active_plugins 	 = array();

			foreach ( $plugins as $k => $v ) {
				// Take care of formatting the data how we want it.
				$formatted = array();
				$formatted['name'] = strip_tags( $v['Name'] );
				if ( isset( $v['Version'] ) ) {
					$formatted['version'] = strip_tags( $v['Version'] );
				}
				if ( isset( $v['Author'] ) ) {
					$formatted['author'] = strip_tags( $v['Author'] );
				}
				if ( isset( $v['Network'] ) ) {
					$formatted['network'] = strip_tags( $v['Network'] );
				}
				if ( isset( $v['PluginURI'] ) ) {
					$formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
				}
				if ( in_array( $k, $active_plugins_keys ) ) {
					// Remove active plugins from list so we can show active and inactive separately
					unset( $plugins[$k] );
					$active_plugins[$k] = $formatted;
				} else {
					$plugins[$k] = $formatted;
				}
			}
			switch ( $filter ){
				case 'active_plugins':
					$response = $active_plugins;
					break;
				case 'inactive_plugins':
					$response = $plugins;
					break;
				default:
					$response = array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
					break;
			}
			
			return $response;
		}

	}
}
