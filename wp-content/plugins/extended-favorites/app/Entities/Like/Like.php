<?php
namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Post\SyncLikeCount;

class Like 
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
	* Save the Favorite
	*/
	public function update($post_id, $status, $site_id)
	{
		$saveType = $this->settings_repo->saveType();
		$usersync = new SyncSingleLike($post_id, $site_id);
		$usersync->$saveType();
		
		$postsync = new SyncLikeCount($post_id, $status, $site_id);
		$postsync->sync();
	}

}