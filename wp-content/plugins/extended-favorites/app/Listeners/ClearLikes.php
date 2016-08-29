<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Like\Like;
use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Like\SyncAllLikes;
use SimpleFavorites\Entities\Post\SyncLikeCount;

class ClearLikes extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user_repo;

	/**
	* Favorites Sync
	*/
	private $likes_sync;

	public function __construct()
	{
		parent::__construct();
		$this->user_repo = new UserRepository;
		$this->dislikes_sync = new SyncAllLikes;
		$this->setFormData();
		$this->clearLikes();
		$this->sendResponse();
	}

	/**
	* Set Form Data
	*/
	private function setFormData()
	{
		$this->data['siteid'] = intval(sanitize_text_field($_POST['siteid']));
	}

	/**
	* Remove all user's favorites from the specified site
	*/
	private function clearLikes()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('likes_before_clear', $this->data['siteid'], $user);
		
		$likes = $this->user_repo->getAllLikes();
		foreach($likes as $key => $site_likes){
			if ( $site_likes['site_id'] == $this->data['siteid'] ) {
				$this->updateLikeCounts($site_likes);
				unset($likes[$key]);
			}
		}
		$this->likes_sync->sync($likes);
		
		do_action('likes_after_clear', $this->data['siteid'], $user);
	}

	/**
	* Update all the cleared post favorite counts
	*/
	private function updateLikeCounts($site_likes)
	{
		foreach($site_likes['posts'] as $like){
			$count_sync = new SyncLikeCount($like, 'inactive', $this->data['siteid']);
			$count_sync->sync();
		}
	}

	/**
	* Set and send the response
	*/
	private function sendResponse()
	{
		$likes = $this->user_repo->formattedLikes();
		$this->response(array(
			'status' => 'success',
			'likes' => $likes
		));
	}

}