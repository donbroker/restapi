<?php 

namespace SimpleFavorites\Entities\User;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Helpers;
use SimpleFavorites\Entities\Favorite\FavoritesArrayFormatter;
use SimpleFavorites\Entities\Like\LikesArrayFormatter;
use SimpleFavorites\Entities\Dislike\DislikesArrayFormatter;

class UserRepository 
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
	}

	/**
	* Display button for current user
	* @return boolean
	*/
	public function getsButton()
	{
		if ( is_user_logged_in() ) return true;
		if ( $this->settings_repo->anonymous('display') ) return true;
		return false;
	}

	/**
	* Get All of current user's favorites (includes all sites)
	* @return array (multidimensional)
	*/
	public function getAllFavorites()
	{
		if ( is_user_logged_in() ) return $this->getLoggedInFavorites();
		$saveType = $this->settings_repo->saveType();
		$favorites = ( $saveType == 'cookie' ) ? $this->getCookieFavorites() : $this->getSessionFavorites();
		return $this->favoritesWithSiteID($favorites);
	}

	/**
	* Get All of current user's favorites (includes all sites)
	* @return array (multidimensional)
	*/
	public function getAllLikes()
	{
		if ( is_user_logged_in() ) return $this->getLoggedInLikes();
		$saveType = $this->settings_repo->saveType();
		$likes = ( $saveType == 'cookie' ) ? $this->getCookieLikes() : $this->getSessionLikes();
		return $this->likesWithSiteID($likes);
	}

	/**
	* Get All of current user's favorites (includes all sites)
	* @return array (multidimensional)
	*/
	public function getAllDislikes()
	{
		if ( is_user_logged_in() ) return $this->getLoggedInDislikes();
		$saveType = $this->settings_repo->saveType();
		$dislikes = ( $saveType == 'cookie' ) ? $this->getCookieDislikes() : $this->getSessionDislikes();
		return $this->dislikesWithSiteID($dislikes);
	}

	/**
	* Get User's Favorites by Site ID (includes a single site)
	* @return array (flat)
	*/
	public function getFavorites($user_id = null, $site_id = null)
	{
		if ( is_user_logged_in() || $user_id ) return $this->getLoggedInFavorites($user_id, $site_id);
		$saveType = $this->settings_repo->saveType();
		$favorites = ( $saveType == 'cookie' ) ? $this->getCookieFavorites($site_id) : $this->getSessionFavorites($site_id);
		return $favorites;
	}

	/**
	* Get User's Favorites by Site ID (includes a single site)
	* @return array (flat)
	*/
	public function getLikes($user_id = null, $site_id = null)
	{
		if ( is_user_logged_in() || $user_id ) return $this->getLoggedInLikes($user_id, $site_id);
		$saveType = $this->settings_repo->saveType();
		$likes = ( $saveType == 'cookie' ) ? $this->getCookieLikes($site_id) : $this->getSessionLikes($site_id);
		return $likes;
	}

	/**
	* Get User's Favorites by Site ID (includes a single site)
	* @return array (flat)
	*/
	public function getDislikes($user_id = null, $site_id = null)
	{
		if ( is_user_logged_in() || $user_id ) return $this->getLoggedInDislikes($user_id, $site_id);
		$saveType = $this->settings_repo->saveType();
		$dislikes = ( $saveType == 'cookie' ) ? $this->getCookieDislikes($site_id) : $this->getSessionDislikes($site_id);
		return $dislikes;
	}

	/**
	* Check for Site ID in user's favorites
	* Multisite Compatibility for >1.1
	* 1.2 compatibility with new naming structure
	* @since 1.1
	*/
	private function favoritesWithSiteID($favorites)
	{
		if ( Helpers::keyExists('site_favorites', $favorites) ){
			foreach($favorites as $key => $site_favorites){
				if ( !isset($favorites[$key]['site_favorites']) ) continue;
				$favorites[$key]['posts'] = $favorites[$key]['site_favorites'];
				unset($favorites[$key]['site_favorites']);
				if ( isset($favorites[$key]['total']) ) unset($favorites[$key]['total']);
			}
		}
		if ( Helpers::keyExists('site_id', $favorites) ) return $favorites;
		$new_favorites = array(
			array(
				'site_id' => 1,
				'posts' => $favorites
			)
		);
		return $new_favorites;
	}

	/**
	* Check for Site ID in user's favorites
	* Multisite Compatibility for >1.1
	* 1.2 compatibility with new naming structure
	* @since 1.1
	*/
	private function likesWithSiteID($likes)
	{
		if ( Helpers::keyExists('site_likes', $likes) ){
			foreach($likes as $key => $site_likes){
				if ( !isset($likes[$key]['site_likes']) ) continue;
				$likes[$key]['posts'] = $likes[$key]['site_likes'];
				unset($likes[$key]['site_likes']);
				if ( isset($likes[$key]['total']) ) unset($likes[$key]['total']);
			}
		}
		if ( Helpers::keyExists('site_id', $likes) ) return $likes;
		$new_likes = array(
			array(
				'site_id' => 1,
				'posts' => $likes
			)
		);
		return $new_likes;
	}

	/**
	* Check for Site ID in user's favorites
	* Multisite Compatibility for >1.1
	* 1.2 compatibility with new naming structure
	* @since 1.1
	*/
	private function dislikesWithSiteID($dislikes)
	{
		if ( Helpers::keyExists('site_dislikes', $dislikes) ){
			foreach($dislikes as $key => $site_dislikes){
				if ( !isset($likes[$key]['site_dislikes']) ) continue;
				$dislikes[$key]['posts'] = $dislikes[$key]['site_dislikes'];
				unset($dislikes[$key]['site_dislikes']);
				if ( isset($dislikes[$key]['total']) ) unset($dislikes[$key]['total']);
			}
		}
		if ( Helpers::keyExists('site_id', $dislikes) ) return $dislikes;
		$new_dislikes = array(
			array(
				'site_id' => 1,
				'posts' => $dislikes
			)
		);
		return $new_dislikes;
	}

	/**
	* Get Logged In User Favorites
	*/
	private function getLoggedInFavorites($user_id = null, $site_id = null)
	{
		$user_id = ( isset($user_id) ) ? $user_id : get_current_user_id();
		$favorites = get_user_meta($user_id, 'simplefavorites');
		
		if ( empty($favorites) ) return array(array('site_id'=>1, 'posts' => array()));
		
		$favorites = $this->favoritesWithSiteID($favorites[0]);

		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Get Logged In User Favorites
	*/
	private function getLoggedInLikes($user_id = null, $site_id = null)
	{
		$user_id = ( isset($user_id) ) ? $user_id : get_current_user_id();
		$likes = get_user_meta($user_id, 'likes');
		
		if ( empty($likes) ) return array(array('site_id'=>1, 'posts' => array()));
		
		$likes = $this->likesWithSiteID($likes[0]);

		return ( !is_null($site_id) ) ? Helpers::pluckSiteLikes($site_id, $likes) : $likes;
	}

	/**
	* Get Logged In User Favorites
	*/
	private function getLoggedInDislikes($user_id = null, $site_id = null)
	{
		$user_id = ( isset($user_id) ) ? $user_id : get_current_user_id();
		$dislikes = get_user_meta($user_id, 'dislikes');
		
		if ( empty($dislikes) ) return array(array('site_id'=>1, 'posts' => array()));
		
		$dislikes = $this->dislikesWithSiteID($dislikes[0]);

		return ( !is_null($site_id) ) ? Helpers::pluckSiteDislikes($site_id, $dislikes) : $dislikes;
	}

	/**
	* Get Session Favorites
	*/
	private function getSessionFavorites($site_id = null)
	{
		if ( !isset($_SESSION['simplefavorites']) ) $_SESSION['simplefavorites'] = array();
		$favorites = $_SESSION['simplefavorites'];
		$favorites = $this->favoritesWithSiteID($favorites);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Get Session Favorites
	*/
	private function getSessionLikes($site_id = null)
	{
		if ( !isset($_SESSION['likes']) ) $_SESSION['likes'] = array();
		$likes = $_SESSION['likes'];
		$likes = $this->likesWithSiteID($likes);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteLikes($site_id, $likes) : $likes;
	}

	/**
	* Get Session Favorites
	*/
	private function getSessionDislikes($site_id = null)
	{
		if ( !isset($_SESSION['dislikes']) ) $_SESSION['dislikes'] = array();
		$dislikes = $_SESSION['dislikes'];
		$dislikes = $this->dislikesWithSiteID($dislikes);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteDislikes($site_id, $dislikes) : $dislikes;
	}

	/**
	* Get Cookie Favorites
	*/
	private function getCookieFavorites($site_id = null)
	{
		if ( !isset($_COOKIE['simplefavorites']) ) $_COOKIE['simplefavorites'] = json_encode(array());
		$favorites = json_decode(stripslashes($_COOKIE['simplefavorites']), true);
		$favorites = $this->favoritesWithSiteID($favorites);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Get Cookie Favorites
	*/
	private function getCookieLikes($site_id = null)
	{
		if ( !isset($_COOKIE['likes']) ) $_COOKIE['likes'] = json_encode(array());
		$likes = json_decode(stripslashes($_COOKIE['likes']), true);
		$likes = $this->likesWithSiteID($likes);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteLikes($site_id, $likes) : $likes;
	}

	/**
	* Get Cookie Favorites
	*/
	private function getCookieDislikes($site_id = null)
	{
		if ( !isset($_COOKIE['dislikes']) ) $_COOKIE['dislikes'] = json_encode(array());
		$dislikes = json_decode(stripslashes($_COOKIE['dislikes']), true);
		$dislikes = $this->dislikesWithSiteID($dislikes);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteDislikes($site_id, $dislikes) : $dislikes;
	}

	/**
	* Has the user favorited a specified post?
	* @param int $post_id
	* @param int $site_id
	*/
	public function isFavorite($post_id, $site_id = 1, $user_id = null)
	{
		$favorites = $this->getFavorites($user_id, $site_id);
		if ( in_array($post_id, $favorites) ) return true;
		return false;
	}

	/**
	* Has the user favorited a specified post?
	* @param int $post_id
	* @param int $site_id
	*/
	public function isLike($post_id, $site_id = 1, $user_id = null)
	{
		$likes = $this->getLikes($user_id, $site_id);
		if ( in_array($post_id, $likes) ) return true;
		return false;
	}

		/**
	* Has the user favorited a specified post?
	* @param int $post_id
	* @param int $site_id
	*/
	public function isDislike($post_id, $site_id = 1, $user_id = null)
	{
		$dislikes = $this->getDislikes($user_id, $site_id);
		if ( in_array($post_id, $dislikes) ) return true;
		return false;
	}

	/**
	* Does the user count in total favorites?
	* @return boolean
	*/
	public function countsInTotal()
	{
		if ( is_user_logged_in() ) return true;
		return $this->settings_repo->anonymous('save');
	}


	/**
	* Format an array of favorites
	* @param $post_id - int, post to add to array (for session/cookie favorites)
	* @param $site_id - int, site id for post_id
	*/
	public function formattedFavorites($post_id = null, $site_id = null, $status = null)
	{
		$favorites = $this->getAllFavorites();
		$formatter = new FavoritesArrayFormatter;
		return $formatter->format($favorites, $post_id, $site_id, $status);
	}

	/**
	* Format an array of favorites
	* @param $post_id - int, post to add to array (for session/cookie favorites)
	* @param $site_id - int, site id for post_id
	*/
	public function formattedLikes($post_id = null, $site_id = null, $status = null)
	{
		$likes = $this->getAllLikes();
		$formatter = new LikesArrayFormatter;
		return $formatter->format($likes, $post_id, $site_id, $status);
	}

	/**
	* Format an array of favorites
	* @param $post_id - int, post to add to array (for session/cookie favorites)
	* @param $site_id - int, site id for post_id
	*/
	public function formattedDislikes($post_id = null, $site_id = null, $status = null)
	{
		$dislikes = $this->getAllDislikes();
		$formatter = new DislikesArrayFormatter;
		return $formatter->format($dislikes, $post_id, $site_id, $status);
	}

}