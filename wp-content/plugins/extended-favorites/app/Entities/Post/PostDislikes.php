<?php

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Post\DislikeCount;

/**
* Get the users who have favorited a specific post
*/
class PostDislikes
{
	/**
	* Post ID
	*/
	private $post_id;

	/**
	* Site ID
	*/
	private $site_id;

	/**
	* User Repository
	*/
	private $user_repo;

	/**
	* Favorite Count
	*/
	private $dislike_count;

	public function __construct($post_id, $site_id)
	{
		$this->post_id = ( $post_id ) ? $post_id : get_the_id();
		$this->site_id = ( $site_id ) ? $site_id : 1;
		$this->user_repo = new UserRepository;
		$this->dislike_count = new DislikeCount;
	}

	/**
	* Get an array of users who favorited the post
	* @return array of user objects
	*/
	public function getUsers()
	{
		$users = $this->getAllUsers();
		foreach($users as $key => $user){
			if ( !$this->user_repo->isDislike($this->post_id, $this->site_id, $user->ID) ){
				unset($users[$key]);
			}
		}
		return $users;
	}

	/**
	* Get all Users
	*/
	private function getAllUsers()
	{
		$user_query = new \WP_User_Query(array(
			'blog_id' => ( $this->site_id ) ? $this->site_id : 1
		));
		$users = $user_query->get_results();
		return $users;
	}

	/**
	* Get the number of Anonymous Users who have favorited the post
	*/
	public function anonymousCount()
	{
		$total_count = $this->dislike_count->getCount($this->post_id, $this->site_id);
		$registered_count = count($this->getUsers());
		return $total_count - $registered_count;
	}

	/**
	* Get an HTML formatted list of users who have favorited the post
	* @param string $separator (list, or string to separate each item)
	* @param boolean $include_anonymous
	* @param string $anonymous_label
	* @param string $anonymous_label_single
	*/
	public function userList($separator, $include_anonymous, $anonymous_label, $anonymous_label_single)
	{
		$users = $this->getUsers();
		$total = ( $include_anonymous ) ? count($users) + 1 : count($users);
		$anonymous_count = $this->anonymousCount();
		
		$out = ( $separator == 'list' ) ? '<ul>' : '';
		foreach($users as $key => $user){
			if ( $separator == 'list' ) $out .= '<li>';
			$out .= $user->display_name;
			if ( $separator == 'list' ) {
				$out .= '</li>';
			} else {
				if ( $key + 1 < $total ) $out .= $separator;
			}
		}

		if ( $include_anonymous ){
			$label = ( $anonymous_count == 1 ) ? $anonymous_label_single : $anonymous_label;

			if ( $separator == 'list' ){
				$out .= '<li>' . $anonymous_count . ' ' . $label . '</li>';
			} else {
				$out .= $anonymous_count . ' ' . $label;
			}
		}

		if ( $separator == 'list' ) $out .= '</ul>';
		
		return apply_filters('dislikes_user_list', $out, $users, $anonymous_count);
	}
}