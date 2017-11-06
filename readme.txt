=== Flight by Canto ===
Contributors: Canto Inc, ianthekid, flightjim
Tags: brand management, cloud storage, DAM, digital asset management, file storage, image management, photo library, Flight by Canto
Requires at least: 4.4
Tested up to: 4.8.3
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily find and publish your creative assets directly to wordpress without having to search through emails or folders, using digital asset management by Canto.

== Description ==

Easily find and publish photos, images, and any other web-safe media file from directly to your WordPress website. Simplify collaboration with your your creative team by retrieving media without having to search through emails or folders.

You can browse your Flight Library using the folder tree-menu to quickly find files saved within Albums. Or use the Global Search to find exactly what you need by searching for text within filenames, descriptions, comments, keywords, tags, and even by the name of person who uploaded it.

Define your size, alignment and link options as you normally would. Once you click to insert the image, it will be automatically saved locally to your Wordpress Media library. This will ensure it is indexed by Google for SEO within your permalink structure, and also saved when you backup your website or cache images on a CDN.

Don't have a Flight account? <a href="https://www.canto.com/flight/?utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress">Start free trial</a>

== Installation ==

Installing "Flight by Canto" can be done either by searching for "Flight by Canto" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

Configure and authorize your account under "Settings > Flight by Canto" the left nav menu. Click "Connect" and enter in your account credentials. You will be autmatically redirected back to Wordpress.

All set, enjoy!

== Screenshots ==

1. Flight Add Media seamless integration
2. Flight Insert into Post with customized image sizes
3. Flight Plugin settings to integrate with your account

== Frequently Asked Questions ==

= RTFM - Read the Flight Manual! =

For help installing or using the plugin, refer to <a href="https://www.canto.com/flight-help/knowledge-base/flight-wordpress-plugin/?utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress">Flight Help - Wordpress</a>

= Can I use this plugin without a Flight account? =

Unfortunately not. However, you are welcome to sign up today for free! <a href="https://www.canto.com/flight/?utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress">Start free trial</a>

= How do I authorize my account? =

This plugin requires you to connect to your Flight account using an admin (Pilot) account.

== Changelog ==

= 2.0.0 =
* 2017-11-05
* New Media Upload interface using latest version of ReactJS
* NEW: Duplicate check option added to settings. Will check for existing Flight media imports when enabled
* NEW: Automatic updater to pull in new versions of files added to Wordpress from Flight
* NEW: Load more images by scrolling to bottom instead of button
* NEW: Full size preview to Insert into Post modal. Additional metadata provided in modal
* Bug Fix: Automatically pulls in filenames, tags for descriptions, copyright and terms for SEO
* Bug Fix: stability and speed improvements
* Bug Fix: Folders open/close one at a time instead of entire tree
* Bug Fix: Flight API update for S3 location paths

= 1.3.3 =
* 2017-09-20
* Bug Fix: fix some header parse issue when we use new version of AWS load balancer

= 1.3.2 =
* 2016-11-28
* NEW: add filter function
* NEW: retrieve Copyright and Terms & Conditions information for contents
* Bug Fix: fix some mall defects

= 1.3.1 =
* 2016-09-22
* FIX: Flight API URI Location

= 1.3.0 =
* 2016-06-06
* NEW: New process for authenticating using OAuth method, no longer need an API key to use plugin. Improved compatibility with different hosting providers.
* UPDATE: Improved UI with interface enhancements for browsing and scrolling. Larger thumbnail previews.

= 1.2.3 =
* 2016-04-12
* Bug Fix: Copying media now uses a more stable connection with shared hosting providers
* Added inherited styles to avoid conflict with other backend plugins

= 1.2.0 =
* 2016-03-07
* NEW: Global Search - Searches within filenames, descriptions, comments, keywords, tags, and author.
* NEW: Added Flight Library in Folder/Album structure
* Added error notification for missing token
* Added curl option for token request issues experienced for some users
* Added loading spinner during authorization

= 1.1.0 =
* 2016-03-01
* A completely new interface built with ReactJS
* Speed increase, more than 5 times for loading images than before
* NEW: Browse Folders and Albums in your Library to find images quickly
* NEW: Popup quick preview of images
* NEW: Auto load filename as alt text for improved SEO
* Removed local image caching thanks for ReactJS. So you will always get the up-to-date file and not host un-needed image cache

= 1.0.5 =
* 2015-11-05
* Updated compatibility with Flight JFK release security policy
* Compatibility with Wordpress multisite and roots.io/sage different plugin paths
* Added local copy of loading spinning wheel

= 1.0.4 =
* 2015-10-07
* Bug fix: added trim() to OAuth response code variable

= 1.0.3 =
* 2015-09-14
* Add delete options for uninstalling plugin

= 1.0.2 =
* 2015-09-14
* Updated directory structure use plugin directory name flight-by-canto

= 1.0.1 =
* 2015-09-08
* Updated WP Ajax loading gif >> /wp-admin/images/wpspin_light-2x.gif

= 1.0 =
* 2015-08-01
* Initial release

== Upgrade Notice ==

= 1.3.0 =
* 2016-06-06
* NEW: New process for authenticating using OAuth method, no longer need an API key to use plugin. Improved compatibility with different hosting providers.
* UPDATE: Improved UI with interface enhancements for browsing and scrolling. Larger thumbnail previews.
