<?php 

namespace SimpleFavorites\Events;

use SimpleFavorites\Listeners\NonceHandler;
use SimpleFavorites\Listeners\FavoriteButton;
use SimpleFavorites\Listeners\FavoritesArray;
use SimpleFavorites\Listeners\ClearFavorites;
use SimpleFavorites\Listeners\FavoriteCount;
use SimpleFavorites\Listeners\FavoriteList;

use SimpleFavorites\Listeners\LikeButton;
use SimpleFavorites\Listeners\LikesArray;
use SimpleFavorites\Listeners\ClearLikes;
use SimpleFavorites\Listeners\LikeCount;
use SimpleFavorites\Listeners\LikeList;

use SimpleFavorites\Listeners\DislikeButton;
use SimpleFavorites\Listeners\DislikessArray;
use SimpleFavorites\Listeners\ClearDislikes;
use SimpleFavorites\Listeners\DislikeCount;
use SimpleFavorites\Listeners\DislikeList;

class RegisterPublicEvents 
{

	public function __construct()
	{
		// Generate a Nonce
		add_action( 'wp_ajax_nopriv_simplefavorites_nonce', array($this, 'nonce' ));
		add_action( 'wp_ajax_simplefavorites_nonce', array($this, 'nonce' ));

		// Front End Favorite Button
		add_action( 'wp_ajax_nopriv_simplefavorites_favorite', array($this, 'favoriteButton' ));
		add_action( 'wp_ajax_simplefavorites_favorite', array($this, 'favoriteButton' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_like', array($this, 'likeButton' ));
		add_action( 'wp_ajax_simplefavorites_like', array($this, 'likeButton' ));
		
		add_action( 'wp_ajax_nopriv_simplefavorites_dislike', array($this, 'dislikeButton' ));
		add_action( 'wp_ajax_simplefavorites_dislike', array($this, 'dislikeButton' ));	

		// User's Favorited Posts (array of IDs)
		add_action( 'wp_ajax_nopriv_simplefavorites_array', array($this, 'favoritesArray' ));
		add_action( 'wp_ajax_simplefavorites_array', array($this, 'favoritesArray' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_like_array', array($this, 'likesArray' ));
		add_action( 'wp_ajax_simplefavorites_like_array', array($this, 'likesArray' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_dislike_array', array($this, 'dislikesArray' ));
		add_action( 'wp_ajax_simplefavorites_dislike_array', array($this, 'dislikesArray' ));

		// Clear Favorites
		add_action( 'wp_ajax_nopriv_simplefavorites_clear', array($this, 'clearFavorites' ));
		add_action( 'wp_ajax_simplefavorites_clear', array($this, 'clearFavorites' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_like_clear', array($this, 'clearLikes' ));
		add_action( 'wp_ajax_simplefavorites_like_clear', array($this, 'clearLikes' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_dislike_clear', array($this, 'clearDislikes' ));
		add_action( 'wp_ajax_simplefavorites_dislike_clear', array($this, 'clearDislikes' ));

		// Total Favorite Count
		add_action( 'wp_ajax_nopriv_simplefavorites_totalcount', array($this, 'favoriteCount' ));
		add_action( 'wp_ajax_simplefavorites_totalcount', array($this, 'favoriteCount' ));
				
		add_action( 'wp_ajax_nopriv_simplefavorites_like_totalcount', array($this, 'likeCount' ));
		add_action( 'wp_ajax_simplefavorites_like_totalcount', array($this, 'likeCount' ));
		
		add_action( 'wp_ajax_nopriv_simplefavorites_dislike_totalcount', array($this, 'dislikeCount' ));
		add_action( 'wp_ajax_simplefavorites_dislike_totalcount', array($this, 'dislikeCount' ));

		// Single Favorite List
		add_action( 'wp_ajax_nopriv_simplefavorites_list', array($this, 'favoriteList' ));
		add_action( 'wp_ajax_simplefavorites_list', array($this, 'favoriteList' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_like_list', array($this, 'likeList' ));
		add_action( 'wp_ajax_simplefavorites_like_list', array($this, 'likeList' ));

		add_action( 'wp_ajax_nopriv_simplefavorites_dislike_list', array($this, 'dislikeList' ));
		add_action( 'wp_ajax_simplefavorites_dislike_list', array($this, 'dislikeList' ));

	}

	/**
	* Favorite Button
	*/
	public function favoriteButton()
	{
		new FavoriteButton;
	}

	/**
	* Favorite Button
	*/
	public function likeButton()
	{
		new LikeButton;
	}

	/**
	* Favorite Button
	*/
	public function dislikeButton()
	{
		new DislikeButton;
	}
	/**
	* Generate a Nonce
	*/
	public function nonce()
	{
		new NonceHandler;
	}

	/**
	* Get an array of current user's favorites
	*/
	public function favoritesArray()
	{
		new FavoritesArray;
	}

	/**
	* Get an array of current user's favorites
	*/
	public function likesArray()
	{
		new LikesArray;
	}

	/**
	* Get an array of current user's favorites
	*/
	public function dislikesArray()
	{
		new DislikesArray;
	}

	/**
	* Clear all Favorites
	*/
	public function clearFavorites()
	{
		new ClearFavorites;
	}

	/**
	* Clear all Favorites
	*/
	public function clearLikes()
	{
		new ClearLikes;
	}

	/**
	* Clear all Favorites
	*/
	public function clearDislikes()
	{
		new ClearDislikes;
	}

	/**
	* Favorite Count for a single post
	*/
	public function favoriteCount()
	{
		new FavoriteCount;
	}

	/**
	* Favorite Count for a single post
	*/
	public function likeCount()
	{
		new LikeCount;
	}

	/**
	* Favorite Count for a single post
	*/
	public function dislikeCount()
	{
		new DislikeCount;
	}

	/**
	* Single Favorite List for a Specific User
	*/
	public function favoriteList()
	{
		new FavoriteList;
	}

	/**
	* Single Favorite List for a Specific User
	*/
	public function likeList()
	{
		new LikeList;
	}

	/**
	* Single Favorite List for a Specific User
	*/
	public function dislikeList()
	{
		new DislikeList;
	}

}