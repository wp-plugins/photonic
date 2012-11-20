<?php
/**
 * Processor for Instagram. This extends the Photonic_OAuth2_Processor class and defines methods local to Instagram.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Instagram_Processor extends Photonic_OAuth2_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_instagram_client_id, $photonic_instagram_client_secret;
		$this->client_id = $photonic_instagram_client_id;
		$this->client_secret = $photonic_instagram_client_secret;
		$this->provider = 'instagram';
		$this->oauth_version = '2.0';
		$this->response_type = 'code';
		$this->scope = 'basic';

		$cookie = Photonic::parse_cookie();
		global $photonic_instagram_allow_oauth;
		$this->oauth_done = false;
		if ($photonic_instagram_allow_oauth && isset($cookie['instagram']) && isset($cookie['instagram']['oauth_token']) && isset($cookie['instagram']['oauth_refresh_token'])) { // OAuth2, so no Access token secret
			if ($this->is_token_expired($cookie['instagram'])) {
				$this->refresh_token($cookie['instagram']['oauth_refresh_token']);
				$cookie = Photonic::parse_cookie(); // Refresh the cookie object based on the results of the refresh token
				if ($this->is_token_expired($cookie['instagram'])) { // Tried refreshing, but didn't work
					$this->oauth_done = false;
				}
				else {
					$this->oauth_done = true;
				}
			}
			else {
				$this->oauth_done = true;
			}
		}
		else if (!isset($cookie['instagram']) || !isset($cookie['instagram']['oauth_token']) || !isset($cookie['instagram']['oauth_refresh_token'])) {
			$this->oauth_done = false;
		}
	}

	public function authentication_url() {
		return 'https://api.instagram.com/oauth/authorize';
	}

	public function access_token_url() {
		return 'https://api.instagram.com/oauth/access_token';
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @param array $attr
	 */
	protected function get_gallery_images($attr = array()) {
		// TODO: Implement get_gallery_images() method.
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public function parse_token($response) {
		// TODO: Implement parse_token() method.
	}
}