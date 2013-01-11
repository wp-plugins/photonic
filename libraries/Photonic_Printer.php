<?php
/**
 * Class for printing various components. All processors must use the methods defined here for consistency.
 *
 * @package Photonic
 * @subpackage Libraries
 */

class Photonic_Printer {
	/**
	 * Prints the header for a section. Typically used for albums / photosets / groups, where some generic information about the album / photoset / group is available.
	 *
	 * @param string $provider The provider for which the header is being displayed
	 * @param array $header The header object, which contains the title, thumbnail source URL and the link where clicking on the thumb will take you
	 * @param string $type Indicates what type of object is being displayed like gallery / photoset / album etc. This is added to the CSS class.
	 * @param array $hidden Contains the elements that should be hidden from the header display.
	 * @param array $counters Contains counts of the object that the header represents. In most cases this has just one value. Zenfolio objects have multiple values.
	 * @param string $link Should clicking on the thumbnail / title take you anywhere?
	 * @param string $display Indicates if this is on the page or in a popup
	 * @return string
	 */
	function process_object_header($provider, $header, $type = 'group', $hidden = array(), $counters = array(), $link, $display = 'in-page') {
		$ret = '';
		if (!empty($header['title'])) {
			global $photonic_external_links_in_new_tab;
			$title = esc_attr($header['title']);
			if (!empty($photonic_external_links_in_new_tab)) {
				$target = ' target="_blank" ';
			}
			else {
				$target = '';
			}

			$anchor = '';
			if (!empty($header['thumb_url'])) {
				$image = '<img src="'.$header['thumb_url'].'" alt="'.$title.'" />';

				if ($link) {
					$anchor = "<a href='".$header['link_url']."' class='photonic-header-thumb photonic-$provider-$type-solo-thumb' title='".$title."' $target>".$image."</a>";
				}
				else {
					$anchor = "<div class='photonic-header-thumb photonic-$provider-$type-solo-thumb'>$image</div>";
				}
			}

			if (empty($hidden['thumbnail']) || empty($hidden['title']) || empty($hidden['counter'])) {
				$ret .= "<div class='photonic-$provider-$type'>";

				if (empty($hidden['thumbnail'])) {
					$ret .= $anchor;
				}
				if (empty($hidden['title']) || empty($hidden['counter'])) {
					$ret .= "<div class='photonic-header-details photonic-$type-details'>";
					if (empty($hidden['title'])) {
						if ($link) {
							$ret .= "<div class='photonic-header-title photonic-$type-title'><a href='".$header['link_url']."' $target>".$title.'</a></div>';
						}
						else {
							$ret .= "<div class='photonic-header-title photonic-$type-title'>".$title.'</div>';
						}
					}
					if (empty($hidden['counter'])) {
						$counter_texts = array();
						if (!empty($counters['groups'])) {
							$counter_texts[] = sprintf(_n('%s group', '%s groups', $counters['groups'], 'photonic'), $counters['groups']);
						}
						if (!empty($counters['sets'])) {
							$counter_texts[] = sprintf(_n('%s set', '%s sets', $counters['sets'], 'photonic'), $counters['sets']);
						}
						if (!empty($counters['photos'])) {
							$counter_texts[] = sprintf(_n('%s photo', '%s photos', $counters['photos'], 'photonic'), $counters['photos']);
						}

						apply_filters('photonic_modify_counter_texts', $counter_texts, $counters);

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
}