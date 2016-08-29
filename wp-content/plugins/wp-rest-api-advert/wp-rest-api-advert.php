<?php
/**
 * WP REST API - Advert
 *
 * @package             REST_Api_Advert
 * @author              Fred Hong <fred@idomedia.ca>
 * @license             MIT
 *
 * @wordpress-plugin
 * Plugin Name:         WP REST API - Advert
 * Description:         Customize REST API for news
 * Version:             0.0.1
 * Author:              Fred Hong
 * Author URI:          https://fredhong.ca
 * License:             MIT
 */

add_filter( 'register_post_type_args', 'add_show_in_rest' , 11, 2 );

function add_show_in_rest( $args, $post_type ){
  	if($post_type === 'advert'){
	  if(!array_key_exists ( 'show_in_rest' , $args ) || ( $args['show_in_rest'] === false )){
	  	$args['show_in_rest'] = true; 
	  }
	}
  	return $args;
}

add_action( 'rest_api_init', 'advert_register_fields' );
function advert_register_fields() {
    // register_rest_field('advert',
    //     'favorites_count',
    //     array(
    //         'get_callback'    => 'advert_get_favorites_count',
    //         'update_callback' => 'advert_update_favorites_count',
    //         'schema'          => null, 
    //     )
    // );

    // register_rest_field('advert',
    // 	'is_favorite',
    // 	array(
    // 		'get_callback'    => 'advert_get_is_favorite',
    // 		'update_callback' => 'advert_update_is_favorite',
    // 		'schema'          => null,
    // 	)
    // );

    register_rest_field('advert',
    	'advert_views',
    	array(
    		'get_callback'    => 'advert_get_views',
    		'upate_callback'  => 'advert_update_views',
    		'schema'          => null
    	)
    );

    register_rest_field('advert',
        'advert_data',
        array(
            'get_callback'    => 'advert_get_advert_data',
            'update_callback' => 'advert_update_advert_data',
            'schema'          => null
        )
    );
}

function advert_get_favorites_count($object, $field_name, $request) {
	return 1;
}

function advert_update_favorites_count($value, $object, $field_name) {
	return true;
}

function advert_get_is_favorite($object, $field_name, $request) {
	$user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isFavorite($object['id'], $site_id = 1, $user_id);
}

function advert_update_is_favorite($value, $object, $field_name) {
	return true;
}

function advert_get_views($object, $field_name, $request) {
	return pvc_get_post_views($object['id']);
}

function advert_update_views($value, $object, $field_name) {
	return true;
}

function advert_get_advert_data($object, $field_name, $request){
    $advert_data = array();
    $advert_id = $object['id'];
    if($object['type'] === 'advert'){
        $advert_data['advert_category'] = get_post_meta($advert_id, 'advert_category', true);
        $advert_data['adverts_person'] = get_post_meta($advert_id, 'adverts_person', true);
        $advert_data['adverts_email'] = get_post_meta($advert_id, 'adverts_email', true);
        $advert_data['adverts_phone'] = get_post_meta($advert_id, 'adverts_phone', true);
        $advert_data['adverts_price'] = get_post_meta($advert_id, 'adverts_price', true);
        $advert_data['adverts_location'] = get_post_meta($advert_id, 'adverts_location', true);
    }
    return $advert_data;
}

function advert_update_advert_data($value, $object, $field_name){
    return true;
}