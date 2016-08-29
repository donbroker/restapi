<?php

namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Entities\Post\LikeCount;
use SimpleFavorites\Entities\Like\LikeButton;

/**
* Format the user's favorite array to include additional post data
*/
class LikesArrayFormatter
{
	/**
	* Formatted favorites array
	*/
	private $formatted_likes;

	/**
	* Total Favorites Counter
	*/
	private $counter;

	/**
	* Post ID to add to return array
	* For adding/removing session/cookie favorites for current request
	* @var int
	*/
	private $post_id;

	/**
	* Site ID for post to add to array
	* For adding/removing session/cookie favorites for current request
	* @var int
	*/
	private $site_id;

	/**
	* Site ID for post to add to array
	* For adding/removing session/cookie favorites for current request
	* @var string
	*/
	private $status;

	public function __construct()
	{
		$this->counter = new LikeCount;
	}

	public function format($likes, $post_id = null, $site_id = null, $status = null)
	{
		$this->formatted_likes = $likes;
		$this->post_id = $post_id;
		$this->site_id = $site_id;
		$this->status = $status;
		$this->resetIndexes();
		$this->addPostData();
		return $this->formatted_likes;
	}

	/**
	* Reset the favorite indexes
	*/
	private function resetIndexes()
	{
		foreach ( $this->formatted_likes as $site => $site_likes ){
			// Make older posts compatible with new name
			if ( !isset($site_likes['posts']) ) {
				$site_likes['posts'] = $site_likes['site_likes'];
				unset($this->formatted_likes[$site]['site_likes']);
			}
			foreach ( $site_likes['posts'] as $key => $like ){
				unset($this->formatted_likes[$site]['posts'][$key]);
				$this->formatted_likes[$site]['posts'][$like]['post_id'] = $like;
			}
		}
	}

	/**
	* Add the post type to each favorite
	*/
	private function addPostData()
	{
		$this->checkCurrentPost();
		foreach ( $this->formatted_likes as $site => $site_likes ){
			foreach ( $site_likes['posts'] as $key => $like ){
				$site_id = $this->formatted_likes[$site]['site_id'];
				$this->formatted_likes[$site]['posts'][$key]['post_type'] = get_post_type($key);
				$this->formatted_likes[$site]['posts'][$key]['title'] = get_the_title($key);
				$this->formatted_likes[$site]['posts'][$key]['permalink'] = get_the_permalink($key);
				$this->formatted_likes[$site]['posts'][$key]['total'] = $this->counter->getCount($key, $site_id);
				$button = new LikeButton($key, $site_id);
				$this->formatted_likes[$site]['posts'][$key]['button'] = $button->display(false);
			}
			$this->formatted_likes[$site] = array_reverse($this->formatted_likes[$site]);
		}
	}

	/**
	* Make sure the current post is updated in the array
	* (for cookie/session favorites, so AJAX response returns array with correct posts without page refresh)
	*/
	private function checkCurrentPost()
	{
		if ( !isset($this->post_id) || !isset($this->site_id) ) return;
		if ( is_user_logged_in() ) return;
		foreach ( $this->formatted_likes as $site => $site_likes ){
			if ( $site_likes['site_id'] == $this->site_id ) {
				if ( isset($site_likes['posts'][$this->post_id]) && $this->status == 'inactive' ){
					unset($this->formatted_likes[$site]['posts'][$this->post_id]);
				} else {
					$this->formatted_likes[$site]['posts'][$this->post_id] = array('post_id' => $this->post_id);
				}
			}
		}
	}


}