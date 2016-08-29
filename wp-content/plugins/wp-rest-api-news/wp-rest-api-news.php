<?php
/**
 * WP REST API - News
 *
 * @package             REST_Api_News
 * @author              Fred Hong <fred@idomedia.ca>
 * @license             MIT
 *
 * @wordpress-plugin
 * Plugin Name:         WP REST API - News
 * Description:         Customize REST API for news
 * Version:             0.0.1
 * Author:              Fred Hong
 * Author URI:          https://fredhong.ca
 * License:             MIT
 */

add_image_size( 'app_image_1', 600, 336 );
add_image_size( 'app_image_2', 400, 300 );
add_image_size( 'app_image_3', 500, 400 );
add_image_size( 'app_image_4', 600, 500 );

add_action( 'rest_api_init', 'news_register_fields' );
function news_register_fields() {
    register_rest_field( 'post',
        'featured_image_url',
        array(
            'get_callback'    => 'news_get_featured_image_url',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field( 'post' , 
       	'news_source',
    	array(
    		'get_callback'    => 'news_get_news_source',
    		'update_callback' => null,
    		'schema'          => null,
    	)
    );

    register_rest_field('post',
    	'media_file_urls',
    	array(
    		'get_callback'    => 'news_get_media_file_urls',
    		'update_callback' => null,
    		'schema'          => null,
    	)
    );

    register_rest_field('post',
    	'plain_text_content',
    	array(
    		'get_callback'    => 'news_get_plain_text_content',
    		'update_callback' => null,
    		'schema'          => null,
    	)
    );


    register_rest_field('post',
        'is_favorite',
        array(
            'get_callback'    => 'news_get_is_favorite',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('post',
        'is_like',
        array(
            'get_callback'    => 'news_get_is_like',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('post',
        'is_dislike',
        array(
            'get_callback'    => 'news_get_is_dislike',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('post',
        'favorites_count',
        array(
            'get_callback'    => 'news_get_favorites_count',
            'update_callback' => null,
            'schema'          => null, 
        )
    );


    register_rest_field('post',
        'likes_count',
        array(
            'get_callback'    => 'news_get_likes_count',
            'update_callback' => null,
            'schema'          => null, 
        )
    );

    register_rest_field('post',
        'dislikes_count',
        array(
            'get_callback'    => 'news_get_dislikes_count',
            'update_callback' => null,
            'schema'          => null, 
        )
    );

    register_rest_field('post',
    	'user_favorites',
    	array(
    		'get_callback'   => 'news_get_user_favorites',
    		'update_callback'=> 'news_update_user_favorites',
    		'schema'         => null,
    	)
    );

    register_rest_field('post',
        'user_likes',
        array(
            'get_callback'   => 'news_get_user_likes',
            'update_callback'=> 'news_update_user_likes',
            'schema'         => null,
        )
    );

    register_rest_field('post',
        'user_dislikes',
        array(
            'get_callback'   => 'news_get_user_dislikes',
            'update_callback'=> 'news_update_user_dislikes',
            'schema'         => null,
        )
    );

    register_rest_field('post',
        'human_readable_time',
        array(
            'get_callback'     => 'news_get_human_readable_time',
            'update_callback'  => null,
            'schema'           => null,
        )
    );

    register_rest_field('post',
        'post_views',
        array(
        'get_callback'          => 'news_get_post_views',
        'update_callback'     => null,
        'schema'               => null,
        )
    );

}


function news_get_featured_image_url( $object, $field_name, $request ) {
	$image     = wp_get_attachment_image_src($object['featured_media'], 'app_image_1', true);
	$image_url = $image[0];
	return $image_url;
}

function news_get_news_source( $object, $field_name, $request ) {
    return get_post_meta($object['id'], 'news_source', 'true');
}

function news_get_media_file_urls( $object, $field_name, $request ) {
    $file_urls      = array();
    $files          = array();
    $files['image'] = get_attached_media('image', $object['id']);
    foreach ($files as $certain_type_files_name =>  $certain_type_files){
    	foreach ($certain_type_files as $certain_type_file) {
    		$file_urls["$certain_type_files_name"][] = $certain_type_file->guid;
    	}
    }
    return $file_urls;
}

function news_get_plain_text_content( $object, $field_name, $request ) {
    // return wp_strip_all_tags($object->content->rendered);
    global $post;
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'));
    $finder = new DomXPath($dom);
    
    $pimg = $finder->query('//p | //img');
    $length = $pimg->length;
    
    $content_array = array();

    for ($i = 0; $i < $length; $i++) {
        $element = $pimg->item($i);
        if ($element->tagName == 'img') {
            $src = $element->attributes->getNamedItem('src')->nodeValue;
            $content_array[] = ['img'=>$src];
        } else {
            $text =$element->textContent;
            if(!empty(trim($text))){
                $content_array[] = ['p'=>$text];
            }
        }
    }
    return $content_array;
}


function news_get_favorites_count( $object, $field_name, $request ) {
    return get_favorites_count($object['id']);
}

function news_get_likes_count( $object, $field_name, $request ) {
    return get_likes_count($object['id']);
}

function news_get_dislikes_count( $object, $field_name, $request ) {
    return get_dislikes_count($object['id']);
}

function news_get_is_favorite($object, $field_name, $request) {
    $user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isFavorite($object['id'], $site_id = 1, $user_id);
}

function news_get_is_like($object, $field_name, $request) {
    $user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isLike($object['id'], $site_id = 1, $user_id);
}

function news_get_is_dislike($object, $field_name, $request) {
    $user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isDislike($object['id'], $site_id = 1, $user_id);
}

function news_get_user_favorites( $object, $field_name, $request ) {
    $simplified_users = array();
    $detailed_users = get_users_who_favorited_post($object['id']);
    foreach ($detailed_users as $detailed_user) {
        $simplified_users[] = array(
            'id'            => $detailed_user->data->ID,
            'user_email'    => $detailed_user->data->user_email,
            'display_name'  => $detailed_user->data->display_name,
            'profile_url'   => get_gravatar($detailed_user->data->user_email)
        );    
    } 
    return $simplified_users;     
}

function news_get_user_likes( $object, $field_name, $request ) {
    $simplified_users = array();
    $detailed_users = get_users_who_liked_post($object['id']);
    foreach ($detailed_users as $detailed_user) {
        $simplified_users[] = array(
            'id'            => $detailed_user->data->ID,
            'user_email'    => $detailed_user->data->user_email,
            'display_name'  => $detailed_user->data->display_name,
            'profile_url'   => get_gravatar($detailed_user->data->user_email)
        );    
    } 
    return $simplified_users;   
}


function news_get_user_dislikes( $object, $field_name, $request ) {
    $simplified_users = array();
    $detailed_users = get_users_who_disliked_post($object['id']);
    foreach ($detailed_users as $detailed_user) {
        $simplified_users[] = array(
            'id'            => $detailed_user->data->ID,
            'user_email'    => $detailed_user->data->user_email,
            'display_name'  => $detailed_user->data->display_name,
            'profile_url'   => get_gravatar($detailed_user->data->user_email)
        );    
    } 
    return $simplified_users;   
}

function news_update_user_favorites($value, $object, $field_name){
    if( isset($value) && $value === true ){
        $favorite = new  SimpleFavorites\Entities\Favorite\Favorite;
        $user = new SimpleFavorites\Entities\User\UserRepository;
        $user_id = get_current_user_id();
        $isFavorite = $user->isFavorite($object->ID, $site_id = 1, $user_id)?'inactive':'active';
        $favorite->update($object->ID, $isFavorite, $site_id = 1);
    }
}

function news_update_user_likes($value, $object, $field_name){
    if( isset($value) && $value === true ){
        $favorite = new  SimpleFavorites\Entities\Like\Like;
        $user = new SimpleFavorites\Entities\User\UserRepository;
        $user_id = get_current_user_id();
        $isLike = $user->isLike($object->ID, $site_id = 1, $user_id)?'inactive':'active';
        $favorite->update($object->ID, $isLike, $site_id = 1);
    }
}

function news_update_user_dislikes($value, $object, $field_name){
    if( isset($value) && $value === true ){
        $favorite = new  SimpleFavorites\Entities\Dislike\Dislike;
        $user = new SimpleFavorites\Entities\User\UserRepository;
        $user_id = get_current_user_id();
        $isDislike = $user->isDislike($object->ID, $site_id = 1, $user_id)?'inactive':'active';
        $favorite->update($object->ID, $isDislike, $site_id = 1);
    }
}


function news_get_human_readable_time( $object, $field_name, $request ) {
    return human_time_diff(get_post_time('U', true), current_time('timestamp', true)) . '前';  
}

function news_get_post_views( $object, $field_name, $request ) {
    if (preg_match('/\/wp\/v2\/posts\/\d+/',print_r($request->get_route(), true))){
        global $wpdb;
        $post_id = $object['id'];
        $count = pvc_get_post_views($post_id) + 1;
        $wpdb->query(
            $wpdb->prepare( "
                INSERT INTO " . $wpdb->prefix . "post_views (id, type, period, count)
                VALUES (%d, %d, %s, %d)
                ON DUPLICATE KEY UPDATE count = %d", $post_id, 4, 'total', $count, $count
            )
        );
    }
    return pvc_get_post_views($post_id);
}

