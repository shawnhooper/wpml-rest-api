<?php

/*
Plugin Name: WPML REST API
Version: 1.0
Description: Adds links to posts in other languages into the results of a WP REST API query for sites running the WPML plugin.
Author: Shawn Hooper
Author URI: https://profiles.wordpress.org/shooper
*/

add_action( 'rest_api_init', 'wpmlretapi_slug_register_languages' );

function wpmlretapi_slug_register_languages() {

	// Add WPML fields to all post types
	// Thanks to Roy Sivan for this trick.
	// http://www.roysivan.com/wp-api-v2-adding-fields-to-all-post-types/#.VsH0e5MrLcM

	$post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ), 'names' );
	foreach( $post_types as $post_type ) {
		wpmlrestapi_register_api_field($post_type);
	}
}

function wpmlrestapi_register_api_field($post_type) {
	register_api_field( $post_type,
		'wpml_current_locale',
		array(
			'get_callback'    => 'wpmlretapi_slug_get_current_locale',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_api_field( $post_type,
		'wpml_translations',
		array(
			'get_callback'    => 'wpmlretapi_slug_get_translations',
			'update_callback' => null,
			'schema'          => null,
		)
	);
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
function wpmlretapi_slug_get_translations( $object, $field_name, $request ) {
	$languages = apply_filters('wpml_active_languages', null);
	$translations = [];

	foreach ($languages as $language) {
		$post_id = icl_object_id($object['id'], 'page', false, $language['language_code']);
		$post = get_post($post_id);
		if ($post->ID && $object['id'] !== $post->ID) {
			$translations[] = array('locale' => $language['default_locale'], 'id' => $post->ID, 'post_title' => $post->post_title, 'href' => get_permalink($post->ID));
		}
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
function wpmlretapi_slug_get_current_locale( $object, $field_name, $request ) {
	$langInfo = wpml_get_language_information($object);
	return $langInfo['locale'];
}