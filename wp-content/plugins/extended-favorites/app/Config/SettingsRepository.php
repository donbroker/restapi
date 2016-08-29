<?php 

namespace SimpleFavorites\Config;

use SimpleFavorites\Helpers;

class SettingsRepository 
{

	/**
	* Output Dependency
	* @return boolean
	* @param string - css/js
	* @since 1.0
	*/
	public function outputDependency($dependency = 'css')
	{
		$option = get_option('simplefavorites_dependencies');
		return ( isset($option[$dependency]) && $option[$dependency] == 'true' ) ? true : false;
	}

	/**
	* Anonymous Display Options
	* @param string option (array key)
	* @since 1.0
	* @return boolean
	*/
	public function anonymous($option = 'display')
	{
		$anon_option = get_option('simplefavorites_users');
		if ( isset($anon_option['anonymous'][$option]) 
			&& $anon_option['anonymous'][$option] == 'true') {
			return true;
		}
		return false;
	}

	/**
	* Method of saving favorites for anonymous users
	* @return string - cookie/session
	*/
	public function saveType()
	{
		$option = get_option('simplefavorites_users');
		if ( !isset($option['anonymous']['saveas']) ) return 'cookie';
		return $option['anonymous']['saveas'];
	}

	/**
	* Display in a given Post Type?
	* @param string - post type name
	*/
	public function displayInPostType($posttype)
	{
		$types = get_option('simplefavorites_display');
		if ( !empty($types['posttypes']) && $types !== "" ){
			foreach ( $types['posttypes'] as $key => $type ){
				if ( $key == $posttype && isset($type['display']) && $type['display'] == 'true' ) return $type;
			}
		}
		return false;
	}

	/**
	* Favorite Button Text
	* @return string
	*/
	public function buttonText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['buttontext']) || $option['buttontext'] == "" ) 
			return __('Favorite', 'simplefavorites');
		return esc_html($option['buttontext']);
	}

	/**
	* Favorite Button Text
	* @return string
	*/
	public function likeButtonText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['likebuttontext']) || $option['likebuttontext'] == "" ) 
			return __('Like', 'simplefavorites');
		return esc_html($option['likebuttontext']);
	}

	/**
	* Favorite Button Text
	* @return string
	*/
	public function dislikeButtonText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['dislikebuttontext']) || $option['dislikebuttontext'] == "" ) 
			return __('Dislike', 'simplefavorites');
		return esc_html($option['dislikebuttontext']);
	}

	/**
	* Favorite Button Text (Active state)
	* @return string
	*/
	public function buttonTextFavorited()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['buttontextfavorited']) || $option['buttontextfavorited'] == "" ) 
			return __('Favorited', 'simplefavorites');
		return esc_html($option['buttontextfavorited']);
	}

	/**
	* Favorite Button Text (Active state)
	* @return string
	*/
	public function buttonTextLiked()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['buttontextliked']) || $option['buttontextliked'] == "" ) 
			return __('Liked', 'simplefavorites');
		return esc_html($option['buttontextliked']);
	}


	/**
	* Favorite Button Text (Active state)
	* @return string
	*/
	public function buttonTextDisliked()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['buttontextdisliked']) || $option['buttontextdisliked'] == "" ) 
			return __('Disliked', 'simplefavorites');
		return esc_html($option['buttontextdisliked']);
	}


	/**
	* Clear Favorites Button Text
	* @return string
	*/
	public function clearFavoritesText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['clearfavorites']) || $option['clearfavorites'] == "" ) 
			return __('Clear Favorites', 'simplefavorites');
		return esc_html($option['clearfavorites']);
	}


	/**
	* Clear Favorites Button Text
	* @return string
	*/
	public function clearLikesText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['clearlikes']) || $option['clearlikes'] == "" ) 
			return __('Clear Likes', 'simplefavorites');
		return esc_html($option['clearlikes']);
	}


	/**
	* Clear Favorites Button Text
	* @return string
	*/
	public function clearDislikesText()
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['cleardislikes']) || $option['cleardislikes'] == "" ) 
			return __('Clear Dislikes', 'simplefavorites');
		return esc_html($option['cleardislikes']);
	}

	/**
	* Post Types to show meta box on
	* @return array
	*/
	public function metaEnabled()
	{
		$posttypes = array();
		$types = get_option('simplefavorites_display');
		if ( !isset($types['posttypes']) || $types['posttypes'] == "" ) return $posttypes;
		foreach ( $types['posttypes'] as $key => $type ){
			if ( isset($type['postmeta']) && $type['postmeta'] == 'true' ) array_push($posttypes, $key);
		}
		return $posttypes;
	}

	/**
	* Does the button get the favorite count
	* @return boolean
	* @since 1.1.1
	*/
	public function includeCountInButton()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['buttoncount']) && $option['buttoncount'] == "true" ) ? true : false;
	}


	/**
	* Does the button get the favorite count
	* @return boolean
	* @since 1.1.1
	*/
	public function includeCountInLikeButton()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['likebuttoncount']) && $option['likebuttoncount'] == "true" ) ? true : false;
	}


	/**
	* Does the button get the favorite count
	* @return boolean
	* @since 1.1.1
	*/
	public function includeCountInDislikeButton()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['dislikebuttoncount']) && $option['dilikedbuttoncount'] == "true" ) ? true : false;
	}
	/**
	* Does the button get loading indication?
	* @return boolean
	* @since 1.1.1
	*/
	public function includeLoadingIndicator()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['loadingindicator']['include']) && $option['loadingindicator']['include'] == "true" ) ? true : false;
	}

	/**
	* Does the button get loading indication on page load?
	* @return boolean
	* @since 1.1.3
	*/
	public function includeLoadingIndicatorPreload()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['loadingindicator']['include_preload']) && $option['loadingindicator']['include_preload'] == "true" ) ? true : false;
	}

	/**
	* Loading Text
	* @return string
	* @since 1.1.1
	*/
	public function loadingText()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['loadingindicator']['text']) ) ? esc_html($option['loadingindicator']['text']) : __('Loading', 'simplefavorites');
	}

	/**
	* Loading Image
	* @return string
	* @param $state string
	* @uses simplefavorites_spinner_url filter, simplefavorites_spinner_url_active filter
	* @since 1.1.1
	*/
	public function loadingImage($state = 'inactive')
	{
		$option = get_option('simplefavorites_display');
		if ( !isset($option['loadingindicator']['include_image']) || $option['loadingindicator']['include_image'] !== 'true' ) return false;
		$image_url = Helpers::plugin_url() . '/assets/images/loading.gif';
		
		if ( $state == 'inactive' ){
			$image = '<img src="' . apply_filters('simplefavorites_spinner_url', $image_url) . '" class="simplefavorites-loading" aria-hidden="true" />';
			return $image;
		}
		
		// active state (some users might want different color for active)
		$image = '<img src="' . apply_filters('simplefavorites_spinner_url_active', $image_url) . '" class="simplefavorites-loading" aria-hidden="true" />';
		return $image;
	}

	/**
	* Get text to display in lists if no favorites are saved
	* @return string
	* @since 1.2
	*/
	public function noFavoritesText()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['nofavorites']) && $option['nofavorites'] !== "" ) ? $option['nofavorites'] : __('No Favorites', 'simplefavorites');
	}


	/**
	* Get text to display in lists if no favorites are saved
	* @return string
	* @since 1.2
	*/
	public function noLikesText()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['nolikes']) && $option['nolikes'] !== "" ) ? $option['nolikes'] : __('No Likes', 'simplefavorites');
    }

	
	/**
	* Get text to display in lists if no favorites are saved
	* @return string
	* @since 1.2
	*/
	public function noDislikesText()
	{
		$option = get_option('simplefavorites_display');
		return ( isset($option['nodislikes']) && $option['nodislikes'] !== "" ) ? $option['nodislikes'] : __('No Dislikes', 'simplefavorites');
	}
}