<?php
/**
 * Generic OAuth Client class, to be extended by any new OAuth client.
 * This class is based almost entirely on Abraham Williams' (abraham@abrah.am, http://abrah.am) TwitterOAuth class. This
 * class has been made abstract to let individual clients define their API URLs and host names. Also it makes use of native WP APIs.
 *
 * The original class was the first PHP Library to support OAuth for Twitter's REST API.
 */
abstract class Photonic_OAuth_Client {
	public $http_code; // Last HTTP status code returned
	public $url; // Last API call
	public $host; // Last API root URL
	public $timeout = 30; // Timeout default
	public $ssl_verify_peer = FALSE; // Verify SSL certificate
	public $format; // Response format
	public $decode_json; // Decode returned JSON data
	public $http_info; // Last HTTP headers retunred
	public $user_agent; // User Agent
	public $http_header = array();
	public $method;

	/**
	 * Constructs the OAuth client. Every client defined should pass the host, consumer key and consumer secret to its constructor.
	 *
	 * @param $host
	 * @param $consumer_key
	 * @param $consumer_secret
	 * @param null $oauth_token
	 * @param null $oauth_token_secret
	 * @param string $format
	 * @param bool $decode_json
	 * @param null $user_agent
	 */
	function __construct($host, $consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null, $format = 'json', $decode_json = true, $user_agent = null) {
		$this->host = $host;
		$this->format = $format;
		$this->decode_json = $decode_json;
		$this->user_agent = $user_agent;

		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = null;
		}

		add_action('http_api_curl', array(&$this, 'curl_modify'));
	}

	/**
	 * Access Token URL
	 *
	 * @abstract
	 * @return string
	 */
	public abstract function access_token_URL();

	/**
	 * Authenticate URL
	 *
	 * @abstract
	 * @return string
	 */
	public abstract function authenticate_URL();

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public abstract function authorize_URL();

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public abstract function request_Token_URL();

	/**
	 * Get a request_token from Provider
	 *
	 * @param null $oauth_callback
	 * @return array A key/value array containing oauth_token and oauth_token_secret
	 */
	function get_request_token($oauth_callback = null) {
		$parameters = array();
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}
		$request = $this->oAuthRequest($this->request_Token_URL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * Get the authorize URL
	 *
	 * @param $token
	 * @param bool $sign_in
	 * @return string
	 */
	function get_Authorize_URL($token, $sign_in = true) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}
		if (empty($sign_in)) {
			return $this->authorize_URL() . "?oauth_token={$token}";
		}
		else {
			return $this->authenticate_URL() . "?oauth_token={$token}";
		}
	}

	/**
	 * Exchange request token and secret for an access token and secret, to sign API calls.
	 *
	 * @param bool $oauth_verifier
	 * @return array("oauth_token" => "the-access-token",
	 *				"oauth_token_secret" => "the-access-secret",
	 *				"user_id" => "9436992",
	 *				"screen_name" => "abraham")
	 */
	function getAccessToken($oauth_verifier = false) {
		$parameters = array();
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}
		$request = $this->oAuthRequest($this->access_token_URL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * One time exchange of username and password for access token and secret.
	 *
	 * @param $username
	 * @param $password
	 * @return array("oauth_token" => "the-access-token",
	 *				"oauth_token_secret" => "the-access-secret",
	 *				"user_id" => "9436992",
	 *				"screen_name" => "abraham",
	 *				"x_auth_expires" => "0")
	 */
	function getXAuthToken($username, $password) {
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->oAuthRequest($this->access_token_URL(), 'POST', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * GET wrapper for oAuthRequest.
	 *
	 * @param $url
	 * @param array $parameters
	 * @return array|mixed
	 */
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * POST wrapper for oAuthRequest.
	 *
	 * @param $url
	 * @param array $parameters
	 * @return array|mixed
	 */
	function post($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * DELETE wrapper for oAuthReqeust.
	 *
	 * @param $url
	 * @param array $parameters
	 * @return array|mixed
	 */
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @param $url
	 * @param $method
	 * @param $parameters
	 * @return mixed
	 */
	function oAuthRequest($url, $method, $parameters) {
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}
		$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @param $url
	 * @param $method
	 * @param null $post_fields
	 * @return mixed API results
	 */
	function http($url, $method, $post_fields = NULL) {
		$this->method = $method;
		$curl_args = array(
			'user-agent', $this->user_agent,
			'timeout' => $this->timeout,
			'sslverify' => $this->ssl_verify_peer,
			'headers' => array('Expect:'),
			'method' => $method,
			'body' => $post_fields,
		);

		switch ($method) {
			case 'DELETE':
				if (!empty($post_fields)) {
					$url = "{$url}?{$post_fields}";
				}
		}

		$response = wp_remote_request($url, $curl_args);
		$this->http_code = $response['code'];
		$this->url = $url;
		return $response;
	}

	/**
	 * WP doesn't handle certain parameters for CURL. The following code takes care of them.
	 *
	 * @param $handle
	 * @return
	 */
	function curl_modify($handle) {
		if ($this->method == 'DELETE') {
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		curl_setopt($handle, CURLOPT_HEADERFUNCTION, array(&$this, 'get_header'));
		return $handle;
	}

	/**
	 * Get the header info to store.
	 *
	 * @param $ch
	 * @param $header
	 * @return int
	 */
	function get_header($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}
?>