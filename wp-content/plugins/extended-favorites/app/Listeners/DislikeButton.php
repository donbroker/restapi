<?php 

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Dislike\Dislike;
use SimpleFavorites\Entities\User\UserRepository;

class DislikeButton extends AJAXListenerBase
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
		$this->updateDislike();
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
	private function updateDislike()
	{
		$this->beforeUpdateAction();
		$dislike = new Dislike;
		$dislike->update($this->data['postid'], $this->data['status'], $this->data['siteid']);
		$this->afterUpdateAction();

		$this->response(array(
			'status' => 'success', 
			'dislike_data' => array('id' => $this->data['postid'], 'siteid' => $this->data['siteid'], 'status' => $this->data['status']),
			'dislikes' => $this->user_repo->formattedDislikes($this->data['postid'], $this->data['siteid'], $this->data['status'])
		));
	}

	/**
	* Before Update Action
	* Provides hook for performing actions before a favorite
	*/
	private function beforeUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('dislikes_before_dislike', $this->data['postid'], $this->data['status'], $this->data['siteid'], $user);
	}

	/**
	* After Update Action
	* Provides hook for performing actions after a favorite
	*/
	private function afterUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('dislikes_after_dislike', $this->data['postid'], $this->data['status'], $this->data['siteid'], $user);
	}

}