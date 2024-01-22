<?php

namespace EasyNewsletter\registration;

use easyNewsletter\databaseConnector;
use EasyNewsletter\farnLog;
use easyNewsletter\mailManager;
use Error;
use Exception;
use WP_Query;

/**
 * This class handles everything related to the registration precess of a subscriber.
 */
class registration {

	private string $registrationPageName = 'easyNewsletterForm';
	private string $registrationPageTitle = 'Easy Newsletter Form. Do not delete this!';

	private string $registrationSuccessPageName = 'easyNewsletterRegistrationSuccess';
	public string $registrationSuccessPageTitle = "Easy Newsletter Registration Success Page. Do not delete this!";

	private string $confirmationSuccessPageName = 'easyNewsletterFormConfirmationSuccess';
	public string $confirmationSuccessPageTitle = "Easy Newsletter Confirmation Success Page. Do not delete this!";

	private string $confirmationDeniedPageName = 'easyNewsletterFormConfirmationDenied';
	public string $confirmationDeniedPageTitle = "Easy Newsletter Confirmation Denied Page. Do not delete this!";

	private string $unsubscribedPageName = 'easyNewsletterFormUnsubscribed';
	public string $unsubscribedPageTitle = "Easy Newsletter Unsubscribed Page. Do not delete this!";

	private string $unsubscribedConfirmedPageName = 'easyNewsletterFormUnsubscribedConfirmed';
	public string $unsubscribedConfirmedPageTitle = "Easy Newsletter Unsubscribed Confirmed Page. Do not delete this!";

	private string $registrationFormPageName = 'easyNewsletterRegistrationForm';
	public string $registrationFormPageTitle = "Easy Newsletter Registration Form Page. Do not delete this!";

	private string $unsubscribeFormPageName = 'easyNewsletterUnsubscribeForm';
	public string $unsubscribeFormPageTitle = "Easy Newsletter Unsubscribe Form Page. Do not delete this!";

	/**
	 * @var $registration registration main and only object of this class.
	 */
	private static registration $registration;

	/**
	 * Function that is used in the initiation phase of the plugin.
	 * It is required that this function is called at the beginning of the plugin to work correctly.
	 *
	 * @return void
	 */
	public function init(): void {
		add_shortcode('easyNewsletter', array($this, 'registrationFormFilter'));
		add_shortcode('easyNewsletterUnsubscribeForm', array($this, 'easyNewsletterUnsubscribeForm'));
	}

	/**
	 * Returns the only active instance of that class.
	 * If no object is present a new one is created.
	 *
	 * @return registration The main object of that class
	 */
	public static function instance(): registration {
		if (!isset(self::$registration)){
			self::$registration = new registration();
		}
		return self::$registration;
	}

	/**
	 * Creates the registration Page on plugin creation.
	 *
	 * @return void
	 */
	public function setupRegistrationForm(): void {

		$registrationPostAlreadyExists = false;
		$confirmationSuccessPostAlreadyExists = false;
		$confirmationDeniedPostAlreadyExists = false;
		$unsubscribedPostAlreadyExists = false;
		$unsubscribedConfirmedPostAlreadyExists = false;
		$registrationSuccessPostAlreadyExists = false;
		$registrationFormPostAlreadyExists = false;
		$unsubscribeFormPostAlreadyExists = false;

		$query = new WP_Query(array( "post_type" => "page", "posts_per_page" => "-1" ));

		while ($query->have_posts()){
			$query->the_post();

			$dbc = databaseConnector::instance();

			if ( $dbc->getSettingFromDB("registrationPageID") == get_the_ID() && get_post_status(get_the_ID()) == 'publish'){
				$registrationPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("confirmationSuccessPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$confirmationSuccessPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("confirmationDeniedPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$confirmationDeniedPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("unsubscribedPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$unsubscribedPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("unsubscribedConfirmedPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$unsubscribedConfirmedPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("registrationSuccessPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$registrationSuccessPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("registrationFormPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$registrationFormPostAlreadyExists = true;
			}
			if ($dbc->getSettingFromDB("unsubscribeFormPageID") == get_the_ID()  && get_post_status(get_the_ID()) == 'publish'){
				$unsubscribeFormPostAlreadyExists = true;
			}
		}

		if ( !$registrationPostAlreadyExists){
			$wordpress_page = array(
				'post_title'    => $this->registrationPageTitle,
				'post_content'  => '<!-- wp:shortcode -->[easyNewsletter]<!-- /wp:shortcode -->',
				'post_name' => $this->registrationPageName,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' => 'page'
			);
			wp_insert_post( $wordpress_page );
			databaseConnector::instance()->saveSettingInDB("registrationPageID",post_exists($this->registrationPageTitle));
		}
		if (!$confirmationSuccessPostAlreadyExists){
			$this->setupSubSite($this->confirmationSuccessPageName, $this->confirmationSuccessPageTitle, '<!-- wp:heading {"level":1} --><h1>Confirmation Page Success</h1><!-- /wp:heading -->');
			databaseConnector::instance()->saveSettingInDB("confirmationSuccessPageID",post_exists($this->confirmationSuccessPageTitle));
		}
		if (!$confirmationDeniedPostAlreadyExists){
			$this->setupSubSite($this->confirmationDeniedPageName, $this->confirmationDeniedPageTitle, '<!-- wp:heading {"level":1} --><h1>Confirmation Page Failure</h1><!-- /wp:heading -->');
			databaseConnector::instance()->saveSettingInDB("confirmationDeniedPageID",post_exists($this->confirmationDeniedPageTitle));
		}
		if (!$unsubscribedPostAlreadyExists){
			$this->setupSubSite($this->unsubscribedPageName, $this->unsubscribedPageTitle, '<!-- wp:heading {"level":1} --><h1>Unsubscribed Page</h1>');
			databaseConnector::instance()->saveSettingInDB("unsubscribedPageID",post_exists($this->unsubscribedPageTitle));
		}
		if (!$unsubscribedConfirmedPostAlreadyExists){
			$this->setupSubSite($this->unsubscribedConfirmedPageName, $this->unsubscribedConfirmedPageTitle, '<!-- wp:heading {"level":1} --><h1>Unsubscribed Page</h1>');
			databaseConnector::instance()->saveSettingInDB("unsubscribedConfirmedPageID",post_exists($this->unsubscribedConfirmedPageTitle));
		}
		if (!$registrationSuccessPostAlreadyExists){
			$this->setupSubSite($this->registrationSuccessPageName, $this->registrationSuccessPageTitle, '<!-- wp:heading {"level":1} --><h1>Registration Success.</h1><!-- /wp:heading --><!-- wp:paragraph --><p>Please confirm your Email.</p><!-- /wp:paragraph -->');
			databaseConnector::instance()->saveSettingInDB("registrationSuccessPageID",post_exists($this->registrationSuccessPageTitle));
		}
		if (!$registrationFormPostAlreadyExists){
			$this->setupSubSite($this->registrationFormPageName, $this->registrationFormPageTitle, '<!-- wp:paragraph --><p>Text above Form</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>{{registrationForm}}</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Text underneath Form</p><!-- /wp:paragraph -->');
			databaseConnector::instance()->saveSettingInDB("registrationFormPageID",post_exists($this->registrationFormPageTitle));
		}
		if (!$unsubscribeFormPostAlreadyExists){
			$wordpress_page = array(
				'post_title'    => $this->unsubscribeFormPageTitle,
				'post_content'  => '<!-- wp:shortcode -->[easyNewsletterUnsubscribeForm]<!-- /wp:shortcode -->',
				'post_name' => $this->unsubscribeFormPageName,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' => 'page'
			);
			wp_insert_post( $wordpress_page );
			databaseConnector::instance()->saveSettingInDB("unsubscribeFormPageID",post_exists($this->unsubscribeFormPageTitle));
		}
	}

	public function setupSubSite($pageName, $pageTitle, $content): void{
		$parentID = post_exists($this->registrationPageTitle);
		$wordpress_page = array(
			'post_title'    => $pageTitle,
			'post_content'  => $content,
			'post_name' => $pageName,
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' => 'page',
			'post_parent' => $parentID
		);
		wp_insert_post( $wordpress_page );
	}

	/**
	 * Handles the different contents on the registration page, depending on the URL parameters.
	 *
	 * @return string|bool
	 */
	public function registrationFormFilter(): string|bool {
		ob_start();
		if (empty($_GET) || !isset($_GET['confirmed']) && !isset($_GET['unsubscribed']) && !isset($_GET["submitUnsubscribed"])){
			include 'registrationForm.php';
		}
		else if (isset($_GET['confirmed']) && isset($_GET['email']) && isset($_GET['token'])){
			include 'registrationConfirmed.php';
		}
		else if (isset($_GET['unsubscribed'])&& isset($_GET['email']) && isset($_GET['token'])){
			include 'registrationUnsubscribed.php';
		}
		else if (isset($_GET["submitUnsubscribed"])){
			include "registrationUnsubscribedConfirmed.php";
		}
		return ob_get_clean();
	}

	public function easyNewsletterUnsubscribeForm(): string|bool {
		ob_start();
		$email = sanitize_email($_GET["email"]);
		if (isset($_GET["submit"]) && isset($email)){
			try {
				mailManager::instance()->sendUnsubscribeMail($email);
				echo "<div class='en_unsubscribeFromSuccessMessageContainer'><p>Unsubscribe e-mail send to: ".$email."</p></div>";
			} catch ( Error | Exception $e){
				echo "<div class='en_unsubscribeFromErrorMessageContainer'><p>The e-mail was not found in out mailing list!</p></div>";
				farnLog::log("Error: ".$e->getMessage());
			}
		} else{
			echo "<form class='en_unsubscribeForm'><input type='text' name='email'><input type='submit' name='submit'></form>";
		}
		return ob_get_clean();
	}
}