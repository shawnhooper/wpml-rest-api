<?php

/*
Plugin Name: WPML REST API
Version: 1.1.1
Description: Adds links to posts in other languages into the results of a WP REST API query for sites running the WPML plugin.
Author: Shawn Hooper
Author URI: https://profiles.wordpress.org/shooper
*/

namespace Actionable\WordPress\WPML\REST_API;

use function add_action;
use function apply_filters;
use function do_action;
use function get_option;
use function get_post;
use function get_post_types;
use function is_plugin_active;
use function is_wp_error;
use function trailingslashit;
use function wpml_get_active_languages_filter;
use function wpml_get_language_information;

add_action( 'rest_api_init', __NAMESPACE__ . '\\init', 1000 );

function init() {

	// Check if WPML is installed
	include_once( \ABSPATH . 'wp-admin/includes/plugin.php' );

	if (!is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
		return;
	}
	
	$available_langs = wpml_get_active_languages_filter('', array('skip_missing' => false, ) );
		
	if ( ! empty( $available_langs ) && ! isset( $GLOBALS['icl_language_switched'] ) || ! $GLOBALS['icl_language_switched'] ) {
		if ( isset( $_REQUEST['wpml_lang'] ) ) {
			$lang = $_REQUEST['wpml_lang'];
		} else if ( isset( $_REQUEST['lang'] ) ) {
			$lang = $_REQUEST['lang'];
		}
		
		if ( isset( $lang ) && in_array( $lang, array_keys( $available_langs ) ) ) {
			do_action( 'wpml_switch_language', $lang );
		}
	}

	// Add WPML fields to all post types
	// Thanks to Roy Sivan for this trick.
	// http://www.roysivan.com/wp-api-v2-adding-fields-to-all-post-types/#.VsH0e5MrLcM

	$post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ), 'names' );
	foreach( $post_types as $post_type ) {
		register_api_field($post_type);
	}
}

function register_api_field($post_type) {
	register_rest_field( $post_type,
		'wpml_current_locale',
		array(
			'get_callback'    => __NAMESPACE__ . '\\get_current_locale',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field( $post_type,
		'wpml_translations',
		array(
			'get_callback'    => __NAMESPACE__ . '\\slug_get_translations',
			'update_callback' => null,
			'schema'          => null,
		)
	);
}

/**
 * Calculate the relative path for this post, supports also nested pages
 *
 * @param WP_Post $thisPost
 * @return string the relative path for this page e.g. `root-page/child-page`
 */
function calculate_rel_path(WP_Post $thisPost): string
{
    $post_name = $thisPost->post_name;
    if ($thisPost->post_parent > 0) {
        $cur_post = get_post($thisPost->post_parent);
        if (isset($cur_post)) {
            $rel_path = calculate_rel_path($cur_post);
            return $rel_path . "/" . $post_name;
        }
    }
    return $post_name;
}

/**
* Retrieve available translations
*
* @param array $object Details of current post.
* @param string $field_name Name of field.
* @param WP_REST_Request $request Current request
*
* @return mixed
*/
function slug_get_translations( $object, $field_name, $request ) {
	global $sitepress;
	$languages = apply_filters('wpml_active_languages', null);
	$translations = [];
	$show_on_front = get_option( 'show_on_front' );
	$page_on_front = get_option( 'page_on_front' );

	foreach ($languages as $language) {
		$post_id = apply_filters( 'wpml_object_id', $object['id'], 'post', false, $language['language_code'] );
		if ($post_id === null || $post_id == $object['id']) continue;
		$thisPost = get_post($post_id);

		// Only show published posts
		if ( 'publish' !== $thisPost->post_status) {
			continue;
		}

		$href = apply_filters( 'wpmlrestapi_translations_href', $language['url'], $thisPost );

		if ( 'page' == $show_on_front && $object['id'] == $page_on_front ) {
			$href = trailingslashit( $href );
		} else {
			if (strpos($href, '?') !== false) {
				$href = str_replace('?', '/' . $thisPost->post_name . '/?', $href);
			} else {

				if (substr($href, -1) !== '/') {
					$href .= '/';
				}

				$href .= $thisPost->post_name . '/';
			}
		}

		$translations[] = array('locale' => $language['default_locale'], 'id' => $thisPost->ID, 'post_title' => $thisPost->post_title, 'href' => $href);
	}

	return $translations;
}

/**
 * Retrieve the current locale
 *
 * @param array $object Details of current post.
 * @param string $field_name Name of field.
 * @param WP_REST_Request $request Current request
 *
 * @return mixed
 */
function get_current_locale( $object, $field_name, $request ) {
	$langInfo = wpml_get_language_information($object);
	if (!is_wp_error($langInfo)) {
		return $langInfo['locale'];	
	}
}
