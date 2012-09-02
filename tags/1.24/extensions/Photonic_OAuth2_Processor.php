<?php

abstract class Photonic_OAuth2_Processor extends Photonic_Processor {
	public $scope, $response_type, $client_id, $client_secret, $state;

	function __construct() {
		parent::__construct();
	}

	public abstract function authentication_url();

	public abstract function access_token_url();

	public function redirect_url() {
		return get_site_url();
	}

	public function authorize() {
		$method = 'POST';
		$parameters = array(
			'response_type' => $this->response_type,
			'client_id' => $this->client_id,
			'redirect_uri' => $this->redirect_url(),
			'state' => md5($this->client_secret.'picasa'),
			'scope' => $this->scope,
		);
		return $this->authentication_url()."?".$this->build_query($parameters);
	}

	/**
	 * Takes an OAuth request token and exchanges it for an access token.
	 *
	 * @param $request_token
	 */
	function get_access_token($request_token) {
		$method = 'POST';

		$signature = $this->generate_signature($this->access_token_URL(), array(), $method, $request_token);
		$parameters = array (
			'oauth_consumer_key' => $this->api_key,
			'oauth_nonce' => $this->nonce,
			'oauth_signature' => $signature,
			'oauth_signature_method' => $this->oauth_signature_method(),
			'oauth_timestamp' => $this->oauth_timestamp,
			'oauth_token' => $request_token['oauth_token'],
			'oauth_version' => $this->oauth_version,
		);

		if (isset($request_token['oauth_verifier'])) {
			$parameters['oauth_verifier'] = $request_token['oauth_verifier'];
		}

		if ($this->provider == 'smug') {
			$parameters['method'] = $this->access_token_URL();
		}

		$end_point = $this->provider == 'smug' ? $this->end_point() : $this->access_token_URL();
		if ($method == 'GET') {
			$end_point .= '?'.Photonic_Processor::build_query($parameters);
			$parameters = null;
		}

		$response = Photonic::http($end_point, $method, $parameters);
		$token = $this->parse_token($response);

		$secret = 'photonic_'.$this->provider.'_client_secret';
		global $$secret;
		// We will has the secret to store the cookie. Otherwise the cookie for the visitor will have the secret for the app for the plugin user.
		$secret = md5($$secret, false);

		if (isset($token['oauth_token']) && isset($token['oauth_token_secret'])) {
			setcookie('photonic-'.$secret.'-oauth-token', $token['oauth_token'], time() + 365 * 60 * 60 * 24, COOKIEPATH);
			setcookie('photonic-'.$secret.'-oauth-token-secret', $token['oauth_token_secret'], time() + 365 * 60 * 60 * 24, COOKIEPATH);
			setcookie('photonic-'.$secret.'-oauth-token-type', 'access', time() + 365 * 60 * 60 * 24, COOKIEPATH);
		}

		return $token;
	}
}