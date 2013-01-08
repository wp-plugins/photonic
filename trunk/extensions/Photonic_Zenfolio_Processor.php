<?php
/**
 * Processor for Zenfolio photos. This extends the Photonic_Processor class and defines methods local to Zenfolio.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Zenfolio_Processor extends Photonic_Processor {
	var $user_name, $user_agent, $token, $service_url, $secure_url, $unlocked_realms;
	function __construct() {
		parent::__construct();
		$this->user_agent = "Photonic for ".get_home_url();
		$query_url = add_query_arg('dummy', 'dummy');
		$query_url = remove_query_arg('dummy');
		if (stripos($query_url, ':') === FALSE) {
			$protocol = 'http';
		}
		else {
			$protocol = substr($query_url, 0, stripos($query_url, ':'));
		}

		//$this->service_url = $protocol.'://api.zenfolio.com/api/1.6/zfapi.asmx';
		$this->service_url = 'http://api.zenfolio.com/api/1.6/zfapi.asmx';
		$this->secure_url = 'https://www.zenfolio.com/api/1.6/zfapi.asmx';
		$this->unlocked_realms = array();
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @param array $attr
	 * @return mixed|string|void
	 */
	public function get_gallery_images($attr = array()) {
		global $photonic_zenfolio_thumb_size, $photonic_zenfolio_position;

		$attr = array_merge(array(
			'style' => 'default',
			'columns' => 'auto',
			'thumb_size' => $photonic_zenfolio_thumb_size,
			'limit' => 20,
		), $attr);
		extract($attr);

		if (empty($limit)) {
			$limit = 20;
		}

		if (empty($columns)) {
			$columns = 'auto';
		}

		if (!isset($thumb_size)) {
			$thumb_size = $photonic_zenfolio_thumb_size;
		}

		$method = 'GetPopularPhotos';
		$params = array();
		if (!empty($view)) {
			switch ($view) {
				case 'photos':
					if (!empty($object_id)) {
						$method = 'LoadPhoto';
						if(($h = stripos($object_id, 'h')) !== false) {
							$object_id = substr($object_id, $h + 1);
							$object_id = hexdec($object_id);
						}
						else if (($p = stripos($object_id, 'p')) !== false) {
							$object_id = substr($object_id, $p + 1);
						}
						else if (strlen($object_id) == 7) {
							$object_id = hexdec($object_id);
						}

						$params['photoId'] = $object_id;
						$params['level'] = 'Full';
					}
					else if (!empty($text)) {
						$params['searchId'] = '';
						if (!empty($sort_order)) {
							$params['sortOrder'] = $sort_order; // Popularity | Date | Rank
						}
						else {
							$params['sortOrder'] = 'Date';
						}
						$params['query'] = $text;
						$params['offset'] = 0;
						$params['limit'] = $limit;
						$method = 'SearchPhotoByText';
					}
					else if (!empty($category_code)) {
						$params['searchId'] = '';
						if (!empty($sort_order)) {
							$params['sortOrder'] = $sort_order; // Popularity | Date
						}
						else {
							$params['sortOrder'] = 'Date';
						}
						$params['categoryCode'] = $category_code;
						$params['offset'] = 0;
						$params['limit'] = $limit;
						$method = 'SearchPhotoByCategory';
					}
					else if (!empty($kind)) {
						$params['offset'] = 0;
						$params['limit'] = $limit;
						switch ($kind) {
							case 'popular':
								$method = 'GetPopularPhotos';
								break;

							case 'recent':
								$method = 'GetRecentPhotos';
								break;

							default:
								return __('Invalid <code>kind</code> parameter.', 'photonic');
						}
					}
					else {
						return __('The <code>kind</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					break;

				case 'photosets':
					if (!empty($object_id)) {
						$method = 'LoadPhotoSet';
						if(($p = stripos($object_id, 'p')) !== false) {
							$object_id = substr($object_id, $p + 1);
						}

						$params['photosetId'] = $object_id;
						$params['level'] = 'Full';
						$params['includePhotos'] = true;
					}
					else if (!empty($text) && !empty($photoset_type)) {
						$params['searchId'] = '';
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						if (!empty($sort_order)) {
							$params['sortOrder'] = $sort_order; // Popularity | Date | Rank
						}
						else {
							$params['sortOrder'] = 'Date';
						}
						$params['query'] = $text;
						$params['offset'] = 0;
						$params['limit'] = $limit;
						$method = 'SearchSetByText';
					}
					else if (!empty($category_code) && !empty($photoset_type)) {
						$params['searchId'] = '';
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						if (!empty($sort_order)) {
							$params['sortOrder'] = $sort_order; // Popularity | Date
						}
						else {
							$params['sortOrder'] = 'Date';
						}
						$params['categoryCode'] = $category_code;
						$params['offset'] = 0;
						$params['limit'] = $limit;
						$method = 'SearchSetByCategory';
					}
					else if (!empty($kind) && !empty($photoset_type)) {
						switch ($kind) {
							case 'popular':
								$method = 'GetPopularSets';
								break;

							case 'recent':
								$method = 'GetRecentSets';
								break;

							default:
								return __('Invalid <code>kind</code> parameter.', 'photonic');
						}
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						// These have to be after the $params['type'] assignment
						$params['offset'] = 0;
						$params['limit'] = $limit;
					}
					else if (empty($kind)) {
						return __('The <code>kind</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					else if (empty($photoset_type)) {
						return __('The <code>photoset_type</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					break;

				case 'hierarchy':
					if (empty($login_name)) {
						return __('The <code>login_name</code> parameter is required.', 'photonic');
					}
					$method = 'LoadGroupHierarchy';
					$params['loginName'] =  $login_name;
					break;

				case 'group':
					if (empty($object_id)) {
						return __('The <code>object_id</code> parameter is required.', 'photonic');
					}
					$method = 'LoadGroup';
					if(($f = stripos($object_id, 'f')) !== false) {
						$object_id = substr($object_id, $f + 1);
					}
					$params['groupId'] =  $object_id;
					$params['level'] = 'Full';
					$params['includeChildren'] = true;
					break;
			}
		}

		$photonic_zenfolio_position++;
		$response = $this->make_call($method, $params);

		if (isset($_COOKIE['photonic-zf-keyring'])) {
			$realms = $this->make_call('KeyringGetUnlockedRealms', array('keyring' => $_COOKIE['photonic-zf-keyring']));
			if (!empty($realms) && !empty($realms->result)) {
				$this->unlocked_realms = $realms->result;
			}
		}

		if (!empty($panel)) {
			$ret = "<div class='photonic-zenfolio-panel photonic-panel'>";
			$display = 'popup';
		}
		else {
			$ret = "<div class='photonic-zenfolio-stream' id='photonic-zenfolio-stream-$photonic_zenfolio_position'>";
			$display = 'in-page';
		}
		$ret .= $this->process_response($method, $response, $columns, $display, $thumb_size);
		$ret .= "</div>\n";
		return $ret;
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public function parse_token($response) {
		// TODO: Update content when authentication gets supported
	}

	/**
	 * Calls a Zenfolio method with the passed parameters. The method is called using JSON-RPC. WP's wp_remote_request
	 * method doesn't work here because of specific CURL parameter requirements.
	 *
	 * @param $method
	 * @param $params
	 * @param bool $force_secure
	 * @return array|mixed
	 */
	function make_call($method, $params, $force_secure = false) {
		$request['method'] = $method;
		$request['params'] = array_values($params);
		$request['id'] = 1;
		$bodyString = json_encode($request);
		$bodyLength = strlen($bodyString);

		$headers = array();
		$headers[] = 'Host: www.zenfolio.com';
		$headers[] = 'X-Zenfolio-User-Agent: '.$this->user_agent;
		if($this->token) {
			$headers[] = 'X-Zenfolio-Token: '.$this->token;
		}
		if (isset($_COOKIE['photonic-zf-keyring'])) {
			$headers[] = 'X-Zenfolio-Keyring: '.$_COOKIE['photonic-zf-keyring'];
		}
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Content-Length: '.$bodyLength."\r\n";
		$headers[] = $bodyString;

		$cert = trailingslashit(PHOTONIC_PATH).'include/misc/cacert.crt';

		if ($force_secure) {
			$ch = curl_init($this->service_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//			curl_setopt($ch, CURLOPT_CAINFO, $cert); // Set the location of the CA-bundle
		}
		else {
			$ch = curl_init($this->service_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		$response = curl_exec($ch);
//		print_r(curl_getinfo($ch, CURLINFO_HEADER_OUT));

		curl_close($ch);

		$response = json_decode($response);
		return $response;
	}

	/**
	 * Routing function that takes the response and redirects it to the appropriate processing function.
	 *
	 * @param $method
	 * @param $response
	 * @param $columns
	 * @param string $display
	 * @param int $thumb_size
	 * @return mixed|string|void
	 */
	function process_response($method, $response, $columns, $display = 'in-page', $thumb_size = 1) {
		if (!empty($response->result)) {
			$result = $response->result;
			$ret = '';
			switch ($method) {
				case 'GetPopularPhotos':
				case 'GetRecentPhotos':
				case 'SearchPhotoByText':
				case 'SearchPhotoByCategory':
					$ret = $this->process_photos($result, $columns, $display, $thumb_size);
					break;

				case 'LoadPhoto':
					$ret = $this->process_photo($result);
					break;

				case 'GetPopularSets':
				case 'GetRecentSets':
				case 'SearchSetByText':
				case 'SearchSetByCategory':
					$ret = $this->process_sets($result, $columns, $thumb_size);
					break;

				case 'LoadPhotoSet':
					$ret = $this->process_set($result, $columns, $display, $thumb_size);
					break;

				case 'LoadGroupHierarchy':
					$ret = $this->process_group_hierarchy($result, $columns, $display, $thumb_size);
					break;

				case 'LoadGroup':
					$ret = $this->process_group($result, $columns, $display, $thumb_size);
					break;
			}
			return $ret;
		}
		else if (!empty($response->error)) {
			if (!empty($response->error->message)) {
				return $response->error->message;
			}
			else {
				return __('Unknown error', 'photonic');
			}
		}
		else {
			return __('Unknown error', 'photonic');
		}
	}

	/**
	 * Takes an array of photos and displays each as a thumbnail. Each thumbnail, upon clicking launches a lightbox.
	 *
	 * @param $response
	 * @param $columns
	 * @param string $display
	 * @param int $thumb_size
	 * @return mixed|string|void
	 */
	function process_photos($response, $columns, $display = 'in-page', $thumb_size = 1) {
		if (!is_array($response)) {
			if (empty($response->Photos) || !is_array($response->Photos)) {
				return __('Response is not an array', 'photonic');
			}
			$response = $response->Photos;
		}

		global $photonic_slideshow_library, $photonic_zenfolio_position, $photonic_zenfolio_photos_per_row_constraint, $photonic_zenfolio_main_size, $photonic_zenfolio_photo_title_display, $photonic_gallery_panel_items;

		$a_class = '';
		$col_class = '';
		if ($photonic_slideshow_library != 'none') {
			$a_class = 'launch-gallery-'.$photonic_slideshow_library." ".$photonic_slideshow_library;
		}
		if (Photonic::check_integer($columns)) {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}

		if ($col_class == '' && $photonic_zenfolio_photos_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-photos';
		}
		else if ($col_class == '') {
			$col_class = 'photonic-gallery-'.$photonic_zenfolio_photos_per_row_constraint.'c';
		}

		$a_rel = 'lightbox-photonic-zenfolio-stream-'.$photonic_zenfolio_position;
		if ($photonic_slideshow_library == 'prettyphoto') {
			$a_rel = 'photonic-prettyPhoto['.$a_rel.']';
		}

		$ul_class = '';
		$ret = '';
		if ($display == 'popup') {
			$ul_class = "class='slideshow-grid-panel lib-$photonic_slideshow_library'";
			$ret .= "<div class='photonic-zenfolio-panel-content photonic-panel-content fix'>";
		}
		$ret .= "<ul $ul_class>";

		$counter = 0;
		$type = '$type';
		foreach ($response as $photo) {
			if (empty($photo->$type) || $photo->$type != 'Photo') {
				continue;
			}
			$counter++;
			$appendage = array();
			if (isset($photo->Sequence)) {
				$appendage[] = 'sn='.$photo->Sequence;
			}
			if (isset($photo->UrlToken)) {
				$appendage[] = 'tk='.$photo->UrlToken;
			}
/*			$appendage = implode('&', $appendage);
			if ($appendage) {
				$appendage = '?'.$appendage;
			}*/

			$thumb = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$thumb_size.'.jpg';
			$orig = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$photonic_zenfolio_main_size.'.jpg';
			$title = esc_attr($photo->Title);
			$shown_title = '';
			if ($photonic_zenfolio_photo_title_display == 'below') {
				$shown_title = '<span class="photonic-photo-title">'.$title.'</span>';
			}

			if ($display == 'in-page') {
				$ret .= '<li class="photonic-zenfolio-image photonic-zenfolio-photo '.$col_class.'">';
			}
			else if ($counter % $photonic_gallery_panel_items == 1) {
				$ret .= "<li class='photonic-zenfolio-image'>";
			}

			$ret .= '<a href="'.$orig.'" class="'.$a_class.'" rel="'.$a_rel.'" title="'.$title.'"><img alt="'.$title.'" src="'.$thumb.'"/>'.$shown_title.'</a>';
			if ($display == 'page') {
				$ret .= "</li>";
			}
			else {
				if ($counter % $photonic_gallery_panel_items == 0) {
					$ret .= "</li>";
				}
			}
		}
		if ($ret != '<ul>') {
			if (substr($ret, -5) != "</li>") {
				$ret .= "</li>";
			}
			$ret .= '</ul>';
		}
		else {
			$ret = '';
		}

		if ($photonic_zenfolio_photo_title_display == 'tooltip') {
			$ret .= "<script type='text/javascript'>\$j('.photonic-zenfolio-panel a').each(function() { \$j(this).data('title', \$j(this).attr('title')); }); \$j('.photonic-zenfolio-panel a').each(function() { if (!(\$j(this).parent().hasClass('photonic-header-title'))) { var iTitle = \$j(this).find('img').attr('alt'); \$j(this).tooltip({ bodyHandler: function() { return iTitle; }, showURL: false });}})</script>";
		}

		if ($display == 'popup') {
			if ($photonic_slideshow_library == 'fancybox') {
				$ret .= "<script type='text/javascript'>\$j('a.launch-gallery-fancybox').each(function() { \$j(this).fancybox({ transitionIn:'elastic', transitionOut:'elastic',speedIn:600,speedOut:200,overlayShow:true,overlayOpacity:0.8,overlayColor:\"#000\",titleShow:Photonic_JS.fbox_show_title,titlePosition:Photonic_JS.fbox_title_position});});</script>";
			}
			else if ($photonic_slideshow_library == 'colorbox') {
				$ret .= "<script type='text/javascript'>\$j('a.launch-gallery-colorbox').each(function() { \$j(this).colorbox({ opacity: 0.8, maxWidth: '95%', maxHeight: '95%', slideshow: Photonic_JS.slideshow_mode, slideshowSpeed: Photonic_JS.slideshow_interval });});</script>";
			}
			else if ($photonic_slideshow_library == 'prettyphoto') {
				$ret .= "<script type='text/javascript'>\$j(\"a[rel^='photonic-prettyPhoto']\").prettyPhoto({ theme: Photonic_JS.pphoto_theme, autoplay_slideshow: Photonic_JS.slideshow_mode, slideshow: parseInt(Photonic_JS.slideshow_interval), show_title: false, social_tools: '', deeplinking: false });</script>";
			}
			$ret .= "</div>";
		}

		if (is_archive()) {
			global $photonic_archive_thumbs;
			if (!empty($photonic_archive_thumbs) && $counter < $photonic_archive_thumbs) {
				$this->is_more_required = false;
			}
		}

		return $ret;
	}

	/**
	 * Prints a single photo with the title as an <h3> and the caption as the image caption.
	 *
	 * @param $photo
	 * @return string
	 */
	function process_photo($photo) {
		$type = '$type';
		if (empty($photo->$type) || $photo->$type != 'Photo') {
			return '';
		}
		$ret = '';
		if (!empty($photo->Title)) {
			$ret .= '<h3>'.$photo->Title.'</h3>';
		}
		global $photonic_zenfolio_main_size, $photonic_external_links_in_new_tab;
		if (!empty($photonic_external_links_in_new_tab)) {
			$target = " target='_blank' ";
		}
		else {
			$target = '';
		}

		$orig = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$photonic_zenfolio_main_size.'.jpg';
		$img = '<img src="'.$orig.'" alt="'.esc_attr($photo->Caption).'" />';
		$img = '<a href="'.$photo->PageUrl.'" title="'.esc_attr($photo->Title).'" '.$target.' >'.$img.'</a>';
		if (!empty($photo->Caption)) {
			$ret .= "<div class='wp-caption'>".$img."<div class='wp-caption-text'>".$photo->Caption."</div></div>";
		}
		else {
			$ret .= $img;
		}
		return $ret;
	}

	/**
	 * Takes an array of photosets and displays a thumbnail for each of them. Password-protected thumbnails might be excluded via the options.
	 *
	 * @param $response
	 * @param $columns
	 * @param int $thumb_size
	 * @return mixed|string|void
	 */
	function process_sets($response, $columns, $thumb_size = 1) {
		if (!is_array($response)) {
			if (empty($response->PhotoSets) || !is_array($response->PhotoSets)) {
				return __('Response is not an array', 'photonic');
			}
			$response = $response->PhotoSets;
		}

		global $photonic_zenfolio_sets_per_row_constraint, $photonic_zenfolio_sets_constrain_by_count, $photonic_zenfolio_position, $photonic_zenfolio_set_title_display, $photonic_zenfolio_hide_set_photos_count_display, $photonic_zenfolio_hide_password_protected_thumbnail;
		$ret = '<ul>';

		if ($columns != 'auto') {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}
		else if ($photonic_zenfolio_sets_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-photosets';
		}
		else {
			$col_class = 'photonic-gallery-'.$photonic_zenfolio_sets_constrain_by_count.'c';
		}

		$counter = 0;
		foreach ($response as $photoset) {
			if (empty($photoset->TitlePhoto)) {
				continue;
			}
			if (!empty($photoset->AccessDescriptor) && !empty($photoset->AccessDescriptor->AccessType) && $photoset->AccessDescriptor->AccessType == 'Password' && !empty($photonic_zenfolio_hide_password_protected_thumbnail)) {
				continue;
			}

			if (!empty($photoset->AccessDescriptor) && !empty($photoset->AccessDescriptor->AccessType) && $photoset->AccessDescriptor->AccessType == 'Password') {
				if (!in_array($photoset->AccessDescriptor->RealmId, $this->unlocked_realms)) {
					$passworded = 'photonic-zenfolio-set-passworded';
				}
				else {
					$passworded = '';
				}
			}
			else {
				$passworded = '';
			}
			$id = $photoset->Id;

			$photo = $photoset->TitlePhoto;
			$thumb = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$thumb_size.'.jpg';
			$title = esc_attr($photoset->Title);
			$image = '<img src="'.$thumb.'" alt="'.$title.'" />';

			$anchor = "<a href='".$photoset->PageUrl."' class='photonic-zenfolio-set-thumb photonic-zenfolio-set-thumb-$thumb_size $passworded' id='photonic-zenfolio-set-thumb-".$id.'-'.$photonic_zenfolio_position."' title='".$title."'>".$image."</a>";
			$text = '';
			if ($photonic_zenfolio_set_title_display == 'below') {
				$text = "<span class='photonic-photoset-title'>".$title."</span>";
				if (!$photonic_zenfolio_hide_set_photos_count_display) {
					$text .= '<span class="photonic-photoset-photo-count">'.sprintf(__('%s photos', 'photonic'), $photoset->photos).'</span>';
				}
			}
			$password_prompt = '';
/*			if ($passworded) {
				$prompt_title = esc_attr(__('Enter Password', 'photonic'));
				$prompt_submit = esc_attr(__('Access', 'photonic'));
				$form_url = admin_url('admin-ajax.php');
				$password_prompt = "
				<div class='photonic-password-prompter' id='photonic-zenfolio-prompter-$id-$photonic_zenfolio_position' title='$prompt_title'>
					<form class='photonic-password-form photonic-zenfolio-form' action='$form_url'>
						<input type='password' name='photonic-zenfolio-password' />
						<input type='hidden' name='photonic-zenfolio-realm' value='{$photoset->AccessDescriptor->RealmId}' />
						<input type='hidden' name='action' value='photonic_verify_password' />
						<input type='submit' name='photonic-zenfolio-submit' value='$prompt_submit' />
					</form>
				</div>";
			}*/
			$ret .= "<li class='photonic-zenfolio-image photonic-zenfolio-set-thumb ".$col_class."' id='photonic-zenfolio-set-".$id.'-'.$photonic_zenfolio_position."'>".$anchor.$text.$password_prompt."</li>";
			$counter++;
		}

		if ($ret == '<ul>') {
			$ret = '';
		}
		else {
			$ret .= '</ul>';
		}

		if (is_archive()) {
			global $photonic_archive_thumbs;
			if (!empty($photonic_archive_thumbs) && $counter < $photonic_archive_thumbs) {
				$this->is_more_required = false;
			}
		}

		return $ret;
	}

	/**
	 * Displays a header with a basic summary for a photoset, along with thumbnails for all associated photos.
	 *
	 * @param $response
	 * @param $columns
	 * @param string $display
	 * @param int $thumb_size
	 * @return string
	 */
	function process_set($response, $columns, $display = 'in-page', $thumb_size = 1) {
		$ret = '';
		if (is_array($response->Photos)) {
			global $photonic_zenfolio_link_set_page, $photonic_zenfolio_hide_set_thumbnail, $photonic_zenfolio_hide_set_title, $photonic_zenfolio_hide_set_photo_count;

			$ret .= $this->process_object_header(
				$response,
				'set',
				array(
					'thumbnail' => !empty($photonic_zenfolio_hide_set_thumbnail),
					'title' => !empty($photonic_zenfolio_hide_set_title),
					'counter' => !empty($photonic_zenfolio_hide_set_photo_count),
				),
				array(
					'photos' => $response->ImageCount,
				),
				!empty($photonic_zenfolio_link_set_page),
				$display,
				$thumb_size
			);
			$ret .= $this->process_photos($response->Photos, $columns, $display, $thumb_size);
		}
		return $ret;
	}

	/**
	 * For a given user this prints out the group hierarchy. This starts with the root level and first prints all immediate
	 * children photosets. It then goes into each child group and recursively displays the photosets for each of them in separate sections.
	 *
	 * @param $response
	 * @param $columns
	 * @param string $display
	 * @param int $thumb_size
	 * @return mixed|string|void
	 */
	function process_group_hierarchy($response, $columns, $display = 'in-page', $thumb_size = 1) {
		if (empty($response->Elements)) {
			return __('No galleries, collections or groups defined for this user', 'photonic');
		}

		$ret = $this->process_group($response, $columns, $display, $thumb_size);
		return $ret;
	}

	/**
	 * For a given group this displays the immediate children photosets and then recursively displays all children groups.
	 *
	 * @param $group
	 * @param $columns
	 * @param string $display
	 * @param int $thumb_size
	 * @return string
	 */
	function process_group($group, $columns, $display = 'in-page', $thumb_size = 1) {
		$ret = '';
		$type = '$type';
		if (!isset($group->Elements)) {
			$object_id = $group->Id;
			$method = 'LoadGroup';
			if(($f = stripos($object_id, 'f')) !== false) {
				$object_id = substr($object_id, $f + 1);
			}
			$params = array();
			$params['groupId'] =  $object_id;
			$params['level'] = 'Full';
			$params['includeChildren'] = true;
			$response = $this->make_call($method, $params);
			if (!empty($response->result)) {
				$group = $response->result;
			}
		}

		if (empty($group->Elements)) {
			return '';
		}

		$elements = $group->Elements;
		$photosets = array();
		$groups = array();
		global $photonic_zenfolio_hide_password_protected_thumbnail;
		$image_count = 0;
		foreach ($elements as $element) {
			if ($element->$type == 'PhotoSet') {
				if (!empty($element->AccessDescriptor) && !empty($element->AccessDescriptor->AccessType) && $element->AccessDescriptor->AccessType == 'Password' && !empty($photonic_zenfolio_hide_password_protected_thumbnail)) {
					continue;
				}
				$photosets[] = $element;
				$image_count += $element->ImageCount;
			}
			else if ($element->$type == 'Group') {
				$groups[] = $element;
			}
		}

		global $photonic_zenfolio_hide_empty_groups;
		if (!empty($group->Title) && ($image_count > 0 || empty($photonic_zenfolio_hide_empty_groups))) {
			global $photonic_zenfolio_link_group_page, $photonic_zenfolio_hide_group_title, $photonic_zenfolio_hide_group_photo_count, $photonic_zenfolio_hide_group_group_count, $photonic_zenfolio_hide_group_set_count;
			$ret .= $this->process_object_header(
				$group,
				'group',
				array(
					'thumbnail' => true,
					'title' => !empty($photonic_zenfolio_hide_group_title),
					'counter' => !(empty($photonic_zenfolio_hide_group_photo_count) || empty($photonic_zenfolio_hide_group_group_count) || empty($photonic_zenfolio_hide_group_set_count)),
				),
				array(
					'sets' => empty($photonic_zenfolio_hide_group_set_count) ? count($photosets) : 0,
					'groups' => empty($photonic_zenfolio_hide_group_group_count) ? count($groups) : 0,
					'photos' => empty($photonic_zenfolio_hide_group_photo_count)? $image_count : 0,
				),
				!empty($photonic_zenfolio_link_group_page),
				$display,
				$thumb_size
			);
		}

		$ret .= $this->process_sets($photosets, $columns, $thumb_size);

		foreach ($groups as $group) {
			$ret .= $this->process_group($group, $columns, $display, $thumb_size);
		}

		return $ret;
	}

	/**
	 * Displays the header for a Group or a Photoset, with a thumbnail (if available), a title and photo/photoset/group counts as available.
	 *
	 * @param $object
	 * @param string $type
	 * @param array $hidden
	 * @param array $counters
	 * @param $link
	 * @param string $display
	 * @param int $thumb_size
	 * @return string
	 */
	function process_object_header($object, $type = 'group', $hidden = array(), $counters = array(), $link, $display = 'in-page', $thumb_size = 1) {
		$ret = '';
		if (!empty($object->Title)) {
			global $photonic_external_links_in_new_tab;
			$title = esc_attr($object->Title);
			if (!empty($photonic_external_links_in_new_tab)) {
				$target = ' target="_blank" ';
			}
			else {
				$target = '';
			}

			$anchor = '';
			if (!empty($object->TitlePhoto)) {
				$photo = $object->TitlePhoto;
				$thumb = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$thumb_size.'.jpg';
				$image = '<img src="'.$thumb.'" alt="'.$title.'" />';

				if ($link) {
					$anchor = "<a href='".$object->PageUrl."' class='photonic-header-thumb photonic-zenfolio-$type-solo-thumb' title='".$title."' $target>".$image."</a>";
				}
				else {
					$anchor = "<div class='photonic-header-thumb photonic-zenfolio-$type-solo-thumb'>$image</div>";
				}
			}

			if (empty($hidden['thumbnail']) || empty($hidden['title']) || empty($hidden['counter'])) {
				$ret .= "<div class='photonic-zenfolio-$type'>";

				if (empty($hidden['thumbnail'])) {
					$ret .= $anchor;
				}
				if (empty($hidden['title']) || empty($hidden['counter'])) {
					$ret .= "<div class='photonic-header-details photonic-$type-details'>";
					if (empty($hidden['title'])) {
						if ($link) {
							$ret .= "<div class='photonic-header-title photonic-$type-title'><a href='".$object->PageUrl."' $target>".$title.'</a></div>';
						}
						else {
							$ret .= "<div class='photonic-header-title photonic-$type-title'>".$title.'</div>';
						}
					}
					if (empty($hidden['counter'])) {
						$counter_texts = array();
						if (!empty($counters['groups'])) {
							if ($counters['groups'] == 1) {
								$counter_texts[] = __('1 group', 'photonic');
							}
							else {
								$counter_texts[] = sprintf(__('%s groups', 'photonic'), $counters['groups']);
							}
						}
						if (!empty($counters['sets'])) {
							if ($counters['sets'] == 1) {
								$counter_texts[] = __('1 set', 'photonic');
							}
							else {
								$counter_texts[] = sprintf(__('%s sets', 'photonic'), $counters['sets']);
							}
						}
						if (!empty($counters['photos'])) {
							if ($counters['photos'] == 1) {
								$counter_texts[] = __('1 photo', 'photonic');
							}
							else {
								$counter_texts[] = sprintf(__('%s photos', 'photonic'), $counters['photos']);
							}
						}

						if (!empty($counter_texts)) {
							$ret .= "<span class='photonic-header-info photonic-$type-photos'>".implode(', ', $counter_texts).'</span>';
						}
					}
					$ret .= "</div><!-- .photonic-$type-details -->";
				}
				$ret .= "</div>";
			}
		}

		return $ret;
	}

	/**
	 * Displays a popup photoset. This is invoked upon clicking on a photoset thumbnail on the main page.
	 */
	function display_set() {
		$panel = $_POST['panel'];
		$panel = substr($panel, 28);
		$set = substr($panel, 0, strpos($panel, '-'));
		$thumb_size = $_POST['thumb_size'];
		echo $this->get_gallery_images(array('view' => 'photosets', 'object_id' => $set, 'panel' => $panel, 'thumb_size' => $thumb_size));
		die();
	}

	function verify_password() {
		if (empty($_REQUEST['photonic-zenfolio-password'])) {
			return __('Please enter a password.', 'photonic');
		}
		else if (empty($_REQUEST['photonic-zenfolio-realm'])) {
			return __('Unknown error.', 'photonic');
		}
		else {
			$method = 'KeyringAddKeyPlain';
			$params = array();
			$params['keyring'] = isset($_COOKIE['photonic-zf-keyring']) ? $_COOKIE['photonic-zf-keyring'] : '';
			$params['realmId'] = $_REQUEST['photonic-zenfolio-realm'];
			$params['password'] = $_REQUEST['photonic-zenfolio-password'];

			$response = $this->make_call($method, $params, true);

			if (!empty($response->result)) {
				$result = $response->result;
				setcookie('photonic-zf-keyring', $result, time() + 365 * 60 * 60 * 24, COOKIEPATH);
				return 'Success'; // NOT TO BE TRANSLATED!!!
			}
			else if (!empty($response->error)) {
				if (!empty($response->error->message)) {
					return $response->error->message;
				}
				else {
					return __('Unknown error', 'photonic');
				}
			}
			else {
				return __('Unknown error', 'photonic');
			}
		}
	}
}
