<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Like\Like;
use SimpleFavorites\Entities\User\UserRepository;

class LikeButton extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user_repo;

	public function __construct()
	{
		parent::__construct();
		$this->user_repo = new UserRepository;
		$this->setFormData();
		$this->updateLike();
	}

	/**
	* Set Form Data
	*/
	private function setFormData()
	{
		$this->data['postid'] = intval(sanitize_text_field($_POST['postid']));
		$this->data['siteid'] = intval(sanitize_text_field($_POST['siteid']));
		$this->data['status'] = ( $_POST['status'] == 'active') ? 'active' : 'inactive';
	}

	/**
	* Update the Favorite
	*/
	private function updateLike()
	{
		$this->beforeUpdateAction();
		$like = new Like;
		$like->update($this->data['postid'], $this->data['status'], $this->data['siteid']);
		$this->afterUpdateAction();

		$this->response(array(
			'status' => 'success', 
			'like_data' => array('id' => $this->data['postid'], 'siteid' => $this->data['siteid'], 'status' => $this->data['status']),
			'likes' => $this->user_repo->formattedLikes($this->data['postid'], $this->data['siteid'], $this->data['status'])
		));
	}

	/**
	* Before Update Action
	* Provides hook for performing actions before a favorite
	*/
	private function beforeUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('likes_before_like', $this->data['postid'], $this->data['status'], $this->data['siteid'], $user);
	}

	/**
	* After Update Action
	* Provides hook for performing actions after a favorite
	*/
	private function afterUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('likes_after_like', $this->data['postid'], $this->data['status'], $this->data['siteid'], $user);
	}

}