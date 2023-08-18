<?php
namespace easyNewsletter;

echo "<div class='en_newsletter_preview'>" . mailManager::instance()->htmlInjection(newsletterPostType::convertContent(get_the_ID()), false, get_the_ID()) . "</div>";
?>