<?php 

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Dislike\DislikeButton;

/**
* Post Actions and Filters
*/
class PostHooksDislike
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	/**
	* The Content
	*/
	private $content;

	/**
	* The Post Object
	*/
	private $post;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_filter('the_content', array($this, 'filterContent'));
	}

	/**
	* Filter the Content
	*/
	public function filterContent($content)
	{
		global $post;
		$this->post = $post;
		$this->content = $content;

		$display = $this->settings_repo->displayInPostType($post->post_type);
		if ( !$display ) return $content;

		return $this->addDislikeButton($display);
	}

	/**
	* Add the Favorite Button
	* @todo add favorite button html
	*/
	private function addDislikeButton($display_in)
	{
		$output = '';
		
		if ( isset($display_in['before_content']) && $display_in['before_content'] == 'true' ){
			$output .= get_dislikes_button();
		}
		
		$output .= $this->content;

		if ( isset($display_in['after_content']) && $display_in['after_content'] == 'true' ){
			$output .= get_dislikes_button();
		}
		return $output;
	}

}