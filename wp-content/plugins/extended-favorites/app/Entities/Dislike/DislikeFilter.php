<?php 

namespace SimpleFavorites\Entities\Dislike;

/**
* Filters an array of favorites using provided array of filters
*/
class DislikeFilter 
{

	/**
	* Favorites
	* @var array of post IDs
	*/
	private $dislikes;

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

	public function __construct($dislikes, $filters)
	{
		$this->dislikes = $dislikes;
		$this->filters = $filters;
	}

	public function filter()
	{
		if ( isset($this->filters['post_type']) && is_array($this->filters['post_type']) ) $this->filterByPostType();
		if ( isset($this->filters['terms']) && is_array($this->filters['terms']) ) $this->filterByTerm();
		return $this->dislikes;
	}

	/**
	* Filter favorites by post type
	* @since 1.1.1
	* @param array $favorites
	*/
	private function filterByPostType()
	{
		foreach($this->dislikes as $key => $dislike){
			$post_type = get_post_type($dislike);
			if ( !in_array($post_type, $this->filters['post_type']) ) unset($this->dislike[$key]);
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
		$dislikes = $this->dislikes;
		
		foreach ( $dislikes as $key => $dislike ) :

			$all_terms = array();
			$all_filters = array();

			foreach ( $taxonomies as $taxonomy => $terms ){
				if ( !isset($terms) || !is_array($terms) ) continue;
				$post_terms = wp_get_post_terms($dislike, $taxonomy, array("fields" => "slugs"));
				if ( !empty($post_terms) ) $all_terms = array_merge($all_terms, $post_terms);
				$all_filters = array_merge($all_filters, $terms);
			}

			$dif = array_diff($all_filters, $all_terms);
			if ( !empty($dif) ) unset($dislikes[$key]);		

		endforeach;

		$this->dislikes = $dislikes;
	}

}