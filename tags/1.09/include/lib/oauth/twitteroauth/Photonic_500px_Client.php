<?php
/**
 * OAuth client for 500px. This is based heavily on Abraham Williams' TwitterOAuth functions.
 *
 */
class Photonic_500px_Client extends Photonic_OAuth_Client {
	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'https://api.500px.com/v1/oauth/access_token';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		return 'https://api.500px.com/v1/oauth/authorize';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		return 'https://api.500px.com/v1/oauth/authorize';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_Token_URL() {
		return 'https://api.500px.com/v1/oauth/request_token';
	}
}
?>