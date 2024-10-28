=== Attribution Query String Manager ===
Contributors: kasigi
Tags: analytics, ads, link, links, post, page, posts
Requires at least: 3.5
Tested up to: 3.8.1
License: GPLv2 or later

This plugin will help manage query string variables to ensure that desired variables are always included on certain domains. This plugin was developed to assist with affiliate tracking needs for sites/blogs that link to separate purchase flows. (It is not limited to this purpose).

== Description ==

This tool will scan links with the specified domains for the query string variables listed below and ensure that they are updated with the appropriate values. These valued can be defined by:

1. Post/Page settings
2. URL
3. Client Side Cookie
4. Server Session Cache
5. Defaults (in the global admin)

The list above indicates the order of override - in short, settings for a post/page will cause mismatching values set via url to be ignored. Note: This processing will only apply to material output via the_content() (this includes pages & posts).

For example - if the domain is www.wordpress.org and the query string variables defined are "affiliateID" and "trafficSource", any links to www.wordpress.org would have the affiliateID & trafficSource query string variables appended. If they are already in the link's url, it will update those variables to the correct values.


== Screenshots ==

1. This is an example of the administrative panel.


== Installation ==

1. Upload the folder `attribution-query-string-manager` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add query string variable names and domains in the admin settings panel "Query String Manager"


== Changelog ==

= 0.1.1 (2014-05-04)
* Patch to prevent duplication of inputs when urls managed are substrings of eachother

= 0.1.0 (2013-12-10) =
* Beta version


== Requirements ==


