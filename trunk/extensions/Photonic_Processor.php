<?php
/**
 * Class to be extended by individual processors
 *
 * @package Photonic
 * @subpackage Extensions
 */

abstract class Photonic_Processor {
	public $library, $thumb_size, $full_size;

	function Photonic_Processor() {
		global $photonic_slideshow_library;
		$this->library = $photonic_slideshow_library;
	}

	abstract protected function process_response($args = array());
}
?>