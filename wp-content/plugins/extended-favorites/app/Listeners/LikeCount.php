<?php

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Post\LikeCount as LikeCounter;

/**
* Return the total number of favorites for a specified post
*/
class LikeCount extends AJAXListenerBase
{
	/**
	* Favorite Counter
	*/
	private $like_counter;

	public function __construct()
	{
		parent::__construct();
		$this->like_counter = new LikeCounter;
		$this->setData();
		$this->sendCount();
	}

	private function setData()
	{
		$this->data['postid'] = ( isset($_POST['postid']) ) ? intval($_POST['postid']) : null;
		$this->data['siteid'] = ( isset($_POST['siteid']) ) ? intval($_POST['siteid']) : null;
	}

	private function sendCount()
	{
		$this->response(array(
			'status' => 'success',
			'count' => $this->like_counter->getCount($this->data['postid'], $this->data['siteid'])
		));
	}
}