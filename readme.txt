=== WP REST JMESPath ===
Contributors: elyobo
Donate link: 
Tags: 
Requires at least: 4.2
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds support for [JMESPath](http://jmespath.org) queries to all WP REST API endpoints.

== Description ==

A common request for the WP REST API is to limit the fields returned by the API.  Modifying the underlying response objects is not recommended as this is likely to cause compatibility issues with other consumers of the API; this plugin instead allows consumers to specify the data that they need using the JMESPath query language for JSON to request only the data that they need from the response.

Usage


The plugin checks REST API requests for the `_query` parameter; if present it treates this as a JMESPath expression and applies it to the response, e.g.

    curl -s --globoff  "http://my.wordpress.site/wp-json/wp/v2/pages?_query=[0:2].{id: id, title: title.rendered}"

could be used to select only the first two posts (with the [0:2] slice) and from there build an object with id and title fields selected from id and title.rendered.

The [JMESPath](http://jmespath.org) site has an interactive tutorial and examples.

== Installation ==

1. Upload `wp-login-nonce.php` to the `/wp-content/plugins/` directory or the `/wp-content/mu-plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress if uploaded to `/wp-content/plugins/`.

== Frequently asked questions ==



== Screenshots ==



== Changelog ==



== Upgrade notice ==
