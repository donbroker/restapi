<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\User\UserRepository;

/**
* Return an array of user's favorited posts
*/
class LikesArray extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user;

	/**
	* User Favorites
	* @var array
	*/
	private $likes;

	public function __construct()
	{
		$this->user = new UserRepository;
		$this->setLikes();
		$this->response(array('status'=>'success', 'likes' => $this->likes));
	}

	/**
	* Get the Favorites
	*/
	private function setLikes()
	{
		$likes = $this->user->formattedLikes();
		$this->likes = $likes;
	}
}