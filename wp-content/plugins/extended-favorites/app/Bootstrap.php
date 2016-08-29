<?php 

namespace SimpleFavorites;

/**
* Plugin Bootstrap
*/
class Bootstrap 
{

	public function __construct()
	{
		$this->init();
		add_action( 'init', array($this, 'startSession') );
		add_filter( 'plugin_action_links_' . 'favorites/favorites.php', array($this, 'settingsLink' ) );
		add_action( 'plugins_loaded', array($this, 'addLocalization') );
	}

	/**
	* Initialize
	*/
	public function init()
	{
		new Config\Settings;
		new Activation\Activate;
		new Activation\Dependencies;
		new Entities\Post\PostHooks;
		new Entities\Post\PostHooksLike;
		new Entities\Post\PostHooksDislike;
		new Events\RegisterPublicEvents;
		new Entities\Post\PostMeta;
		new Entities\Post\PostMetaLike;
		new Entities\Post\PostMetaDislike;
		new API\Shortcodes\ButtonShortcode;
		new API\Shortcodes\LikeButtonShortcode;
		new API\Shortcodes\DislikeButtonShortcode;
		new API\Shortcodes\FavoriteCountShortcode;
		new API\Shortcodes\LikeCountShortcode;
		new API\Shortcodes\DislikeCountShortcode;
		new API\Shortcodes\UserFavoritesShortcode;
		new API\Shortcodes\UserLikesShortcode;
		new API\Shortcodes\UserDislikesShortcode;
		new API\Shortcodes\UserFavoriteCount;
		new API\Shortcodes\UserLikeCount;
		new API\Shortcodes\UserDislikeCount;
		new API\Shortcodes\PostFavoritesShortcode;
		new API\Shortcodes\PostLikesShortcode;
		new API\Shortcodes\PostDislikesShortcode;
		new API\Shortcodes\ClearFavoritesShortcode;
		new API\Shortcodes\ClearLikesShortcode;
		new API\Shortcodes\ClearDislikesShortcode;
	}

	/**
	* Add a link to the settings on the plugin page
	*/
	public function settingsLink($links)
	{ 
		$settings_link = '<a href="options-general.php?page=simple-favorites">' . __('Settings', 'simplefavorites') . '</a>'; 
		$help_link = '<a href="http://favoriteposts.com">' . __('FAQ','simplefavorites') . '</a>'; 
		array_unshift($links, $help_link); 
		array_unshift($links, $settings_link);
		return $links; 
	}

	/**
	* Localization Domain
	*/
	public function addLocalization()
	{
		load_plugin_textdomain(
			'simplefavorites', 
			false, 
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
	}

	/**
	* Initialize a Session
	*/
	public function startSession()
	{
		if ( !session_id() ) session_start();
	}

}