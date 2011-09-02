<?php
/**
 * Processor for Flickr Galleries
 */

class Photonic_Flickr_Processor extends Photonic_Processor {
	function process_response($args = array()) {
		return $this->library;
	}
}
?>