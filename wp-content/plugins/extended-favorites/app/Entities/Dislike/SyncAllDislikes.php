<?php

namespace SimpleFavorites\Entities\Dislike;

use SimpleFavorites\Config\SettingsRepository;

/**
* Sync all favorites for a specific site
*/
class SyncAllDislikes
{
	/**
	* Favorites to Save
	* @var array
	*/
	private $dislikes;

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
	public function sync($dislikes)
	{
		$this->dislikes = $dislikes;
		$saveType = $this->settings_repo->saveType();
		$this->$saveType();
		$this->updateUserMeta();
	}

	/**
	* Sync Session Favorites
	*/
	private function session()
	{
		return $_SESSION['dislikes'] = $this->dislikes;
	}

	/**
	* Sync a Cookie Favorite
	*/
	public function cookie()
	{
		setcookie('dislikes', json_encode($this->dislikes), time()+3600, '/' );
		return;
	}

	/**
	* Update User Meta (logged in only)
	*/
	private function updateUserMeta()
	{
		if ( !is_user_logged_in() ) return false;
		return update_user_meta( get_current_user_id(), 'dislikes', $this->dislikes );
	}
}