<?php 

namespace SimpleFavorites\Entities\User;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Like\LikeFilter;
use SimpleFavorites\Helpers;
use SimpleFavorites\Entities\Like\LikeButton;
use SimpleFavorites\Config\SettingsRepository;

class UserLikes
{

	/**
	* User ID
	* @var int
	*/
	private $user_id;

	/**
	* Site ID
	* @var int
	*/
	private $site_id;

	/**
	* Display Links
	* @var boolean
	*/
	private $links;

	/**
	* Filters
	* @var array
	*/
	private $filters;

	/**
	* User Repository
	*/
	private $user_repo;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct($user_id = null, $site_id = null, $links = false, $filters = null)
	{
		$this->user_id = $user_id;
		$this->site_id = $site_id;
		$this->links = $links;
		$this->filters = $filters;
		$this->user_repo = new UserRepository;
		$this->settings_repo = new SettingsRepository;
	}

	/**
	* Get an array of favorites for specified user
	*/
	public function getLikesArray()
	{
		$likes = $this->user_repo->getLikes($this->user_id, $this->site_id);
		if ( isset($this->filters) && is_array($this->filters) ) $likes = $this->filterLikes($likes);
		return $this->removeInvalidLikes($likes);
	}

	/**
	* Remove non-existent or non-published favorites
	* @param array $favorites
	*/
	private function removeInvalidLikes($likes)
	{
		foreach($likes as $key => $like){
			if ( !$this->postExists($like) ) unset($likes[$key]);
		}
		return $likes;
	}

	/**
	* Filter the favorites
	* @since 1.1.1
	* @param array $favorites
	*/
	private function filterLikes($likes)
	{
		$likes = new LikeFilter($likes, $this->filters);
		return $likes->filter();
	}	

	/**
	* Return an HTML list of favorites for specified user
	* @param $include_button boolean - whether to include the favorite button
	*/
	public function getLikesList($include_button = false)
	{
		if ( is_null($this->site_id) || $this->site_id == '' ) $this->site_id = get_current_blog_id();
		
		$likes = $this->getLikesArray();
		$no_likes = $this->settings_repo->noLikesText();

		// Post Type filters for data attr
		$post_types = '';
		if ( isset($this->filters['post_type']) ){
			$post_types = implode(',', $this->filters['post_type']);
		}
		
		if ( is_multisite() ) switch_to_blog($this->site_id);
		
		$out = '<ul class="likes-list" data-userid="' . $this->user_id . '" data-links="true" data-siteid="' . $this->site_id . '" ';
		$out .= ( $include_button ) ? 'data-includebuttons="true"' : 'data-includebuttons="false"';
		$out .= ( $this->links ) ? ' data-includelinks="true"' : ' data-includelinks="false"';
		$out .= ' data-nolikestext="' . $no_likes . '"';
		$out .= ' data-posttype="' . $post_types . '"';
		$out .= '>';
		foreach ( $likes as $key => $like ){
			$out .= '<li data-postid="' . $like . '">';
			if ( $include_button ) $out .= '<p>';
			if ( $this->links ) $out .= '<a href="' . get_permalink($like) . '">';
			$out .= get_the_title($like);
			if ( $this->links ) $out .= '</a>';
			if ( $include_button ){
				$button = new LikeButton($like, $this->site_id);
				$out .= '</p><p>';
				$out .= $button->display(false) . '</p>';
			}
			$out .= '</li>';
		}
		if ( empty($likes) ) $out .= '<li data-postid="0" data-nolikes>' . $no_likes . '</li>';
		$out .= '</ul>';
		if ( is_multisite() ) restore_current_blog();
		return $out;
	}

	/**
	* Check if post exists and is published
	*/
	private function postExists($id)
	{
		$status = get_post_status($id);
		return( !$status || $status !== 'publish') ? false : true;
	}

}