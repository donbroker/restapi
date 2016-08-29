<?php 

namespace SimpleFavorites\API\Shortcodes;

class LikeCountShortcode
{

	/**
	* Shortcode Options
	* @var array
	*/
	private $options;

	public function __construct()
	{
		add_shortcode('like_count', array($this, 'renderView'));
	}

	/**
	* Shortcode Options
	*/
	private function setOptions($options)
	{
		$this->options = shortcode_atts(array(
			'post_id' => '',
			'site_id' => ''
		), $options);
	}

	/**
	* Render the count
	* @param $options, array of shortcode options
	*/
	public function renderView($options)
	{
		$this->setOptions($options);
		return get_likes_count($this->options['post_id'], $this->options['site_id']);
	}

}