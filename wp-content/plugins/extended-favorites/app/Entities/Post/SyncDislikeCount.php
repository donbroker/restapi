<?php 

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Entities\Post\DislikeCount;
use SimpleFavorites\Entities\User\UserRepository;

/**
* Updates the favorite count for a given post
*/
class SyncDislikeCount 
{

	/**
	* Post ID
	* @var int
	*/
	private $post_id;

	/**
	* Site ID
	* @var int
	*/
	private $site_id;

	/**
	* Status
	* @var string
	*/
	private $status;

	/**
	* Favorite Count
	* @var object
	*/
	private $dislike_count;

	/**
	* User Repository
	*/
	private $user;

	public function __construct($post_id, $status, $site_id)
	{
		$this->post_id = $post_id;
		$this->status = $status;
		$this->site_id = $site_id;
		$this->dislike_count = new DislikeCount;
		$this->user = new UserRepository;
	}

	/**
	* Sync the Post Total Favorites
	*/
	public function sync()
	{
		if ( !$this->user->countsInTotal() ) return false;
		$count = $this->dislike_count->getCount($this->post_id, $this->site_id);
		$count = ( $this->status == 'active' ) ? $count + 1 : max(0, $count - 1);
		return update_post_meta($this->post_id, 'dislikes_count', $count);
	}

}