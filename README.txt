=== EasyNewsletter ===
Contributors: farndesign
Tags: newsletter
Requires at least: 6.0
Tested up to: 6.3
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Create and send your newsletter directly from your WordPress website with no dependency on an external email marketing tool and no ongoing costs.

== Description ==

Streamline your communication with users by effortlessly managing newsletters with our newsletter plugin. 
Allow users to register for your newsletter through a customizable interface. 
Utilizing a double opt-in mechanism, ensure compliance with your mailing list.

Craft captivating newsletters directly within the WordPress block editor using our plugin. 
Experience the convenience of tailoring your content to perfection while leveraging the power of WordPress blocks to create appealing newsletters in a already known environment.

Customize your newsletter distribution strategy by setting the desired number of emails to be sent together. 
This efficient batching system optimizes your email delivery process while maintaining reliability.

With our built-in preview feature, visualize your newsletter exactly as your recipients will. 
Ensure flawless content presentation and resolve any discrepancies before sending.

Elevate your newsletters by seamlessly attaching files from your WordPress media library. Our plugin seamlessly integrates with the WordPress file selector, making it easy to enrich your newsletter's content and enhance engagement with included attachments.

== Frequently Asked Questions ==

= How to install the plugin? =

1. Clone the repo or download the .zip archive
   ```sh
   git clone TODO
   ```
   or
   Download: TODO

2. Add the plugin files to the Wordpress instance.
- Create a new Folder in: `/wp-content/plugins/easynewsletter`
- Move all files into the new `easynewsletter` folder

3. Activate the plugin at the Wordpress plugin page http://localhost/wp-admin/plugins.php

#### Install via Wordpress Plugin Store

TODO

= How to use the plugin? =

## Configure the basics

After installing Easy Newsletter, you can now make adjustments to the default settings. 
To do so, go to "Easy Newsletter" in your WordPress dashboard. 
Click on "Settings" to adjust the settings. 
You should edit the following fields:

- Sender email address
- Sender email name
- Reply to email address

The "Sender Email Address" is the email address from which your newsletter will be sent, enter your desired email address here. 
The "Sender Email Name" is the name that will be displayed to your subscribers when they receive your newsletter. 
In the field "Reply to email address" you can set to which email address replies to your newsletter should be sent.

In addition, you can change a lot of more specific options, for example, how many emails should be sent per interval, which subscriber categories there are and which subscriber list you want to use. 
Explanations of all settings can be found in the [documentation](https://www.easy-wordpress-plugins.de/easy-newsletter-dokumentation).

## Create a registration form

In order for readers to sign up for your newsletter, you need a signup form. 
Our Easy Newsletter plugin already has a predefined form that you can customize and place on your website.

On the "Settings" page you can define which fields are requested when new readers sign up for your newsletter. 
The default is the salutation, the first and last name and the email address. 
You can use the checkboxes to select or deselect additional form fields. 
This way you can activate spam protection for the registration form as well as add a double opt-in.

Everything else about the newsletter signup form can be found under the pages in the WordPress dashboard. 
There Easy Newsletter has added seven pages that are required for the plugin and must not be deleted.

You can find the signup form on the page "Easy Newsletter Form", if you click on the page in edit mode, you will see the shortcode `[easyNewsletter]` there, which you can also use on other pages.

You can make enhancements to your registration form on the "Easy Newsletter Registration Form Page". 
Here you can for example add a headline for your registration form, add an image or a background color. 
All this can be easily done using the standard WordPress blocks.
You can also customize the look of your registration form using CSS, but this is not necessary.

## Edit the standard emails

After customizing your subscription form, you should make some adjustments to the standard emails of Easy Newsletter. 
These emails are sent when readers subscribe or unsubscribe to your newsletter.
When they sign up, they receive a welcome and sign-up email, and when they unsubscribe, they receive an unsubscribe email. 
You can find the three default Easy Newsletter emails in the WordPress dashboard under Easy Newsletter "All Newsletters". 
The emails are named as follows:

- Default - Welcome
- Default - Unsubscribed
- Default - Activation

To edit the default emails, you can simply open them and customize them using your editor. 
So for each of the three emails you can add your desired text and other content like your logo or images. 
For this you can simply use the default WordPress blocks. 
Make sure not to delete the already existing links in the standard emails.

Welcome your new subscribers in the Welcome email, ask them to confirm their subscription to the newsletter in the Activation email and assure them about their unsubscription from the newsletter in the Unsubscribe email. 
When you are satisfied with the changes in the emails, save them using the "Update" button. You can also send a test e-mail by clicking the "Send test e-mail" button. 
You can set the email address for the test email in the right side column under "Newsletter Settings".

## Create your first newsletter

Now that you have successfully created your subscribers, it's time to create your first newsletter with Easy Newsletter. 
To do this, click on "All Newsletters" in the WordPress dashboard and then on the "Create" button. Now you are in your usual editor with the WordPress custom blocks. 
Here you can enter the title of your newsletter and add content. 
Just use the usual WordPress blocks and add your text, images and other desired content.

If you have content that repeats in every newsletter, such as a footer for your newsletter, you can also create reusable blocks and include them.

In addition to the design and content settings, you can create your subject line (Subject), a preview text (Newsletter Excerpt) and a test email address for your newsletter in the right side column under the item "Newsletter Settings".

If you want to make more complex settings for your newsletter, you can read our [documentation](https://www.easy-wordpress-plugins.de/easy-newsletter-dokumentation) of Easy Newsletter. 
There we describe all the possibilities that the plugin offers you.

## Send the newsletter

Once you have checked your newsletter and are satisfied with the result, you can send it to your subscribers. 
To do this, simply click on the "Send newsletter" button in the newsletter. 
A pop-up will open in which you can see how many recipients your newsletter will be sent to. 
If you are sure that everything is correct, confirm the sending by clicking on "Yes". 
If something is still wrong, you can cancel the sending by clicking "No".

== Screenshots ==

1. Newsletter Creation
2. Sign Up Form
3. Newsletter Check
4. Newsletter Meta Fields
5. Subscribers Overview
6. Plugin Overview
7. Newsletter Overview
8. Settings
9. Newsletter Attachments and HTML Replacement

== Changelog ==

= 1.0.0 =
* First release

== Upgrade Notice ==

= 1.0.0 =
* Download to use the first version of the plugin. Should download the update.
