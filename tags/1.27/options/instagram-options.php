<?php
global $photonic_instagram_options;

$photonic_instagram_options = array(
	array("name" => "Instagram settings",
		"desc" => "Control settings for Instagram",
		"category" => "instagram-settings",
		"type" => "section",),

	array("name" => "Instagram Client ID",
		"desc" => "Enter your Instagram Client ID. You can get / create one from Instagram's <a href='https://code.google.com/apis/console#access'>API Console</a>.
			While setting up your Instagram Client ID from the API Console:
			<ol>
				<li>Use the option for 'Client ID for web applications'.</li>
				<li>Make sure that you add ".site_url()." as your Redirect URI. <strong>Without that your authentication will not work.</strong></li>
			</ol>
			<strong>You only need this if you have private photos that you want people to login to see.</strong>",
		"id" => "instagram_client_id",
		"grouping" => "instagram-settings",
		"type" => "text",
		"std" => ""),

	array("name" => "Instagram Client Secret",
		"desc" => "Enter your Instagram Client Secret.	You only need this if you have private photos that you want people to login to see.",
		"id" => "instagram_client_secret",
		"grouping" => "instagram-settings",
		"type" => "text",
		"std" => ""),

	array("name" => "Private Photos",
		"desc" => "Let visitors of your site login to Instagram to see private photos for which they have permissions (will show a login button if they are not logged in)",
		"id" => "instagram_allow_oauth",
		"grouping" => "instagram-settings",
		"type" => "checkbox",
		"std" => ""),

	array("name" => "Login Box Text",
		"desc" => "If private photos are enabled, this is the text users will see before the login button (you can use HTML tags here)",
		"id" => "instagram_login_box",
		"grouping" => "instagram-settings",
		"type" => "textarea",
		"std" => "Some features that you are trying to access may be visible to logged in users of Instagram only. Please login if you want to see them."),

	array("name" => "Login Button Text",
		"desc" => "If private photos are enabled, this is the text users will see before the login button (you can use HTML tags other than &lt;a&gt; here)",
		"id" => "instagram_login_button",
		"grouping" => "instagram-settings",
		"type" => "text",
		"std" => "Login"),

	array("name" => "Instagram Photos - \"In-page\" View",
		"desc" => "Control settings for Instagram Photos when displayed in your page",
		"category" => "instagram-photos",
		"type" => "section",),

	array("name" => "What is this section?",
		"desc" => "Options in this section are in effect when you use the shortcode format <code>[gallery type='instagram' user_id='abc']</code>. In other words, the photos are printed directly on the page.",
		"grouping" => "instagram-photos",
		"type" => "blurb",),
);