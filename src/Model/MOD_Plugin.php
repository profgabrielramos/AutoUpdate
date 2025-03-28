<?php
namespace MOD\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;
use MOD\Helper\MOD_Utils as Utils;

class MOD_Plugin {

	/**
	 * API Timeout
	 * 
	 * @since 3.1.0
	 */
	const API_TIMEOUT  = 200;

	/**
	 * PATH Plugins
	 * 
	 * @since 3.1.0
	 */
	const PATH_PLUGINS = '/mod-updates/plugins';

	/**
	 * Plugin API JSON
	 *
	 * @since 3.1.0
	 * @return Array
	 */
	public static function get_plugin_api_json() {
		$token = Utils::mod_get_token();

		if ( !$token ) {
			return;
		}

		$response = wp_remote_get(
			Core::API_CLIENTS . '/plugins/' . $token,
			[
				'headers'   => ['content-type' => 'application/json'],
				'timeout'   => self::API_TIMEOUT,
				'sslverify' => false
			]
		);

		if ( is_wp_error( $response ) ) {
            error_log( 'get_plugin_api_json: Erro na requisição da API: ' . $response->get_error_message() );
            return;
        }

		$response_body = wp_remote_retrieve_body( $response );
		$response      = json_decode( $response_body );

		if ( $response ) {
			$plugins     = $response[0]->plugins;
			$response_id = Utils::mod_decrypt_data( $response[0]->id );

			if ( !$plugins || !$response_id ) {
				return;
			}

			$hash       = str_replace( $token.':', '', $response_id );
			$wp_plugins = self::mod_get_plugin_info();
			$wp_slugs   = array_column( $wp_plugins, 'slug' );
			$wp_version = array_column( $wp_plugins, 'version' );

			update_option( '_mod_plugins_datajson', $plugins );

			foreach ( $plugins as $plugin ) {
				foreach ( $wp_slugs as $key => $plugin_slug ) {
					if ( $plugin_slug === $plugin->slug ) {
						if ( version_compare( $wp_version[$key], $plugin->version, '<' ) ) {
							$url = $plugin->url .'&private_token='.$hash;

							self::save_plugin_tmp( $url, $plugin->slug );
						}
					}
				}
			}
		}
	}

	/**
	 * Save Plugin TMP
	 *
	 * @since 3.1.0
	 * @param String $url
	 * @param String $slug
	 * @return void
	 */
	public static function save_plugin_tmp( $url, $slug ) {
		$upload_dir = wp_upload_dir();
		$path       = $upload_dir['basedir'] . self::PATH_PLUGINS;
        
		if ( !file_exists( $path ) ) {
            mkdir( $path, 0775 );
        }

		$file_slug = $path .'/'. $slug;
		$zip_file  = $file_slug . '.zip';
		$fh        = fopen( $zip_file, "w" );
		$ch        = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_FILE, $fh );
		curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
            error_log( 'Erro cURL: ' . curl_error( $ch ) );
        }

		curl_close( $ch );

		if ( filesize( $zip_file ) > 0 ) {
			$zip = new \ZipArchive;
	
			if ( $zip->open( $zip_file ) === true ) {
				$extractResult = $zip->extractTo( $path );
	
				if ( !$extractResult ) {
					error_log( 'save_plugin_tmp: Falha ao extrair arquivo ZIP.' );
				}
	
				unlink( $zip_file );
	
				Utils::mod_download_folder( $path, WP_PLUGIN_DIR );
	
				$zip->close();
			} else {
				error_log( 'save_plugin_tmp: Falha ao abrir arquivo ZIP.' );
			}
		} else {
			error_log( 'save_plugin_tmp: Arquivo ZIP vazio.' );
		}
	}

	/**
	 * Get Plugin Info
	 *
	 * @since 3.1.0
	 * @return Array
	 */
	public static function mod_get_plugin_info() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins' );

		foreach ( $all_plugins as $key => $value ) {
			$is_active = ( in_array( $key, $active_plugins ) ) ? true : false;
			$slug      = substr( $key, 0, stripos( $key, "/" ) );

			if ( $is_active == true && $slug != MOD_SLUG ) {
				$plugins[] = [
					'name'     => $value['Name'],
					'slug'     => $slug,
					'version'  => $value['Version'],
					'active'   => $is_active,
				];
			}
		}

		return $plugins;
	}
}