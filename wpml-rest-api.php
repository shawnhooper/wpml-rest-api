<?php

/*
Plugin Name: WPML REST API
Version: 2.0.1
Description: Adds links to posts in other languages into the results of a WP REST API query for sites running the WPML plugin.
Author: Shawn Hooper
Author URI: https://profiles.wordpress.org/shooper
*/

namespace ShawnHooper\WPML;

use WP_REST_Request;
use RuntimeException;

class WPML_REST_API
{

    private array $translations = [];

    public function wordpress_hooks(): void
    {
        add_action('rest_api_init', [$this, 'init'], 1000);
    }

    public function init(): void
    {
        // Check if WPML is installed
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            return;
        }

        $available_languages = wpml_get_active_languages_filter('', ['skip_missing' => false]);

        if ((!empty($available_languages) && !isset($GLOBALS['icl_language_switched'])) || !$GLOBALS['icl_language_switched']) {
            if (isset($_REQUEST['wpml_lang'])) {
                $lang = $_REQUEST['wpml_lang'];
            } else if (isset($_REQUEST['lang'])) {
                $lang = $_REQUEST['lang'];
            }

            if (isset($lang) && array_key_exists($lang, $available_languages)) {
                do_action('wpml_switch_language', $lang);
            }
        }

        // Add WPML fields to all post types
        // Thanks to Roy Sivan for this trick.
        // http://www.roysivan.com/wp-api-v2-adding-fields-to-all-post-types/#.VsH0e5MrLcM

        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        foreach ($post_types as $post_type) {
            $this->register_api_field($post_type);
        }
    }

    /**
     * @param string $post_type
     * @return void
     */
    public function register_api_field(string $post_type): void
    {
        register_rest_field($post_type,
            'wpml_current_locale',
            array(
                'get_callback' => [$this, 'get_current_locale'],
                'update_callback' => null,
                'schema' => null,
            )
        );

        register_rest_field($post_type,
            'wpml_translations',
            array(
                'get_callback' => [$this, 'get_translations'],
                'update_callback' => null,
                'schema' => null,
            )
        );
    }

    /**
     * REST API ENDPOINT Handler
     *
     * Retrieve the current locale
     *
     * @param array $object Details of current post.
     * @param string $field_name Name of field.
     * @param WP_REST_Request $request Current request
     *
     * @return string
     * @throws RuntimeException
     * @noinspection PhpUnusedParameterInspection
     */
    public function get_current_locale(array $object, string $field_name, WP_REST_Request $request): string
    {
        $langInfo = wpml_get_language_information($object);
        if (is_wp_error($langInfo)) {
            throw new RuntimeException('Unable to retrieve the current locale');
        }
        return $langInfo['locale'];
    }

    /**
     * REST API ENDPOINT Handler
     *
     * Retrieve available translations
     *
     * @param array $object Details of current post.
     * @param string $field_name Name of field.
     * @param WP_REST_Request $request Current request
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function get_translations(array $object, string $field_name, WP_REST_Request $request): array
    {
        $languages = apply_filters('wpml_active_languages', null);

        foreach ($languages as $language) {
            $this->get_translations_for_language($object, $language);
        }

        return $this->translations;
    }

    /**
     * @param array $object
     * @param array $language
     * @return void
     */
    private function get_translations_for_language(array $object, array $language) : void {
        $post_id = wpml_object_id_filter($object['id'], 'post', false, $language['language_code']);
        if ($post_id === null || $post_id === $object['id']) {
            $this->translations = [];
            return;
        }

        $thisPost = get_post($post_id);

        $translation = [
            'locale' => $language['default_locale'],
            'id' => $thisPost->ID,
            'slug' => $thisPost->post_name,
            'post_title' => $thisPost->post_title,
            'href' => get_permalink($thisPost),
        ];

        $this->translations[$language['default_locale']] = apply_filters('wpmlrestapi_get_translation', $translation, $thisPost, $language);
    }
}

$GLOBALS['WPML_REST_API'] = new WPML_REST_API();
$GLOBALS['WPML_REST_API']->wordpress_hooks();
