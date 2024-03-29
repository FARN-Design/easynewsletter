<?php

namespace EasyNewsletter;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WP_Post;
use WP_Query;

/**
 * This class handles everything related to the subscribers Posttype.
 */
class subscriberPostType {

	/**
	 * @var subscriberPostType The main and only object of this class.
	 */
	private static subscriberPostType $subscriber_post_type;

	//Fields that can be used in the registration process and the corresponding display Name (Translation)
	//The array key from this array are also the meta-value Keys for the subscriber Posts
	// <registrationField> => <Display Name>
	public static array $metaFieldsOptional;

	//Used for internal workflows. DO NOT Remove or Rename those entries!!!
	public static array $metaFieldsRequired;

	/**
	 * Function that is used in the initiation phase of the plugin.
	 * It is required that this function is called at the beginning of the plugin to work correctly.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action('init', array($this, 'createSubscriberPostType'));

		add_filter( 'manage_en_subscribers_posts_columns', array( $this, "addBackendSubscriberColumns" ) );
		add_action( 'manage_posts_custom_column',  array( $this,'addBackendSubscriberColumnsContent') );
		add_action( 'transition_post_status', array($this, 'denyPrivateStatus'),10,3);

		//add_action( 'admin_footer', array( $this,'jsAjaxVariables' ));

		add_action( 'admin_enqueue_scripts', array( $this,'enqueueSubscriberScriptsAndStyles') );
		add_action( 'wp_ajax_saveBackendSubscriberCustomContent', array( $this,'saveBackendSubscriberCustomContent'));
		add_action( 'wp_ajax_nopriv_saveBackendSubscriberCustomContent', array( $this,'saveBackendSubscriberCustomContent'));

		add_action( 'wp_ajax_addBackendSubscriber', array( $this,'addBackendSubscriber'));
		add_action( 'wp_ajax_nopriv_addBackendSubscriber', array( $this,'addBackendSubscriber'));


		self::$metaFieldsOptional = array(
	        'en_salutation' => __('Salutation','easynewsletter'),
	        'en_gender' => __('Gender','easynewsletter'),
	        'en_firstName' => __('First Name', 'easynewsletter'),
	        'en_lastName' => __('Last Name','easynewsletter'),
	        'en_telephoneNumber' => __('Telephone Number', 'easynewsletter'));

        self::$metaFieldsRequired = array(
	        'en_eMailAddress' => __('E-Mail','easynewsletter'),
	        'en_status' => __('Status', 'easynewsletter'),
	        'en_doubleOptIn' => __('Double Opt In','easynewsletter'),
	        'en_token' => __('Token','easynewsletter'),
	        'en_lastReceived' => __('Last Newsletter','easynewsletter'),
	        'en_allReceived' => __('All Received Newsletter','easynewsletter'),
	        'en_subscriberCategory' => __('Subscriber Category','easynewsletter'),
	        'en_subscriberRole' => __('Subscriber Role','easynewsletter'));
	}

	/**
	 * Returns the only active instance of that class.
	 * If no object is present a new one is created.
	 *
	 * @return subscriberPostType The main object of that class
	 */
	public static function instance(): subscriberPostType {
		if (!isset(self::$subscriber_post_type)){
			self::$subscriber_post_type = new subscriberPostType();
		}
		return self::$subscriber_post_type;
	}

	/**
	 * Creates the subscribers posttype.
	 *
	 * @return void
	 */
	public function createSubscriberPostType(): void {

		register_post_type( 'en_subscribers',
			array(
				'labels'       => array(
					'name'          => __( 'Subscribers', 'easynewsletter'),
					'singular_name' => __( 'Subscriber', 'easynewsletter')
				),
				'public'            => false,
				'show_ui'           => true,
                'show_in_nav_menus' => true,
                'show_in_menu'      => false,
				'can_export'        => true,
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => 'Subscribers/Subscriber' ),
				'show_in_rest'      => true,
				'supports'          => array( 'title', 'editor', 'author', 'excerpt', 'custom-fields' )
			)
		);
	}

	/**
	 * Enqueues scripts and styles for the edit-columns-function
	 *
	 * @return void
	 */
	public function enqueueSubscriberScriptsAndStyles(): void {
		global $post_type;
		if( 'en_subscribers' == $post_type ){
			wp_enqueue_script( 'editSubscriberColumns-script', plugins_url("easynewsletter//EasyNewsletter/resources/editSubscriberColumns.js"));
			wp_enqueue_style( 'editSubscriberColumns-style', plugins_url("easynewsletter/EasyNewsletter/resources/editSubscriberColumns.css"));

			wp_localize_script(
				'editSubscriberColumns-script',
				'en_ajax_obj',
				[
					'url' => admin_url('admin-ajax.php'),
					"nonce" => wp_create_nonce( "secure_nonce_name" )
				]
			);
		}
	}

	/**
	 * Adds new columns to the posttype subscribers.
	 * @param $columns array All columns as an array for the corresponding posttype.
	 *
	 * @return array all columns that are displayed in this posttype.
	 */
	public function addBackendSubscriberColumns( array $columns ): array {
		unset( $columns['author'] );

		//Combined the registrationFields from the registration.php and all other field (email / status) as a colum in backend
		return array_merge( $columns, self::$metaFieldsOptional, self::$metaFieldsRequired);
	}

	/**
	 * Adds content to each colum per post in the subscriber PostType.
	 * @param $columnName string The column name where content needs to be added.
	 *
	 * @return void
	 */
	public function addBackendSubscriberColumnsContent( string $columnName ): void {
		global $post;
		$metaFields = array_merge(self::$metaFieldsRequired, self::$metaFieldsOptional);
		// Handle only the en_allReceived field

        if (isset($metaFields[$columnName])){
            switch ($columnName){
                case 'en_allReceived':
                    $allReceived = unserialize(get_post_meta($post->ID, $columnName, true));
		            if (is_array($allReceived)){
			            if (empty($allReceived)){
				            echo "<div><p>No received Newsletter</p></div>";
                            break;
			            }
			            echo "<div>".esc_html (implode( ',', $allReceived ))."</div>";
                        break;
		            }
		            echo "<div><p style='color:red;'>Serialized Array required!</p></div>";
                    break;
                case "en_subscriberCategory":
	                if (!is_array(get_post_meta($post->ID, 'en_subscriberCategory', true))){
		                echo "<div><p style='color:red;'>Serialized Array provided, Array required!</p></div>";
	                } else{
		                echo "<div>".esc_html(serialize(get_post_meta($post->ID, $columnName, true)))."</div>";
	                }
                    break;
                case 'en_lastReceived':
                case 'en_token':
                case 'en_doubleOptIn':
                    echo "<div>".esc_attr(get_post_meta($post->ID, $columnName, true))."</div>";
                    break;
                default:
	                $columnValue = get_post_meta($post->ID, $columnName, true);
	                // create edit_input to make it possible to edit the columnValue
	                $columnContent = "<div class='edit_input'><input type='text' name='".$columnName."' value='".$columnValue."'></div>";
	                // create column_content to display the current columnValue
	                $columnContent .= "<div class='column_content' data-field-name='".$columnName."'>".$columnValue."</div>";
	                // create edit/save/exit-buttons to interact with the edit_input
	                $columnContent .= "<div class='edit_button dashicons-before dashicons-edit' title='bearbeiten'></div>";
	                $columnContent .= "<div class='save_button dashicons-before dashicons-saved' title='speichern'></div>";
	                $columnContent .= "<div class='exit_button dashicons-before dashicons-no-alt' title='abbrechen'></div>";
	                echo $columnContent;
            }
        }
	}

	/**
	 * Saves all values from javascript (via ajax) to the database
	 *
     */
	#[NoReturn] function saveBackendSubscriberCustomContent(): void {
	  check_ajax_referer( 'secure_nonce_name', 'security' );

	  // prevent XSS
	  $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
	  $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

	  // get values from post
	  $content = sanitize_text_field($_POST['content']);
	  $post_id = sanitize_key($_POST['post_id']);
	  $field_name = sanitize_text_field($_POST['field_name']);
	  // save value to post_meta
	  update_post_meta($post_id,$field_name,$content);

	  wp_die();
	}

	/**
	 * Generates a doubleOptIn Token for the user. Used on multiple occasions when the user need to be identified.
	 * @param string $eMail the email from the subscriber.
	 *
	 * @return string the generated token.
	 */
	public function generateDoubleOptInToken(string $eMail): string {
		$returnValue = "";
		try {
			$randomInt = random_int( 0, 2023 );
			$returnValue = hash("md5",$eMail.$randomInt);
		} catch ( Exception $e ) {
			farnLog::log("Internal Error while generating token: " .$e);
			wp_send_json_error("Internal Error while generating token");
		}
		return $returnValue;
	}

	/**
	 * Used to deny that the subscriber post get the private status.
	 * @param string $new_status new post state.
	 * @param string $old_status old post stat.
	 * @param WP_Post $post post object.
	 *
	 * @return WP_Post The new Post object.
	 */
	public function denyPrivateStatus(string $new_status, string $old_status, WP_Post $post): WP_Post {
		if ($new_status == 'private' && $post->post_type == 'en_subscribers'){
			$post->post_status = 'publish';
			wp_update_post($post);
		}
		return $post;
	}


	/**
	 * @throws Exception
	 */
	public function addBackendSubscriber(): void {
		check_ajax_referer( 'secure_nonce_name', 'security' );

		// prevent XSS
		$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$email = sanitize_email($_POST["email"]);
		if (!empty($email)) {
			echo 'Username or email missing!';
		}

		$query = new WP_Query(array( "post_type" => "en_subscribers", "posts_per_page" => "-1" ));
		while ($query->have_posts()){
			$query->the_post();
			if (get_post_meta( get_the_ID(),"en_eMailAddress", true) == $email){
				throw new Exception("This email is already registered!");
			}
		}


		$id = wp_insert_post(array(
			"post_type" => "en_subscribers",
			"post_title" => $email,
			"post_status" => "publish",
		));

		update_post_meta($id, "en_eMailAddress", $email);
		subscriberHandler::instance()->fillRequireMetaFieldsWithDefaults($id);

		wp_die();
	}
}