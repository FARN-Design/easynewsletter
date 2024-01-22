<?php

namespace EasyNewsletter;

use WP_Query;

class metaDataWrapper {

	//Aufbau vom Array: "MetaField" → "methoden namen aus dieser Klasse"
	public static array $availableMetaFieldsForCustomHtmlInjection = [
		"en_firstName" => "getFirstName",
		"en_lastName" => "getLastName",
		"en_salutation" => "getSalutation",
	];

	public static string $subscriberMode;

	public static function init(): void{
		self::$subscriberMode = databaseConnector::instance()->getSettingFromDB("subscriberMode");
	}

	public static function getFirstName($subscriberID): string{
		return get_post_meta( $subscriberID, "en_firstName", true );
	}

	public static function getLastName($subscriberID): string{
		return get_post_meta( $subscriberID, "en_lastName", true );
	}

	public static function getSalutation($subscriberID): string{
		return get_post_meta( $subscriberID, "en_salutation", true );
	}

	public static function getStatus($subscriberID): string{
		return get_post_meta( $subscriberID, "en_status", true );
	}

	public static function getLastReceivedNewsletter($subscriberID): string{
		return get_post_meta( $subscriberID, "en_lastReceived", true );
	}

	public static function getAllReceivedNewsletterAsArray($subscriberID): array{
		return unserialize( get_post_meta( $subscriberID, "en_allReceived", true ));
	}

	public static function getEmail($subscriberID): string{
		return get_post_meta( $subscriberID, "en_eMailAddress", true );
	}

	public static function getToken($subscriberID): string{
		return get_post_meta( $subscriberID, "en_token", true );
	}

	public static function getAllSubscriberIDsAsArray(): array{
		$allSubscriberIDs = array();
		$query = new WP_Query( array( "post_type" => "en_subscribers", "posts_per_page" => "-1" ) );
		while ($query->have_posts()){
			$query->the_post();
			$allSubscriberIDs[] = get_the_ID();
		}
		return $allSubscriberIDs;
	}

	public static function setLastReceivedNewsletter($userID, $newLastReceivedNewsletter): void{
		update_post_meta( $userID, 'en_lastReceived', $newLastReceivedNewsletter );
	}

	public static function setAllLastReceivedNewsletter($userID, array $newAllLastReceivedNewsletter): void{
		update_post_meta( $userID, 'en_allReceived', serialize( $newAllLastReceivedNewsletter ) );
	}

	public static function getSubscriberIdByMail($mail): string | false{
		$query = new WP_Query( array( "post_type" => "en_subscribers", "posts_per_page" => "-1" ) );
		while ($query->have_posts()){
			$query->the_post();
			if (get_post_meta(get_the_ID(), "en_eMailAddress", true) == $mail){
				return get_the_ID();
			}
		}
		return false;
	}

	public static function saveMetaFields($subscriberID, $metaKey, $metaValue): void {
		update_post_meta($subscriberID, $metaKey, $metaValue);
	}
}