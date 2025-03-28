<?php
namespace MOD\Helper;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;

class MOD_Utils {
	/**
	 * Sanitize value from custom method
	 *
	 * @since 1.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function request( $type, $name, $default, $sanitize = 'rm_tags' ) {
		$request = filter_input_array( $type, FILTER_SANITIZE_SPECIAL_CHARS );

		if ( ! isset( $request[ $name ] ) || empty( $request[ $name ] ) ) {
			return $default;
		}

		return self::sanitize( $request[ $name ], $sanitize );
	}

	/**
	 * Sanitize value from methods post
	 *
	 * @since 1.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function post( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_POST, $name, $default, $sanitize );
	}

	/**
	 * Sanitize value from methods get
	 *
	 * @since 1.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function get( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_GET, $name, $default, $sanitize );
	}

	/**
	 * Sanitize value from cookie
	 *
	 * @since 1.0
	 * @param String $name
	 * @param Mixed $default
	 * @param String|Array $sanitize
	 * @return Mixed
	*/
	public static function cookie( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_COOKIE, $name, $default, $sanitize );
	}

	/**
	 * Get filtered super global server by key
	 *
	 * @since 1.0
	 * @param String $key
	 * @return String
	*/
	public static function server( $key ) {
		$value = self::get_value_by( $_SERVER, strtoupper( $key ) );

		return self::rm_tags( $value, true );
	}

	/**
	 * Verify request by nonce
	 *
	 * @since 1.0
	 * @param String $name
	 * @param String $action
	 * @return Boolean
	*/
	public static function verify_nonce_post( $name, $action ) {
		return wp_verify_nonce( self::post( $name, false ), $action );
	}

	/**
	 * Sanitize requests
	 *
	 * @since 1.0
	 * @param String $value
	 * @param String|Array $sanitize
	 * @return String
	*/
	public static function sanitize( $value, $sanitize ) {
		if ( ! is_callable( $sanitize ) ) {
	    	return ( false === $sanitize ) ? $value : self::rm_tags( $value );
		}

		if ( is_array( $value ) ) {
			return array_map( $sanitize, $value );
		}

		return call_user_func( $sanitize, $value );
	}

	/**
	 * Properly strip all HTML tags including script and style
	 *
	 * @since 1.0
	 * @param Mixed String|Array $value
	 * @param Boolean $remove_breaks
	 * @return Mixed String|Array
	 */
	public static function rm_tags( $value, $remove_breaks = false ) {
		if ( empty( $value ) || is_object( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return array_map( __METHOD__, $value );
		}

	    return wp_strip_all_tags( $value, $remove_breaks );
	}

	/**
	 * Find the position of the first occurrence of a substring in a string
	 *
	 * @since 1.0
	 * @param String $value
	 * @param String $search
	 * @return Boolean
	*/
	public static function indexof( $value, $search ) {
		return ( false !== strpos( $value, $search ) );
	}

	/**
	 * Verify request ajax
	 *
	 * @since 1.0
	 * @param null
	 * @return Boolean
	*/
	public static function is_request_ajax() {
		return ( strtolower( self::server( 'HTTP_X_REQUESTED_WITH' ) ) === 'xmlhttprequest' );
	}

	/**
	 * Get charset option
	 *
	 * @since 1.0
	 * @param Null
	 * @return String
	 */
	public static function get_charset() {
		return self::rm_tags( get_bloginfo( 'charset' ) );
	}

	/**
	 * Descode html entityes
	 *
	 * @since 1.0
	 * @param String $string
	 * @return String
	 */
	public static function html_decode( $string ) {
		return html_entity_decode( $string, ENT_NOQUOTES, self::get_charset() );
	}

	/**
	 * Get value by array index
	 *
	 * @since 1.0
	 * @param Array $args
	 * @param String|int $index
	 * @return String
	 */
	public static function get_value_by( $args, $index, $default = '' ) {
		if ( ! array_key_exists( $index, $args ) || empty( $args[ $index ] ) ) {
			return $default;
		}

		return $args[ $index ];
	}

	/**
	 * Admin sanitize url
	 *
	 * @since 1.0
	 * @param String $path
	 * @return String
	 */
	public static function get_admin_url( $path = '' ) {
		return esc_url( get_admin_url( null, $path ) );
	}

	/**
	 * Site URL
	 *
	 * @since 1.0
	 * @param String $path
	 * @return String
	 */
	public static function get_site_url( $path = '' ) {
		return esc_url( get_site_url( null, $path ) );
	}

	/**
	 * Permalink url sanitized
	 *
	 * @since 1.0
	 * @param Integer $post_id
	 * @return String
	 */
	public static function get_permalink( $post_id = 0 ) {
		return esc_url( get_permalink( $post_id ) );
	}

	/**
	 * Add prefix in string
	 *
	 * @since 1.0
	 * @param String $after
	 * @param String $before
	 * @return String
	 */
	public static function add_prefix( $after, $before = '' ) {
		return $before . Core::SLUG . $after;
	}

	/**
	 * Get date formatted for i18n
	 *
	 * @param String $date
	 * @param String $format
	 * @return String
	 */
	public static function convert_date_i18n( $date, $format = 'd/m/Y' ) {
		return empty( $date ) ? '' : self::convert_date( $date, $format, '/', '-' );
	}

	/**
	 * Get date formatted for SQL
	 *
	 * @param String $date
	 * @param String $format
	 * @return String
	 */
	public static function convert_date_for_sql( $date, $format = 'Y-m-d' ) {
		return empty( $date ) ? '' : self::convert_date( $date, $format, '/', '-' );
	}

	/**
	 * Conversion of date
	 *
	 * @param String $date
	 * @param String $format
	 * @param String $search
	 * @param String $replace
	 * @return String
	 */
	public static function convert_date( $date, $format = 'Y-m-d', $search = '/', $replace = '-' ) {
		if ( $search && $replace ) {
			$date = str_replace( $search, $replace, $date );
		}

		return date_i18n( $format, strtotime( $date ) );
	}

	/**
	 * Get template
	 *
	 * @param String $file
	 * @param Array $args
	 */
	public static function get_template( $file, $args = [] ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$locale = Core::plugin_dir_path() . $file . '.php';

		if ( ! file_exists( $locale ) ) {
			return;
		}

		include $locale;
	}

	/**
	 * Get Token
	 *
	 * @return string
	 */
	public static function mod_get_token() {
		$options = get_option( 'mod_auto_updates_field' );
		$token   = $options['mod_token_number'];

		return $token;
	}

	public static function mod_plugin_dir( $args ) {
        $upload_dir = wp_upload_dir();
        $mod_path   = $upload_dir['basedir'].'/'.Core::TMP_MOD;

        if ( ! file_exists( $mod_path ) ) mkdir( $mod_path, 0775 );
        $plugin_file  = trailingslashit( $mod_path ) . Core::PLUGINS_JSON;

        if ( file_exists( $plugin_file ) ) {
            return;
        }

        $fp = fopen( $plugin_file, 'w' );

        fwrite( $fp, json_encode( $args ) );
        fclose( $fp );
    }

	public static function get_plugin_json() {
		$upload_dir  = wp_upload_dir();
        $mod_path    = $upload_dir['basedir'].'/'.Core::TMP_MOD;
		$plugin_file = trailingslashit( $mod_path ) . Core::PLUGINS_JSON;

        return ( file_exists( $plugin_file ) ) ? file_get_contents( $plugin_file ) : '';
    }
	/**
	 * Remove Files
	 *
	 * @return Boolean
	 */
	public static function is_empty_dir( $dirname ) {
		$files = glob( $dirname.'*' );

		foreach ( $files as $file ) {
			if ( is_file( $file ) )
			unlink($file);
		}
	}

	public static function mod_get_client( $token ) {
		$response = wp_remote_get(
			Core::API_CLIENTS . '/single/'. $token,
		[
			'timeout'   => 200,
			'sslverify' => false
		]);

		$response_body = wp_remote_retrieve_body( $response );
		$response      = json_decode( $response_body );
		$status        = isset( $response->data->status ) ? $response->data->status : false;

		if ( $status == 404 ) {
			update_option( 'mod_client_email', '' );
			update_option( 'mod_client_expire_in', '' );
            update_option( 'mod_auto_updates_field', [ 'mod_token_number' => $token ] );

			wp_send_json_error( [
                'message' => isset( $response->message ) ? $response->message : __( 'Tente novamente mais tarde!' ),
            ] );
		}

		$email     = isset( $response[0]->email ) ? $response[0]->email : '';
		$expire_in = isset( $response[0]->expire_in ) ? $response[0]->expire_in : '';

		if ( !$email ) {
			wp_send_json_error( [
				'message' => $response,
			] );
		}

		update_option( 'mod_client_email', $email );
		update_option( 'mod_client_expire_in', $expire_in );
		update_option( 'mod_auto_updates_field', [ 'mod_token_number' => $token ] );

		return [
			'email'     => $email,
			'expire_in' => $expire_in
		];
	}

	public static function mod_get_client_curl( $token ) {
		$curl = curl_init();

		curl_setopt_array(
			$curl,
			[
				CURLOPT_URL            => Core::API_CLIENTS . '/'. $token,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "GET",
				CURLOPT_POSTFIELDS     => "",
				CURLOPT_SSL_VERIFYPEER => false
			]
		);

		$response       = curl_exec( $curl );
		$response_error = curl_error( $curl );

		curl_close( $curl );

		if ( $response_error ) {
			wp_send_json_error( [
                'message' => $response_error,
            ] );
		}

		$response = json_decode( $response );
		$status   = isset( $response->data->status ) ? $response->data->status : false;

		if ( $status == 404 ) {
			wp_send_json_error( [
                'message' => $response,
            ] );
		}

		$response = isset( $response[0] ) ? $response[0] : '';

		return (array) $response;
	}

	public static function mod_get_expire_date( $expire_in ) {
		$now       = time();
		$expire_in = strtotime( self::convert_date_for_sql( $expire_in ) );
		$date_diff = $expire_in - $now;


		return intval( round( $date_diff / ( 60 * 60 * 24 ), 0 ) );
	}

	public static function mod_download_folder( $path, $directory ) {
		$scanned_directory = array_diff( scandir( $path ), [ '..', '.' ] );

		foreach ( $scanned_directory as $file ) {
			if ( is_dir( $path . '/' . $file ) ) {
				$new_name = substr( $file, 0, strpos( $file, '-main-' ) );
				rename( $path . '/' . $file, $path . '/' . $new_name );

				if ( ! empty( $new_name ) ) {
					$source_directory      =  $path . '/' . $new_name;
					$destination_directory = $directory . '/' . $new_name;

					self::mod_copy_directory( $source_directory, $destination_directory );
					self::mod_delete_directory( $source_directory );
				}
			}
		}
	}

	public static function mod_copy_directory( $source_directory, $destination_directory ) {
		if ( file_exists( $destination_directory ) ) {
			self::mod_rrmdir( $destination_directory );
		}

		mkdir( $destination_directory, 0755 );

		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $source_directory, \RecursiveDirectoryIterator::SKIP_DOTS ), \RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $item ) {
			if ( $item->isDir()) {
				mkdir( $destination_directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName() );
			} else {
				copy( $item, $destination_directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName() );
			}
		}
	}

	public static function mod_rrmdir( $dir ) {
		if ( is_dir( $dir ) ) {
			$objects = scandir($dir);

			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( filetype( $dir . "/" . $object ) == "dir" ) {
						self::mod_rrmdir( $dir . "/" . $object );
					} else {
						unlink( $dir . "/" . $object );
					}
				}
			}

			reset( $objects );
			rmdir( $dir );
		}
	}

	public static function mod_delete_directory( $path ) {
		$dir = opendir( $path );

		while( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				$full = $path . '/' . $file;

				if ( is_dir( $full ) ) {
					self::mod_delete_directory( $full );
				} else {
					unlink( $full );
				}
			}
		}

		closedir( $dir );
		rmdir( $path );
	}

	public static function mod_decrypt_data( $encrypted_data ) {

		if ( ! $encrypted_data ) {
			return;
		}

		$encrypted_data = base64_decode( $encrypted_data );
		$iv             = substr( $encrypted_data, 0, 16 );
   		$data           = substr( $encrypted_data, 16 );
		$key            = 'mercadodigital';

		return openssl_decrypt( $data, 'AES-256-CBC', $key, 0, $iv );
	}
}
