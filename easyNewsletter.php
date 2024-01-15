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

namespace easyNewsletter;

//-----------------------------Requirements-----------------------------

require_once ('farnLog.php');
require_once ('databaseConnector.php');
require_once ('mailManager.php');
require_once ('menuPage.php');
require_once( 'registration/registration.php' );
require_once ('subscriberPostType.php');
require_once ('newsletterPostType.php');
require_once ('metaDataWrapper.php');
require_once ('subscriberHandler.php');

//-----------------------------Plugin Code-----------------------------

if (!defined('ABSPATH')){
	die('Nothing to see here!');
}

//Creates a new Object of the main class to initiate the plugin.
new easyNewsletter();

//TODO Checks for Updates Method
//wp_update_plugins();

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

	public static function updateFarnCronService(){
        //Not Available
        return false;
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
			'/wp-content/plugins/'.basename(dirname(__FILE__)).'/resources/jsx/index.js',
			[ 'wp-edit-post' ],
			false,
			false
		);
	}

	/**
	 * Defines ajax_url and ajax_nonce (for security reasons) for javascript
	 *
	 */
	function jsAjaxVariables(){ ?>
		<script type="text/javascript">
            var ajax_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            var ajax_nonce = '<?php echo wp_create_nonce( "secure_nonce_name" ); ?>';
		</script>
		<?php
	}

	function my_plugin_init() {
		load_plugin_textdomain( 'easynewsletter', false, basename(dirname(__FILE__)).'/resources/language' );
	}

}


