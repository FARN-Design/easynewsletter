<?php

/*
Plugin Name: Easy Newsletter
Plugin URI: https://www.easy-wordpress-plugins.de/
Description: Provides a Newsletter functionality with complete WordPress integration.
Version: 1.0.0
Author: Farn - Digital Brand Design
Author URI: https://www.farn.de/
Text Domain: easynewsletter
Domain Path: resources/language
License: GNU GENERAL PUBLIC LICENSE Version 3
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
			'/wp-content/plugins/'.basename(dirname(__FILE__)).'/EasyNewsletter/resources/jsx/index.js',
			[ 'wp-edit-post' ],
		);
	}

	/**
	 * Defines ajax_url and ajax_nonce (for security reasons) for javascript
	 *
	 */
	function jsAjaxVariables(): void {

        wp_add_inline_script("ajax-variables", "
            const ajax_url = ".admin_url( "admin-ajax.php" ).";
            const ajax_nonce = ".wp_create_nonce( "secure_nonce_name" ).";", "before")
        ?>
		<script type="text/javascript">
            const ajax_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            const ajax_nonce = '<?php echo wp_create_nonce( "secure_nonce_name" ); ?>';
        </script>
		<?php
	}

	function my_plugin_init(): void {
		load_plugin_textdomain( 'easynewsletter', false, plugins_url("EasyNewsletter/resources/language", __FILE__));
	}

}


