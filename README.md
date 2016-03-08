WP REST JMESPath
================

Adds support for [JMESPath](http://jmespath.org) queries to all WP REST API
endpoints.


Installation
============

Install as per usual into `wp-content/plugins` or `wp-content/mu-plugins`.


Usage
=====

The plugin checks REST API requests for the `_query` parameter; if present it treates this as a JMESPath expression and applies it to the response, e.g.

    curl -s --globoff  "http://my.wordpress.site/wp-json/wp/v2/pages?_query=[0:2].{id: id, title: title.rendered}"

could be used to select only the first two posts (with the [0:2] slice) and from there build an object with id and title fields selected from id and title.rendered.

The [JMESPath](http://jmespath.org) site has an interactive tutorial and examples.
