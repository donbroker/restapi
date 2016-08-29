<?php 

namespace SimpleFavorites\Entities\Dislike;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Post\DislikeCount;
use SimpleFavorites\Config\SettingsRepository;

class DislikeButton 
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

		$count = new DislikeCount();
		$count = $count->getCount($this->post_id, $this->site_id);

		$disliked = ( $this->user->isDislike($this->post_id, $this->site_id) ) ? true : false;

		$text = ( $disliked )
			? html_entity_decode($this->settings_repo->buttonTextdisLiked()) 
			: html_entity_decode($this->settings_repo->dislikeButtonText());

		$out = '<button class="dislike-button';
		
		// Button Classes
		if ( $disliked ) $out .= ' active';
		if ( $this->settings_repo->includeCountInDislikeButton() ) $out .= ' has-count';
		
		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading ) $out .= ' loading';

		$out .= '" data-postid="' . $this->post_id . '" data-siteid="' . $this->site_id . '" data-favoritecount="' . $count . '">';

		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading){
			$out .= $this->settings_repo->loadingText();
			$spinner = ($disliked) ? $this->settings_repo->loadingImage('active') : $this->settings_repo->loadingImage();
			if ( $spinner ) $out .= $spinner;
		} else {
			$out .= $text;
			if ( $this->settings_repo->includeCountInDislikeButton() ) $out .= '<span class="dislike-button-count">' . $count . '<span>';
		}
		$out .= '</button>';
		return $out;
	}

}