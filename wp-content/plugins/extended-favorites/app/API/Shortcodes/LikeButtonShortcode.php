<?php 

namespace SimpleFavorites\API\Shortcodes;

class LikeButtonShortcode 
{

	/**
	* Shortcode Options
	* @var array
	*/
	private $options;

	public function __construct()
	{
		add_shortcode('like_button', array($this, 'renderView'));
	}

	/**
	* Shortcode Options
	*/
	private function setOptions($options)
	{
		$this->options = shortcode_atts(array(
			'post_id' => null,
			'site_id' => null
		), $options);
	}

	/**
	* Render the Button
	* @param $options, array of shortcode options
	*/
	public function renderView($options)
	{
		$this->setOptions($options);
		return get_likes_button($this->options['post_id'], $this->options['site_id']);
	}

}