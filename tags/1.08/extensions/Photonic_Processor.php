<?php
/**
 * Gallery processor class to be extended by individual processors. This class has an abstract method called <code>get_gallery_images</code>
 * that has to be defined by each inheriting processor.
 *
 * @package Photonic
 * @subpackage Extensions
 */

abstract class Photonic_Processor {
	public $library, $thumb_size, $full_size;

	function __construct() {
		global $photonic_slideshow_library;
		$this->library = $photonic_slideshow_library;
	}

	abstract protected function get_gallery_images($attr = array());
}
?>