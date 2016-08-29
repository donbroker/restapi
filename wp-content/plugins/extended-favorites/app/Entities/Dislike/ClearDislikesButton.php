<?php

namespace SimpleFavorites\Entities\Dislike;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Config\SettingsRepository;

class ClearDislikesButton
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
		if ( !$this->text ) $this->text = $this->settings_repo->clearDislikesText();
		if ( !$this->site_id ) $this->site_id = 1;
		$out = '<button class="dislikes-clear" data-siteid="' . $this->site_id . '">' . $this->text . '</button>';
		return $out;
	}
}