<?php

namespace easyNewsletter;

use metaBoxes\metaBoxes;
use WpOrg\Requests\Exception;

include "metaBoxes/metaBoxes.php";

/**
 * This class handles everything related to the newsletter Posttype
 */
class newsletterPostType {

	/**
	 * @var newsletterPostType The main and only object of this class.
	 */
	private static newsletterPostType $newsletter_post_type;

	/**
	 * Function that is used in the initiation phase of the plugin.
	 * It is required that this function is called at the beginning of the plugin to work correctly.
	 * @return void
	 */
	public function init(): void {
		add_action('init', array($this, 'createNewsletterPostType'));
		add_filter( 'manage_en_newsletters_posts_columns', array( $this, "addBackendNewsletterColumns" ) );
		add_action( 'manage_posts_custom_column',  array( $this,'addBackendNewsletterColumnsContent') );

		add_action( 'init', array($this,'registerNewsletterPostMeta') );
		add_action("admin_menu",array($this, "addMenuPage"));

		add_action( 'admin_print_scripts-post.php', array($this, 'enqueue_script'), 11 );
		add_action( 'admin_print_scripts-post-new.php', array($this, 'enqueue_script'), 11 );
		add_action( 'wp_ajax_en_sendNewsletterTestMail', array( $this,'en_sendNewsletterTestMail'));
		add_action( 'wp_ajax_nopriv_en_sendNewsletterTestMail', array( $this,'en_sendNewsletterTestMail'));

		add_action( 'wp_ajax_en_sendNewsletter', array( $this,'en_sendNewsletter'));
		add_action( 'wp_ajax_nopriv_en_sendNewsletter', array( $this,'en_sendNewsletter'));

		add_action( 'wp_ajax_en_wantToSendNewsletter', array( $this,'en_wantToSendNewsletter'));
		add_action( 'wp_ajax_nopriv_en_wantToSendNewsletter', array( $this,'en_wantToSendNewsletter'));

		add_filter( 'single_template', array( $this,'en_pageTemplate') );

		add_action("save_post_en_newsletters", array($this, "saveLastEditPost"), 10, 1);

		add_action('enqueue_block_editor_assets', array($this, 'remove_editor_styles_for_post_type'));

		metaBoxes::addAllMetaBoxes();
	}


	/**
	 * Returns the only active instance of that class.
	 * If no object is present a new one is created.
	 * @return newsletterPostType The main object of that class
	 */
	public static function instance(): newsletterPostType {
		if (!isset(self::$newsletter_post_type)){
			self::$newsletter_post_type = new newsletterPostType();
		}
		return self::$newsletter_post_type;
	}

	/**
	 * Created the newsletter posttype.
	 * @return void
	 */
	public function createNewsletterPostType(): void {
		register_post_type( 'en_newsletters',
			array(
				'labels'       => array(
					'name'          => __( 'Newsletter' ),
					'singular_name' => __( 'Newsletter' )
				),
				'public'       => true,
                'show_ui'      => true,
				'show_in_menu' => false,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'Newsletters/Newsletter' ),
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'author', 'excerpt', 'custom-fields' )
			)
		);
	}

	public function addMenuPage(){
		if (true){
			add_submenu_page(
				'easyNewsletter',
				__('All Newsletter', 'easynewsletter'), /*page title*/
				__('All Newsletter', 'easynewsletter'), /*menu title*/
				'manage_options', /*roles and capabiliyt needed*/
				"edit.php?post_type=en_newsletters",
				"",
				1
			);
		}
	}

	/**
	 * Adds new columns to the posttype newsletters.
	 * @param $columns array All columns as an array for the corresponding posttype.
	 *
	 * @return array all columns that are displayed in this posttype.
	 */
	public function addBackendNewsletterColumns( array $columns ): array {
		unset( $columns['author'] );

		//Combined the registrationFields from the registration.php and all other field (email / status) as a colum in backend
		return array_merge( $columns, array(
			'en_alreadySend' => __( 'Already Send', 'easynewsletter')));
	}

	/**
	 * Adds content to each colum per post in the newsletter PostType.
	 * @param $column string The column name where content needs to be added.
	 *
	 * @return void
	 */
	public function addBackendNewsletterColumnsContent( $column ): void {
		global $post;
		if ($column == 'en_alreadySend'){
			echo get_post_meta($post->ID, 'en_alreadySend', true);
		}
	}

	/**
	 * Creates default newsletter posts, as default mails.
	 *
	 * @return void
	 */
	public function createDefaultMails(): void {
		$this->createDefaultWelcomeMail('Welcome', '<!-- wp:paragraph --><p>welcome content</p><!-- /wp:paragraph -->', 'Welcome');
		$this->createDefaultWelcomeMail('Unsubscribed', '<!-- wp:paragraph --><p>Please click on this like to unsubscribe: <a href="https://{{unsubscribeURL}}">UNsubscribe Link</a></p><!-- /wp:paragraph -->', 'Unsubscribe');
		$this->createDefaultWelcomeMail('Activation', '<!-- wp:paragraph --><p>activation content: link: <a href="https://{{activationURL}}">Activation Link</a></p><!-- /wp:paragraph -->', 'Activation');
	}

	/**
	 * Creates a new newsletter post.
	 * @param String $postTitle The title of the new post.
	 * @param String $content The content of the new post.
	 * @param String $postName The name of the new post.
	 *
	 * @return void
	 */
	private function createDefaultWelcomeMail(String $postTitle, String $content, String $postName): void {
		$basePostTitle = $postTitle;
		$postTitle = "Default - " . $postTitle;

		$postID = post_exists($postTitle);
		if ( $postID == 0 || get_post_status($postID) != 'publish'){
			$wordpress_page = array(
				'post_title'    => $postTitle,
				'post_content'  => $content,
				'post_name' => $postName,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' => 'en_newsletters'
			);
			wp_insert_post( $wordpress_page );
		}
		$postID = post_exists($postTitle);
		switch ($basePostTitle){
			case "Welcome":
				add_post_meta( $postID, 'en_subject', "Welcome Mail", true);
				databaseConnector::instance()->saveSettingInDB("standardWelcomePost", $postID);
				break;
			case "Unsubscribed":
				add_post_meta( $postID, 'en_subject', "Unsubscribed Mail", true);
				databaseConnector::instance()->saveSettingInDB("standardUnsubscribePost", $postID);
				break;
			case "Activation":
				add_post_meta( $postID, 'en_subject', "Activation Mail", true);
				databaseConnector::instance()->saveSettingInDB("standardActivationPost", $postID);
				break;
		}
	}

	/**
	 * register postmeta-fields to store options, that are specific for single newsletters (en_newsletters)
	 *
	 * @return void
	 */
	public function registerNewsletterPostMeta(): void {
		/* subject */
		register_post_meta( 'en_newsletters', 'en_subject', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		] );
		/* Excerpt */
		register_post_meta( 'en_newsletters', 'en_excerpt', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		] );
		/* e-mail-address for test-mails */
		register_post_meta( 'en_newsletters', 'en_test_emailaddress', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		] );
		/* target group roles */
		register_post_meta( 'en_newsletters', 'en_target_group_roles', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		] );
		/* target group categories */
		register_post_meta( 'en_newsletters', 'en_target_group_categories', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
		] );
		/* custom HTML Injection -- Marvin*/
		register_post_meta( 'en_newsletters', 'en_custom_html_injection', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
			"default" => serialize(array())
		] );
		/* Newsletter attachments -- Marvin*/
		register_post_meta( 'en_newsletters', 'en_newsletter_attachments', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
			"default" => serialize(array())
		] );
		//PostType is registered as String but will be converted to an array when first used in CustomMetaFieldConditionBox
		//TODO Fix the registration to matching type Status = "active"
		register_post_meta( 'en_newsletters', 'en_newsletter_customConditions', [
			'show_in_rest' => true,
			'single' => true,
			'type' => 'string',
			"default" => "",
		] );
	}

	public static function convertContent($postID) : string{
		$enl_mailcontent_head = '<html>
		<head>
			<meta content="text/html;charset=UTF-8" http-equiv="Content-Type">
			<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
		  	<meta name="format-detection" content="telephone=no">
		  	<meta name="color-scheme" content="light">
			<meta name="supported-color-schemes" content="light">
		<style>
			'.file_get_contents( get_site_url()."/wp-includes/css/dist/block-library/style.min.css" ).'
			'.file_get_contents( __DIR__ . "/resources/newsletterBaseStyle.css" ).'
			'.databaseConnector::instance()->getSettingFromDB("newsletterCSS").'
		</style>
		</head>
		<body>
			<div style="display:none;" class="en_previewtext">'.get_post_meta($postID,"en_excerpt", true).'</div>
			<table class="enl-wrapper"><tr><td class="enl-inner">';

		$enl_mailcontent_foot = '</td></tr></table>
		</body>
		</html>';


		$content_post = get_post($postID);
		$content = $content_post->post_content;
		$enl_mailcontent_body = $content;

		/* Convert Content */

		$enl_mailcontent_body = apply_filters('the_content', $enl_mailcontent_body);

		include('resources/simple_html_dom.php');

		// Cover
		$enl_mailcontent_body = str_get_html($enl_mailcontent_body);
		foreach ($enl_mailcontent_body->find('div.wp-block-cover') as $element) {
		 // find Imagesource from img inside
		 $cover_imagesource = $element->find('img.wp-block-cover__image-background', 0)->src;
		 $elementstyle_converted = str_replace('min-height:', 'background-image:url('.$cover_imagesource.');min-height:inherit;height:', $element->style);
		 $element->outertext = '<div class="'.$element->class.'" style="'.$elementstyle_converted.'">'.$element->innertext.'</div>';
		}
		// Delete inner Cover Image img
		$enl_mailcontent_body = str_get_html($enl_mailcontent_body);
		foreach ($enl_mailcontent_body->find('img.wp-block-cover__image-background') as $element) {
		 $element->outertext = '';
		}
		// Columns to Table
		$enl_mailcontent_body = str_get_html($enl_mailcontent_body);
		foreach ($enl_mailcontent_body->find('div.wp-block-columns') as $element) {
		 if($element->hasClass('is-not-stacked-on-mobile')){
		 }else{
		 	$element->addClass('is-stacked-on-mobile');
		 }
		 $element->outertext = '<table class="'.$element->class.'"><tr>'.$element->innertext.'</tr></table>';
		}
		// Column to TD
		$enl_mailcontent_body = str_get_html($enl_mailcontent_body);
		foreach ($enl_mailcontent_body->find('div.wp-block-column') as $element) {
		 $elementstyle_converted = str_replace('flex-basis:', 'width:', $element->style);
		 $element->outertext = '<td class="'.$element->class.'" style="'.$elementstyle_converted.'">'.$element->innertext.'</td>';
		}
		/* switch figure to div */
		$enl_mailcontent_body = str_replace('<figure', '<div', $enl_mailcontent_body);
		$enl_mailcontent_body = str_replace('</figure>', '</div>', $enl_mailcontent_body);

		return $enl_mailcontent_head.$enl_mailcontent_body.$enl_mailcontent_foot;
	}

	function enqueue_script() {
		global $post_type;
		if( 'en_newsletters' == $post_type ) {
			wp_enqueue_script( 'newsletters-admin-edit-script', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/resources' . '/newslettersPostTypeAdmin.js' );
			wp_enqueue_style( 'newsletters-admin-edit-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/resources/newslettersPostTypeAdmin.css' );
			wp_enqueue_style( 'newsletters-admin-base-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/resources/newsletterBaseStyle.css' );
			echo'<style>'.databaseConnector::instance()->getSettingFromDB("newsletterCSS").'</style>';
		}
	}

	/* This removes the default editor styles css file defined in the wordpress theme */
	function remove_editor_styles_for_post_type() {
		global $post_type;
		if( 'en_newsletters' == $post_type ) {
	        remove_theme_support('editor-styles');
	    }
	}



	/**
	 * checks the number of subscribers and returns it to js
	 *
	 */
	function en_wantToSendNewsletter(){
		check_ajax_referer( 'secure_nonce_name', 'security' );

		// prevent XSS
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		echo count(mailManager::instance()->getAllNewsletterReceiverIDsAsArray($_POST["post_id"]));

		wp_die();
	}

	/**
	 * Sends Newsletter to all subscribers
	 *
	 */
	function en_sendNewsletter(){
		check_ajax_referer( 'secure_nonce_name', 'security' );

		// prevent XSS
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$dbc = databaseConnector::instance();
		$sendingInProgress = $dbc->getSettingFromDB("sendingInProgress");
		( $sendingInProgress == "true" ) ? $sendingInProgress = true : $sendingInProgress = false;

		if ($sendingInProgress){
			echo __("sendingInProgress","easynewsletter");
			wp_die();
		}

		$newsletterTitle = get_the_title($_POST["post_id"]);

		$dbc->saveSettingInDB("sendingInProgress", "true");
		$dbc->saveSettingInDB("activeNewsletter", $newsletterTitle);
		$dbc->saveSettingInDB("activeNewsletterID", $_POST["post_id"]);
		$dbc->saveSettingInDB("lastSendNewsletterID", $_POST["post_id"]);

		farnLog::log("Started Newsletter sending process.");
		farnLog::log("Current Newsletter: ". $newsletterTitle.", ID: ".$_POST["post_id"]);

		easyNewsletter::updateFarnCronService();
		wp_die();
	}

	/**
	 * Sends test mail to test mail subscriber specified in newsletter post type meta field 
	 *
	 */
	function en_sendNewsletterTestMail(){
		check_ajax_referer( 'secure_nonce_name', 'security' );

		// prevent XSS
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$subject = get_post_meta($_POST["post_id"],"en_subject", true);
		$content = self::convertContent($_POST["post_id"]);
		$content = mailManager::instance()->htmlInjection($content,get_current_user_id(), $_POST["post_id"]);
		$receiver = get_post_meta($_POST["post_id"],"en_test_emailaddress", true);
		$attachments = mailManager::instance()->generateAttachments(unserialize(get_post_meta($_POST["post_id"], "en_newsletter_attachments", true)));
		mailManager::instance()->sendMail($receiver, $subject, $content, $_POST["post_id"], $attachments);

		echo __("Receiver:", "easynewsletter")." ".$receiver;

		wp_die();
	}


	/**
	 * defines the frontend page template view for newsletters, containing the real html that we send via mail
	 *
	 */
	function en_pageTemplate($template) {

		global $post;
		
		if ( 'en_newsletters' === $post->post_type && locate_template( array( 'newsletterPageTemplate.php' ) ) !== $template ) {
			return plugin_dir_path( __FILE__ ) . 'newsletterPageTemplate.php';
		}
		return $template;
	
	}

	public function saveLastEditPost($post_id){
		databaseConnector::instance()->saveSettingInDB("lastEditedNewsletterID", $post_id);
	}



}