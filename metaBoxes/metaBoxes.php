<?php

namespace metaBoxes;

use easyNewsletter\farnLog;

include "addAttachmentBox.php";
include "htmlInjectionBox.php";
class metaBoxes {

	public static function addAllMetaBoxes(){
		$metaBoxes = new metaBoxes();
		add_action( "admin_enqueue_scripts", array($metaBoxes, "metaBoxesScripts"));

		new addAttachmentBox();
		new htmlInjectionBox();
	}

	public function metaBoxesScripts(){
		//get current admin screen, or null
		$screen = get_current_screen();
		// verify admin screen object
		if (is_object($screen)) {
			// enqueue only for specific post types
			if (in_array($screen->post_type, ['post', 'en_newsletters'])) {
				// enqueue script
				wp_enqueue_script('en_metaBoxesScripts', plugin_dir_url(__DIR__) . '/resources/newsletterPostTypeMetaBoxes.js', ['jquery']);
				// localize script, create a custom js object
				wp_localize_script(
					'en_metaBoxesScripts',
					'en_metaBoxes_obj',
					[
						'url' => admin_url('admin-ajax.php'),
					]
				);
			}
		}
	}
}