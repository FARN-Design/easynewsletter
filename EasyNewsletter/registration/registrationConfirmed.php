<?php

namespace easyNewsletter;

use WP_Query;

$email = sanitize_email($_GET["email"]);
$token = sanitize_text_field($_GET["token"]);


$query = new WP_Query(array("post_type" => "en_subscribers", "posts_per_page" => "-1"));

while ($query->have_posts()){
	$query->the_post();

	if (get_post_meta( get_the_ID(),"en_eMailAddress", true) == $email){
		if (get_post_meta( get_the_ID(),"en_token", true) == $token){
			//No escape because we want to get the raw value
			echo get_the_content("", false, databaseConnector::instance()->getSettingFromDB("confirmationSuccessPageID"));
			update_post_meta(get_the_ID(), "en_doubleOptIn", "confirmed");
			update_post_meta(get_the_ID(), "en_status", "active");
			mailManager::instance()->sendWelcomeMail($email);
			return;
		}
	}
}
//No escape because we want to get the raw value
echo get_the_content("", false, databaseConnector::instance()->getSettingFromDB("confirmationDeniedPageID"));