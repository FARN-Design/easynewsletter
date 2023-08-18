<?php

namespace easyNewsletter;

class mailManager {

	/**
	 * @var mailManager The only object of that class.
	 */
	private static mailManager $mailManager;

	private static array $mailHeader;

	public function __construct() {
		$senderName = databaseConnector::instance()->getSettingFromDB("senderName");
		$senderMail = databaseConnector::instance()->getSettingFromDB("senderEmailAddress");
		$replyMail = databaseConnector::instance()->getSettingFromDB("replyTo");

		self::$mailHeader = array(
			"Content-Type: text/html",
			'From: '.$senderName.' <'.$senderMail.'>',
			"Reply-To: ".$senderName." <".$replyMail.">"
		);
	}

	/**
	 * Function that is used in the initiation phase of the plugin.
	 * It is required that this function is called at the beginning of the plugin to work correctly.
	 * @return void
	 */
	public function init(): void {
		add_filter( 'cron_schedules', array($this, 'easyNewsletterCronInterval' ));

		add_action( 'easyNewsletterHook', array($this,'easyNewsletterCronExecution'));
		if ( ! wp_next_scheduled( 'easyNewsletterHook' ) ) {
			wp_schedule_event( time(), 'easyNewsletterInterval', 'easyNewsletterHook' );
		}
	}

	/**
	 * Unset the Wp-Cron Schedule when the plugin is deactivated.
	 * It is necessary to call this function in the deactivation hook of the plugin @return void
	 * @link easyNewsletter::deactivation() to work correctly.
	 *
	 */
	public function deactivate(): void {
		$timestamp = wp_next_scheduled( 'easyNewsletterHook' );
		wp_unschedule_event( $timestamp, 'easyNewsletterHook' );
	}

	/**
	 * Returns the only active instance of that class.
	 * If no object is present a new one is created.
	 *
	 * @return mailManager The main object of that class
	 */
	public static function instance(): mailManager {
		if (!isset(self::$mailManager)){
			self::$mailManager = new mailManager();
		}
		return self::$mailManager;
	}

	/**
	 * Sends an E-Mail via the wp_mail function.
	 * Currently, without any transformation.
	 * Adds a URL to unsubscribe to the content.
	 *
	 * @param $receiver string the receiver of the Mail.
	 * @param $subject string the subject of the Mail.
	 * @param $message string the message of the Mail.
	 * @param $token string the token unique to the receiver. Used to generate the URL to unsubscribe.
	 *
	 * @return void
	 */
	public function sendMail( string $receiver, string $subject, string $message, string $postID, array $attachments): void {
		wp_mail($receiver, $subject, $message, self::$mailHeader, $attachments);
	}

	/**
	 * The function is called when a new subscriber is created via the
	 *
	 * @param $receiver
	 *
	 * @return void @link subscriberHandler::addSubscriber() function.
	 */
	public function sendActivationMail($receiver): void {
		$postID = databaseConnector::instance()->getSettingFromDB("standardActivationPost");
		$subject = get_post_meta($postID,"en_subject", true);
		$content = newsletterPostType::convertContent($postID);
		$content = $this->htmlInjection($content, metaDataWrapper::getSubscriberIdByMail($receiver), databaseConnector::instance()->getSettingFromDB("standardActivationPost"));
		wp_mail($receiver, $subject, $content, self::$mailHeader);
	}

	public function sendUnsubscribeMail($receiver): void {
		$postID = databaseConnector::instance()->getSettingFromDB("standardUnsubscribePost");
		$subject = get_post_meta($postID,"en_subject", true);
		$content = newsletterPostType::convertContent($postID);
		$content = $this->htmlInjection($content, metaDataWrapper::getSubscriberIdByMail($receiver), databaseConnector::instance()->getSettingFromDB("standardUnsubscribePost"));
		wp_mail($receiver, $subject, $content, self::$mailHeader);
	}

	public function sendWelcomeMail($receiver, $token): void{
		$postID = databaseConnector::instance()->getSettingFromDB("standardWelcomePost");
		$subject = get_post_meta($postID,"en_subject", true);
		$content = newsletterPostType::convertContent($postID);
		$content = $this->htmlInjection($content, metaDataWrapper::getSubscriberIdByMail($receiver), databaseConnector::instance()->getSettingFromDB("standardWelcomePost"));
		wp_mail($receiver, $subject, $content, self::$mailHeader);
	}

	public function sendAdminMail($subject, $content): void {
		$receiver = get_option('admin_email');
		wp_mail($receiver, $subject, $content, self::$mailHeader);
	}

	/**
	 * Provides the schedule in wich the emails are send to the subscribers.
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function easyNewsletterCronInterval( $schedules ): mixed {
		$intervalInSeconds = databaseConnector::instance()->getSettingFromDB("intervalInSeconds");
		$schedules['easyNewsletterInterval'] = array(
			'interval' => $intervalInSeconds,
			'display'  => esc_html__( 'every_'.$intervalInSeconds.'_seconds' ), );
		return $schedules;
	}

	/**
	 * The main function that is executed each wp-cron intervall.
	 * @return void
	 */
	public function easyNewsletterCronExecution(): void {
		$maxMailsPerInterval  = (int) databaseConnector::instance()->getSettingFromDB( "maxEmailPerInterval" );
		$mailsPerInterval  = $maxMailsPerInterval;
		$sendingInProgress = databaseConnector::instance()->getSettingFromDB( "sendingInProgress" );
		//Inline if to set the variable with real true and false values. String to bool conversion.
		( $sendingInProgress == "true" ) ? $sendingInProgress = true : $sendingInProgress = false;

		$dbc               = databaseConnector::instance();

		if (!$sendingInProgress){
			return;
		}


		$currentNewsletter = $dbc->getSettingFromDB( "activeNewsletter" );
		$currentNewsletterID = $dbc->getSettingFromDB( "activeNewsletterID" );
		$content = newsletterPostType::convertContent($currentNewsletterID);
		$allReceiverIDs = $this->getAllNewsletterReceiverIDsAsArray($currentNewsletterID);

		farnLog::log("Starting Newsletter Sending Interval.");

		foreach ( $allReceiverIDs as $currentID ) {

			if ($mailsPerInterval <= 0 || !$sendingInProgress){ break;}

			//Fill the variables with the Post MetaFields
			$en_allReceived  = metaDataWrapper::getAllReceivedNewsletterAsArray($currentID);
			$en_eMailAddress = metaDataWrapper::getEmail($currentID);
			$en_token        = metaDataWrapper::getToken($currentID);

			//Updates last received Newsletter to the current Newsletter
			metaDataWrapper::setLastReceivedNewsletter($currentID, $currentNewsletter);
			$en_allReceived[] = $currentNewsletter;
			metaDataWrapper::setAllLastReceivedNewsletter($currentID, $en_allReceived);
			$subject = get_post_meta($currentNewsletterID,"en_subject", true);
			$contentInjected = $this->htmlInjection($content, $currentID, $currentNewsletterID);
			$attachments = $this->generateAttachments(unserialize(get_post_meta($currentNewsletterID, "en_newsletter_attachments", true)));
			$this->sendMail( $en_eMailAddress, $subject, $contentInjected, $currentNewsletterID, $attachments);
			//Counts the Mails Per Interval Down
			$mailsPerInterval = $mailsPerInterval - 1;
		}

		farnLog::log("Finished Newsletter Sending Interval.");

		$numberOfSendNewsletter = $maxMailsPerInterval - $mailsPerInterval;
		if ($mailsPerInterval > 0){
			$this->stopNewsletterSending("Finished");
		}
	}

	public function getAllNewsletterReceiverIDsAsArray(string $currentNewsletterID):array{
		$subscriberIDs = metaDataWrapper::getAllSubscriberIDsAsArray();
		$receiverIDs = array();
		$targetSubscriberCategory = explode(",", get_post_meta($currentNewsletterID, "en_target_group_categories", true));
		$targetSubscriberRole = explode(",", get_post_meta($currentNewsletterID, "en_target_group_roles", true));
		$currentNewsletter = get_the_title($currentNewsletterID);
		$customRulesMetaField = get_post_meta($currentNewsletterID, "en_newsletter_customConditions", true);

		foreach ($subscriberIDs as $subscriber_id){
			$en_status       = metaDataWrapper::getStatus($subscriber_id);
			$en_lastReceived = metaDataWrapper::getLastReceivedNewsletter($subscriber_id);
			if ($en_status == "active" && $en_lastReceived != $currentNewsletter){
				$receiverIDs[] = $subscriber_id;
			}
		}
		return $receiverIDs;
	}

	private function generateUnsubscribeURL($email, $token): string{
		$registrationPageId = databaseConnector::instance()->getSettingFromDB("registrationPageID");
		$postPermalink = get_permalink($registrationPageId);
		$url = $postPermalink."?unsubscribed=true&email=" . $email . "&token=" . $token;
		$url = str_replace('https://',"",$url);
		$url = str_replace('http://',"",$url);
		return $url;
	}

	private function generateActivationURL($receiver, $token){
		$registrationPageId = databaseConnector::instance()->getSettingFromDB("registrationPageID");
		$postPermalink = get_permalink($registrationPageId);
		$url = $postPermalink."?confirmed=true&email=".$receiver."&token=".$token;
		$url = str_replace('https://',"",$url);
		$url = str_replace('http://',"",$url);
		return $url;
	}

	public function htmlInjection(String $htmlContent, $userID, $newsletterID): string{
		//add replacements by adding elements to this array.


		if ( ! $userID ){
			$replacementArray = array(
				'{{vorname}}' => "Max",
				'{{nachname}}' => "Mustermann",
				'{{anrede}}' => "Herr",
				'{{ansprache}}' => "Sehr geehrter Herr Max Mustermann",
				'{{unsubscribeURL}}' => "urlUnsubscribePlaceholder",
				'{{activationURL}}' => "urlActivationPlaceholder",
			);
		} else{
			$replacementArray = array(
				'{{vorname}}' => metaDataWrapper::getFirstName($userID),
				'{{nachname}}' => metaDataWrapper::getLastName($userID),
				'{{anrede}}' => metaDataWrapper::getSalutation($userID),
				'{{ansprache}}' => $this->anspracheHandleUser($userID),
				'{{unsubscribeURL}}' => $this->generateUnsubscribeURL(metaDataWrapper::getEmail($userID), metaDataWrapper::getToken($userID)),
				'{{activationURL}}' => $this->generateActivationURL(metaDataWrapper::getEmail($userID), metaDataWrapper::getToken($userID)),
			);
		}

		foreach ($replacementArray as $key => $value){
			$htmlContent = str_replace($key, $value, $htmlContent);
		}

		if (! $userID ){
			return $htmlContent;
		}

		//Custom metaField handling
		$newsletterCustomInjection = unserialize(get_post_meta($newsletterID, "en_custom_html_injection", true));
		foreach ($newsletterCustomInjection as $key => $value){
			$metaDataWrapperFunctionName = metaDataWrapper::$availableMetaFieldsForCustomHtmlInjection[$value];
			$userMetaField = metaDataWrapper::$metaDataWrapperFunctionName($userID);
			$htmlContent = str_replace($key, $userMetaField, $htmlContent);
		}

		return $htmlContent;
	}

	function anspracheHandleUser($userID): string{
		$salutation = metaDataWrapper::getSalutation($userID);
		$firstName = metaDataWrapper::getFirstName($userID);
		$lastName = metaDataWrapper::getLastName($userID);
		if ($firstName == null && $lastName == null){
			return "Sehr geehrte Damen und Herren";
		}
		else if (str_contains($salutation,'Herr')) {
			return "Sehr geehrter ".$salutation." ".$firstName." ".$lastName;
		}
		else if (str_contains($salutation,'Frau')){
			return "Sehr geehrte ".$salutation." ".$firstName." ".$lastName;
		}
		return "Sehr geehrte Damen und Herren";
	}

	/**
	 * @return void
	 */
	public function stopNewsletterSending($flag): void {
		$dbc = databaseConnector::instance();

		$activeNewsletter = $dbc->getSettingFromDB("activeNewsletter");
		$activeNewsletterID = $dbc->getSettingFromDB("activeNewsletterID");

		$content = $flag." Newsletter sending process from newsletter: ".$activeNewsletter. " with the ID: ". $activeNewsletterID;

		$dbc->saveSettingInDB( "sendingInProgress", "false" );
		$dbc->saveSettingInDB( "activeNewsletter", "" );
		$dbc->saveSettingInDB( "activeNewsletterID", "" );
		easyNewsletter::updateFarnCronService();

		farnLog::log("Stopped Newsletter Sending Process");

		$this->sendAdminMail($flag." newsletter sending process.", $content);
	}

	public function generateAttachments(array $attachmentMetaFieldContent): array{
		$outputArray = array();
		foreach ($attachmentMetaFieldContent as $attachment){
			$offset = strpos($attachment, "/uploads");
			unset($attachmentMetaFieldContent[$attachment]);
			$outputArray[] = WP_CONTENT_DIR . substr( $attachment, $offset );
		}
		return $outputArray;
	}
}