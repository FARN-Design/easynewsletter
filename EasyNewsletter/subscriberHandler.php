<?php

namespace EasyNewsletter;

use WP_Query;

class subscriberHandler {

	private static subscriberHandler $subscriber_handler;

	public static function instance(): subscriberHandler {
		if (!isset(self::$subscriber_handler)){
			self::$subscriber_handler = new subscriberHandler();
		}
		return self::$subscriber_handler;
	}

	public function addNewSubscriber(array $metaFields, bool $imported = false): bool|int{
		//handle subscriber as Subscriber Post Type
		if (!$this->createSubscriberInDefaultMode($metaFields)){
			return false;
		}

		//get newly created User
		$subscriberID = metaDataWrapper::getSubscriberIdByMail($metaFields["en_eMailAddress"]);


		//Fill required MetaData with defaults
		$this->fillRequireMetaFieldsWithDefaults($subscriberID);

		//Fill Metadata form sign up form
		foreach ($metaFields as $key => $value){
			metaDataWrapper::saveMetaFields($subscriberID, $key, $value);
		}

		if ($imported){
			//Change to active state and confirm double opt-in
			metaDataWrapper::saveMetaFields($subscriberID,"en_status", "active");
			metaDataWrapper::saveMetaFields($subscriberID,"en_doubleOptIn", "confirmed");
		} else {
			//send activation confirmation mail
			mailManager::instance()->sendActivationMail($metaFields['en_eMailAddress']);
		}

		return $subscriberID;
	}

	public function fillRequireMetaFieldsWithDefaults($subscriberID): void{
		foreach (subscriberPostType::$metaFieldsRequired as $key => $value){
			if (databaseConnector::instance()->getSettingFromDB("subscriberMode") == "user")
			{
				if (get_user_meta($subscriberID, $key) != null){
					continue;
				}
			}
			switch ($key){
				case 'en_eMailAddress':
					break;
				case 'en_doubleOptIn':
					metaDataWrapper::saveMetaFields($subscriberID, $key, "Request send");
					break;
				case 'en_token':
					metaDataWrapper::saveMetaFields($subscriberID, $key, subscriberPostType::instance()->generateDoubleOptInToken(metaDataWrapper::getEmail($subscriberID)));
					break;
				case 'en_status':
					metaDataWrapper::saveMetaFields($subscriberID, $key, "inactive");
					break;
				case 'en_allReceived':
					metaDataWrapper::saveMetaFields($subscriberID, $key, serialize(array()));
					break;
				case "en_subscriberCategory":
					metaDataWrapper::saveMetaFields($subscriberID, $key, array("default"));
					break;
				default: metaDataWrapper::saveMetaFields($subscriberID, $key, "default");
			}
		}
	}

	private function createSubscriberInDefaultMode($metaFields): bool{
		$query = new WP_Query(array( "post_type" => "en_subscribers", "posts_per_page" => "-1" ));
		while ($query->have_posts()){
			$query->the_post();
			if (get_post_meta( get_the_ID(),"en_eMailAddress", true) == $metaFields["en_eMailAddress"]){
				if (get_post_meta(get_the_ID(), "en_status", true) == 'unsubscribed'){
					$this->updateSubscriberMeta(get_the_ID(), $metaFields);
					return true;
				}else{
					return false;
				}
			}
		}

		$subscriber = array(
			'post_title'    => $metaFields['en_eMailAddress'],
			'post_content'  => '',
			'post_name' => $metaFields['en_eMailAddress'],
			'post_status'   => 'private',
			'post_author'   => 1,
			'post_type' => 'en_subscribers',
			'guid' => 'test',
			'meta_input' => array_merge($metaFields)
		);
		wp_insert_post( $subscriber );
		return true;
	}

	public function updateSubscriberMeta(int $id, array $metaFields): void {
		foreach ($metaFields as $key => $value){
			metaDataWrapper::saveMetaFields($id, $key, $value);
		}
		metaDataWrapper::saveMetaFields($id, "en_status", "inactive");
		metaDataWrapper::saveMetaFields($id, "en_doubleOptIn", "Request send");

		mailManager::instance()->sendActivationMail($metaFields['en_eMailAddress']);
	}
}