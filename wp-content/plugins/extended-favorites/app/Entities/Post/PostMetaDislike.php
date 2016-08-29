<?php 

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Post\DislikeCount;

class PostMetaDislike
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	
	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_action( 'add_meta_boxes', array($this, 'dislikeCountBox') );
	}

	/**
	* Add the Favorite Count Meta Box for enabled Types
	*/
	public function dislikeCountBox()
	{
		foreach ( $this->settings_repo->metaEnabled() as $type ){
			add_meta_box(
				'dislikes',
				__( 'Dislikes', 'simplefavorites' ),
				array($this, 'dislikeCount'),
				$type,
				'side',
				'low'
			);
		}
	}

	/**
	* The favorite count
	*/
	public function dislikeCount()
	{
		global $post;
		$count = new LikeCount;
		echo '<strong>' . __('Total Dislikes', 'simplefavorites') . ':</strong> ';
		echo $count->getCount($post->ID);
		echo '<input type="hidden" name="dislikes_count" value="' . $count->getCount($post->ID) . '">';
	}

}