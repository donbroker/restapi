<?php

namespace SimpleFavorites\Entities\Dislike;

use SimpleFavorites\Entities\Post\DislikeCount;

/**
* Format the user's favorite array to include additional post data
*/
class DislikesArrayFormatter
{
	/**
	* Formatted favorites array
	*/
	private $formatted_dislikes;

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
		$this->counter = new DislikeCount;
	}

	public function format($dislikes, $post_id = null, $site_id = null, $status = null)
	{
		$this->formatted_dislikes = $dislikes;
		$this->post_id = $post_id;
		$this->site_id = $site_id;
		$this->status = $status;
		$this->resetIndexes();
		$this->addPostData();
		return $this->formatted_dislikes;
	}

	/**
	* Reset the favorite indexes
	*/
	private function resetIndexes()
	{
		foreach ( $this->formatted_dislikes as $site => $site_dislikes ){
			// Make older posts compatible with new name
			if ( !isset($site_dislikes['posts']) ) {
				$site_dislikes['posts'] = $site_dislikes['site_dislikes'];
				unset($this->formatted_dislikes[$site]['site_dislikes']);
			}
			foreach ( $site_dislikes['posts'] as $key => $dislike ){
				unset($this->formatted_dislikes[$site]['posts'][$key]);
				$this->formatted_dislikes[$site]['posts'][$dislike]['post_id'] = $dislike;
			}
		}
	}

	/**
	* Add the post type to each favorite
	*/
	private function addPostData()
	{
		$this->checkCurrentPost();
		foreach ( $this->formatted_dislikes as $site => $site_dislikes ){
			foreach ( $site_dislikes['posts'] as $key => $dislike ){
				$site_id = $this->formatted_dislikes[$site]['site_id'];
				$this->formatted_dislikes[$site]['posts'][$key]['post_type'] = get_post_type($key);
				$this->formatted_dislikes[$site]['posts'][$key]['title'] = get_the_title($key);
				$this->formatted_dislikes[$site]['posts'][$key]['permalink'] = get_the_permalink($key);
				$this->formatted_dislikes[$site]['posts'][$key]['total'] = $this->counter->getCount($key, $site_id);
				$button = new DislikeButton($key, $site_id);
				$this->formatted_dislikes[$site]['posts'][$key]['button'] = $button->display(false);
			}
			$this->formatted_dislikes[$site] = array_reverse($this->formatted_dislikes[$site]);
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
		foreach ( $this->formatted_dislikes as $site => $site_dislikes ){
			if ( $site_dislikes['site_id'] == $this->site_id ) {
				if ( isset($site_dislikes['posts'][$this->post_id]) && $this->status == 'inactive' ){
					unset($this->formatted_dislikes[$site]['posts'][$this->post_id]);
				} else {
					$this->formatted_dislikes[$site]['posts'][$this->post_id] = array('post_id' => $this->post_id);
				}
			}
		}
	}


}