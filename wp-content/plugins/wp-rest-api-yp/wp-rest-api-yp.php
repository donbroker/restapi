<?php
/**
 * WP REST API - Yellowpage
 *
 * @package             REST_Api_Yellowpage
 * @author              Fred Hong <fred@idomedia.ca>
 * @license             MIT
 *
 * @wordpress-plugin
 * Plugin Name:         WP REST API - Yellowpage
 * Description:         Customize REST API for news
 * Version:             0.0.1
 * Author:              Fred Hong
 * Author URI:          https://fredhong.ca
 * License:             MIT
 */

add_action( 'rest_api_init', 'yellowpage_register_fields' );
function yellowpage_register_fields() {
    register_rest_field('gd_place',
        'favorites_count',
        array(
            'get_callback'    => 'yellowpage_get_favorites_count',
            'update_callback' => 'yellowpage_update_favorites_count',
            'schema'          => null, 
        )
    );

    register_rest_field('gd_place',
    	'is_favorite',
    	array(
    		'get_callback'    => 'yellowpage_get_is_favorite',
    		'update_callback' => 'yellowpage_update_is_favorite',
    		'schema'          => null,
    	)
    );

    register_rest_field('gd_place',
    	'gd_place_views',
    	array(
    		'get_callback'    => 'yellowpage_get_views',
    		'upate_callback'  => 'yellowpage_update_views',
    		'schema'          => null
    	)
    );

    register_rest_field('gd_place',
        'user_favorites',
        array(
            'get_callback'   => 'news_get_user_favorites',
            'update_callback'=> null,
            'schema'         => null,
        )
    );

    register_rest_field('gd_place',
        'yp_test_field',
        array(
            'get_callback'      => 'get_yp_test_field',
            'update_callback'   => 'update_yp_test_field',
            'schema'            => null
        )
    );
}


function yellowpage_get_favorites_count($object, $field_name, $request) {
	return 1;
}

function yellowpage_update_favorites_count($value, $object, $field_name) {
	return true;
}

function yellowpage_get_is_favorite($object, $field_name, $request) {
	$user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isFavorite($object['id'], $site_id = 1, $user_id);
}

function yellowpage_update_is_favorite($object, $field_name, $request) {
	return true;
}

function yellowpage_get_views($object, $field_name, $request) {
	return pvc_get_post_views($object['id']);
}

function yellowpage_update_views($object, $field_name, $request) {
	return true;
}