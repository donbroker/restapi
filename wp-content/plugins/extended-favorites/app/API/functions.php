<?php
/**
* Primary plugin API functions
*/
use SimpleFavorites\Entities\Favorite\FavoriteButton;
use SimpleFavorites\Entities\Like\LikeButton;
use SimpleFavorites\Entities\Dislike\DislikeButton;
use SimpleFavorites\Entities\Post\FavoriteCount;
use SimpleFavorites\Entities\Post\LikeCount;
use SimpleFavorites\Entities\Post\DislikeCount;
use SimpleFavorites\Entities\User\UserFavorites;
use SimpleFavorites\Entities\User\UserLikes;
use SimpleFavorites\Entities\User\UserDislikes;
use SimpleFavorites\Entities\Post\PostFavorites;
use SimpleFavorites\Entities\Post\PostLikes;
use SimpleFavorites\Entities\Post\PostDislikes;
use SimpleFavorites\Entities\Favorite\ClearFavoritesButton;
use SimpleFavorites\Entities\Like\ClearLikesButton;
use SimpleFavorites\Entities\Dislike\ClearDislikesButton;


/**
* Get the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_favorites_button($post_id = null, $site_id = null)
{
	global $blog_id;
	if ( !$post_id ) $post_id = get_the_id();
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$button = new FavoriteButton($post_id, $site_id);
	return $button->display();
}


/**
* Get the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_likes_button($post_id = null, $site_id = null)
{
	global $blog_id;
	if ( !$post_id ) $post_id = get_the_id();
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$button = new LikeButton($post_id, $site_id);
	return $button->display();
}


/**
* Get the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_dislikes_button($post_id = null, $site_id = null)
{
	global $blog_id;
	if ( !$post_id ) $post_id = get_the_id();
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$button = new DislikeButton($post_id, $site_id);
	return $button->display();
}


/**
* Echos the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_favorites_button($post_id = null, $site_id = null)
{	
	echo get_favorites_button($post_id, $site_id);
}

/**
* Echos the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_likes_button($post_id = null, $site_id = null)
{	
	echo get_likes_button($post_id, $site_id);
}


/**
* Echos the favorite button
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_dislikes_button($post_id = null, $site_id = null)
{	
	echo get_dislikes_button($post_id, $site_id);
}


/**
* Get the Favorite Total Count for a Post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_favorites_count($post_id = null, $site_id = null)
{
	if ( !$post_id ) $post_id = get_the_id();
	$count = new FavoriteCount();
	return $count->getCount($post_id, $site_id);
}


/**
* Get the Favorite Total Count for a Post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_likes_count($post_id = null, $site_id = null)
{
	if ( !$post_id ) $post_id = get_the_id();
	$count = new LikeCount();
	return $count->getCount($post_id, $site_id);
}


/**
* Get the Favorite Total Count for a Post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function get_dislikes_count($post_id = null, $site_id = null)
{
	if ( !$post_id ) $post_id = get_the_id();
	$count = new DislikeCount();
	return $count->getCount($post_id, $site_id);
}

/**
* Echo the Favorite Count
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_favorites_count($post_id = null, $site_id = null)
{
	echo get_favorites_count($post_id, $site_id);
}


/**
* Echo the Favorite Count
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_likes_count($post_id = null, $site_id = null)
{
	echo get_likes_count($post_id, $site_id);
}


/**
* Echo the Favorite Count
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return string
*/
function the_dislikes_count($post_id = null, $site_id = null)
{
	echo get_dislikes_count($post_id, $site_id);
}


/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function get_user_favorites($user_id = null, $site_id = null, $filters = null)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$favorites = new UserFavorites($user_id, $site_id, $links = false, $filters);
	return $favorites->getFavoritesArray();
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function get_user_likes($user_id = null, $site_id = null, $filters = null)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$likes = new UserLikes($user_id, $site_id, $links = false, $filters);
	return $likes->getLikesArray();
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return array
*/
function get_user_dislikes($user_id = null, $site_id = null, $filters = null)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$likes = new UserDislikes($user_id, $site_id, $links = false, $filters);
	return $likes->getDislikesArray();
}

/**
* HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function get_user_favorites_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$favorites = new UserFavorites($user_id, $site_id, $include_links, $filters);
	return $favorites->getFavoritesList($include_button);
}

/**
* HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function get_user_likes_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$likes = new UserLikes($user_id, $site_id, $include_links, $filters);
	return $likes->getLikesList($include_button);
}

/**
* HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function get_user_dislikes_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	global $blog_id;
	$site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
	if ( !is_multisite() ) $site_id = 1;
	$dislikes = new UserDislikes($user_id, $site_id, $include_links, $filters);
	return $dislikes->getDislikesList($include_button);
}


/**
* Echo HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function the_user_favorites_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	echo get_user_favorites_list($user_id, $site_id, $include_links, $filters, $include_button);
}

/**
* Echo HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function the_user_likes_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	echo get_user_likes_list($user_id, $site_id, $include_links, $filters, $include_button);
}


/**
* Echo HTML List of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $include_button boolean, whether to include the favorite button for each item
* @return string
*/
function the_user_dislikes_list($user_id = null, $site_id = null, $include_links = false, $filters = null, $include_button = false)
{
	echo get_user_dislikes_list($user_id, $site_id, $include_links, $filters, $include_button);
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $html boolean, whether to output html (important for AJAX updates). If false, an integer is returned
* @return int
*/
function get_user_favorites_count($user_id = null, $site_id = null, $filters = null, $html = false)
{
	$favorites = get_user_favorites($user_id, $site_id, $filters);
	$posttypes = ( isset($filters['post_type']) ) ? implode(',', $filters['post_type']) : 'all';
	$out = "";
	if ( !$site_id ) $site_id = 1;
	if ( $html ) $out .= '<span class="simplefavorites-user-count" data-posttypes="' . $posttypes . '" data-siteid="' . $site_id . '">';
	$out .= count($favorites);
	if ( $html ) $out .= '</span>';
	return $out;
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $html boolean, whether to output html (important for AJAX updates). If false, an integer is returned
* @return int
*/
function get_user_likes_count($user_id = null, $site_id = null, $filters = null, $html = false)
{
	$likes = get_user_likes($user_id, $site_id, $filters);
	$posttypes = ( isset($filters['post_type']) ) ? implode(',', $filters['post_type']) : 'all';
	$out = "";
	if ( !$site_id ) $site_id = 1;
	if ( $html ) $out .= '<span class="likes-user-count" data-posttypes="' . $posttypes . '" data-siteid="' . $site_id . '">';
	$out .= count($likes);
	if ( $html ) $out .= '</span>';
	return $out;
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @param $html boolean, whether to output html (important for AJAX updates). If false, an integer is returned
* @return int
*/
function get_user_dislikes_count($user_id = null, $site_id = null, $filters = null, $html = false)
{
	$dislikes = get_user_dislikes($user_id, $site_id, $filters);
	$posttypes = ( isset($filters['post_type']) ) ? implode(',', $filters['post_type']) : 'all';
	$out = "";
	if ( !$site_id ) $site_id = 1;
	if ( $html ) $out .= '<span class="dislikes-user-count" data-posttypes="' . $posttypes . '" data-siteid="' . $site_id . '">';
	$out .= count($dislikes);
	if ( $html ) $out .= '</span>';
	return $out;
}


/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return string
*/
function the_user_favorites_count($user_id = null, $site_id = null, $filters = null)
{
	echo get_user_favorites_count($user_id, $site_id, $filters);
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return string
*/
function the_user_likes_count($user_id = null, $site_id = null, $filters = null)
{
	echo get_user_likes_count($user_id, $site_id, $filters);
}

/**
* Get an array of User Favorites
* @param $user_id int, defaults to current user
* @param $site_id int, defaults to current blog/site
* @param $filters array of post types/taxonomies
* @return string
*/
function the_user_dislikes_count($user_id = null, $site_id = null, $filters = null)
{
	echo get_user_dislikes_count($user_id, $site_id, $filters);
}


/**
* Get an array of users who have favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return array of user objects
*/
function get_users_who_favorited_post($post_id = null, $site_id = null)
{
	$users = new PostFavorites($post_id, $site_id);
	return $users->getUsers();
}

/**
* Get an array of users who have favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return array of user objects
*/
function get_users_who_liked_post($post_id = null, $site_id = null)
{
	$users = new PostLikes($post_id, $site_id);
	return $users->getUsers();
}

/**
* Get an array of users who have favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @return array of user objects
*/
function get_users_who_disliked_post($post_id = null, $site_id = null)
{
	$users = new PostDislikes($post_id, $site_id);
	return $users->getUsers();
}


/**
* Get a list of users who favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $separator string, custom separator between items (defaults to HTML list)
* @param $include_anonmyous boolean, whether to include anonmyous users
* @param $anonymous_label string, label for anonymous user count
* @param $anonymous_label_single string, singular label for anonymous user count
*/
function the_users_who_favorited_post($post_id = null, $site_id = null, $separator = 'list', $include_anonymous = true, $anonymous_label = 'Anonymous Users', $anonymous_label_single = 'Anonymous User')
{
	$users = new PostFavorites($post_id, $site_id);
	echo $users->userList($separator, $include_anonymous, $anonymous_label, $anonymous_label_single);
}

/**
* Get a list of users who favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $separator string, custom separator between items (defaults to HTML list)
* @param $include_anonmyous boolean, whether to include anonmyous users
* @param $anonymous_label string, label for anonymous user count
* @param $anonymous_label_single string, singular label for anonymous user count
*/
function the_users_who_liked_post($post_id = null, $site_id = null, $separator = 'list', $include_anonymous = true, $anonymous_label = 'Anonymous Users', $anonymous_label_single = 'Anonymous User')
{
	$users = new PostLikes($post_id, $site_id);
	echo $users->userList($separator, $include_anonymous, $anonymous_label, $anonymous_label_single);
}

/**
* Get a list of users who favorited a post
* @param $post_id int, defaults to current post
* @param $site_id int, defaults to current blog/site
* @param $separator string, custom separator between items (defaults to HTML list)
* @param $include_anonmyous boolean, whether to include anonmyous users
* @param $anonymous_label string, label for anonymous user count
* @param $anonymous_label_single string, singular label for anonymous user count
*/
function the_users_who_disliked_post($post_id = null, $site_id = null, $separator = 'list', $include_anonymous = true, $anonymous_label = 'Anonymous Users', $anonymous_label_single = 'Anonymous User')
{
	$users = new PostDislikes($post_id, $site_id);
	echo $users->userList($separator, $include_anonymous, $anonymous_label, $anonymous_label_single);
}


/**
* Get the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function get_clear_favorites_button($site_id = null, $text = null)
{
	$button = new ClearFavoritesButton($site_id, $text);
	return $button->display();
}

/**
* Get the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function get_clear_likes_button($site_id = null, $text = null)
{
	$button = new ClearLikesButton($site_id, $text);
	return $button->display();
}

/**
* Get the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function get_clear_dislikes_button($site_id = null, $text = null)
{
	$button = new ClearDislikesButton($site_id, $text);
	return $button->display();
}


/**
* Print the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function the_clear_favorites_button($site_id = null, $text = null)
{
	echo get_clear_favorites_button($site_id, $text);
}


/**
* Print the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function the_clear_likes_button($site_id = null, $text = null)
{
	echo get_clear_likes_button($site_id, $text);
}

/**
* Print the clear favorites button
* @param $site_id int, defaults to current blog/site
* @param $text string, button text - defaults to site setting
* @return string
*/
function the_clear_dislikes_button($site_id = null, $text = null)
{
	echo get_clear_dislikes_button($site_id, $text);
}

/*
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source https://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
        $url = '<img src="' . $url . '"';
        foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}