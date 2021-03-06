<?php 

namespace SimpleFavorites\API\Shortcodes;

class ClearLikesShortcode
{

	/**
	* Shortcode Options
	* @var array
	*/
	private $options;

	public function __construct()
	{
		add_shortcode('clear_likes_button', array($this, 'renderView'));
	}

	/**
	* Shortcode Options
	*/
	private function setOptions($options)
	{
		$this->options = shortcode_atts(array(
			'site_id' => null,
			'text' => null
		), $options);
	}

	/**
	* Render the Button
	* @param $options, array of shortcode options
	*/
	public function renderView($options)
	{
		$this->setOptions($options);
		return get_clear_likes_button($this->options['site_id'], $this->options['text']);
	}

}