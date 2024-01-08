# WPML REST API #
**Contributors:** [shooper](https://profiles.wordpress.org/shooper/)  
**Donate link:** http://shawnhooper.ca/  
**Tags:** wpml, api, rest  
**Requires at least:** 5.2  
**Tested up to:** 6.4.2
**Requires PHP:** 7.4  
**Stable tag:** trunk  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Get translations details with the WP REST API on sites running WordPress & WPML

## Description ##

This plugin adds links to pages and posts in other languages into the results of a WP REST API query for sites running the WPML plugin.

It adds a "wpml_current_locale" string containing the locale code of the current response, and a "wpml_translations" array
containing the available translations for this plugin.

## Screenshots ##

1. This screenshot shows an excerpt of the JSON returned by the WP REST API when a page has translations available

## Changelog ##

### 2.0.1 (2024-01-07) ###
* Fixed: Some posts without translations would return a random post when no translation was available

### 2.0.0 (2022-10-27) ###
* Fixed: Permalink style /yyyy/mm/dd/post_name/ returns slug without the dates (reported by @lukas-hablitzel)
* Change: array keys in the wpml_translations response are now the locale code instead of an integer
* Updated PHP Style: Short array syntax
* Updated PHP Style: Added return types to all methods
* Updated PHP Style: Added types to all parameters
* Updated PHP Style: replaced str_pos with str_contains and str_ends_with

### 1.1.4 (2022-05-31) ###
* Update build tool dependencies to fix CVE-2022-1537, CVE-2022-0436 and CVE-2020-7729

### 1.1.3 (2022-03-18) ###
* Returns the post slug (post_name column) in the return value (thanks @mags1317)

### 1.1.2 (2021-06-27) ###
* Fixed child page URL handling
* Adds 'wpmlrestapi_get_translation' filter immediately before adding translation to array (thanks @elskwid)

### 1.1.1 (2021-01-10) ###
* Fix: Refactored into a class and namesapce (thanks @szepeviktor)
* Changed minimum required version to 5.2 (for PHP 7 support)

### 1.1 (2021-01-05) ###
* Fix: properly renders URLs for pages that have parent parents (Thank you @rburgst)
* Fix: Respect home and allow filtering of language url (Thank you ghost contributor)
* Fix: Updated WPML filters/functions to replace deprecated ones.
* Fix: No longer returns draft translations
* Fix: When adding new posts - PHP Fatal error:  Uncaught Error: Cannot use object of type WP_Error as array (Thanks @darenzammit!)

### 1.0.5 ###
* Allows language switching by specifying 'lang' or 'wpml_lang' parameters on the query string.
* Typos in code fixed.

### 1.0 ###
* First release.
