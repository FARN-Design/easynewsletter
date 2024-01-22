<?php
namespace easyNewsletter;

//No escape here because we want to get the html elements as content!
echo "<div class='en_newsletter_preview'>" . mailManager::instance()->htmlInjection(newsletterPostType::convertContent(get_the_ID()), false, get_the_ID()) . "</div>";
