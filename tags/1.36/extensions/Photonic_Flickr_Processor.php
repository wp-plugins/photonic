<?php
/**
 * Processor for Flickr Galleries
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Flickr_Processor extends Photonic_OAuth1_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_flickr_api_key, $photonic_flickr_api_secret;
		$this->api_key = $photonic_flickr_api_key;
		$this->api_secret = $photonic_flickr_api_secret;
		$this->provider = 'flickr';
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
	 * - user_id: can be obtained from the Helpers page
	 * - tags: comma-separated list of tags
	 * - tag_mode: any | all, tells whether any tag should be used or all
	 * - text: string for text search
	 * - sort: date-posted-desc | date-posted-asc | date-taken-asc | date-taken-desc | interestingness-desc | interestingness-asc | relevance
	 * - group_id: group id for which photos will be displayed
	 *
	 * @param array $attr
	 * @return string|void
	 * @since 1.02
	 */
	function get_gallery_images($attr = array()) {
		global $photonic_flickr_api_key, $photonic_flickr_position, $photonic_carousel_mode;
		global $photonic_flickr_login_shown, $photonic_flickr_allow_oauth, $photonic_flickr_oauth_done;

		$attr = array_merge(array(
			'style' => 'default',
	//		'view' => 'photos'  // photos | collections | galleries | photosets: if only a user id is passed, what should be displayed?
			// Defaults from WP ...
			'columns'    => 'auto',
			'size'       => 's',
			'privacy_filter' => '',
			'per_page' => 100,
		), $attr);
		extract($attr);

		if (!isset($photonic_flickr_api_key) || trim($photonic_flickr_api_key) == '') {
			return __("Flickr API Key not defined", 'photonic');
		}

		$format = 'format=json&';

		$query_urls = array();
		$query = '&api_key='.$photonic_flickr_api_key;

		$ret = "";
		if (isset($view) && isset($user_id)) {
			switch ($view) {
				case 'collections':
					if (!isset($collection_id)) {
						$collections = $this->get_collection_list($user_id);
						foreach ($collections as $collection) {
							$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.collections.getTree&collection_id='.$collection['id'];
						}
					}
					break;

				case 'galleries':
					if (!isset($gallery_id)) {
						$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.galleries.getList';
					}
					break;

				case 'photosets':
					if (!isset($photoset_id)) {
						$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photosets.getList';
					}
					break;

				case 'photo':
					if (isset($photo_id)) {
						$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photos.getInfo';
					}
					break;

				case 'photos':
				default:
					$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photos.search';
					break;
			}
		}
		else if (isset($view) && $view == 'photos' && isset($group_id)) {
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photos.search';
		}
		else if (isset($view) && $view == 'photo' && isset($photo_id)) {
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photos.getInfo';
//			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photos.getExif';
		}

		// Collection > galleries > photosets
		if (isset($collection_id)) {
			$collections = $this->get_collection_list($user_id, $collection_id);
			foreach ($collections as $collection) {
				$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.collections.getTree&collection_id='.$collection['id'];
			}
		}
		else if (isset($gallery_id)) {
			if (!isset($user_id)) {
				return __('User id is required for displaying a single gallery', 'photonic');
			}
			$temp_query = 'http://api.flickr.com/services/rest/?method=flickr.galleries.getList&user_id='.$user_id.'&api_key='.$photonic_flickr_api_key;

			if ($photonic_flickr_oauth_done) {
				$end_point = Photonic_Processor::get_normalized_http_url($temp_query);
				if (strstr($temp_query, $end_point) > -1) {
					$params = substr($temp_query, strlen($end_point));
					if (strlen($params) > 1) {
						$params = substr($params, 1);
					}
					$params = Photonic_Processor::parse_parameters($params);
					$signed_args = $this->sign_call($end_point, 'GET', $params);
					$temp_query = $end_point.'?'.Photonic_Processor::build_query($signed_args);
				}
			}

			$feed = Photonic::http($temp_query);
			if (!is_wp_error($feed) && 200 == $feed['response']['code']) {
				$feed = $feed['body'];
				$feed = simplexml_load_string($feed);
				if (is_a($feed, 'SimpleXMLElement')) {
					$main_attributes = $feed->attributes();
					if ($main_attributes['stat'] == 'ok') {
						$children = $feed->children();
						if (count($children) != 0) {
							if (isset($feed->galleries)) {
								$galleries = $feed->galleries;
								$galleries = $galleries->gallery;
								if (count($galleries) > 0) {
									$gallery = $galleries[0];
									$gallery = $gallery->attributes();
									$global_dbid = $gallery['id'];
									$global_dbid = substr($global_dbid, 0, stripos($global_dbid, '-'));
								}
							}
						}
					}
				}
			}
			if (isset($global_dbid)) {
				$gallery_id = $global_dbid.'-'.$gallery_id;
			}
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.galleries.getInfo';
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.galleries.getPhotos';
		}
		else if (isset($photoset_id)) {
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photosets.getInfo';
			$query_urls[] = 'http://api.flickr.com/services/rest/?'.$format.'method=flickr.photosets.getPhotos';
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
		else if (isset($photo_id)) {
			$query .= '&photo_id='.$photo_id;
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

		global $photonic_archive_thumbs;
		if (is_archive()) {
			if (isset($photonic_archive_thumbs) && !empty($photonic_archive_thumbs)) {
				if (isset($per_page) && $photonic_archive_thumbs < $per_page) {
					$query .= '&per_page='.$photonic_archive_thumbs;
					$this->show_more_link = true;
				}
				else if (isset($per_page)) {
					$query .= '&per_page='.$per_page;
				}
			}
			else if (isset($per_page)) {
				$query .= '&per_page='.$per_page;
			}
		}
		else if (isset($per_page)) {
			$query .= '&per_page='.$per_page;
		}

		$login_required = false;
		if (isset($privacy_filter) && trim($privacy_filter) != '') {
			$query .= '&privacy_filter='.$privacy_filter;
			$login_required = $privacy_filter == 1 ? false : true;
		}

		// Allow users to define additional query parameters
		$query_urls = apply_filters('photonic_flickr_query_urls', $query_urls, $attr);
		$query = apply_filters('photonic_flickr_query', $query, $attr);

		if (isset($photonic_carousel_mode) && $photonic_carousel_mode == 'on') {
			$carousel = 'photonic-carousel jcarousel-skin-tango';
		}
		else {
			$carousel = '';
		}

		if (!$photonic_flickr_login_shown && $photonic_flickr_allow_oauth && is_singular() && !$photonic_flickr_oauth_done && $login_required) {
			$post_id = get_the_ID();
			$ret .= $this->get_login_box($post_id);
			$photonic_flickr_login_shown = true;
		}

		foreach ($query_urls as $query_url) {
			$method = 'flickr.photos.getInfo';
			$ret .= "<div class='photonic-flickr-stream $carousel'>";
			if ((isset($view) && $view != 'photo') || !isset($view)) {
				$ret .= "<ul>";
			}
			$iterator = array();
			if (is_array($query_url)) {
				$iterator = $query_url;
			}
			else {
				$iterator[] = $query_url;
			}

			foreach ($iterator as $nested_query_url) {
				$photonic_flickr_position++;
				$merged_query = $nested_query_url.$query;

				$end_point = Photonic_Processor::get_normalized_http_url($merged_query);
				$params = array();
				if (strstr($merged_query, $end_point) > -1) {
					$params = substr($merged_query, strlen($end_point));
					if (strlen($params) > 1) {
						$params = substr($params, 1);
					}
					$params = Photonic_Processor::parse_parameters($params);
					if (isset($params['jsoncallback'])) {
						unset($params['jsoncallback']);
					}
					$params['nojsoncallback'] = 1;
					$method = $params['method'];

					// We only worry about signing the call if the authentication is done. Otherwise we just show what is available.
					if ($photonic_flickr_oauth_done) {
						$signed_args = $this->sign_call($end_point, 'GET', $params);
						$merged_query = $end_point.'?'.Photonic_Processor::build_query($signed_args);
					}
					else {
						$merged_query = $end_point.'?'.Photonic_Processor::build_query($params);
					}
				}

				$ret .= $this->process_query($merged_query, $method, $columns, isset($user_id) ? $user_id : '');
			}
			if ((isset($view) && $view != 'photo') || !isset($view)) {
				$ret .= "</ul>";
			}
			if ($this->show_more_link && $method != 'flickr.photosets.getInfo' && $method != 'flickr.photos.getInfo' && $method != 'flickr.galleries.getInfo') {
				$ret .= $this->more_link_button(get_permalink().'#photonic-flickr-stream-'.$photonic_flickr_position);
			}
			$ret .= "</div>";
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
		global $photonic_flickr_api_key, $photonic_flickr_oauth_done;
		$query = 'http://api.flickr.com/services/rest/?method=flickr.collections.getTree&user_id='.$user_id.'&api_key='.$photonic_flickr_api_key;
		if ($collection_id != '') {
			$query .= '&collection_id='.$collection_id;
		}

		if ($photonic_flickr_oauth_done) {
			$end_point = Photonic_Processor::get_normalized_http_url($query);
			if (strstr($query, $end_point) > -1) {
				$params = substr($query, strlen($end_point));
				if (strlen($params) > 1) {
					$params = substr($params, 1);
				}
				$params = Photonic_Processor::parse_parameters($params);
				$signed_args = $this->sign_call($end_point, 'GET', $params);
				$query = $end_point.'?'.Photonic_Processor::build_query($signed_args);
			}
		}

		$feed = Photonic::http($query);
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
							$processed = array();
							foreach ($collections as $collection) {
								$collection_attrs = $collection->attributes();
								if (isset($collection_attrs['id'])) {
									if (!in_array($collection_attrs['id'], $processed)) {
										$iterative = $this->get_nested_collections($collection, $processed);
										$ret = array_merge($ret, $iterative);
									}
								}
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
	 * @param $processed
	 * @return array
	 */
	function get_nested_collections($collection, &$processed) {
		$attributes = $collection->attributes();
		$id = isset($attributes['id']) ? (string)$attributes['id'] : '';
		if (in_array($id, $processed)) {
			return array();
		}
		$processed[] = $id;
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
				$inner_attribubtes = $inner_collection->attributes();
				$processed[] = $inner_attribubtes['id'];
//				$inner = $this->get_nested_collections($inner_collection);
//				$ret = array_merge($ret, $inner);
			}
		}
		return $ret;
	}

	function sign_js_call() {
		if (isset($_POST['method'])) {
			$method = $_POST['method'];
			global $photonic_flickr_api_key, $photonic_flickr_oauth_done;
			$query = 'http://api.flickr.com/services/rest/?format=json&api_key='.$photonic_flickr_api_key.'&method='.$method.'&nojsoncallback=1';
			if (isset($_POST['photoset_id'])) {
				$photoset_id = $_POST['photoset_id'];
				if ($photoset_id != '') {
					$query .= '&photoset_id='.$photoset_id;
				}
			}

			if (isset($_POST['gallery_id'])) {
				$gallery_id = $_POST['gallery_id'];
				if ($gallery_id != '') {
					$query .= '&gallery_id='.$gallery_id;
				}
			}

			if ($photonic_flickr_oauth_done) {
				$end_point = Photonic_Processor::get_normalized_http_url($query);
				if (strstr($query, $end_point) > -1) {
					$params = substr($query, strlen($end_point));
					if (strlen($params) > 1) {
						$params = substr($params, 1);
					}
					$params = Photonic_Processor::parse_parameters($params);
					$signed_args = $this->sign_call($end_point, 'GET', $params);
					$query = $end_point.'?'.Photonic_Processor::build_query($signed_args);
				}
			}
			echo $query;
		}
		die();
	}

	function process_query($query, $method, $columns, $user) {
		$ret = '';
		$response = wp_remote_request($query);

		if (!is_wp_error($response)) {
			if ($response['response']['code'] == 200) {
				$body = $response['body'];
				$body = json_decode($body);
				switch ($method) {
					case 'flickr.photos.getInfo':
						if (isset($body->photo)) {
							$photo = $body->photo;
							$ret .= $this->process_photo($photo);
						}
						break;

					case 'flickr.photos.search':
						if (isset($body->photos) && isset($body->photos->photo)) {
							$photos = $body->photos->photo;
							$ret .= $this->process_photos($photos, '', $columns);
						}
						break;

					case 'flickr.photosets.getInfo':
						if (isset($body->photoset)) {
							$photoset = $body->photoset;
							$ret .= $this->process_photoset_header($photoset);
						}
						break;

					case 'flickr.photosets.getPhotos':
						if (isset($body->photoset)) {
							$photoset = $body->photoset;
							if (isset($photoset->photo) && isset($photoset->owner)) {
								$owner = $photoset->owner;
								$ret .= $this->process_photos($photoset->photo, $owner, $columns);
							}
						}
						break;

					case 'flickr.photosets.getList':
						if (isset($body->photosets)) {
							$photosets = $body->photosets;
							$ret .= $this->process_photosets($photosets, $columns, $user);
						}
						break;

					case 'flickr.galleries.getInfo':
						if (isset($body->gallery)) {
							$gallery = $body->gallery;
							$ret .= $this->process_gallery_header($gallery);
						}
						break;

					case 'flickr.galleries.getPhotos':
						if (isset($body->photos)) {
							$photos = $body->photos;
							if (isset($photos->photo)) {
								$ret .= $this->process_photos($photos->photo, '', $columns);
							}
						}
						break;

					case 'flickr.galleries.getList':
						if (isset($body->galleries)) {
							$galleries = $body->galleries;
							$ret .= $this->process_galleries($galleries, $columns, $user);
						}
						break;

					case 'flickr.collections.getTree':
						if (isset($body->collections)) {
							$collections = $body->collections;
							$ret .= $this->process_collections($collections, $columns, $user);
						}
						break;
				}
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
		global $photonic_flickr_main_size, $photonic_external_links_in_new_tab;
		$ret = '';
		$main_size = $photonic_flickr_main_size == 'none' ? '' : '_'.$photonic_flickr_main_size;
		$orig = "http://farm".$photo->farm.".static.flickr.com/".$photo->server."/".$photo->id."_".$photo->secret.$main_size.".jpg";
		$ret .= "<img src='".$orig."'>";
		if (!empty($photonic_external_links_in_new_tab)) {
			$target = ' target="_blank" ';
		}
		else {
			$target = '';
		}

		if (isset($photo->urls) && isset($photo->urls->url) && count($photo->urls->url) > 0) {
			$ret = "<a href='".$photo->urls->url[0]->_content."' $target>".$ret."</a>";
		}
		if (isset($photo->description) && $photo->description->_content != '') {
			$ret = "<div class='wp-caption'>".$ret."<div class='wp-caption-text'>".$photo->description->_content."</div></div>";
		}
		if (isset($photo->title)) {
			$ret = "<h3 class='photonic-single-photo-header photonic-single-flickr-photo-header'>".$photo->title->_content."</h3>".$ret;
		}
		return $ret;
	}

	/**
	 * Prints thumbnails for all photos returned in a query. This is used for printing the results of a search, tag, photoset or gallery.
	 * The photos are printed in-page.
	 *
	 * @param $photos
	 * @param string $owner
	 * @param string $columns
	 * @return string
	 */
	function process_photos($photos, $owner = '', $columns = 'auto') {
		global $photonic_slideshow_library, $photonic_flickr_position, $photonic_flickr_photos_per_row_constraint, $photonic_flickr_thumb_size, $photonic_flickr_main_size, $photonic_flickr_view, $photonic_flickr_photo_title_display;
		$main_size = $photonic_flickr_main_size == 'none' ? '' : '_'.$photonic_flickr_main_size;
		$a_class = '';
		$col_class = '';
		if ($photonic_slideshow_library != 'none') {
			$a_class = 'launch-gallery-'.$photonic_slideshow_library." ".$photonic_slideshow_library;
		}
		if (Photonic::check_integer($columns)) {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}

		if ($col_class == '' && $photonic_flickr_photos_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-photos';
		}
		else if ($col_class == '') {
			$col_class = 'photonic-gallery-'.$photonic_flickr_photos_per_row_constraint.'c';
		}
		$a_rel = 'lightbox-photonic-flickr-stream-'.$photonic_flickr_position;
		if ($photonic_slideshow_library == 'prettyphoto') {
			$a_rel = 'photonic-prettyPhoto['.$a_rel.']';
		}

		$ret = '';
		$photonic_flickr_view = __('View in Flickr', 'photonic');
		global $photonic_external_links_in_new_tab;
		if (!empty($photonic_external_links_in_new_tab)) {
			$target = " target='_blank' ";
		}
		else {
			$target = '';
		}

		$counter = 0;
		foreach ($photos as $photo) {
			$thumb = 'http://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.'_'.$photonic_flickr_thumb_size.'.jpg';
			$orig = 'http://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.$main_size.'.jpg';
			if (isset($photo->owner)) {
				$owner = $photo->owner;
			}
			$url = "http://www.flickr.com/photos/".$owner."/".$photo->id;
			$orig = $photonic_slideshow_library == 'none' ? $url : $orig;
			$original_title = esc_attr($photo->title);

			$flickr_view = "<a href='".$url."' $target>".$photonic_flickr_view."</a>";
			$title = $photonic_slideshow_library == 'none' ? $original_title : ($original_title == '' ? $flickr_view : $original_title.' | '.$flickr_view);
			$shown_title = '';
			if ($photonic_flickr_photo_title_display == 'below') {
				$shown_title = '<span class="photonic-photo-title">'.$title.'</span>';
			}
			$ret .= '<li class="photonic-flickr-image photonic-flickr-photo '.$col_class.'"><a href="'.$orig.'" class="'.$a_class.'" rel="'.$a_rel.'" title="'.$title.'"><img alt="'.$original_title.'" src="'.$thumb.'"/></a>'.$shown_title.'</li>';
			$counter++;
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
	 * Prints the header for an in-page photoset.
	 *
	 * @param $photoset
	 * @return string
	 */
	function process_photoset_header($photoset) {
		global $photonic_flickr_thumb_size, $photonic_flickr_hide_set_thumbnail, $photonic_flickr_hide_set_title, $photonic_flickr_hide_set_photo_count, $photonic_flickr_position, $photonic_external_links_in_new_tab;
		$owner = $photoset->owner;
		$thumb = "http://farm".$photoset->farm.".static.flickr.com/".$photoset->server."/".$photoset->primary."_".$photoset->secret."_".$photonic_flickr_thumb_size.".jpg";
		$title = esc_attr($photoset->title->_content);
		$image = '<img src="'.$thumb.'" alt="'.$title.'" />';
		$flickr_link = 'http://www.flickr.com/photos/'.$owner.'/sets/'.$photoset->id;
		if (!empty($photonic_external_links_in_new_tab)) {
			$target = ' target="_blank" ';
		}
		else {
			$target = '';
		}
		$anchor = "<a href='".$flickr_link."' class='photonic-header-thumb photonic-flickr-set-solo-thumb' title='".$title."' $target>".$image."</a>";

		$ret = '';
		if (!($photonic_flickr_hide_set_thumbnail && $photonic_flickr_hide_set_title && $photonic_flickr_hide_set_photo_count)) {
			// Have to make use of "li" because we are in a "ul"
			$ret .= "<li class='photonic-flickr-set'>";

			if (!$photonic_flickr_hide_set_thumbnail) {
				$ret .= $anchor;
			}
			if (!($photonic_flickr_hide_set_title && $photonic_flickr_hide_set_photo_count)) {
				$ret .= "<div class='photonic-header-details photonic-set-details'>";
				if (!$photonic_flickr_hide_set_title) {
					$ret .= "<div class='photonic-header-title photonic-set-title'><a href='".$flickr_link."' $target>".$title.'</a></div>';
				}
				if (!$photonic_flickr_hide_set_photo_count) {
					$photo_count = sprintf(__('%s photos', 'photonic'), $photoset->photos);
					$ret .= "<span class='photonic-header-info photonic-set-photos'>".$photo_count.'</span>';
				}
				$ret .= "</div><!-- .photonic-collection-details -->";
			}
			$ret .= "</li>";
		}
		return $ret;
	}

	/**
	 * Prints thumbnails for each photoset returned in a query.
	 *
	 * @param $photosets
	 * @param $columns
	 * @param $user
	 * @return string
	 */
	function process_photosets($photosets, $columns, $user) {
		global $photonic_flickr_collection_set_per_row_constraint, $photonic_flickr_collection_set_constrain_by_count, $photonic_flickr_thumb_size, $photonic_flickr_position, $photonic_flickr_collection_set_title_display, $photonic_flickr_hide_collection_set_photos_count_display;
		$ret = '';

		if ($columns != 'auto') {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}
		else if ($photonic_flickr_collection_set_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-photosets';
		}
		else {
			$col_class = 'photonic-gallery-'.$photonic_flickr_collection_set_constrain_by_count.'c';
		}

		$counter = 0;
		foreach ($photosets->photoset as $photoset) {
			$id = $photoset->id;
			$thumb = "http://farm".$photoset->farm.".static.flickr.com/".$photoset->server."/".$photoset->primary."_".$photoset->secret."_".$photonic_flickr_thumb_size.".jpg";
			$title = esc_attr($photoset->title->_content);
			$owner = isset($photoset->owner) ? $photoset->owner : $user;
			$image = "<img src='".$thumb."' alt='".$title."' />";
			$anchor = "<a href='http://www.flickr.com/photos/".$owner.'/sets/'.$photoset->id."' class='photonic-flickr-set-thumb' id='photonic-flickr-set-thumb-".$id.'-'.$photonic_flickr_position.'-'.$photoset->id."' title='".$title."'>".$image."</a>";
			$text = '';
			if ($photonic_flickr_collection_set_title_display == 'below') {
				$text = "<span class='photonic-photoset-title'>".$title."</span>";
				if (!$photonic_flickr_hide_collection_set_photos_count_display) {
					$text .= '<span class="photonic-photoset-photo-count">'.sprintf(__('%s photos', 'photonic'), $photoset->photos).'</span>';
				}
			}
			$ret .= "<li class='photonic-flickr-image photonic-flickr-set-thumb ".$col_class."' id='photonic-flickr-set-".$id.'-'.$photonic_flickr_position."-".$photoset->id."'>".$anchor.$text."</li>";
			$counter++;
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
	 * Shows the header for a gallery invoked in-page.
	 *
	 * @param $gallery
	 * @return string
	 */
	function process_gallery_header($gallery) {
		global $photonic_flickr_thumb_size, $photonic_flickr_hide_gallery_thumbnail, $photonic_flickr_hide_gallery_title, $photonic_flickr_hide_gallery_photo_count, $photonic_external_links_in_new_tab;
		$ret = '';

		$thumb = "http://farm".$gallery->primary_photo_farm.".static.flickr.com/".$gallery->primary_photo_server."/".$gallery->primary_photo_id."_".$gallery->primary_photo_secret."_".$photonic_flickr_thumb_size.".jpg";
		$title = esc_attr($gallery->title->_content);

		if (!empty($photonic_external_links_in_new_tab)) {
			$target = ' target="_blank" ';
		}
		else {
			$target = '';
		}
		$image = "<img src='".$thumb."' alt='".$title."' />";
		$flickr_link = $gallery->url.'/';
		$anchor = "<a href='".$flickr_link."' class='photonic-header-thumb photonic-flickr-gallery-solo-thumb' ".$title."' $target>".$image."</a>";

		if (!($photonic_flickr_hide_gallery_thumbnail && $photonic_flickr_hide_gallery_title && $photonic_flickr_hide_gallery_photo_count)) {
			$ret .= "<li class='photonic-flickr-gallery'>";
			if (!$photonic_flickr_hide_gallery_thumbnail) {
				$ret .= $anchor;
			}
			if (!($photonic_flickr_hide_gallery_title && $photonic_flickr_hide_gallery_photo_count)) {
				$ret .= "<div class='photonic-header-details photonic-gallery-details'>";
				if (!$photonic_flickr_hide_gallery_title) {
					$ret .= "<div class='photonic-header-title photonic-gallery-title'><a href='".$flickr_link."' $target>".$title.'</a></div>';
				}
				if (!$photonic_flickr_hide_gallery_photo_count) {
					$photo_count = sprintf(__('%s photos', 'photonic'), $gallery->count_photos);
					$ret .= "<span class='photonic-header-info photonic-gallery-photos'>".$photo_count.'</span>';
				}
				$ret .= "</div><!-- .photonic-header-details -->";
			}
			$ret .= "</li>";
		}
		return $ret;
	}

	/**
	 * Prints out the thumbnails for all galleries belonging to a user.
	 *
	 * @param $galleries
	 * @param $columns
	 * @param $user
	 * @return string
	 */
	function process_galleries($galleries, $columns, $user) {
		global $photonic_flickr_thumb_size, $photonic_flickr_position, $photonic_flickr_galleries_per_row_constraint, $photonic_flickr_galleries_constrain_by_count, $photonic_flickr_gallery_title_display, $photonic_flickr_hide_gallery_photos_count_display;
		$ret = '';

		if ($columns != 'auto') {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}
		else if ($photonic_flickr_galleries_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-galleries';
		}
		else {
			$col_class = 'photonic-gallery-'.$photonic_flickr_galleries_constrain_by_count.'c';
		}

		foreach ($galleries->gallery as $gallery) {
			$id = $gallery->id;
			$thumb = "http://farm".$gallery->primary_photo_farm.".static.flickr.com/".$gallery->primary_photo_server."/".$gallery->primary_photo_id."_".$gallery->primary_photo_secret."_".$photonic_flickr_thumb_size.".jpg";
			$title = esc_attr($gallery->title->_content);

			$image = "<img src='".$thumb."' alt='".$title."' />";
			$anchor = "<a href='".$gallery->url."/' class='photonic-flickr-gallery-thumb photonic-flickr-gallery-thumb-user-$user' id='photonic-flickr-gallery-thumb-".$id.'-'.$photonic_flickr_position.'-'.$gallery->id."' title='".$title."'>".$image."</a>";
			$text = '';

			if ($photonic_flickr_gallery_title_display == 'below') {
				$text = "<span class='photonic-gallery-title'>".$title."</span>";
				if (!$photonic_flickr_hide_gallery_photos_count_display) {
					$text .= '<span class="photonic-photoset-photo-count">'.sprintf(__('%s photos', 'photonic'), $gallery->count_photos).'</span>';
				}
			}

			$ret .= "<li class='photonic-flickr-image photonic-flickr-gallery-thumb ".$col_class."' id='photonic-flickr-gallery-".$id.'-'.$photonic_flickr_position."-".$gallery->id."'>".$anchor.$text."</li>";
		}
		return $ret;
	}

	/**
	 * Prints a collection header, followed by thumbnails of all sets in that collection.
	 *
	 * @param $collections
	 * @param $columns
	 * @param $user
	 * @return string
	 */
	function process_collections($collections, $columns, $user) {
		global $photonic_flickr_hide_empty_collection_details, $photonic_external_links_in_new_tab, $photonic_flickr_collection_set_per_row_constraint, $photonic_flickr_collection_set_constrain_by_count, $photonic_flickr_hide_collection_thumbnail, $photonic_flickr_hide_collection_title, $photonic_flickr_hide_collection_set_count, $photonic_flickr_thumb_size, $photonic_flickr_position, $photonic_flickr_collection_set_title_display, $photonic_flickr_hide_collection_set_photos_count_display;
		$ret = '';

		if (!empty($photonic_external_links_in_new_tab)) {
			$target = ' target="_blank" ';
		}
		else {
			$target = '';
		}

		if ($columns != 'auto') {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}
		else if ($photonic_flickr_collection_set_per_row_constraint == 'padding') {
			$col_class = 'photonic-pad-photosets';
		}
		else {
			$col_class = 'photonic-gallery-'.$photonic_flickr_collection_set_constrain_by_count.'c';
		}
		foreach ($collections->collection as $collection) {
			$dont_show = false;
			if (empty($collection->set) && !empty($photonic_flickr_hide_empty_collection_details)) {
				$dont_show = true;
			}
			$id = $collection->id;
			if (!$dont_show) {
				$url_id = substr($id, stripos($id, '-') + 1);
				$collection_a = "http://www.flickr.com/photos/".$user."/collections/".$url_id;
				$ret .= "<li class='photonic-flickr-image photonic-flickr-collection photonic-flickr-collection-".$id."' id='photonic-flickr-collection-".$id."'>";
				if (!($photonic_flickr_hide_collection_thumbnail && $photonic_flickr_hide_collection_title && $photonic_flickr_hide_collection_set_count)) {
					if (!$photonic_flickr_hide_collection_thumbnail) {
						$ret .= "<a href='".$collection_a."' class='photonic-header-thumb photonic-flickr-collection-thumb' $target><img src='".$collection->iconsmall."' /></a>";
					}
					if (!($photonic_flickr_hide_collection_title && $photonic_flickr_hide_collection_set_count)) {
						$ret .= "<div class='photonic-header-details photonic-collection-details'>";
						if (!$photonic_flickr_hide_collection_title) {
							$ret .= "<div class='photonic-header-title photonic-collection-title'><a href='".$collection_a."'>".$collection->title.'</a></div>';
						}
						if (!$photonic_flickr_hide_collection_set_count && isset($collection->set)) {
							$photosets = $collection->set;
							$ret .= "<span class='photonic-header-info photonic-collection-sets'>".sprintf(__('%s sets', 'photonic'), count($photosets)).'</span>';
						}
						$ret .= "</div><!-- .photonic-collection-details -->";
					}
				}
				$ret .= "</li>";
			}

			if (isset($collection->set) && !empty($collection->set)) {
				$photosets = $collection->set;
				foreach ($photosets as $set) {
					$set_url = 'http://api.flickr.com/services/rest/?format=json&nojsoncallback=1&&api_key='.$this->api_key.'&method=flickr.photosets.getInfo&photoset_id='.$set->id;
					$set_response = wp_remote_request($set_url);
					if (!is_wp_error($set_response) && isset($set_response['response']) && isset($set_response['response']['code']) && $set_response['response']['code'] == 200) {
						$set_response = json_decode($set_response['body']);
						if ($set_response->stat != 'fail' && isset($set_response->photoset)) {
							$photoset = $set_response->photoset;
							$id = $photoset->id;
							$thumb = "http://farm".$photoset->farm.".static.flickr.com/".$photoset->server."/".$photoset->primary."_".$photoset->secret."_".$photonic_flickr_thumb_size.".jpg";
							$title = esc_attr($photoset->title->_content);
							$owner = $photoset->owner;

							$image = "<img src='".$thumb."' alt='".$title."' />";
							$anchor = "<a href='http://www.flickr.com/photos/".$owner.'/sets/'.$id."' class='photonic-flickr-set-thumb' id='photonic-flickr-set-thumb-".$id.'-'.$photonic_flickr_position.'-'.$photoset->id."' title='".$title."'>".$image."</a>";

							$text = '';
							if ($photonic_flickr_collection_set_title_display == 'below') {
								$text = "<span class='photonic-photoset-title'>".$title."</span>";
								if ($photonic_flickr_hide_collection_set_photos_count_display) {
									$text .= '<span class="photonic-photoset-photo-count">'.sprintf(__('%s photos', 'photonic'), $photoset->photos).'</span>';
								}
							}

							$ret .= "<li class='photonic-flickr-image photonic-flickr-set-thumb ".$col_class."' id='photonic-flickr-set-".$id.'-'.$photonic_flickr_position."-".$id."'>".$anchor.$text."</li>";
						}
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'http://www.flickr.com/services/oauth/access_token';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		return 'http://www.flickr.com/services/oauth/authorize';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		return 'http://www.flickr.com/services/oauth/authorize';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_token_URL() {
		return 'http://www.flickr.com/services/oauth/request_token';
	}

	public function end_point() {
		return 'http://api.flickr.com/services/rest/';
	}

	function parse_token($response) {
		$body = $response['body'];
		$token = Photonic_Processor::parse_parameters($body);
		return $token;
	}

	public function check_access_token_method() {
		return 'flickr.test.login';
	}

	/**
	 * Method to validate that the stored token is indeed authenticated.
	 *
	 * @param $request_token
	 * @return array|WP_Error
	 */
	function check_access_token($request_token) {
		$parameters = array('method' => $this->check_access_token_method(), 'format' => 'json', 'nojsoncallback' => 1);
		$signed_parameters = $this->sign_call($this->end_point(), 'GET', $parameters);

		$end_point = $this->end_point();
		$end_point .= '?'.Photonic_Processor::build_query($signed_parameters);
		$parameters = null;

		$response = Photonic::http($end_point, 'GET', $parameters);
		return $response;
	}
}
