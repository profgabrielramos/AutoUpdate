<?php
namespace MOD\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use MOD\MOD_Core as Core;
use MOD\Helper\MOD_Utils as Utils;

class MOD_Theme {

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
	const PATH_THEMES = '/mod-updates/themes';

	/**
	 * Theme API JSON
	 *
	 * @since 3.1.0
	 * @return Array
	 */
	public static function get_theme_api_json() {
		$token = Utils::mod_get_token();

		if ( !$token ) {
			return;
		}

		$response = wp_remote_get(
			Core::API_CLIENTS . '/themes/' . $token,
			[
				'headers'   => ['content-type' => 'application/json'],
				'timeout'   => self::API_TIMEOUT,
				'sslverify' => false
			]
		);

		if ( is_wp_error( $response ) ) {
            error_log( 'get_theme_api_json: Erro na requisição da API: ' . $response->get_error_message() );
            return;
        }

		$response_body = wp_remote_retrieve_body( $response );
		$response      = json_decode( $response_body );

		if ( $response  ) {
			$themes      = $response[0]->themes;
			$response_id = Utils::mod_decrypt_data( $response[0]->id );

			if ( !$themes || !$response_id ) {
				return;
			}

			$hash       = str_replace( $token.':', '', $response_id );
			$wp_themes  = self::mod_get_theme_info();
			$wp_slugs   = array_column( $wp_themes, 'slug' );
			$wp_version = array_column( $wp_themes, 'version' );

			update_option( '_mod_themes_datajson', $themes );

			foreach ( $themes as $theme ) {
				$url = $theme->url .'&private_token='.$hash;

				foreach ( $wp_slugs as $key => $theme_slug ) {
					 if ( $theme_slug === $theme->slug ) {
						if ( version_compare( $wp_version[$key], $theme->version, '<' ) ) {
					 		$url = $theme->url .'&private_token='.$hash;

							self::save_theme_tmp( $url, $theme->slug );
						}
					}
				}
			}
		}
	}

	/**
	 * Save Theme TMP
	 *
	 * @since 3.1.0
	 * @param String $url
	 * @param String $slug
	 * @return void
	 */
	public static function save_theme_tmp( $url, $slug ) {
		$upload_dir = wp_upload_dir();
		$path       = $upload_dir['basedir'] . self::PATH_THEMES;
        
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
					error_log( 'save_theme_tmp: Falha ao extrair arquivo ZIP.' );
				}
	
				unlink( $zip_file );
	
				Utils::mod_download_folder( $path, WP_THEME_DIR );
	
				$zip->close();
			} else {
				error_log( 'save_theme_tmp: Falha ao abrir arquivo ZIP.' );
			}
		} else {
			error_log( 'save_theme_tmp: Arquivo ZIP vazio.' );
		}
	}

	/**
	 * Get Theme Info
	 *
	 * @since 3.1.0
	 * @return Array
	 */
	public static function mod_get_theme_info() {
		$themes     = [];
		$get_themes = wp_get_themes();

		foreach ( $get_themes as $theme ) {
			$stylesheet = $theme->get_stylesheet();
			$themes[]   = [
				'name'    => $theme->get( 'Name' ),
				'slug'    => $stylesheet,
				'version' => $theme->get( 'Version' )
			];
		}

		return $themes;
	}
}