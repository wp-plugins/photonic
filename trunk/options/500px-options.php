<?php
global $photonic_500px_options;

$photonic_500px_options = array(
	array("name" => "500px settings",
		"desc" => "Control settings for 500px",
		"category" => "500px-settings",
		"type" => "section",),

	array("name" => "500px API Consumer Key",
		"desc" => "To make use of the 500px functionality you have to use your 500px API Consumer Key.
							You can <a href='http://developers.500px.com/oauth_clients'>register an application and obtain a key online</a> if you don't have one.
							Note that you are responsible for following all of the 500px API's <a href='http://developer.500px.com/docs/terms'>Terms of Service</a>",
		"id" => "500px_api_key",
		"grouping" => "500px-settings",
		"type" => "text",
		"std" => ""),

	array("name" => "500px API Consumer Secret",
		"desc" => "You have to enter the Customer Secret provided by 500px after you have registered your application.",
		"id" => "500px_api_secret",
		"grouping" => "500px-settings",
		"type" => "text",
		"std" => ""),

	array("name" => "500px Authentication",
		"desc" => "To make use of the Flickr functionality you have to use your Flickr API Key.
							You can <a href='http://www.flickr.com/services/api/misc.api_keys.html'>obtain a key online</a> if you don't have one.
							Note that you are responsible for following all of the Flickr API's <a href='http://www.flickr.com/services/api/tos/'>Terms of Service</a>",
		"id" => "500px_api_auth",
		"grouping" => "500px-settings",
		"type" => "ajax-button",
		"conditional" => true,
		"conditions" => array('operator' => 'NOR', 'conditions' => array('500px_api_key' => '', '500px_api_secret' => '')),
		"std" => "Authenticate your site"),
);
?>