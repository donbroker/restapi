<?php
/*
Plugin Name: List All Post Type
Plugin URI: http://idomedia.ca
Description: List All Registered Post Types
Version: 0.0.1
Author: Fred Hong
Author URI: http://fredhong.ca
License: MIT
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_filter( 'register_post_type_args', 'add_show_in_rest' , 11, 2 );

function add_show_in_rest( $args, $post_type ){

  if(!array_key_exists ( 'show_in_rest' , $args ) || ( $args['show_in_rest'] === false )){
  	$args['show_in_rest'] = true; 
  }
  return $args;
}

add_action('admin_menu', 'list_all_post_type_setup_menu');
 
function list_all_post_type_setup_menu(){
        add_menu_page( 'Post Types Page', 'Post Types', 'manage_options', 'list-post-types', 'list_post_types' );
}

function list_post_types(){

	$post_types = get_post_types( array(), 'objects' );

	foreach ( $post_types  as $post_type ) {

   		echo '<pre>' . var_dump($post_type) . '</pre>';
	}
}

add_action('admin_menu', 'rest_api_test_menu');
 
function rest_api_test_menu(){
      add_submenu_page ( 'list-post-types', 'Rest API Test', 'Rest API Test', 'manage_options', 'rest-api-test', 'rest_api_test' );
}

function rest_api_test(){
      $args = array(
      
      );
      $url = add_query_arg($args, rest_url('wp/v2/posts/1'));
      $get_response = wp_remote_get($url, array( 'timeout' => 120, 'httpversion' => '1.1' ));
      if ( is_wp_error( $get_response ) ) { 
        echo sprintf( 'Your URL %1s could not be retrieved', $url ); 
      } else {
        $body = wp_remote_retrieve_body( $get_response );
        $data_array = json_decode($body, true);
        echo '<br>++++++++++++++++++++++++++++++<br>';
        echo $url;
        // var_dump($body);
        // var_dump($data_array);
        echo '<br>++++++++++++++++++++++++++++++<br>';
        echo '<pre>';
        print_r($data_array);
        echo '</pre>';

        echo '<br>----------------------------------<br>';
        $post_url = rest_url('wp/v2/posts');
        $headers  = array(
          'Authorization' => 'Basic ' . base64_encode( 'fred' . ':' . 'Pembina!1483')
        );

        $data_array_post = array();
        $data_array_post['title'] = $data_array['title']['rendered'];
        var_dump($data_array_post);
        
        $post_response = wp_remote_post($post_url, array(
            'method'       => 'POST',
            'headers'      => $headers,
            'body'         => array('title' => 'Hello Gaia'),
            'timeout'      => 120
            )
        );

        //Checking if error occurred.  
        if ( is_wp_error( $post_response ) ) {         
          $error_message = $post_response->get_error_message();   
          echo sprintf(  '<p>Error: %1s</p>', $error_message );   
        } else {   
          echo 'Response:<pre>';   
          print_r( $post_response );   
          echo '</pre>';   
        }
      }
}