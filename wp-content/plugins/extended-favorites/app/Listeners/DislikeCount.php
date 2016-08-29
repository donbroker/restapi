<?php

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Post\DislikeCount as DislikeCounter;

/**
* Return the total number of favorites for a specified post
*/
class DislikeCount extends AJAXListenerBase
{
	/**
	* Favorite Counter
	*/
	private $dislike_counter;

	public function __construct()
	{
		parent::__construct();
		$this->dislike_counter = new DislikeCounter;
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
			'count' => $this->dislike_counter->getCount($this->data['postid'], $this->data['siteid'])
		));
	}
}