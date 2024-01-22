<?php

namespace easyNewsletter;

function getSendingInProgressStatus(): string{
	$status = databaseConnector::instance()->getSettingFromDB("sendingInProgress");
    if ($status == "false"){
        return "<span style='color: red'>Not in Progress</span>";
    } else{
	    return "<span style='color: lightgreen'>Sending in Progress</span>";
    }
}

function getActiveSendingNewsletter(): string{
	$activeNewsletter = databaseConnector::instance()->getSettingFromDB("activeNewsletter");
    if (empty($activeNewsletter)){
        return "not active";
    }
	return "<a href='/wp-admin/post.php?post=".databaseConnector::instance()->getSettingFromDB("activeNewsletterID")."&action=edit'>$activeNewsletter</a>";
}

function getSubscriberMode(): string{
	return databaseConnector::instance()->getSettingFromDB("subscriberMode");
}

function getNewsletterSubscriberCount(): string{
	$subscriberIDs = metaDataWrapper::getAllSubscriberIDsAsArray();
    return sizeof($subscriberIDs);
}

function getActiveNewsletterSubscriberCount(): string{
    $activeCount = 0;
	$subscriberIDs = metaDataWrapper::getAllSubscriberIDsAsArray();
    foreach ($subscriberIDs as $id){
	    $en_status       = metaDataWrapper::getStatus($id);
        if ($en_status == "active"){
            $activeCount++;
        }
    }
    return $activeCount;
}

function getNewsletterCount(): string{
	return wp_count_posts( "en_newsletters")->publish;
}

function getLastSendNewsletter(): string{
    $lastSendNewsletterID = databaseConnector::instance()->getSettingFromDB("lastSendNewsletterID");
    $postTitle = get_the_title($lastSendNewsletterID);
	if (empty($lastSendNewsletterID)){
		return "Empty";
	}
	return "<a href='/wp-admin/post.php?post=".$lastSendNewsletterID."&action=edit'>$postTitle</a>";
}

function getLastEditedNewsletter(): string {
	$lastEditedNewsletterID = databaseConnector::instance()->getSettingFromDB( "lastEditedNewsletterID" );
	$postTitle            = get_the_title( $lastEditedNewsletterID );
	if ( empty( $lastEditedNewsletterID ) ) {
		return "Empty";
	}

	return "<a href='/wp-admin/post.php?post=" . $lastEditedNewsletterID . "&action=edit'>$postTitle</a>";
}

function getSubscribersLink(): string{
    if (databaseConnector::instance()->getSettingFromDB("subscriberMode") == "user"){
        return "/wp-admin/users.php";
    } else{
        return "/wp-admin/edit.php?post_type=en_subscribers";
    }
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e("Easy Newsletter Overview", "easynewsletter");?></h1>
    <hr class="wp-header-end">
    <table class="en_tableStyle">
        <thead>
            <tr>
                <th><h2><?php _e("All Newsletter", "easynewsletter");?></h2></th>
                <th><h2><?php _e("All Subscriber", "easynewsletter");?></h2></th>
                <th><h2><?php _e("Settings", "easynewsletter");?></h2></th>
            </tr>
        </thead>
        <tbody>
        <tr class="en_overviewInfos">
            <td>
                <div>
                    <p>Newsletter Count: <?php echo getNewsletterCount();?></p>
                    <p>Last send Newsletter: <?php echo getLastSendNewsletter();?></p>
                    <p>Last edited Newsletter: <?php echo getLastEditedNewsletter();?></p>
                </div>
            </td>
            <td>
                <div>
                    <p>All Newsletter Subscriber count: <?php echo esc_attr(getNewsletterSubscriberCount());?></p>
                    <p>Active Newsletter Subscriber count: <?php echo esc_attr(getActiveNewsletterSubscriberCount());?></p>
                </div>
            </td>
            <td>
                <div>
                    <p>Newsletter sending Progress: <?php echo getSendingInProgressStatus();?></p>
                    <p>Current Newsletter sending: <?php echo esc_attr(getActiveSendingNewsletter());?></p>
                    <p>Current Subscriber Mode: <?php echo esc_attr(getSubscriberMode());?></p>
                </div>
            </td>
        </tr>
        <tr>
            <td><a class="button-primary" href="/wp-admin/edit.php?post_type=en_newsletters"><?php _e("To All Newsletter", "easynewsletter");?></a></td>
            <td><a class="button-primary" href="<?php echo esc_attr(getSubscribersLink());?>"><?php _e("To All Subscribers", "easynewsletter");?></a></td>
            <td><a class="button-primary" href="/wp-admin/admin.php?page=easyNewsletterSettings"><?php _e("To Settings", "easynewsletter");?></a></td>
        </tr>
        </tbody>
    </table>
</div>

