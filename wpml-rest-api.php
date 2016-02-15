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
	register_api_field( 'post',
			'language_code',
			array(
					'get_callback'    => 'wpmlretapi_slug_get_current_locale',
					'update_callback' => null,
					'schema'          => null,
			)
	);

	register_api_field( null,
		'other_locales',
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
	$languages = icl_get_languages();

	foreach ($languages as $language) {
		$thisPostInOtherLanguage = icl_object_id($object['id'], 'page', false, $language['language_code']);
		if ($thisPostInOtherLanguage && $object['id'] !== $thisPostInOtherLanguage) {
			$translations[] = array('locale' => $language['default_locale'], 'id' => $thisPostInOtherLanguage, 'link' => get_permalink($thisPostInOtherLanguage));
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