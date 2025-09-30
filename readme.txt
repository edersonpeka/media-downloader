=== Media Downloader ===
Contributors: edersonpeka
Tags: media, audio, podcast, player, mp3
Requires at least: 5.0
Tested up to: 6.8.2
Stable tag: 0.4.7.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ederson@gmail.com&lc=BR&currency_code=BRL&item_name=Media%20Downloader%20Wordpress%20Plugin

Lists MP3 files from a folder.

== Description ==

**Note: Updating from versions prior to 0.3 requires reactivating!**

Media Downloader plugin lists MP3 files from a folder through the [mediadownloader] shortcode. It reads MP3 information directly from the files. It also can try to get rid of stupid content blockers (mainly corporatives), changing all links to .MP3 files into some download URL without the string "MP3".

== Installation ==

1. Extract the contents of the archive
2. Upload the contents of the mediadownloader folder to your 'wp-content/plugins' folder
3. Log in to your WordPress admin and got to the 'Plugins' section. You should now see Media Downloader in the list of available plugins
4. Activate the plugin by clicking the 'activate' link
5. Now go to the 'Options' section and select 'Media Downloader' where you can configure the plugin

== Frequently Asked Questions ==

= How should I configure it? Where should I throw my MP3 files? How do I use this thing? What's the smart tag syntax? =

An example may help... Say you have a folder called "music" under your root folder, and for its time it has some subfolders, as, "Beethoven", "Mozart", "Bach" and "Haendel".

First of all, you should configure Media Downloader by typing "music" in the "MP3 Folder" field, on settings page (and then clicking on "Update Options", for sure).

That done, you can edit a post talking 'bout Johann Sebastian Bach and insert anywhere on it the shortcode, [mediadownloader folder="Bach"]. Media Downloader will create a list of all files under the "music/Bach" directory. This is actually very simple. ;-)

(The [mediadownloader] shortcode accepts several parameters. You can learn more about them in plugin's settings page.)

--

Spanish translation: Jonathan Jose from www.flowconversatilidad.net

