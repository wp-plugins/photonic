<?php
/**
 * OAuth client for SmugMug. This is based heavily on Abraham Williams' TwitterOAuth functions.
 *
 */
require(PHOTONIC_PATH . '/include/lib/oauth/twitteroauth/OAuth_Client.php');

class Photonic_SmugMug_Client extends Photonic_OAuth_Client {
	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'http://api.smugmug.com/services/oauth/getAccessToken.mg';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		return 'http://api.smugmug.com/services/oauth/authorize.mg';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		return 'http://api.smugmug.com/services/oauth/authorize.mg';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_Token_URL() {
		return 'http://api.smugmug.com/services/oauth/getRequestToken.mg';
	}

/*	function get_access_token($oauth_verifier = false) {
		$perms = (array_key_exists('Permissions', $args)) ? $args['Permissions'] : 'Public';
		$access = (array_key_exists('Access', $args)) ? $args['Access'] : 'Read';
		return $token;
	}*/

	public function authorize($args = array()) {
//		$args = phpSmug::processArgs(func_get_args());
		$perms = (array_key_exists('Permissions', $args)) ? $args['Permissions'] : 'Public';
		$access = (array_key_exists('Access', $args)) ? $args['Access'] : 'Read';
		return $this->authorize_URL()."?Access=$access&Permissions=$perms&oauth_token={$this->oauth_token}";
	}
}