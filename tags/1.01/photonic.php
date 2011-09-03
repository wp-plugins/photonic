<?php
/**
 * Plugin Name: Photonic
 * Plugin URI: http://aquoid.com/news/plugins/photonic/
 * Description: Extends the native gallery shortcode to support Flickr and Picasa. JS libraries like Fancybox and Colorbox are supported. The plugin also helps convert a regular WP gallery into a slideshow.
 * Version: 1.01
 * Author: Sayontan Sinha
 * Author URI: http://mynethome.net/blog
 * License: GNU General Public License (GPL), v2 (or newer)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2009 - 2011 Sayontan Sinha. All rights reserved.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class Photonic {
	var $version, $registered_extensions, $defaults, $plugin_name, $options_page_name;
	function Photonic() {
		global $photonic_options, $photonic_setup_options, $photonic_is_ie6;
		require_once(plugin_dir_path(__FILE__)."/options/photonic-options.php");

		$this->plugin_name = plugin_basename(__FILE__);

		add_action('admin_menu', array(&$this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_admin_scripts'));
		add_action('admin_init', array(&$this, 'admin_init'));

		$photonic_options = get_option('photonic_options');
		if (isset($photonic_options) && is_array($photonic_options)) {
			foreach ($photonic_setup_options as $default_option) {
				if (isset($default_option['id'])) {
					$mod_key = 'photonic_'.$default_option['id'];
					global $$mod_key;
					if (isset($photonic_options[$default_option['id']])) {
						$$mod_key = $photonic_options[$default_option['id']];
					}
					else {
						$$mod_key = $default_option['std'];
					}
				}
			}
		}
		if (isset($photonic_options) && is_array($photonic_options)) {
			foreach ($photonic_options as $key => $value) {
				$mod_key = 'photonic_'.$key;
				global $$mod_key;
				$$mod_key = $value;
			}
		}

		// Gallery
		add_filter('post_gallery', array(&$this, 'modify_gallery'), 20, 2);
		add_action('wp_enqueue_scripts', array(&$this, 'add_scripts'), 20);
		add_action('wp_head', array(&$this, 'print_scripts'), 20);

		add_action('wp_ajax_photonic_picasa_display_album', array(&$this, 'picasa_display_album'));
		add_action('wp_ajax_nopriv_photonic_picasa_display_album', array(&$this, 'picasa_display_album'));

		$this->registered_extensions = array();
		$this->add_extensions();

		//WP provides a global $is_IE, but we specifically need to find IE6x (or, heaven forbid, IE5x). Note that older versions of Opera used to identify themselves as IE6, so we exclude Opera.
		$photonic_is_ie6 = preg_match('/\bmsie [56]/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/\bopera/i', $_SERVER['HTTP_USER_AGENT']);

		$locale = get_locale();
		load_textdomain('photonic', locate_template(array("languages/{$locale}.mo", "{$locale}.mo")));
	}

	/**
	 * Adds a menu item to the "Settings" section of the admin page.
	 *
	 * @return void
	 */
	function add_admin_menu() {
		global $photonic_options_manager;
		$this->options_page_name = add_options_page('Photonic', 'Photonic', 'edit_theme_options', 'photonic-options-manager', array(&$photonic_options_manager, 'render_options_page'));
		$this->set_version();
	}

	/**
	 * Adds all scripts and their dependencies to the <head> of the Photonic administration page. This takes care to not add scripts on other admin pages.
	 *
	 * @param $hook
	 * @return void
	 */
	function add_admin_scripts($hook) {
		if ($this->options_page_name == $hook) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-tabs');

			wp_enqueue_script('photonic-jquery-ui-custom', plugins_url('include/scripts/jquery-ui/jquery-ui-1.8.12.custom.js', __FILE__), array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-position'));
			wp_enqueue_script('photonic-jscolor', plugins_url('include/scripts/jscolor/jscolor.js', __FILE__));

			wp_enqueue_script('photonic-admin-js', plugins_url('include/scripts/admin.js', __FILE__), array('jquery'), $this->version);

			wp_enqueue_style('photonic-admin-jq', plugins_url('include/scripts/jquery-ui/css/jquery-ui-1.7.3.custom.css', __FILE__), array(), $this->version);
			wp_enqueue_style('photonic-admin-css', plugins_url('include/css/admin.css', __FILE__), array('photonic-admin-jq'), $this->version);

			global $photonic_options;
			$js_array = array(
				'category' => isset($photonic_options) && isset($photonic_options['last-set-section']) ? $photonic_options['last-set-section'] : 'generic-settings',
			);
			wp_localize_script('photonic-admin-js', 'Photonic_Admin_JS', $js_array);
		}
	}

	/**
	 * Adds all scripts and their dependencies to the <head> section of the page.
	 *
	 * @return void
	 */
	function add_scripts() {
		global $photonic_slideshow_library, $photonic_flickr_api_key, $photonic_flickr_thumb_size, $photonic_flickr_main_size, $photonic_fbox_title_position, $photonic_gallery_panel_width, $photonic_gallery_panel_items, $photonic_gallery_panel_items_per_row;
		global $photonic_flickr_hide_collection_thumbnail, $photonic_flickr_hide_collection_title, $photonic_flickr_hide_collection_set_count, $photonic_flickr_collection_set_title_display, $photonic_flickr_hide_collection_set_photos_count_display;
		global $photonic_flickr_photos_constrain_by_count, $photonic_flickr_collection_set_constrain_by_count, $photonic_flickr_collection_set_per_row_constraint, $photonic_flickr_photos_per_row_constraint;
		global $photonic_flickr_hide_set_thumbnail, $photonic_flickr_hide_set_title, $photonic_flickr_hide_set_photo_count, $photonic_flickr_hide_set_pop_thumbnail, $photonic_flickr_hide_set_pop_title, $photonic_flickr_hide_set_pop_photo_count;
		global $photonic_flickr_photos_pop_per_row_constraint, $photonic_flickr_photos_pop_constrain_by_count, $photonic_flickr_photo_pop_title_display, $photonic_flickr_photo_title_display;
		global $photonic_picasa_photo_title_display, $photonic_picasa_photo_pop_title_display, $photonic_wp_thumbnail_title_display;

		wp_enqueue_script('photonic', plugins_url('include/scripts/photonic.js', __FILE__), array('jquery'), $this->version);
		wp_deregister_script('jquery-cycle');
		wp_enqueue_script('jquery-cycle', plugins_url('include/scripts/jquery.cycle.all.min.js', __FILE__), array('jquery'), $this->version);

		if ($photonic_slideshow_library == 'fancybox') {
			wp_enqueue_script('photonic-slideshow', plugins_url('include/scripts/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery'), $this->version);
		}
		else if ($photonic_slideshow_library == 'colorbox') {
			wp_enqueue_script('photonic-slideshow', plugins_url('include/scripts/jquery.colorbox-min.js', __FILE__), array('jquery'), $this->version);
		}

		wp_enqueue_script('photonic-modal', plugins_url('include/scripts/jquery.simplemodal.1.4.1.min.js', __FILE__), array('jquery'), $this->version);

		$js_array = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'flickr_api_key' => $photonic_flickr_api_key,
			'flickr_position' => 0,
			'flickr_thumbnail_size' => $photonic_flickr_thumb_size,
			'flickr_main_size' => $photonic_flickr_main_size,
			'flickr_view' => __('View in Flickr', 'photonic'),
			'flickr_set_count' => __('{#} sets', 'photonic'),
			'flickr_photo_count' => __('{#} photos', 'photonic'),
			'fbox_show_title' => $photonic_fbox_title_position == 'none' ? false : true,
			'fbox_title_position' => $photonic_fbox_title_position == 'none' ? 'outside' : $photonic_fbox_title_position,
			'flickr_hide_collection_thumbnail' => $photonic_flickr_hide_collection_thumbnail == 'on' ? true : false,
			'flickr_hide_collection_title' => $photonic_flickr_hide_collection_title == 'on' ? true : false,
			'flickr_hide_collection_set_count' => $photonic_flickr_hide_collection_set_count == 'on' ? true : false,
			'flickr_hide_set_thumbnail' => $photonic_flickr_hide_set_thumbnail == 'on' ? true : false,
			'flickr_hide_set_title' => $photonic_flickr_hide_set_title == 'on' ? true : false,
			'flickr_hide_set_photo_count' => $photonic_flickr_hide_set_photo_count == 'on' ? true : false,
			'flickr_hide_set_pop_thumbnail' => $photonic_flickr_hide_set_pop_thumbnail == 'on' ? true : false,
			'flickr_hide_set_pop_title' => $photonic_flickr_hide_set_pop_title == 'on' ? true : false,
			'flickr_hide_set_pop_photo_count' => $photonic_flickr_hide_set_pop_photo_count == 'on' ? true : false,

			'flickr_collection_set_title_display' => $photonic_flickr_collection_set_title_display,
			'flickr_hide_collection_set_photos_count_display' => $photonic_flickr_hide_collection_set_photos_count_display == 'on' ? true : false,
			'flickr_collection_set_per_row_constraint' => $photonic_flickr_collection_set_per_row_constraint,
			'flickr_collection_set_constrain_by_count' => $photonic_flickr_collection_set_constrain_by_count,
			'flickr_photos_per_row_constraint' => $photonic_flickr_photos_per_row_constraint,
			'flickr_photos_constrain_by_count' => $photonic_flickr_photos_constrain_by_count,
			'flickr_photos_pop_per_row_constraint' => $photonic_flickr_photos_pop_per_row_constraint,
			'flickr_photos_pop_constrain_by_count' => $photonic_flickr_photos_pop_constrain_by_count,

			'flickr_photo_pop_title_display' => $photonic_flickr_photo_pop_title_display,
			'flickr_photo_title_display' => $photonic_flickr_photo_title_display,
			'picasa_photo_title_display' => $photonic_picasa_photo_title_display,
			'picasa_photo_pop_title_display' => $photonic_picasa_photo_pop_title_display,
			'wp_thumbnail_title_display' => $photonic_wp_thumbnail_title_display,

			'slideshow_library' => $photonic_slideshow_library,
			'gallery_panel_width' => $photonic_gallery_panel_width,
			'gallery_panel_items' => $photonic_gallery_panel_items,
		);
		wp_localize_script('photonic', 'Photonic_JS', $js_array);

		$template_directory = get_template_directory();
		$stylesheet_directory = get_stylesheet_directory();

		if ($photonic_slideshow_library == 'fancybox') {
			if (@file_exists($stylesheet_directory.'/scripts/fancybox/jquery.fancybox-1.3.4.css')) {
				wp_enqueue_style("photonic-slideshow", get_stylesheet_directory_uri().'/scripts/fancybox/jquery.fancybox-1.3.4.css', array(), $this->version);
			}
			else if (@file_exists($template_directory.'/scripts/fancybox/jquery.fancybox-1.3.4.css')) {
				wp_enqueue_style("photonic-slideshow", get_template_directory_uri().'/scripts/fancybox/jquery.fancybox-1.3.4.css', array(), $this->version);
			}
			else {
				wp_enqueue_style("photonic-slideshow", plugins_url('include/scripts/fancybox/jquery.fancybox-1.3.4.css', __FILE__), array(), $this->version);
			}
		}
		else if ($photonic_slideshow_library == 'slimbox2') {
			if (@file_exists($stylesheet_directory.'/scripts/slimbox/slimbox2.css')) {
				wp_enqueue_style("photonic-slideshow", get_stylesheet_directory_uri().'/scripts/slimbox/slimbox2.css', array(), $this->version);
			}
			else if (@file_exists($template_directory.'/scripts/slimbox/slimbox2.css')) {
				wp_enqueue_style("photonic-slideshow", get_template_directory_uri().'/scripts/slimbox/slimbox2.css', array(), $this->version);
			}
			else {
				wp_enqueue_style("photonic-slideshow", plugins_url('include/scripts/slimbox/slimbox2.css', __FILE__), array(), $this->version);
			}
		}
		else if ($photonic_slideshow_library == 'colorbox') {
			if (@file_exists($stylesheet_directory.'/scripts/colorbox/colorbox.css')) {
				wp_enqueue_style("photonic-slideshow", get_stylesheet_directory_uri().'/scripts/colorbox/colorbox.css', array(), $this->version);
			}
			else if (@file_exists($template_directory.'/scripts/colorbox/colorbox.css')) {
				wp_enqueue_style("photonic-slideshow", get_template_directory_uri().'/scripts/colorbox/colorbox.css', array(), $this->version);
			}
			else {
				wp_enqueue_style("photonic-slideshow", plugins_url('include/scripts/colorbox/colorbox.css', __FILE__), array(), $this->version);
			}
		}

		wp_enqueue_style('photonic', plugins_url('include/css/photonic.css', __FILE__), array(), $this->version);
	}

	/**
	 * Prints the dynamically generated CSS based on option selections.
	 *
	 * @return void
	 */
	function print_scripts() {
		global $photonic_flickr_collection_set_constrain_by_padding, $photonic_flickr_photos_constrain_by_padding, $photonic_flickr_photos_pop_constrain_by_padding;
		global $photonic_picasa_photos_pop_constrain_by_padding, $photonic_picasa_photos_constrain_by_padding, $photonic_wp_slide_align;
		$css = '<style type="text/css">'."\n";
		$css .= ".photonic-pad-photosets { margin: {$photonic_flickr_collection_set_constrain_by_padding}px; }\n";
		$css .= ".photonic-flickr-stream .photonic-pad-photos { margin: 0 {$photonic_flickr_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-picasa-stream .photonic-pad-photos { margin: 0 {$photonic_picasa_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-panel { ".$this->get_bg_css('photonic_flickr_gallery_panel_background').$this->get_border_css('photonic_flickr_set_popup_thumb_border')." }\n";
		$css .= ".photonic-panel .photonic-flickr-image img { ".$this->get_border_css('photonic_flickr_pop_photo_thumb_border').$this->get_padding_css('photonic_flickr_pop_photo_thumb_padding')." }\n";
		$css .= ".photonic-flickr-panel .photonic-pad-photos { margin: 0 {$photonic_flickr_photos_pop_constrain_by_padding}px; }\n";
		$css .= ".photonic-picasa-panel .photonic-pad-photos { margin: 0 {$photonic_picasa_photos_pop_constrain_by_padding}px; }\n";
		$css .= ".photonic-flickr-coll-thumb img { ".$this->get_border_css('photonic_flickr_coll_thumb_border').$this->get_padding_css('photonic_flickr_coll_thumb_padding')." }\n";
		$css .= ".photonic-flickr-set .photonic-flickr-set-solo-thumb img { ".$this->get_border_css('photonic_flickr_set_alone_thumb_border').$this->get_padding_css('photonic_flickr_set_alone_thumb_padding')." }\n";
		$css .= ".photonic-flickr-set-thumb img { ".$this->get_border_css('photonic_flickr_sets_set_thumb_border').$this->get_padding_css('photonic_flickr_sets_set_thumb_padding')." }\n";
		$css .= ".photonic-flickr-set-pop-thumb img { ".$this->get_border_css('photonic_flickr_set_pop_thumb_border').$this->get_padding_css('photonic_flickr_set_pop_thumb_padding')." }\n";
		if (checked($photonic_wp_slide_align, 'on', false)) {
			$css .= ".photonic-post-gallery-img img {margin: auto; display: block}\n";
		}
		$css .= "\n</style>\n";
		echo $css;
	}

	function set_version() {
		$plugin_data = get_plugin_data(__FILE__);
		$this->version = $plugin_data['Version'];
	}

	function admin_init() {
		global $photonic_options_manager;
		require_once(plugin_dir_path(__FILE__)."/photonic-options-manager.php");
		$photonic_options_manager = new Photonic_Options_Manager(__FILE__);
		$photonic_options_manager->init();
	}

	function add_extensions() {
		require_once(plugin_dir_path(__FILE__)."/extensions/Photonic_Processor.php");
		$this->register_extension('Photonic_Flickr_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Flickr_Processor.php");
	}

	public function register_extension($extension, $path) {
		if (@!file_exists($path)) {
			return;
		}
		require_once($path);
		if (!class_exists($extension) || is_subclass_of($extension, 'Photonic_Processor')) {
			return;
		}
		$this->registered_extensions[] = $extension;
	}

	/**
	 * Overrides the native gallery short code, and does a lot more.
	 *
	 * @param $content
	 * @param array $attr
	 * @return string
	 */
	function modify_gallery($content, $attr = array()) {
		global $post;
		if ($attr == null) {
			$attr = array();
		}

		$attr = array_merge(array(
			// Specially for Photonic
			'type' => 'default',  //default, flickr, picasa
			'style' => 'default',   //default, strip-below, strip-above, strip-right, strip-left, no-strip, launch
			'id'         => $post->ID,
		), $attr);

		extract($attr);

		switch ($type) {
			case 'flickr':
				$images = $this->get_flickr_gallery_images($attr);
				break;
			case 'picasa':
				$images = $this->get_picasa_gallery_images($attr);
				break;
			case 'default':
			default:
				$images = $this->get_gallery_images($attr);
				break;
		}

		if (isset($images) && is_array($images)) {
			if (isset($style)) {
				$gallery_html = $this->build_gallery($images, $style, $attr);
				return $gallery_html;
			}
		}
		else if (isset($images)) {
			return $images;
		}

		return $content;
	}

	/**
	 * Gets all images associated with the gallery. This method is lifted almost verbatim from the gallery short-code function provided by WP.
	 * We will take the gallery images and do some fun stuff with styling them in other methods. We cannot use the WP function because
	 * this code is nested within the gallery_shortcode function and we want to tweak that (there is no hook that executes after
	 * the gallery has been retrieved.
	 *
	 * @param  $attr
	 * @return array|bool
	 */
	function get_gallery_images($attr) {
		global $post;
		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if (isset($attr['orderby'])) {
			$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
			if (!$attr['orderby'])
				unset($attr['orderby']);
		}

		extract(shortcode_atts(array(
			'order' => 'ASC',
			'orderby' => 'menu_order ID',
			'id' => $post->ID,
			'itemtag' => 'dl',
			'icontag' => 'dt',
			'captiontag' => 'dd',
			'columns' => 3,
			'size' => 'thumbnail',
			'include' => '',
			'exclude' => ''
		), $attr));

		$id = intval($id);
		if ('RAND' == $order)
			$orderby = 'none';

		if (!empty($include)) {
			$include = preg_replace('/[^0-9,]+/', '', $include);
			$_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

			$attachments = array();
			foreach ($_attachments as $key => $val) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		}
		elseif (!empty($exclude)) {
			$exclude = preg_replace('/[^0-9,]+/', '', $exclude);
			$attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
		}
		else {
			$attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
		}
		return $attachments;
	}

	/**
	 * Builds the markup for a gallery when you choose to use a specific gallery style. The following styles are allowed:
	 * 	1. strip-below: Shows thumbnails for the gallery below a larger image
	 * 	2. strip-above: Shows thumbnails for the gallery above a larger image
	 *  3. no-strip: Doesn't show thumbnails. Useful if you are making it behave like an automatic slideshow.
	 * 	4. launch: Shows a thumbnail for the gallery, which you can click to launch a slideshow.
	 * 	5. default: Shows the native WP styling
	 *
	 * @param $images
	 * @param string $style
	 * @param $attr
	 * @return string
	 */
	function build_gallery($images, $style = 'strip-below', $attr) {
		global $photonic_gallery_number, $photonic_slideshow_library, $photonic_wp_thumbnail_title_display;
		if (!is_array($images)) {
			return $images;
		}

		if (!isset($photonic_gallery_number)) {
			$photonic_gallery_number = 0;
		}

		$attr = array_merge(array(
			'columns' => 3,
			'thumb_width' => 75,
			'thumb_height' => 75,
			'fx' => 'fade', 	// JQuery Cycle effects: fade, scrollUp, scrollDown, scrollLeft, scrollRight, scrollHorz, scrollVert, slideX, slideY, turnUp, turnDown, turnLeft,
								// turnRight, zoom, fadeZoom, blindX, blindY, blindZ, growX, growY, curtainX, curtainY, cover, uncover, wipe
			'timeout' => 4000, 	// Time between slides in ms
			'speed' => 1000,	// Time for each transition
			'pause' => true,	// Pause on hover
		), $attr);

		extract($attr);

		if (!isset($thumb_width) || (isset($thumb_width) && !$this->check_integer($thumb_width))) {
			$thumb_width = 75;
		}
		if (!isset($thumb_height) || (isset($thumb_height) && !$this->check_integer($thumb_height))) {
			$thumb_height = 75;
		}
		if (!isset($columns) || (isset($columns) && !$this->check_integer($columns))) {
			$columns = 3;
		}

		switch ($style) {
			case 'strip-below':
			case 'strip-above':
			case 'no-strip':
				$photonic_gallery_number++;
				$size = 'full';
				$ret = "<div class='photonic-post-gallery $style fix'><ul id='gallery-fancy-$photonic_gallery_number' class='photonic-post-gallery-content fix'>";
				foreach ( $images as $id => $attachment ) {
	//				$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
					$src = wp_get_attachment_image_src($id, $size, false);
					$ret .= "<li class='photonic-post-gallery-img'>";
					if (isset($attachment->post_title)) {
						$title = wptexturize($attachment->post_title);
					}
					else {
						$title = '';
					}
					$ret .= "<img src='".$src[0]."' alt='$title' />";
					$ret .= "</li>";
				}
				$ret .= '</ul></div>';
				ob_start();
	?>
		<script type="text/javascript">
			/* <![CDATA[ */
			$j = jQuery.noConflict();
			$j(document).ready(function() {
				// Builds a JQuery Cycle gallery based on input parameters
				$j('ul.photonic-post-gallery-content').each(function() {
					var parent = $j(this).parent();
				<?php
					$script = '';
					if ($photonic_wp_thumbnail_title_display == 'tooltip') {
						//$script = "<script type='text/javascript'>\$j('.photonic-post-gallery-nav a').each(function() { \$j(this).data('title', \$j(this).attr('title')); }); \$j('.photonic-post-gallery-nav a').each(function() { var iTitle = \$j(this).find('img').attr('alt'); \$j(this).tooltip({ bodyHandler: function() { return iTitle; }, showURL: false });})</script>";
					}
					if ($style == 'strip-below') {
				?>
					 $j("<ul id='" + this.id + "-nav' class='photonic-post-gallery-nav fix'><?php echo $script; ?></ul>").insertAfter($j(this));
				<?php
					}
					else if ($style == 'strip-above') {
				?>
					$j("<ul id='" + this.id + "-nav' class='photonic-post-gallery-nav fix'><?php echo $script; ?></ul>").insertBefore($j(this));
				<?php
					}
				?>

					var thisId = this.id;
					$j(this).cycle({
						pause: 1,
						fit: 1,
						width: '100%',
						<?php if (isset($fx)) { ?>
						fx: '<?php echo $fx; ?>',
						<?php } ?>
						<?php if (isset($speed)) { ?>
						speed: '<?php echo $speed; ?>',
						<?php } ?>
						<?php if (isset($timeout)) { ?>
						timeout: '<?php echo $timeout; ?>',
						<?php } ?>
						<?php if ($style == 'strip-above' || $style == 'strip-below') { ?>
						pager: '#' + thisId + '-nav',

						pagerAnchorBuilder: function(idx, slide) {
							var image = slide.children[0];
							return '<li><a href="#" title="' + image.alt + '"><img src="' + image.src + '" width="<?php echo $thumb_width; ?>" height="<?php echo $thumb_height; ?>" alt="' + image.alt + '" /></a></li>';
						}
						<?php } ?>
					});
				});
			});
			/* ]]> */
		</script>
	<?php
				$ret .= ob_get_contents();
				ob_end_clean();
				break;

			case 'launch':
				$photonic_gallery_number++;
				$slideshow_library_class = ($photonic_slideshow_library == 'none') ? "" : ($photonic_slideshow_library == 'thickbox' ? " class='thickbox' " : " class='launch-gallery-$photonic_slideshow_library' ");
				$size = 'full';
				$ret = "<div class='photonic-post-gallery $style fix'><ul id='gallery-fancy-$photonic_gallery_number-nav' class='photonic-post-gallery-nav fix'>";
				foreach ( $images as $id => $attachment ) {
					$src = wp_get_attachment_image_src($id, $size, false);
					$ret .= "<li class='photonic-gallery-{$columns}c'>";
					if (isset($attachment->post_title)) {
						$title = wptexturize($attachment->post_title);
					}
					else {
						$title = '';
					}
					$ret .= "<a href=\"".$src[0]."\" rel='gallery-fancy-$photonic_gallery_number-group' title='$title' $slideshow_library_class><img src='".$src[0]."' alt='$title' width='$thumb_width' height='$thumb_height' /></a>";
					$ret .= "</li>";
				}
				$ret .= '</ul></div>';
				break;

			case 'default':
			default:
				return "";
		}
		return $ret;
	}

	/**
	 * A very flexible function to display a user's photos from Flickr. This makes use of the Flickr API, hence it requires the user's API key.
	 * The API key is defined in the options. The function makes use of three different APIs:
	 *  1. <a href='http://www.flickr.com/services/api/flickr.photos.search.html'>flickr.photos.search</a> - for retrieving photos based on search critiera
	 *  2. <a href='http://www.flickr.com/services/api/flickr.photosets.getPhotos.html'>flickr.photosets.getPhotos</a> - for retrieving photo sets
	 *  3. <a href='http://www.flickr.com/services/api/flickr.galleries.getPhotos.html'>flickr.galleries.getPhotos</a> - for retrieving galleries
	 *
	 * The following short-code parameters are supported:
	 * All
	 * - per_page: number of photos to display
	 * - view: photos | collections | galleries | photosets, displays hierarchically if user_id is passed
	 * Photosets
	 * - photoset_id
	 * Galleries
	 * - gallery_id
	 * Photos
	 * - user_id: can be obtained from http://idgettr.com
	 * - tags: comma-separated list of tags
	 * - tag_mode: any | all, tells whether any tag should be used or all
	 * - text: string for text search
	 * - sort: date-posted-desc | date-posted-asc | date-taken-asc | date-taken-desc | interestingness-desc | interestingness-asc | relevance
	 * - group_id: group id for which photos will be displayed
	 *
	 * @param  $attr
	 * @return string|void
	 * @since 3.7.9
	 */
	function get_flickr_gallery_images($attr) {
		global $photonic_flickr_api_key, $photonic_flickr_position;

		$attr = array_merge(array(
			'style' => 'default',
	//		'view' => 'photos'  // photos | collections | galleries | photosets: if only a user id is passed, what should be displayed?
			// Defaults from WP ...
			'columns'    => 'auto',
			'size'       => 's',
		), $attr);
		extract($attr);

		if (!isset($photonic_flickr_api_key) || trim($photonic_flickr_api_key) == '') {
			return __("Flickr API Key not defined", 'photonic');
		}

		$format = 'format=json&';
		$json_api = 'jsoncallback=photonicJsonFlickrStreamApi&';

		$query_urls = array();
		$query = '&api_key='.$photonic_flickr_api_key;

		$ret = "";
		if (isset($view) && isset($user_id)) {
			switch ($view) {
				case 'collections':
					$collections = $this->get_collection_list($user_id);
					foreach ($collections as $collection) {
						$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.collections.getTree&collection_id='.$collection['id'];
						$nested = array();
						foreach ($collection['sets'] as $set) {
							$nested[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.photosets.getInfo&photoset_id='.$set['id'];
						}
						$query_urls[] = $nested;
					}
					break;

				case 'galleries':
					$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.galleries.getList';
					break;

				case 'photosets':
					$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.photosets.getList';
					break;

				case 'photos':
				default:
					$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.photos.search';
					break;
			}
		}
		else {
			// Collection > galleries > photosets
			if (isset($collection_id)) {
				$collections = $this->get_collection_list($user_id, $collection_id);
				foreach ($collections as $collection) {
					$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.collections.getTree&collection_id='.$collection['id'];
					$nested = array();
					foreach ($collection['sets'] as $set) {
						$nested[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.photosets.getInfo&photoset_id='.$set['id'];
					}
					$query_urls[] = $nested;
				}
			}
			else if (isset($gallery_id)) {
				$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.galleries.getPhotos';
			}
			else if (isset($photoset_id)) {
				$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'jsoncallback=photonicJsonFlickrHeaderApi&'.'method=flickr.photosets.getInfo';
				$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.$json_api.'method=flickr.photosets.getPhotos';
			}
		}

		if (isset($user_id)) {
			$query .= '&user_id='.$user_id;
		}

		if (isset($collection_id)) {
			$query .= '&collection_id='.$collection_id;
		}
		else if (isset($gallery_id)) {
			$query .= '&gallery_id='.$gallery_id;
		}
		else if (isset($photoset_id)) {
			$query .= '&photoset_id='.$photoset_id;
		}

		if (isset($tags)) {
			$query .= '&tags='.$tags;
		}

		if (isset($tag_mode)) {
			$query .= '&tag_mode='.$tag_mode;
		}

		if (isset($text)) {
			$query .= '&text='.$text;
		}

		if (isset($sort)) {
			$query .= '&sort='.$sort;
		}

		if (isset($group_id)) {
			$query .= '&group_id='.$group_id;
		}

		if (isset($per_page)) {
			$query .= '&per_page='.$per_page;
		}

		// Allow users to define additional query parameters
		//$query_url = apply_filters('photonic_flickr_query_url', $query_url, $attr);
		$query_urls = apply_filters('photonic_flickr_query_urls', $query_urls, $attr);
		$query = apply_filters('photonic_flickr_query', $query, $attr);

		foreach ($query_urls as $query_url) {
			$ret .= "<div class='photonic-flickr-stream'><ul>";
			$iterator = array();
			if (is_array($query_url)) {
				$iterator = $query_url;
			}
			else {
				$iterator[] = $query_url;
			}

			foreach ($iterator as $nested_query_url) {
				$photonic_flickr_position++;
				$ret .= "<script type='text/javascript'>\n";
				if (isset($user_id)) {
					// Cannot use wp_localize_script() here because this is invoked while parsing content; wp_localize_script is invoked way before.
					$ret .= "\tphotonic_flickr_user_".$photonic_flickr_position." = '$user_id';\n";
				}
				if (isset($columns) && $this->check_integer($columns)) {
					$ret .= "\tphotonic_flickr_columns_".$photonic_flickr_position." = $columns;\n";
				}
				else {
					$ret .= "\tphotonic_flickr_columns_".$photonic_flickr_position." = 'auto';\n";
				}
				$ret .= "</script>\n";
				$ret .= "<script type='text/javascript' src='".$nested_query_url.$query."'></script>\n";
			}
			$ret .= "</ul></div>";
		}
		return $ret;
	}

	/**
	 * Retrieves a list of collection objects for a given user. This first invokes the web-service, then iterates through the collections returned.
	 * For each collection returned it recursively looks for nested collections and sets.
	 *
	 * @param $user_id
	 * @param string $collection_id
	 * @return array
	 */
	function get_collection_list($user_id, $collection_id = '') {
		global $photonic_flickr_api_key;
		$query = 'http://api.flickr.com/services/rest/?method=flickr.collections.getTree&user_id='.$user_id.'&api_key='.$photonic_flickr_api_key;
		if ($collection_id != '') {
			$query .= '&collection_id='.$collection_id;
		}

		$feed = wp_remote_request($query);
		if (!is_wp_error($feed) && 200 == $feed['response']['code']) {
			$feed = $feed['body'];
			$feed = simplexml_load_string($feed);
			if (is_a($feed, 'SimpleXMLElement')) {
				$main_attributes = $feed->attributes();
				if ($main_attributes['stat'] == 'ok') {
					$children = $feed->children();
					if (count($children) != 0) {
						if (isset($feed->collections)) {
							$collections = $feed->collections;
							$collections = $collections->collection;
							$ret = array();
							foreach ($collections as $collection) {
								$iterative = $this->get_nested_collections($collection);
								$ret = array_merge($ret, $iterative);
							}
							return $ret;
						}
					}
				}
			}
		}
		return array();
	}

	/**
	 * Goes through a Flickr collection and recursively fetches all sets and other collections within it. This is returned as
	 * a flattened array.
	 *
	 * @param $collection
	 * @return array
	 */
	function get_nested_collections($collection) {
		$attributes = $collection->attributes();
		$id = isset($attributes['id']) ? (string)$attributes['id'] : '';
		$id = substr($id, strpos($id, '-') + 1);
		$title = isset($attributes['title']) ? (string)$attributes['title'] : '';
		$description = isset($attributes['description']) ? (string)$attributes['description'] : '';
		$thumb = isset($attributes['iconsmall']) ? (string)$attributes['iconsmall'] : (isset($attributes['iconlarge']) ? (string)$attributes['iconlarge'] : '');

		$ret = array();

		$inner_sets = $collection->set;
		$sets = array();
		if (count($inner_sets) > 0) {
			foreach ($inner_sets as $inner_set) {
				$set_attributes = $inner_set->attributes();
				$sets[] = array(
					'id' => (string)$set_attributes['id'],
					'title' => (string)$set_attributes['title'],
					'description' => (string)$set_attributes['description'],
				);
			}
		}
		$ret[] = array(
			'id' => $id,
			'title' => $title,
			'description' => $description,
			'thumb' => $thumb,
			'sets' => $sets,
		);

		$inner_collections = $collection->collection;
		if (count($inner_collections) > 0) {
			foreach ($inner_collections as $inner_collection) {
				$inner = $this->get_nested_collections($inner_collection);
				$ret = array_merge($ret, $inner);
			}
		}
		return $ret;
	}

	/**
	 *
	 *
	 * user_id
	 * kind
	 * album
	 * max_results
	 *
	 * thumb_size
	 * columns
	 * shorten caption
	 * show caption
	 *
	 * @param  $attr
	 * @return string
	 */
	function get_picasa_gallery_images($attr) {
		global $photonic_flickr_position;
		$attr = array_merge(array(
			'style' => 'default',
			'show_captions' => false,
			'crop' => true,
			'display' => 'page',
		), $attr);
		extract($attr);

		if (!isset($user_id) || (isset($user_id) && trim($user_id) == '')) {
			return '';
		}

		$crop_str = 'c';
		if (isset($crop) && trim($crop) != '') {
			$crop = $this->string_to_bool($crop);
			if (!$crop) {
				$crop_str = 'u';
			}
		}
		else {
			$crop = true;
		}

		if (!isset($view)) {
			$view = null;
		}

		$query_url = 'http://picasaweb.google.com/data/feed/api/user/'.$user_id;
		if (isset($album) && trim($album) != '') {
			$query_url .= '/album/'.urlencode($album);
		}

		if (isset($albumid) && trim($albumid) != '') {
			$query_url .= '/albumid/'.urlencode($albumid);
		}

		if (isset($kind) && trim($kind) != '' && in_array(trim($kind), array('album', 'photo', 'tag'))) {
			$kind = trim($kind);
			$query_url .= "?kind=".$kind."&";
		}
		else {
			$kind = '';
			$query_url .= "?".$kind;
		}

		if (!isset($view) || $view == null) {
			if ($kind == 'album') {
				$view = 'album';
			}
			else if ($kind == '') {
				if (!isset($album) && !isset($albumid)) {
					$view = 'album';
				}
			}
		}

		if (isset($max_results) && trim($max_results) != '') {
			$query_url .= 'max-results='.trim($max_results).'&';
		}

		if (isset($thumbsize) && trim($thumbsize) != '') {
			$query_url .= 'thumbsize='.trim($thumbsize).'&';
		}
		else {
			$query_url .= 'thumbsize=75&';
		}
		
		$query_url .= $crop_str;

		$response = wp_remote_request($query_url);
		if (is_wp_error($response)) {
			$rss = '';
		}
		else if (200 != $response['response']['code']) {
			$rss = '';
		}
		else {
			$rss = $response['body'];
		}
/*
		if ($kind == '') {
			echo "<!-- picasapicasa ";print_r($rss); echo "-->";
		}
*/

		$photonic_flickr_position++;
		if ($display != 'popup') {
			$out = "<div class='photonic-picasa-stream' id='photonic-picasa-stream-$photonic_flickr_position'>";
		}
		else {
			$out = "<div class='photonic-picasa-panel photonic-panel'>";
		}
		if (!isset($columns)) {
			$columns = null;
		}
		$out .= $this->picasa_parse_feed($rss, $view, $display, $columns);
		$out .= "</div>";
		return $out;
	}

	/**
	 * Reads the output from Picasa and parses it to generate the front-end output.
	 * In a later release this will be streamlined to use DOM-based parsing instead of event-based parsing.
	 *
	 * @param $rss
	 * @param null $view
	 * @param string $display
	 * @param null $columns
	 * @return string
	 */
	function picasa_parse_feed($rss, $view = null, $display = 'page', $columns = null) {
		global $photonic_flickr_position, $photonic_slideshow_library, $photonic_picasa_photo_title_display, $photonic_gallery_panel_items, $photonic_picasa_photo_pop_title_display;
		global $photonic_picasa_photos_per_row_constraint, $photonic_picasa_photos_constrain_by_count, $photonic_picasa_photos_pop_per_row_constraint, $photonic_picasa_photos_pop_constrain_by_count;

		$p = xml_parser_create();
		xml_parse_into_struct($p, $rss, $vals, $index);
		xml_parser_free($p);

		$opened = false;
		$picasa_title = "NULL";
		$count=0;

		$ul_class = '';
		$out = '';
		if ($display == 'popup') {
			$ul_class = "class='slideshow-grid-panel lib-$photonic_slideshow_library'";
			$out .= "<div class='photonic-picasa-panel-content photonic-panel-content fix'>";
		}
		$out .= "<ul $ul_class>";

		foreach ($vals as $val) {
			if (!$opened) {
				switch ($val["tag"]) {
					case "ENTRY":
						if ($val["type"] == "open") {
							$opened = true;
						}
						break;

					case "TITLE":
						if ($picasa_title == "NULL") {
							$picasa_title = $val["value"];
						}

					case "GPHOTO:NUMPHOTOS":
						if (!isset($numphotos) || (isset($numphotos) && !is_numeric($numphotos))) {
							$numphotos = $val["value"];
						}
						break;

					case "GPHOTO:ID":
						$albumid = $val["value"];
						break;

					case "OPENSEARCH:TOTALRESULTS":
						$result_count = $val["value"];
						break;

					case "GPHOTO:USER":
						$gphotouser = trim($val["value"]);
						break;
				}
			}
			else {
				switch ($val["tag"]) {
					case "ENTRY":
						if ($val["type"] == "close") {
							$opened = false;
						}
						break;

					case "MEDIA:THUMBNAIL":
						$thumb = trim($val["attributes"]["URL"] . "\n");
						break;

					case "MEDIA:CONTENT":
						$href = $val["attributes"]["URL"];
						$filename = basename($href);
						break;

					case "SUMMARY":
						$caption = isset($val["value"]) ? $val["value"] : '';
						break;

					case "GPHOTO:ID":
						$gphotoid = trim($val["value"]);
						break;

					case "GPHOTO:USER":
						$gphotouser = trim($val["value"]);
						break;
				}
			}

			if (isset($thumb) && isset($href) && isset($gphotoid)) {
				// Set image caption
				if (!isset($caption) || (isset($caption) && trim($caption) == "")) {
					$caption = $filename;
				}

				// Keep count of images
				$count++;

				$display_caption = apply_filters('photonic_image_display_caption', $caption);

				// Hide Videos
				$vidpos = stripos($href, "googlevideo");

				if (($vidpos == "")) {
					$li_id = $view == 'album' ? "id='photonic-picasa-album-$gphotouser-$photonic_flickr_position-$gphotoid'" : '';

					if ($display == 'page') {
						if ($columns == null) {
							if ($photonic_picasa_photos_per_row_constraint == 'padding') {
								$pad_class = 'photonic-pad-photos';
							}
							else {
								$pad_class = 'photonic-gallery-'.$photonic_picasa_photos_constrain_by_count.'c';
							}
						}
						else {
							$pad_class = 'photonic-gallery-'.$columns.'c';
						}
						$out .= "<li class='photonic-picasa-image $pad_class' $li_id>";
					}
					else {
						if ($count % $photonic_gallery_panel_items == 1) {
							$out .= "<li class='photonic-picasa-image'>";
						}

						if ($photonic_picasa_photos_pop_per_row_constraint == 'padding') {
							$pad_class = 'photonic-pad-photos';
						}
						else {
							$pad_class = 'photonic-gallery-'.$photonic_picasa_photos_pop_constrain_by_count.'c';
						}
					}
					$library = '';
					$id = '';
					if ($photonic_slideshow_library != 'none') {
						if ($view != 'album' || $display == 'popup') {
							$library = 'launch-gallery-'.$photonic_slideshow_library.' '.$photonic_slideshow_library;
						}
						else {
							$library = 'photonic-picasa-album-thumb';
							$id = "id='photonic-picasa-album-thumb-$gphotouser-$photonic_flickr_position-$gphotoid'";
						}
					}

					$rel = '';
					if ($view != 'album' || $display == 'popup') {
						$rel = "rel='photonic-picasa-stream-$photonic_flickr_position'";
					}

					$a_pad_class = $display == 'popup' ? $pad_class : '';
					$out .= "<a class='$library $a_pad_class' title=\"".esc_attr($display_caption)."\" href='$href' $rel $id>";
					$out .= "<img src='$thumb' alt=\"".esc_attr($display_caption)."\"/>";
					if ($display == 'page' && $photonic_picasa_photo_title_display == 'below') {
						$out .= "<span class='photonic-photo-title'>$display_caption</span>";
					}
					else if ($display == 'popup' && $photonic_picasa_photo_pop_title_display == 'below') {
						$out .= "<span class='photonic-photo-title'>$display_caption</span>";
					}
					$out .= "</a>";
					if ($display == 'page') {
						$out .= "</li>";
					}
					else {
						if ($count % $photonic_gallery_panel_items == 0) {
							$out .= "</li>";
						}
					}
				}

				//----------------------------------
				//Reset the variables
				//----------------------------------
				unset($thumb);
				unset($picasa_title);
				unset($href);
				unset($path);
				unset($url);
				unset($text);
				unset($gphotoid);
			}
		}

		if ($out != '<ul>') {
			if (substr($out, -5) != "</li>") {
				$out .= "</li>";
			}
			$out .= '</ul>';
			if ($photonic_picasa_photo_pop_title_display == 'tooltip') {
				$out .= "<script type='text/javascript'>\$j('.photonic-picasa-panel a').each(function() { \$j(this).data('title', \$j(this).attr('title')); }); \$j('.photonic-picasa-panel a').each(function() { var iTitle = \$j(this).find('img').attr('alt'); \$j(this).tooltip({ bodyHandler: function() { return iTitle; }, showURL: false });})</script>";
			}

			if ($display == 'popup') {
				if ($photonic_slideshow_library == 'fancybox') {
					$out .= "<script type='text/javascript'>\$j('a.launch-gallery-fancybox').each(function() { \$j(this).fancybox({ transitionIn:'elastic', transitionOut:'elastic',speedIn:600,speedOut:200,overlayShow:true,overlayOpacity:0.8,overlayColor:\"#000\",titleShow:Photonic_JS.fbox_show_title,titlePosition:Photonic_JS.fbox_title_position});});</script>";
				}
				else if ($photonic_slideshow_library == 'colorbox') {
					$out .= "<script type='text/javascript'>\$j('a.launch-gallery-colorbox').each(function() { \$j(this).colorbox({ opacity: 0.8 });});</script>";
				}
				$out .= "</div>";
			}
		}
		else {
			$out = '';
		}
		return $out;
	}

	/**
	 * If a Picasa album thumbnail is being displayed on a page, clicking on the thumbnail should launch a popup displaying all
	 * album photos. This function handles the click event and the subsequent invocation of the popup.
	 *
	 * @return void
	 */
	function picasa_display_album() {
		$panel = $_POST['panel'];
		$panel = substr($panel, 28);
		$user = substr($panel, 0, strpos($panel, '-'));
		$album = substr($panel, strpos($panel, '-') + 1);
		$album = substr($album, strpos($album, '-') + 1);
		echo $this->get_picasa_gallery_images(array('user_id' => $user, 'albumid' => $album, 'view' => 'album', 'display' => 'popup'));
		die();
	}

	/**
	 * Checks if a text being passed to it is an integer or not.
	 *
	 * @param $val
	 * @return bool
	 */
	function check_integer($val) {
		if (substr($val, 0, 1) == '-') {
			$val = substr($val, 1);
		}
		return (preg_match('/^\d*$/', $val) == 1);
	}

	/**
	 * Converts a string to a boolean variable, if possible.
	 *
	 * @param $value
	 * @return bool
	 */
	function string_to_bool($value) {
		if ($value == true || $value == 'true' || $value == 'TRUE' || $value == '1') {
			return true;
		}
		else if ($value == false || $value == 'false' || $value == 'FALSE' || $value == '0') {
			return false;
		}
		else {
			return $value;
		}
	}

	/**
	 * Constructs the CSS for a "background" option
	 *
	 * @param $option
	 * @return string
	 */
	function get_bg_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$val_array = array();
			$vals = explode(';', $option_val);
			foreach ($vals as $val) {
				if (trim($val) == '') { continue; }
				$pair = explode('=', $val);
				$val_array[$pair[0]] = $pair[1];
			}
			$option_val = $val_array;
		}
		$bg_string = "";
		$bg_rgba_string = "";
		if ($option_val['colortype'] == 'transparent') {
			$bg_string .= " transparent ";
		}
		else {
			if (isset($option_val['color'])) {
				if (substr($option_val['color'], 0, 1) == '#') {
					$color_string = substr($option_val['color'],1);
				}
				else {
					$color_string = $option_val['color'];
				}
				$rgb_str_array = array();
				if (strlen($color_string)==3) {
					$rgb_str_array[] = substr($color_string, 0, 1).substr($color_string, 0, 1);
					$rgb_str_array[] = substr($color_string, 1, 1).substr($color_string, 1, 1);
					$rgb_str_array[] = substr($color_string, 2, 1).substr($color_string, 2, 1);
				}
				else {
					$rgb_str_array[] = substr($color_string, 0, 2);
					$rgb_str_array[] = substr($color_string, 2, 2);
					$rgb_str_array[] = substr($color_string, 4, 2);
				}
				$rgb_array = array();
				$rgb_array[] = hexdec($rgb_str_array[0]);
				$rgb_array[] = hexdec($rgb_str_array[1]);
				$rgb_array[] = hexdec($rgb_str_array[2]);
				$rgb_string = implode(',',$rgb_array);
				$rgb_string = ' rgb('.$rgb_string.') ';

				if (isset($option_val['trans'])) {
					$bg_rgba_string = $bg_string;
					$transparency = (int)$option_val['trans'];
					if ($transparency != 0) {
						$trans_dec = $transparency/100;
						$rgba_string = implode(',', $rgb_array);
						$rgba_string = ' rgba('.$rgba_string.','.$trans_dec.') ';
						$bg_rgba_string .= $rgba_string;
					}
				}

				$bg_string .= $rgb_string;
			}
		}
		if (trim($option_val['image']) != '') {
			$bg_string .= " url(".$option_val['image'].") ";
			$bg_string .= $option_val['position']." ".$option_val['repeat'];

			if (trim($bg_rgba_string) != '') {
				$bg_rgba_string .= " url(".$option_val['image'].") ";
				$bg_rgba_string .= $option_val['position']." ".$option_val['repeat'];
			}
		}

		if (trim($bg_string) != '') {
			$bg_string = "background: ".$bg_string.";\n";
			if (trim($bg_rgba_string) != '') {
				$bg_string .= "\tbackground: ".$bg_rgba_string.";\n";
			}
		}
		return $bg_string;
	}

	/**
	 * Generates the CSS for borders. Each border, top, right, bottom and left is generated as a separate line.
	 *
	 * @param $option
	 * @return string
	 */
	function get_border_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$edge_array = $this->build_edge_array($option_val);
			$option_val = $edge_array;
		}
		$border_string = '';
		foreach ($option_val as $edge => $selections) {
			$border_string .= "\tborder-$edge: ";
			if (!isset($selections['style'])) {
				$selections['style'] = 'none';
			}
			if ($selections['style'] == 'none') {
				$border_string .= "none";
			}
			else {
				if (isset($selections['border-width'])) {
					$border_string .= $selections['border-width'];
				}
				if (isset($selections['border-width-type'])) {
					$border_string .= $selections['border-width-type'];
				}
				else {
					$border_string .= "px";
				}
				$border_string .= " ".$selections['style']." ";
				if ($selections['colortype'] == 'transparent') {
					$border_string .= "transparent";
				}
				else {
					if (substr($selections['color'], 0, 1) == '#') {
						$border_string .= $selections['color'];
					}
					else {
						$border_string .= '#'.$selections['color'];
					}
				}
			}
			$border_string .= ";\n";
		}
		return $border_string;
	}

	/**
	 * Generates the CSS for use in padding. This generates individual padding strings for each side, top, right, bottom and left.
	 *
	 * @param $option
	 * @return string
	 */
	function get_padding_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$edge_array = $this->build_edge_array($option_val);
			$option_val = $edge_array;
		}
		$padding_string = '';
		foreach ($option_val as $edge => $selections) {
			$padding_string .= "\tpadding-$edge: ";
			if (isset($selections['padding'])) {
				$padding_string .= $selections['padding'];
			}
			else {
				$padding_string .= 0;
			}
			if (isset($selections['padding-type'])) {
				$padding_string .= $selections['padding-type'];
			}
			else {
				$padding_string .= "px";
			}
			$padding_string .= ";\n";
		}
		return $padding_string;
	}

	public function build_edge_array($option_val) {
		$edge_array = array();
		$edges = explode('||', $option_val);
		foreach ($edges as $edge_val) {
			if (trim($edge_val) != '') {
				$edge_options = explode('::', trim($edge_val));
				if (is_array($edge_options) && count($edge_options) > 1) {
					$val_array = array();
					$vals = explode(';', $edge_options[1]);
					foreach ($vals as $val) {
						$pair = explode('=', $val);
						if (is_array($pair) && count($pair) > 1) {
							$val_array[$pair[0]] = $pair[1];
						}
					}
					$edge_array[$edge_options[0]] = $val_array;
				}
			}
		}
		return $edge_array;
	}
}

add_action('init', 'photonic_init');
function photonic_init() {
	global $photonic;
	$photonic = new Photonic();
}
?>