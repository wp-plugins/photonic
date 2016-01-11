# Photonic Gallery for Flickr, Picasa, SmugMug, 500px, Zenfolio and Instagram
- **Contributors**: [sayontan](https://github.com/sayontan), [barisaydinoglu](https://github.com/barisaydinoglu)
- **Donate link**: <http://aquoid.com/news/plugins/photonic/>
- **Tags**: gallery, flickr, picasa, 500px, smugmug, zenfolio, instagram, fancybox, colorbox, prettyphoto, slideshow, jquery, shortcode
- **Requires at least**: WP 3.8
- **Tested up to**: WP 4.2
- **Stable tag**: trunk
- **License**: GPLv3 or later

Galleries on steroids! A stylish plugin for galleries of your public and private WP, Flickr, Picasa, SmugMug, 500px, Zenfolio & Instagram photos.

## Description

Photonic lets you use the WordPress `gallery` shortcode and super-charges it with a lot of added functionality. It adds support for
several new parameters to enhance the content and look-and-feel of your galleries. It supports Flickr photos, Photosets, Galleries and Collections,
along with Picasa photos and albums, SmugMug albums and images, 500px photos and collections, Zenfolio photos, Photosets and Groups, and Instagram photos and users.
You can also enable authentication for your site visitors that will let them see private and protected photos from each provider.

In case you happen to be using a theme or plugin that already overrides the `gallery`, Photonic provides you with the
option to use your own shortcode for Photonic galleries. This lets your plugins coexist. Bear in mind that if you deactivate Photonic
you will have to remove all instances of this custom shortcode, something that is not required if you stick to the `gallery` shortcode.

### Flickr Support

The following Flickr concepts are supported in Photonic:

* [Photos](http://aquoid.com/news/plugins/photonic/flickr/flickr-photos/)
* [PhotoSets](http://aquoid.com/news/plugins/photonic/flickr/flickr-photosets/)
* [Galleries](http://aquoid.com/news/plugins/photonic/flickr/flickr-galleries/)
* [Collections](http://aquoid.com/news/plugins/photonic/flickr/flickr-collections/)
* [Single Photo](http://aquoid.com/news/plugins/photonic/flickr/flickr-photo/)
* [Authentication](http://aquoid.com/news/plugins/photonic/flickr/flickr-authentication/)

For demos of Flickr support visit the [Flickr page](http://aquoid.com/news/plugins/photonic/flickr/).

### Picasa Support

The following Picasa concepts are supported in Photonic:

* [Photos](http://aquoid.com/news/plugins/photonic/picasa/picasa-photos/)
* [Albums](http://aquoid.com/news/plugins/photonic/picasa/picasa-albums/)

Authentication for Picasa is supported as well. For demos of Picasa support visit the [Picasa page](http://aquoid.com/news/plugins/photonic/picasa/).

### SmugMug Support

The following SmugMug concepts are supported in Photonic:

* [User Tree](http://aquoid.com/news/plugins/photonic/smugmug/smugmug-tree/)
* [Photos](http://aquoid.com/news/plugins/photonic/smugmug/smugmug-photos/)
* [Albums](http://aquoid.com/news/plugins/photonic/smugmug/smugmug-albums/)

For demos of SmugMug support visit the [SmugMug page](http://aquoid.com/news/plugins/photonic/smugmug/).

### 500px Support

Photos on [500px.com are supported in Photonic](http://aquoid.com/news/plugins/photonic/500px/). Sets (collections) are also
supported, but there are [some quirks in 500px.com's API](https://github.com/500px/api-documentation/issues/17).

### Zenfolio Support

The following Zenfolio concepts are supported in Photonic:

* [Photos](http://aquoid.com/news/plugins/photonic/zenfolio/photos/)
* [PhotoSets](http://aquoid.com/news/plugins/photonic/zenfolio/photosets/)
* [Groups](http://aquoid.com/news/plugins/photonic/zenfolio/groups/)
* [Group Hierarchies](http://aquoid.com/news/plugins/photonic/zenfolio/group-hierarchy/)

For demos of Zenfolio support visit the [Zenfolio page](http://aquoid.com/news/plugins/photonic/zenfolio/). Note that
access to protected / private content in Zenfolio is not currently supported.

### Instagram Support

Instagram photos and users [are supported in Photonic](http://aquoid.com/news/plugins/photonic/instagram/). You can
display photos based on popularity, tags, and location-based searches. You can also show thumbnails of users following you or those
followed by you. Some user features require authentication.

### Enhanced Native Galleries
Your existing gallery insertions are left intact. However you can add a `style` parameter to it for special effects. The permitted values for `style` are:

1. `strip-below`: Displays a thumbnail strip with a running slideshow above.
2. `strip-above`: Displays a thumbnail strip with a running slideshow below.
3. `no-strip`: Displays running slideshow with no thumbnails.
4. `launch`: Doesn't display a running slideshow, but shows all thumbnails. On clicking a thumbnail a popup slideshow is shown.

You can also specify a parameter called `fx` to add special effects. This parameter can take any of these values: `fade`
, `scrollUp`, `scrollDown`, `scrollLeft`, `scrollRight`, `scrollHorz`, `scrollVert`
, `slideX`, `slideY`, `turnUp`, `turnDown`, `turnLeft`, `turnRight`, `zoom`
, `fadeZoom`, `blindX`, `blindY`, `blindZ`, `growX`, `growY`, `curtainX`
, `curtainY`, `uncover`, `wipe`. See the [JQuery Cycle site](http://jquery.malsup.com/cycle/)
for demos of the effects.

Parameters such as `speed` can control the slide transition speed, and `thumbnail_size` can control the size of
the thumbnails.

## Installation

You can install the plugin through the WordPress installer under **Plugins &rarr; Add New** by searching for it,
or by uploading the file downloaded from here. Alternatively you can download the file from here, unzip it and move the unzipped
contents to the `wp-content/plugins` folder of your WordPress installation. You will then be able to activate the plugin.

## Screenshots

For the plugin in action see the [plugin page](http://aquoid.com/news/plugins/photonic/).

1.	To insert the shortcode for Photonic through the Media Uploader click on "Add Media".
2.	You will see a new tab for "Photonic".
3.	Clicking on the "Photonic" tab will show you new tabs, one for each type of gallery. Fill out what you need and click "Insert into post"

## Frequently Asked Questions

### If I disable the plugin what happens to the galleries?

Obviously, your galleries will not show. However, since you are using the native `gallery` shortcode, you will
not see any empty shortcode tags on your site.

### What about other photo-sharing platforms?

Suggestions are welcome for other photo-sharing platforms. In addition there will be support provided for the generic
Media RSS format, which can facilitate integration with platforms such as ZenPhoto.

### What about other JS libraries?

Currently Fancybox, Colorbox and PrettyPhoto are supported. Both lighter and heavier alternatives are being considered.
If you have specific suggestions please feel free to contact the plugin author.

Note that there have been slight modifications to both the above scripts to make them interact with JQuery tooltips.

### Are there any known issues?

Yes, there are a couple dealing with tooltips. The plugin optionally uses the [JQuery Tooltip](http://bassistance.de/jquery-plugins/jquery-plugin-tooltip/)
script to show image tooltips. The drawback is that for IE7 the native HTML tooltip and the custom tooltip are shown simultaneously.
This is a bug with the thumbnail script.

In addition, if you access a password-protected album in SmugMug in Photonic, you don't get the desired results.

### Are translations supported?

Yes, but only for the plugin front-end. The admin panel is not translated at this point. Also note that any strings included
in the third-party JS scripts are not translated.

## Changelog

### 1.49

* Security patch - updated PrettyPhoto to latest version

### 1.48

* Fixed Zenfolio API issues.

### 1.47

* Fixed a problem with the Flickr helpers.
* Fixed miscellaneous issues with SmugMug.

### 1.46

* 500px API updated to accommodate the new URL formats.

### 1.45

* Flickr API updated to use https instead of http, as per Flickr's transition plans.

### 1.44

* Options panel was not working for WP 3.6. This has been fixed.

### 1.43

* Added Thickbox support
* Fixed an incompatibility between Photonic and themes from Themify.
* Added capability to exclude passworded galleries from displaying for SmugMug.
* Added support for alternative shortcode - specifying this will mean Photonic will not use the gallery shortcode

### 1.42

* Massively refactored code. Several lines of JS code taken off, lots of PHP code modularized.
* Added option to use a Colorbox skin defined in the theme folder
* Updated SimpleModal, Colorbox and PrettyPhoto to latest versions.
* Swapped the use of "live" for "on". This means Photonic needs WP 3.3 at least (since "on" was introduced in JQuery 1.7)
* Removed the "View in Flickr" text from the plugin.
* Ensured consistent behaviour with the lightbox title linking behaviour across all providers.
* Introduced non-bundled lightbox support, with support for FancyBox2
* Removed the modified scripts for FancyBox and ColorBox - now the original scripts are used.

### 1.41

* Resolved a conflict between JetPack "Publicize" and protected access. The login box was not showing up.
* Added the "ids" parameter to the shortcode insertion UI.
* Added options to control thumbnail, title and photo count display in SmugMug album headers

### 1.40

* Added Zenfolio support for public photos.
* Fixed an issue with the shortcode generation for Instagram tags.
* Added the 'photonic_register_extensions' action hook for registering more extensions.
* Fixed some PHP notices that were showing up in debug mode when no options were set.
* Fixed an option to prevent SmugMug album thumbnails and titles from linking to the SmugMug page.

### 1.36

* Rewrote the Picasa processor to be DOM-based instead of event-based.
* Added option to let users display the Picasa image title instead of the description.
* Fixed a problem with Flickr Collections that was causing nested collections to display repeatedly.
* Fixed a problem with Flickr Collections that was making the collections link to an invalid page.
* Picasa thumbnails in the popup now have the same dimensions as those in the main page.

### 1.35

* Added Instagram support
* Gave Photonic its own menu item
* Added some helpers to let people find their Flickr and Instagram IDs
* Fixed a bug that was preventing Flickr Galleries from showing their overlaid popups
* Added capability to display external links in a new tab

### 1.30

* Removed singular check for 500px.com

### 1.29

* Added support for 500px.com collections.
* Fixed authentication problems in 500px.com.
* Added capability to show a different number of photos on an archive page than on the page for a single post/page
* Changed the Flickr API calls to be PHP-based instead of JS-based.

### 1.28

* Removed some debugging statements.

### 1.27

* Fixed a minor issue with the displaying of the login box for Picasa.

### 1.26

* Added support for displaying single photos in 500px.com.
* Added authentication support for Picasa.
* Added more skins for Colorbox.

### 1.25

* Changed some code so that not being connected to the web doesn't throw an error for 500px.
* Fixed an issue that was preventing 500px photos from being shown on certain servers.

### 1.24

* Added date filtering support for 500px.com. Thanks to Bart Kuipers (http://www.bartkuipers.com/) for the code.
*   Removed the included script for JQuery Dimensions, which was causing conflicts with other JQuery plugins. Dimensions has been merged into JQuery Core a while back.

### 1.23

*   Added search support for 500px.com, via tags and terms
*   Added new categories, sort criteria and capability to exclude a category for 500px.com

### 1.22

*   Made minor corrections to authentication behaviour. If not connected to the internet, Photonic was returning a fatal error.
*   Changed the wording for PrettyPhoto licensing. PrettyPhoto is licensed under GPL, and that is now explicitly stated in the options page.
*   Added the capability to include shortcodes inside the parameters of the gallery shortcode (nested shortcodes)

### 1.21

* Added support for SmugMug password-protected albums where the user has access to the albums. Other albums show up, but clicking on them takes the users nowhere.
* Fixed a bug that was killing the last thumbnail in a popup for SmugMug.

### 1.20

* Added authentication support for Flickr, 500px.com and SmugMug.
*   Disabled the showing of password-protected SmugMug galleries.

### 1.12

* Fixed a problem with handling double quotes in the title of Flickr photos.

### 1.11

* Combined SimpleModal script with Photonic to save an HTTP request.
*   Deleted some redundant JS files.
*   Added support for a different default type.
*   Added support for single photos in Flickr.

### 1.10

* Fixed a bug with the SmugMug processor, which was preventing the display of an album popup.

### 1.09

* Fixed a bug with the Flickr processor, which was preventing the execution of the shortcode with a group_id parameter
* Fixed a minor problem with the popup panels, where hovering over the panel titles was resulting in a Tooltip error.
* The Picasa inserter was not showing the "album" field. This has been rectified.

### 1.08

* Made a change to the Picasa processor to handle the recent change by Google. Google's change was forcing images to 512px.
* Updated the plugin to support WP 3.3.
*   Fixed an issue with FancyBox that was showing server log errors for IE.

### 1.07

* Added support for SmugMug
* Modified the Picasa plugin to not display the file name if no title is found.

### 1.06

* Fixed an issue with the 500px.com extension, which had to be changed to invoke HTTPS instead of HTTP.
* Added support for the "sort" parameter for 500px.
* Fixed an issue with the PrettyPhoto library where you couldn't see the "View in Flickr" link.

### 1.05

* Added support for the PrettyPhoto JS library.
* Added check for _wp_additional_image_sizes. This was causing errors for some people.
* Fixed a bug with the thumbnail_width and thumbnail_height attribute of the plugin. The attributes should have been called thumb_width and thumb_height.
* Added option to launch images in a slideshow mode.
* Fixed a bug that was not accepting border settings for the thumbnails.

### 1.04

* Added support for photos on 500px.
* Modified the Colorbox invocation to fit in the browser window.

### 1.03

* Added support for Gallery objects in Flickr.
* Fixed an issue with the Picasa galleries where under certain circumstances users were getting a "Division by zero" error.
* Fixed an issue where Chrome and Safari were showing an overlap of the slides over the rest of the content in native galleries.
* Updated translation file.

### 1.02

* Added a UI panel for insertion of Photonic galleries through the Add Media screen.
* Restructured code for better extensibility.
* Updated translation file.

### 1.01

* Included a PO file for translation support.
* Fixed a bug to prevent appearance of Photonic stylesheets on other admin pages.

### 1.00

* New version created.

## Upgrade Notice

No upgrade notices at this point.
