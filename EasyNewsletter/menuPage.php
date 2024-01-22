<?php

namespace EasyNewsletter;

/**
 * This class handles everything related to the menu settings page.
 */
class menuPage {

	/**
	 * @var menuPage The only object of that class.
	 */
    private static menuPage $menuPage;

	/**
	 * Function that is used in the initiation phase of the plugin.
	 * It is required that this function is called at the beginning of the plugin to work correctly.
	 * @return void
	 */
    public function init(): void {
	    add_action('admin_menu', array($this, 'adminMenu'));
	    add_action('admin_menu', array($this, 'usersMenu'));
	    add_action('admin_menu', array($this, 'settingsMenu'));

	    add_action('admin_enqueue_scripts', array($this, "addScriptsAndStylesToMenuPages"));
    }

	/**
	 * Returns the only active instance of that class.
	 * If no object is present a new one is created.
	 * @return menuPage The main object of that class
	 */
	public static function instance(): menuPage {
		if (!isset(self::$menuPage)){
			self::$menuPage = new menuPage();
		}
		return self::$menuPage;
	}

	/**
	 * Creates the entry for the admin menu.
	 *
	 * @return void
	 */
	public function adminMenu(): void {
		add_menu_page(
			'Easy Newsletter',
			'Easy Newsletter',
			'manage_options',
			'easyNewsletter',
			[$this, "menuPageContent"]);
	}

	/**
	 * Creates the all users entry for the admin menu.
	 *
	 * @return void
	 */
	public function usersMenu(): void {
		$slug = "edit.php?post_type=en_subscribers";
		if (databaseConnector::instance()->getSettingFromDB("subscriberMode") == "user"){
			$slug = "users.php";
		}
		add_submenu_page(
			"easyNewsletter",
			__('All Subscribers', 'easynewsletter'),
			__('All Subscribers', 'easynewsletter'),
			'manage_options',
			$slug,
			'', 70);
	}

	/**
	 * Creates the settings entry for the admin menu.
	 *
	 * @return void
	 */
	public function settingsMenu(): void {
		add_submenu_page(
			"easyNewsletter",
			__('Settings', 'easynewsletter'),
			__('Settings', 'easynewsletter'),
			'manage_options',
			'easyNewsletterSettings',
			array($this, 'settingsPageContent'), 70);
	}


	/**
	 * Used to fill the content from the settingsPageContent.php to the content page.
	 *
	 * @return void
	 */
	public function settingsPageContent(): void{
		include ('settingsPageContent.php');
	}

	public function menuPageContent(): void{
		include ('menuPageContent.php');
	}

	function addScriptsAndStylesToMenuPages(): void {
		$current_screen = get_current_screen();

		if (strpos( $current_screen->base, 'easyNewsletter' )){
			wp_enqueue_style("en_overviewCSS", plugins_url('resources/overviewPage.css', __FILE__ ));
		}
		// styles for menu icon
		wp_enqueue_style("en_menuIconCSS", plugins_url('resources/menuIcon.css', __FILE__ ));
	}
}
