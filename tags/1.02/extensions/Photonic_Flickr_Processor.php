<?php
/**
 * Processor for Flickr Galleries
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Flickr_Processor extends Photonic_Processor {
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
	 * @param array $attr
	 * @return string|void
	 * @since 1.02
	 */
	function get_gallery_images($attr = array()) {
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
				if (isset($columns) && Photonic::check_integer($columns)) {
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
}
?>