<?php 

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Post\LikeCount;

class PostMetaLike
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	
	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_action( 'add_meta_boxes', array($this, 'likeCountBox') );
	}

	/**
	* Add the Favorite Count Meta Box for enabled Types
	*/
	public function likeCountBox()
	{
		foreach ( $this->settings_repo->metaEnabled() as $type ){
			add_meta_box(
				'likes',
				__( 'Likes', 'simplefavorites' ),
				array($this, 'likeCount'),
				$type,
				'side',
				'low'
			);
		}
	}

	/**
	* The favorite count
	*/
	public function likeCount()
	{
		global $post;
		$count = new LikeCount;
		echo '<strong>' . __('Total Likes', 'simplefavorites') . ':</strong> ';
		echo $count->getCount($post->ID);
		echo '<input type="hidden" name="likes_count" value="' . $count->getCount($post->ID) . '">';
	}

}