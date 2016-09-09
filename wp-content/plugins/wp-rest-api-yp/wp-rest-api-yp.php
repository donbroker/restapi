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
            'update_callback' => null,
            'schema'          => null, 
        )
    );

    register_rest_field('gd_place',
    	'is_favorite',
    	array(
    		'get_callback'    => 'yellowpage_get_is_favorite',
    		'update_callback' => null,
    		'schema'          => null,
    	)
    );

    register_rest_field('gd_place',
    	'gd_place_views',
    	array(
    		'get_callback'    => 'yellowpage_get_place_views',
    		'upate_callback'  => null,
    		'schema'          => null
    	)
    );

    register_rest_field('gd_place',
        'user_favorites',
        array(
            'get_callback'   => 'yellowpage_get_user_favorites',
            'update_callback'=> 'yellowpage_update_user_favorites',
            'schema'         => null,
        )
    );

    register_rest_field('gd_place',
        'plain_text_content',
        array(
            'get_callback'    => 'yellowpage_get_plain_text_content',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('gd_place',
        'reviews',
        array(
            'get_callback'    => 'yellowpage_get_reviews',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('gd_place',
        'featured_image_url',
        array(
            'get_callback'    => 'yellowpage_get_featured_image_url',
            'update_callback' => null,
            'schema'          => null
        )
    );

    register_rest_field('gd_place',
        'tag_names',
        array(
            'get_callback'    => 'yellowpage_get_tag_names',
            'update_callback' => null,
            'schema'          => null
        )
    );

    register_rest_field('gd_placecategory',
        'child',
        array(
            'get_callback'    => 'yellowpage_get_child_terms',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('gd_placecategory',
        'term_order',
        array(
            'get_callback'    => 'yellowpage_get_order',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('gd_placecategory',
        'icon_url',
        array(
            'get_callback'    => 'yellowpage_get_icon_url',
            'update_callback' => null,
            'schema'          => null,
        )
    );

    register_rest_field('gd_placecategory',
        'description',
        array(
            'get_callback'    => 'yellowpage_get_description',
            'update_callback' => null,
            'schema'          => null,
        )
    );

}

function yellowpage_get_tag_names( $object, $field_name, $request ) {
    $tag_ids = $object['gd_place_tags'];
    $tag_names = array();
    foreach ($tag_ids as $tag_id) {
        $tag_names[] = get_term($tag_id, 'gd_place_tags', OBJECT)->name;
    }
    return $tag_names;
}

function yellowpage_get_featured_image_url( $object, $field_name, $request ) {
    $featured_image_id =  $object['featured_media'];
    $featured_image_urls = wp_get_attachment_image_src($featured_image_id, 'app_image_1')[0];
    return $featured_image_urls;
}

function yellowpage_get_reviews( $object, $field_name, $request ) {
    $reviews = array();
    $comments_object = get_comments(
        array(
            'post_id'          => $object['id']
        )
    );
    foreach ($comments_object as $comment) {
        $comment_id                  = $comment->comment_ID;
        $review_object               = geodir_get_review($comment_id);
       
        $user_id                     = $review_object->user_id;
        $user                        = get_user_by('id', $user_id);
        $user_email                  = $user->user_email;

        $review                      = array();
        
        $review['review_id']         = $review_object->id;
        $review['review_time']       = human_time_diff( strtotime($review_object->post_date . ' GMT'), current_time('timestamp', true)). '前';
        $review['review_rating']     = $review_object->overall_rating;
        $review['review_content']    = strip_tags($comment->comment_content);
        $review['user_id']           = $user_id;
        $review['user_email']        = $user_email;
        $review['user_display_name'] = $user->display_name;
        $review['user_roles']        = $user->roles;
        $review['user_profile_url']  = get_gravatar($user_email);
        $review['rating_ip']         = $review_object->rating_ip;
        $review['comment_images']    = $review_object->image_images;

        $reviews[]                   = $review;
    }
    return $reviews;
}



function yellowpage_get_description( $object, $field_name, $request ) {
    return strip_tags(get_tax_meta($object['id'], 'ct_cat_top_desc',false, 'gd_place'));
}


function yellowpage_get_icon_url( $object, $field_name, $request ) {
    return get_tax_meta($object['id'], 'ct_cat_icon', false, 'gd_place')["src"];
}


function yellowpage_get_order( $object, $field_name, $request ) {
    return get_term($object['id'])->term_order;
}

function yellowpage_get_child_terms($object, $field_name, $request) {
    $child_terms_array = array();
    $child_terms_object = get_terms(
        array(
            'taxonomy'        => 'gd_placecategory',
            'hide_empty'      => false,
            'parent'          => $object['id'],
            'orderby'         => 'term_order',
        )
    );
    foreach ($child_terms_object as $term) {
        $term_id = $term->term_id;
        $term_array = array();
        $term_array['id']            = $term_id;
        $term_array['name']          = $term->name;
        $term_array['slug']          = $term->slug;
        $term_array['count']         = $term->count;
        $term_array['term_order']    = $term->order;
        $term_array['icon_url']      = get_tax_meta($term_id, 'ct_cat_icon', false, 'gd_place')["src"];
        $term_array['description']   = strip_tags(get_tax_meta($term_id, 'ct_cat_top_desc',false, 'gd_place'));
        $child_terms_array[]         = $term_array;
    }
    return $child_terms_array;
}

function yellowpage_get_favorites_count($object, $field_name, $request) {
	return get_favorites_count($object['id']);
}


function yellowpage_get_is_favorite($object, $field_name, $request) {
	$user = new SimpleFavorites\Entities\User\UserRepository;
    $user_id = get_current_user_id();
    return $user->isFavorite($object['id'], $site_id = 1, $user_id);
}

function yellowpage_get_place_views($object, $field_name, $request) {
    if (preg_match('/\/geodir\/v1\/places\/\d+/',print_r($request->get_route(), true))){
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
	return pvc_get_post_views($object['id']);
}

function yellowpage_get_user_favorites( $object, $field_name, $request ) {
    $simplified_user = array();
    $detailed_users = get_users_who_favorited_post($object['id']);
    foreach( $detailed_users as $detailed_user ) {
        $simplified_users[] = array(
            'id'            => $detailed_user->data->ID,
            'user_email'    => $detailed_user->data->user_email,
            'display_name'  => $detailed_user->data->display_name,
            'profile_url'   => get_gravatar($detailed_user->data->user_email)
        );
    }
    return $simplified_users;
}

function yellowpage_update_user_favorites( $value, $object, $field_name ) {
    if( isset($value) && $value === true ){
        $favorite    = new SimpleFavorites\Entities\Favorite\Favorite;
        $user        = new SimpleFavorites\Entities\User\UserRepository;
        $user_id     = get_current_user_id();
        $isFavorite  = $user->isFavorite($object->ID, $site_id = 1, $user_id)?'inactive':'active';
        $favorite->update($object->ID, $isFavorite, $site_id = 1);
    }
}


function yellowpage_get_plain_text_content( $object, $field_name, $request ) {
    global $post;
    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'));
    $finder = new DomXPath($dom);
    $content_object = $finder->query('//ul | //p | //img | //h1 | //h2 | //h3 | //h4 | //h5 | //h6 ');
    $length = $content_object->length;
    
    $content_array = array();

    for ($i = 0; $i < $length; $i++) {
        $element  = $content_object->item($i);
        $tag_name = $element->tagName;
        $text     = $element->textContent; 
        switch ($tag_name) {
            case 'img':
                $src = $element->attributes->getNamedItem('src')->nodeValue;
                $content_array[] = ['img'=>$src];
                break;
            case 'p':
                if(!empty(trim($text))){
                    $content_array[] = ['p'=>$text];
                }
                break;
            case 'h1':
                if(!empty(trim($text))){
                    $content_array[] = ['h1'=>$text];
                }
                break;
            case 'h2':
                if(!empty(trim($text))){
                    $content_array[] = ['h2'=>$text];
                }
                break;
            case 'h3':
                if(!empty(trim($text))){
                    $content_array[] = ['h3'=>$text];
                }
                break;
            case 'h4':
                if(!empty(trim($text))){
                    $content_array[] = ['h4'=>$text];
                }
                break;
            case 'h5':
                if(!empty(trim($text))){
                    $content_array[] = ['h5'=>$text];
                }
                break;
            case 'h6':
                if(!empty(trim($text))){
                    $content_array[] = ['h6'=>$text];
                }
                break;
            case 'ul':
                if(!empty(trim($text))){
                    $content_array[] = ['ul'=>$text];
                }
                break;
            default:
        }
    }
    return $content_array;
}