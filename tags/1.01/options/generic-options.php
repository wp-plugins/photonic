<?php
global $photonic_setup_options, $photonic_generic_options;

$photonic_generic_options = array(
	array("name" => "Generic settings",
		"desc" => "Control generic settings for the plugin",
		"category" => "generic-settings",
		"type" => "section",),

	array("name" => "Slideshow libraries",
		"desc" => "Photonic lets you choose from the following JS libraries for gallery slideshows:",
		"id" => "slideshow_library",
		"grouping" => "generic-settings",
		"type" => "radio",
		"options" => array("fancybox" => "<a href='http://fancybox.net/'>FancyBox</a> - ~16KB, wide range of effects",
			"colorbox" => "<a href='http://colorpowered.com/colorbox/'>Colorbox</a> - ~10KB, moderate range of effects",
//							 "slimbox2" => "<a href='http://www.digitalia.be/software/slimbox2'>Slimbox2</a> - ~10KB, moderate range of effects",
			"none" => "None"
		),
		"std" => "fancybox"),

	array("name" => "Native WP Galleries",
		"desc" => "Control settings for native WP gallieries, invoked by <code>[gallery id='abc']</code>",
		"category" => "wp-settings",
		"type" => "section",),

	array("name" => "Alignment of image in slideshow",
		"desc" => "If you pass the <code>style</code> parameter to the <code>gallery</code> shortcode and the style is <code>strip-above</code>, <code>strip-below</code> or <code>no-strip</code> the image in the slide will be centered if you select this.",
		"id" => "wp_slide_align",
		"grouping" => "wp-settings",
		"type" => "checkbox",
		"std" => ""
	),

	array("name" => "Thumbnail Title Display",
		"desc" => "How do you want the title of the Thumbnails displayed?",
		"id" => "wp_thumbnail_title_display",
		"grouping" => "wp-settings",
		"type" => "select",
		"options" => array(
			"regular" => "Normal title display using the HTML \"title\" attribute",
			"below" => "Below the thumbnail",
			"tooltip" => "Using the <a href='http://bassistance.de/jquery-plugins/jquery-plugin-tooltip/'>JQuery Tooltip</a> plugin",
		),
		"std" => "tooltip"),

	array("name" => "JS Library settings",
		"desc" => "Control settings for the JS libraries distributed with the theme",
		"category" => "fbox-settings",
		"type" => "section",),

	array("name" => "Position of title in FancyBox slideshow",
		"desc" => "Fancybox lets you show the title of the image in different positions. Where do you want it?",
		"id" => "fbox_title_position",
		"grouping" => "fbox-settings",
		"type" => "radio",
		"options" => array(
			"outside" => "Outside the slide box",
			"inside" => "Inside the slide box",
			"over" => "Over the image in the slide box",
		),
		"std" => "inside"),

	array("name" => "Flickr / Picasa Popup Panel",
		"desc" => "Control settings for popup panel",
		"category" => "photos-pop",
		"type" => "section",),

	array("name" => "What is this section?",
		"desc" => "Options in this section are in effect when you click on a Photoset thumbnail to launch an overlaid gallery.",
		"grouping" => "photos-pop",
		"type" => "blurb",),

	array("name" => "Overlaid (popup) Gallery Panel Width",
		"desc" => "When you click on a gallery (particularly for Flickr), it launches a panel on top of your page. What is the width you want to assign to this gallery?",
		"id" => "gallery_panel_width",
		"grouping" => "photos-pop",
		"type" => "text",
		"hint" => "Enter the number of pixels here (don't enter 'px').",
		"std" => "800"),

	array("name" => "Overlaid (popup) Gallery Panel background",
		"desc" => "Setup the background of the overlaid gallery (popup).",
		"id" => "flickr_gallery_panel_background",
		"grouping" => "photos-pop",
		"type" => "background",
		"options" => array(),
		"std" => array("color" => '#111111', "image" => "", "trans" => "0",
			"position" => "top left", "repeat" => "repeat", "attachment" => "scroll", "colortype" => "custom")),

	array("name" => "Overlaid (popup) Gallery Border",
		"desc" => "Setup the border of overlaid gallery (popup).",
		"id" => "flickr_set_popup_thumb_border",
		"grouping" => "photos-pop",
		"type" => "border",
		"options" => array(),
		"std" => array(
			'top' => array('colortype' => 'custom', 'color' => '#333333', 'style' => 'solid', 'border-width' => 1, 'border-width-type' => 'px'),
			'right' => array('colortype' => 'custom', 'color' => '#333333', 'style' => 'solid', 'border-width' => 1, 'border-width-type' => 'px'),
			'bottom' => array('colortype' => 'custom', 'color' => '#333333', 'style' => 'solid', 'border-width' => 1, 'border-width-type' => 'px'),
			'left' => array('colortype' => 'custom', 'color' => '#333333', 'style' => 'solid', 'border-width' => 1, 'border-width-type' => 'px'),
		),
	),

	array("name" => "Overlaid Gallery Panel number of items",
		"desc" => "How many thumbnails do you want to show in a gallery panel? The extra thumbnails can be accessed by previous and next links",
		"id" => "gallery_panel_items",
		"grouping" => "photos-pop",
		"type" => "slider",
		"options" => array("range" => "min", "min" => 1, "max" => 100, "step" => 1, "size" => "400px", "unit" => ""),
		"std" => "20"),
);

?>