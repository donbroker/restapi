<?php 

namespace SimpleFavorites\Entities\User;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Dislike\DislikeFilter;
use SimpleFavorites\Helpers;
use SimpleFavorites\Entities\Dislike\DislikeButton;
use SimpleFavorites\Config\SettingsRepository;

class UserDislikes
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
	public function getDislikesArray()
	{
		$dislikes = $this->user_repo->getDislikes($this->user_id, $this->site_id);
		if ( isset($this->filters) && is_array($this->filters) ) $dislikes = $this->filterDislikes($dislikes);
		return $this->removeInvalidDislikes($dislikes);
	}

	/**
	* Remove non-existent or non-published favorites
	* @param array $favorites
	*/
	private function removeInvalidDislikes($dislikes)
	{
		foreach($dislikes as $key => $dislike){
			if ( !$this->postExists($dislike) ) unset($dislikes[$key]);
		}
		return $dislikes;
	}

	/**
	* Filter the favorites
	* @since 1.1.1
	* @param array $favorites
	*/
	private function filterDislikes($dislikes)
	{
		$dislikes = new DislikeFilter($dislikes, $this->filters);
		return $dislikes->filter();
	}	

	/**
	* Return an HTML list of favorites for specified user
	* @param $include_button boolean - whether to include the favorite button
	*/
	public function getDislikesList($include_button = false)
	{
		if ( is_null($this->site_id) || $this->site_id == '' ) $this->site_id = get_current_blog_id();
		
		$dislikes = $this->getDislikesArray();
		$no_dislikes = $this->settings_repo->noDislikesText();

		// Post Type filters for data attr
		$post_types = '';
		if ( isset($this->filters['post_type']) ){
			$post_types = implode(',', $this->filters['post_type']);
		}
		
		if ( is_multisite() ) switch_to_blog($this->site_id);
		
		$out = '<ul class="dislikes-list" data-userid="' . $this->user_id . '" data-links="true" data-siteid="' . $this->site_id . '" ';
		$out .= ( $include_button ) ? 'data-includebuttons="true"' : 'data-includebuttons="false"';
		$out .= ( $this->links ) ? ' data-includelinks="true"' : ' data-includelinks="false"';
		$out .= ' data-nodislikestext="' . $no_dislikes . '"';
		$out .= ' data-posttype="' . $post_types . '"';
		$out .= '>';
		foreach ( $dislikes as $key => $dislike ){
			$out .= '<li data-postid="' . $dislike . '">';
			if ( $include_button ) $out .= '<p>';
			if ( $this->links ) $out .= '<a href="' . get_permalink($dislike) . '">';
			$out .= get_the_title($dislike);
			if ( $this->links ) $out .= '</a>';
			if ( $include_button ){
				$button = new DislikeButton($dislike, $this->site_id);
				$out .= '</p><p>';
				$out .= $button->display(false) . '</p>';
			}
			$out .= '</li>';
		}
		if ( empty($dislikes) ) $out .= '<li data-postid="0" data-nodislikes>' . $no_dislikes . '</li>';
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