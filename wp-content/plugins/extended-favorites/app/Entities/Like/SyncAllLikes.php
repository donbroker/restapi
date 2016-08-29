<?php

namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Config\SettingsRepository;

/**
* Sync all favorites for a specific site
*/
class SyncAllLikes
{
	/**
	* Favorites to Save
	* @var array
	*/
	private $likes;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
	}

	/**
	* Sync the favorites
	*/
	public function sync($likes)
	{
		$this->likes = $likes;
		$saveType = $this->settings_repo->saveType();
		$this->$saveType();
		$this->updateUserMeta();
	}

	/**
	* Sync Session Favorites
	*/
	private function session()
	{
		return $_SESSION['likes'] = $this->likes;
	}

	/**
	* Sync a Cookie Favorite
	*/
	public function cookie()
	{
		setcookie('likes', json_encode($this->likes), time()+3600, '/' );
		return;
	}

	/**
	* Update User Meta (logged in only)
	*/
	private function updateUserMeta()
	{
		if ( !is_user_logged_in() ) return false;
		return update_user_meta( get_current_user_id(), 'likes', $this->likes );
	}
}