<?php

namespace easyNewsletter;

$email = sanitize_email($_GET["email"]);
$token = sanitize_text_field($_GET["token"]);

echo esc_attr(get_the_content("", false, databaseConnector::instance()->getSettingFromDB("unsubscribedPageID")));

echo "<form class='en_unsubscribeConformation'>" .
     "<input type='hidden' name='email' value='".esc_attr($email)."'>" .
     "<input type='hidden' name='token' value='".esc_attr($token)."'>" .
     "<input type='submit' name='submitUnsubscribed' value='Unsubscribe now'>" .
	 "</form>";




