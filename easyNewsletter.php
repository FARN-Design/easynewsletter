<?php

/*
Plugin Name: Easy Newsletter
Plugin URI: https://www.easy-wordpress-plugins.de/
Description: Provides a Newsletter functionality with complete WordPress integration.
Version: 1.0.1
Author: FARN – digital brand design
Author URI: https://www.farn.de/
Text Domain: easynewsletter
Domain Path: resources/language
License: GPLv3 or later
*/

//-----------------------------Requirements-----------------------------

use EasyNewsletter\databaseConnector;
use EasyNewsletter\mailManager;
use EasyNewsletter\menuPage;
use EasyNewsletter\metaDataWrapper;
use EasyNewsletter\newsletterPostType;
use EasyNewsletter\registration\registration;
use EasyNewsletter\subscriberPostType;

require_once ('EasyNewsletter/farnLog.php');
require_once ('EasyNewsletter/databaseConnector.php');
require_once ('EasyNewsletter/mailManager.php');
require_once ('EasyNewsletter/menuPage.php');
require_once( 'EasyNewsletter/registration/registration.php' );
require_once ('EasyNewsletter/subscriberPostType.php');
require_once ('EasyNewsletter/newsletterPostType.php');
require_once ('EasyNewsletter/metaDataWrapper.php');
require_once ('EasyNewsletter/subscriberHandler.php');

//-----------------------------Plugin Code-----------------------------

if (!defined('ABSPATH')){
	die('Nothing to see here!');
}

//Creates a new Object of the main class to initiate the plugin.
new easyNewsletter();

//-----------------------------Main Class-----------------------------
class easyNewsletter{

	/**
	 * This function is called, whenever a new object of the main plugin class is created.
	 * It resembles the plugin initiation function.
	 */
	public function __construct() {

		register_activation_hook(__FILE__, array(self::class, 'activation'));
		register_deactivation_hook(__FILE__, array(self::class, 'deactivation'));

		databaseConnector::instance()->initDatabase();
		mailManager::instance()->init();
		menuPage::instance()->init();
		registration::instance()->init();
		subscriberPostType::instance()->init();
		newsletterPostType::instance()->init();

        metaDataWrapper::init();

		add_action( 'admin_footer', array( $this,'jsAjaxVariables' ));
		add_action( 'enqueue_block_editor_assets', array($this,'enqueueJsxAssets'));

		add_action('init', array($this,'my_plugin_init'));
	}

	/**
	 * This function is executed on the plugin activation.
	 *
	 * @return void
	 */
	public static function activation(): void {
		registration::instance()->setupRegistrationForm();
		newsletterPostType::instance()->createDefaultMails();
		self::addFarnCronService();
	}

	/**
	 * This function is executed on the plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivation(): void {
		mailManager::instance()->deactivate();
	}

	/**
	 * Registers the Domain where this plugin is installed to the farn cron services.
	 *
	 * @return void
	 */
	public static function addFarnCronService(): void {
		//Not Available
	}

	/**
	 * enqueues the main react/jsx-file to handle everything jsx/react related:
	 * - the postmeta-fields for the sidebar of en_newsletters
	 * - custom blocks
	 *
	 * @return void
	 */
	function enqueueJsxAssets(): void {
		wp_enqueue_script(
			'easy-newsletter-jsx-script',
			plugins_url("/EasyNewsletter/resources/jsx/index.js", __FILE__),
			[ 'wp-edit-post' ],
		);
	}

	function my_plugin_init(): void {
		load_plugin_textdomain( 'easynewsletter', false, plugins_url("EasyNewsletter/resources/language", __FILE__));
	}

}


