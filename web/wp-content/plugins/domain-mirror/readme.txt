=== Domain Mirror ===
Contributors: DaveMc
Tags: admin, domain, blogname, siteurl, home
Requires at least: 2.0
Tested up to: 2.2
Stable tag: 1.1

Domain Mirror is a plugin to allow one Wordpress installation to be accessed form more than one domain.

== Description ==

If you have more than one domain and want to point both of them at the same Wordpress installation, you'll find that it doesn't really work very well. Wordpress creates its own internal URLs based on the settings in General Options. This Plugin allows multiple domains to be configured within Wordpress and updates the Weblog Title, Wordpress Address URL and Blog Address URL on-the-fly based on the value of $_SERVER['SERVER_NAME']

This allows one installation to show different URL paths and a different blog title for each domain.

v1.1 changes: Now allows a different Tagline for each domain.

== Credits ==

Copyright 2007 David McAleavy

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

== Installation ==

1.	Download and unpack the archive. If you downloaded the plugin from my site it will unpack to a directory named "AA-DomainMirror", but if you got it from the Wordpress plugin repository it will probably unpack to the direcotory "domain-mirror". Either will work, but you may need to rename the latter to "AA-DomainMirror" if you have problems with some other plugins not seeing the changes. See below.

2.	Copy the whole **AA-DomainMirror** directory to **wp-content/plugins/** If possible, don't change the name of the directory, as the **AA-** at the start is a *horrible* hack to ensure that Wordpress loads this plugin first. This is required as any plugin loaded before it won't be able to see the changes it makes, so will behave as if it's still on the default domain. This puzzled me for quite a while.

3.	Go to your **Plugins** page and activate **Domain Mirror**.

4. 	Go to the **Options -> Domain Mirror** page and configure.

5. For more detailed configuration details see: http://mcaleavy.org/code/domain-mirror/

== Screenshots ==

1. Admin Area

== Frequently Asked Questions ==

The following tags can be used in pages and posts:

[dmBlogTitle] - Inserts the current Weblog Title.

[dmWpAddr] - Inserts the current Wordpress Address URL.

[dmBlogAddr] - Inserts the current Blog Address URL.
