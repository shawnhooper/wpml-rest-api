# WPML REST API #
**Contributors:** shooper  
**Donate link:** http://shawnhooper.ca/  
**Tags:** wpml, api, rest  
**Requires at least:** 4.7  
**Tested up to:** 5.6  
**Stable tag:** trunk  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Adds links to posts in other languages into the results of a WP REST API query for sites running the WPML plugin.

## Description ##

Adds links to posts in other languages into the results of a WP REST API query for sites running the WPML plugin.

## Changelog ##

### 1.1 ###
* Fix: properly renders URLs for pages that have parent parents (Thank you @rburgst)
* Fix: Respect home and allow filtering of language url (Thank you ghost contributor)
* Fix: Updated WPML filters/functions to replace deprecated ones.
* Fix: No longer returns draft translations

### 1.0 ###
* First release.

### 1.1 ###
* Allows language switching by specifying 'lang' or 'wpml_lang' parameters on the query string.
* Typos in code fixed.
