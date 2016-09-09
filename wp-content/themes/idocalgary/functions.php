<?php


if ( ! function_exists( 'idocalgary_setup' ) ) :

function idocalgary_setup() {

	add_image_size( 'app_image_1', 600, 336 );
	add_image_size( 'app_image_2', 400, 300 );
	add_image_size( 'app_image_3', 500, 400 );
	add_image_size( 'app_image_4', 600, 500 );

	load_theme_textdomain( 'idocalgary', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'title-tag' );

	add_theme_support( 'post-thumbnails' );

	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'idocalgary' ),
		'secondary' => esc_html__( 'Footer Menu', 'idocalgary' ),
	) );

	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	add_theme_support( 'custom-logo', array(
   'height'      => 45,
   'width'       => 250,
   'flex-width' => true,
	));
}
endif; 

add_action( 'after_setup_theme', 'idocalgary_setup' );

function idocalgary_scripts() {
	wp_enqueue_style( 'bootstrap', get_template_directory_uri().'/css/bootstrap.css' );	
	wp_enqueue_style( 'fontawesome', get_template_directory_uri().'/css/font-awesome.css' );
	wp_enqueue_style( 'googlefonts', '//fonts.googleapis.com/css?family=Roboto:400,300,700');
	wp_enqueue_style( 'style', get_stylesheet_uri() );
	
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.js', array(), '1.0.0', true );
	wp_enqueue_script( 'scripts', get_template_directory_uri() . '/js/script.js', array(), '1.0.0', true );


	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'idocalgary_scripts' );
add_action('geodir_before_listing_listview', 'add_some_code');
function add_some_code() {
	echo '<h1>hello world</h1>';
}


require get_template_directory() . '/inc/wp_bootstrap_navwalker.php';