<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Dislike\Dislike;
use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Dislike\SyncAllDislikes;
use SimpleFavorites\Entities\Post\SyncDislikeCount;

class ClearDislikes extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user_repo;

	/**
	* Favorites Sync
	*/
	private $dislikes_sync;

	public function __construct()
	{
		parent::__construct();
		$this->user_repo = new UserRepository;
		$this->dislikes_sync = new SyncAllDislikes;
		$this->setFormData();
		$this->clearDislikes();
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
	private function clearDislikes()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('dislikes_before_clear', $this->data['siteid'], $user);
		
		$dislikes = $this->user_repo->getAllDislikes();
		foreach($dislikes as $key => $site_dislikes){
			if ( $site_dislikes['site_id'] == $this->data['siteid'] ) {
				$this->updateDislikeCounts($site_dislikes);
				unset($dislikes[$key]);
			}
		}
		$this->dislikes_sync->sync($dislikes);
		
		do_action('dislikes_after_clear', $this->data['siteid'], $user);
	}

	/**
	* Update all the cleared post favorite counts
	*/
	private function updateDislikeCounts($site_dislikes)
	{
		foreach($site_dislikes['posts'] as $dislike){
			$count_sync = new SyncDislikeCount($dislike, 'inactive', $this->data['siteid']);
			$count_sync->sync();
		}
	}

	/**
	* Set and send the response
	*/
	private function sendResponse()
	{
		$dislikes = $this->user_repo->formattedDislikes();
		$this->response(array(
			'status' => 'success',
			'dislikes' => $dislikes
		));
	}

}