<?php 

namespace SimpleFavorites\Entities\Dislike;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Helpers;

/**
* Sync a single favorite to a given save type
*/
class SyncSingleDislike 
{

	/**
	* The Post ID
	*/
	private $post_id;

	/**
	* The Site ID
	*/
	private $site_id;

	/**
	* User Repository
	*/
	private $user;

	public function __construct($post_id, $site_id)
	{
		$this->user = new UserRepository;
		$this->post_id = $post_id;
		$this->site_id = $site_id;
	}

	/**
	* Sync a Session Favorite
	*/
	public function session()
	{
		if ( $this->user->isDislike($this->post_id, $this->site_id) ) return $_SESSION['dislikes'] = $this->removeDislike();
		return $_SESSION['dislikes'] = $this->addDislike();
	}

	/**
	* Sync a Cookie Favorite
	*/
	public function cookie()
	{
		if ( $this->user->isDislike($this->post_id, $this->site_id) ){
			setcookie('dislikes', json_encode($this->removeDislike()), time()+3600, '/' );
			return;
		}
		setcookie('dislikes', json_encode($this->addDislike()), time()+3600, '/' );
		return;
	}

	/**
	* Update User Meta (logged in only)
	*/
	public function updateUserMeta($dislikes)
	{
		if ( !is_user_logged_in() ) return false;
		return update_user_meta( get_current_user_id(), 'dislikes', $dislikes);
	}

	/**
	* Remove a Favorite
	*/
	private function removeDislike()
	{
		$dislikes= $this->user->getAllDislikes($this->site_id);
		foreach($dislikes as $key => $site_dislikes){
			if ( $site_dislikes['site_id'] !== $this->site_id ) continue;
			foreach($site_dislikes['posts'] as $k => $dislike){
				if ( $dislike == $this->post_id ) unset($dislikes[$key]['posts'][$k]);
			}
		}
		$this->updateUserMeta($dislikes);
		return $dislikes;
	}

	/**
	* Add a Favorite
	*/
	private function addDislike()
	{
		$dislikes = $this->user->getAllDislikes($this->site_id);
		if ( !Helpers::siteExists($this->site_id, $dislikes) ){
			$disLikes[] = array(
				'site_id' => $this->site_id,
				'posts' => array()
			);
		}
		foreach($dislikes as $key => $site_dislikes){
			if ( $site_dislikes['site_id'] !== $this->site_id ) continue;
			$dislikes[$key]['posts'][] = $this->post_id;
		}
		$this->updateUserMeta($dislikes);
		return $dislikes;
	}

}