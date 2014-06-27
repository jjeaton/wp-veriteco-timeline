=== Plugin Name ===
Contributors: jjeaton, youngbhs
Tags: VeriteCo, timeline, custom post types
Requires at least: 3.4
Tested up to: 3.8.1
Stable tag: 1.1.2

WP VeriteCo Timeline integrates VeriteCo's Timeline JS into the WordPress back-end.

== Description ==
WPVT integrates the wonderful JS plugin created by [NU Knight Lab](http://timeline.knightlab.com/ "Timeline JS") seamlessly into your WordPress back-end. It allows you to manage timeline entries through a Timeline custom post type. WPVT automatically generates the JSON data file from your database and styles it according to your settings.

Now creating a timeline is easier than ever with the WPVT shortcode.

== Installation ==

1. Upload the `wp-veriteco-timeline` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Adjust settings under `Settings -> WP Timeline`
4. Create some timeline entries under `Timeline` custom post type
5. Use shortcode `[WPVT]` in your page


== Frequently Asked Questions ==


== Changelog ==

= 1.1.2 (2014-04-13) =

* Fix incompatible regex that was replacing lowercase "v" characters on certain versions of PHP

= 1.1.1 (2014-03-14) =

* Show all timeline posts by default (UI to come)
* Add GPL License

= 1.1 (2014-03-14) =

* Fix: Allow arbitrary HTML content within posts (don't strip slashes from JSON) (props titaniumbones)
* Enhancement: Updated timeline.js to v2.29.1 (props titaniumbones)
* Info: Updated classes, please modify these if you've used them to style anything in your themes (Format: .old => .new) .vmm-timeline => .vco-timeline, .vmm-notouch => .vco-notouch, .container.main => .vco-container.vco-main, .feature => .vco-feature, .vmm-slider => .vco-slider, .navigation => .vco-navigation, .feedback => .vco-feedback

= 1.0 =

* Initial Version.

== Upgrade Notice ==

= 1.1 =

* Info: Updated classes, please modify these if you've used them to style anything in your themes (Format: .old => .new) .vmm-timeline => .vco-timeline, .vmm-notouch => .vco-notouch, .container.main => .vco-container.vco-main, .feature => .vco-feature, .vmm-slider => .vco-slider, .navigation => .vco-navigation, .feedback => .vco-feedback
