<?php 

namespace SimpleFavorites\Entities\Like;

/**
* Filters an array of favorites using provided array of filters
*/
class LikeFilter 
{

	/**
	* Favorites
	* @var array of post IDs
	*/
	private $likes;

	/**
	* Filters
	* @var array
	*
	* Example: 
	*
	* array(
	* 	'post_type' => array('post', 'posttypetwo'),
	*	'terms' => array(
	*		'category' => array(
	*			'termone', 'termtwo', 'termthree'
	*		),
	*		'other-taxonomy' => array(
	*			'termone', 'termtwo', 'termthree'
	*		)
	*	)
	* );
	*
	*/
	private $filters;

	public function __construct($likes, $filters)
	{
		$this->likes = $likes;
		$this->filters = $filters;
	}

	public function filter()
	{
		if ( isset($this->filters['post_type']) && is_array($this->filters['post_type']) ) $this->filterByPostType();
		if ( isset($this->filters['terms']) && is_array($this->filters['terms']) ) $this->filterByTerm();
		return $this->likes;
	}

	/**
	* Filter favorites by post type
	* @since 1.1.1
	* @param array $favorites
	*/
	private function filterByPostType()
	{
		foreach($this->likes as $key => $like){
			$post_type = get_post_type($like);
			if ( !in_array($post_type, $this->filters['post_type']) ) unset($this->likes[$key]);
		}
	}

	/**
	* Filter favorites by terms
	* @since 1.1.1
	* @param array $favorites
	*/
	private function filterByTerm()
	{
		$taxonomies = $this->filters['terms'];
		$likes = $this->likes;
		
		foreach ( $likes as $key => $like ) :

			$all_terms = array();
			$all_filters = array();

			foreach ( $taxonomies as $taxonomy => $terms ){
				if ( !isset($terms) || !is_array($terms) ) continue;
				$post_terms = wp_get_post_terms($like, $taxonomy, array("fields" => "slugs"));
				if ( !empty($post_terms) ) $all_terms = array_merge($all_terms, $post_terms);
				$all_filters = array_merge($all_filters, $terms);
			}

			$dif = array_diff($all_filters, $all_terms);
			if ( !empty($dif) ) unset($likes[$key]);		

		endforeach;

		$this->likes = $likes;
	}

}