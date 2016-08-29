<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\User\UserRepository;

/**
* Return an array of user's favorited posts
*/
class DislikesArray extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user;

	/**
	* User Favorites
	* @var array
	*/
	private $dislikes;

	public function __construct()
	{
		$this->user = new UserRepository;
		$this->setDislikes();
		$this->response(array('status'=>'success', 'dislikes' => $this->dislikes));
	}

	/**
	* Get the Favorites
	*/
	private function setDislikes()
	{
		$dislikes = $this->user->formattedDislikes();
		$this->dislikes = $dislikes;
	}
}