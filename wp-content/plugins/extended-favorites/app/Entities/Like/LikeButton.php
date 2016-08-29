<?php 

namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Post\LikeCount;
use SimpleFavorites\Config\SettingsRepository;

class LikeButton 
{

	/**
	* The Post ID
	*/
	private $post_id;

	/**
	* Site ID
	*/
	private $site_id;

	/**
	* User Respository
	*/
	private $user;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct($post_id, $site_id)
	{
		$this->user = new UserRepository;
		$this->settings_repo = new SettingsRepository;
		$this->post_id = $post_id;
		$this->site_id = $site_id;
	}

	/**
	* Diplay the Button
	* @param boolean loading - whether to include loading class
	* @return html
	*/
	public function display($loading = true)
	{
		if ( !$this->user->getsButton() ) return false;

		$count = new LikeCount();
		$count = $count->getCount($this->post_id, $this->site_id);

		$liked = ( $this->user->isLike($this->post_id, $this->site_id) ) ? true : false;

		$text = ( $liked )
			? html_entity_decode($this->settings_repo->buttonTextLiked()) 
			: html_entity_decode($this->settings_repo->likeButtonText());

		$out = '<button class="like-button';
		
		// Button Classes
		if ( $liked ) $out .= ' active';
		if ( $this->settings_repo->includeCountInLikeButton() ) $out .= ' has-count';
		
		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading ) $out .= ' loading';

		$out .= '" data-postid="' . $this->post_id . '" data-siteid="' . $this->site_id . '" data-favoritecount="' . $count . '">';

		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading){
			$out .= $this->settings_repo->loadingText();
			$spinner = ($liked) ? $this->settings_repo->loadingImage('active') : $this->settings_repo->loadingImage();
			if ( $spinner ) $out .= $spinner;
		} else {
			$out .= $text;
			if ( $this->settings_repo->includeCountInLikeButton() ) $out .= '<span class="like-button-count">' . $count . '<span>';
		}
		$out .= '</button>';
		return $out;
	}

}