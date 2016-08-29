<?php

namespace SimpleFavorites\Entities\Like;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Config\SettingsRepository;

class ClearLikesButton
{
	/**
	* Site ID
	*/
	private $site_id;

	/**
	* User Respository
	*/
	private $user;

	/**
	* The Button Text
	*/
	private $text;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct($site_id, $text)
	{
		$this->user = new UserRepository;
		$this->settings_repo = new SettingsRepository;
		$this->site_id = $site_id;
		$this->text = $text;
	}

	/**
	* Display the button
	*/
	public function display()
	{
		if ( !$this->user->getsButton() ) return false;
		if ( !$this->text ) $this->text = $this->settings_repo->clearLikesText();
		if ( !$this->site_id ) $this->site_id = 1;
		$out = '<button class="likes-clear" data-siteid="' . $this->site_id . '">' . $this->text . '</button>';
		return $out;
	}
}