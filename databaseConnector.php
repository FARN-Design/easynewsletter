<?php

namespace easyNewsletter;

use mysql_xdevapi\Exception;
use stdClass;
use wpdb;

/**
 * This is the DatabaseConnector class wich controls the interaction with the Database.
 */
class databaseConnector {

	private static databaseConnector $database_connector;

	private wpdb $wpdb;
	private string $tableName;
	private string $tableNameSuffix = "easyNewsletter";

	/**
	 * Use this Array to define Settings (and its default value) in the Database.
	 * If a Setting in removed here it will not be removed inside of the database table.
	 * If a Setting is added it will be automatically added to the database table.
	 *
	 * @var array|string[] Setting => SettingValue
	 */
	var array $defaultSettings = [
		'senderEmailAddress' => 'newsletter@sender.de',
		'senderName' => 'Sender Name',
		'replyTo' => 'reply@to.de',
		'maxEmailPerInterval' => '60',
		'intervalInSeconds' => '300',
		'signupFormFields' => '', // will be set as a serialized array('en_eMailAddress') in the constructor.
		'activeNewsletter' => '',
		'activeNewsletterID' => '',
		'subscriberMode' => 'default',
		'sendingInProgress' => 'false',
		'standardActivationPost' => '',
		'standardUnsubscribePost' => '',
		'standardWelcomePost' => '',
		'subscriberCategory' => '', // will be set as a serialized array('default') in the constructor.
		'subscriberRole' => '', // will be set as a serialized array('default') in the constructor.
		'newsletterCSS' => '',
		'addedUserRoleKey' => 'en_user_subscriber',
		"registrationPageID" => "",
		"registrationSuccessPageID" => "",
		"confirmationSuccessPageID" => "",
		"confirmationDeniedPageID" => "",
		"unsubscribedPageID" => "",
		"unsubscribedConfirmedPageID" => "",
		"registrationFormPageID" => "",
		"unsubscribeFormPageID" => "",
		"lastSendNewsletterID" => "",
		"lastEditedNewsletterID" => "",
	];


	function __construct() {

		global $wpdb;
		$this->wpdb      = $wpdb;
		$this->tableName = $this->wpdb->prefix . $this->tableNameSuffix;

		if (!isset(self::$database_connector)){
			$this->initDatabase();
		}

		if ($this->getSettingFromDB("signupFormFields") == ""){
			$this->saveSettingInDB("signupFormFields",serialize(array('en_eMailAddress')));
		}
		if ($this->getSettingFromDB("subscriberCategory") == ""){
			$this->saveSettingInDB("subscriberCategory",serialize(array('default')));
		}
		if ($this->getSettingFromDB("subscriberRole") == ""){
			$this->saveSettingInDB("subscriberRole",serialize(array('default')));
		}
		if ($this->getSettingFromDB("signupFormFields") == ""){
			$this->saveSettingInDB("signupFormFields",serialize(array()));
		}


	}

	//Singleton Implementation
	public static function instance(): databaseConnector {
		if (!isset(self::$database_connector)){
			self::$database_connector = new databaseConnector();
		}
		return self::$database_connector;
	}

	/**
	 * Creates the Database with the predefined Settings Array. @see $defaultSettings
	 *
	 * @return void
	 */
	private function createDatabaseTable(): void {
		// Database tables
		$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS ". $this->tableName . " (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
	    	Setting varchar(255) NOT NULL UNIQUE,
	    	Value text NOT NULL,
	    	PRIMARY KEY (id)
	  	) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Initializes the Database. Creates the database table when its not present.
	 * Also updates new Settings inside the table corresponding to the Settings Array. @see $defaultSettings
	 *
	 * @return void
	 */
	public function initDatabase(): void {

		$this->createDatabaseTable();

		$results = $this->wpdb->get_results('SELECT Setting FROM ' .$this->tableName, ARRAY_N);
		$results = array_column($results,0);

		foreach ($this->defaultSettings as $key => $value) {
			if(!in_array($key, $results)){
				$this->wpdb->insert($this->tableName,
					array(
						'Setting' => $key,
						'Value' => $value ));
			}
		}
	}

	/**
	 * This function gets a setting value from the database.
	 *
	 * @param string $setting Setting as String.
	 *
	 * @return mixed
	 */
	public function getSettingFromDB( string $setting): mixed {

		$sql = "SELECT Value FROM ".$this->tableName . " WHERE Setting = \"" . $setting . "\";";
		$result = $this->wpdb->get_results($sql, ARRAY_N);


		if (sizeof($result) == 0){
			wp_send_json_error("Database Setting not Found in Database: ".$setting);
		}

		return $result[0][0];
	}

	/**
	 * A function to get all settings stored in the database.
	 * To get a specific Value follow that scheme: $settingsMap->'Setting'
	 *
	 * @return stdClass return all Settings as a stdClass.
	 */
	public function getAllSettings(): stdClass {
		$sql = "SELECT * FROM ".$this->tableName;

		$temp = $this->wpdb->get_results($sql, ARRAY_N);

		$settingsMap = new stdClass();

		foreach ($temp as $setting){
			$settingsMap->{$setting[1]} = $setting[2];
		}

		return $settingsMap;
	}

	/**
	 * Stores a new value for a given setting in the database.
	 *
	 * @param string $setting the setting (same name as in the DB) from which the value should change.
	 * @param string $settingsValue the new value of the setting.
	 *
	 * @return void
	 */
	public function saveSettingInDB( string $setting, string $settingsValue): void {
		$sql = "UPDATE ".$this->tableName . " SET Value = '" . $settingsValue . "' WHERE Setting = '" . $setting . "';";
		$this->wpdb->query($sql);
	}
}