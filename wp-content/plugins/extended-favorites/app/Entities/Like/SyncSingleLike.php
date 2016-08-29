<?php 

namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Helpers;

/**
* Sync a single favorite to a given save type
*/
class SyncSingleLike 
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
		if ( $this->user->isLike($this->post_id, $this->site_id) ) return $_SESSION['likes'] = $this->removeLike();
		return $_SESSION['likes'] = $this->addLike();
	}

	/**
	* Sync a Cookie Favorite
	*/
	public function cookie()
	{
		if ( $this->user->isLike($this->post_id, $this->site_id) ){
			setcookie('likes', json_encode($this->removeLike()), time()+3600, '/' );
			return;
		}
		setcookie('likes', json_encode($this->addLike()), time()+3600, '/' );
		return;
	}

	/**
	* Update User Meta (logged in only)
	*/
	public function updateUserMeta($likes)
	{
		if ( !is_user_logged_in() ) return false;
		return update_user_meta( get_current_user_id(), 'likes', $likes);
	}

	/**
	* Remove a Favorite
	*/
	private function removeLike()
	{
		$likes= $this->user->getAllLikes($this->site_id);
		foreach($likes as $key => $site_likes){
			if ( $site_likes['site_id'] !== $this->site_id ) continue;
			foreach($site_likes['posts'] as $k => $like){
				if ( $like == $this->post_id ) unset($likes[$key]['posts'][$k]);
			}
		}
		$this->updateUserMeta($likes);
		return $likes;
	}

	/**
	* Add a Favorite
	*/
	private function addLike()
	{
		$likes = $this->user->getAllLikes($this->site_id);
		if ( !Helpers::siteExists($this->site_id, $likes) ){
			$likes[] = array(
				'site_id' => $this->site_id,
				'posts' => array()
			);
		}
		foreach($likes as $key => $site_likes){
			if ( $site_likes['site_id'] !== $this->site_id ) continue;
			$likes[$key]['posts'][] = $this->post_id;
		}
		$this->updateUserMeta($likes);
		return $likes;
	}

}