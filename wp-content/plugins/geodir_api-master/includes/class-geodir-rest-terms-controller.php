<?php
/**
 * Access terms associated with a taxonomy
 */
class Geodir_REST_Terms_Controller extends WP_REST_Terms_Controller {
	protected $taxonomy;

	public function __construct( $taxonomy ) {
		$this->taxonomy  = $taxonomy;
		$this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$tax_obj         = get_taxonomy( $taxonomy );
		$this->rest_base = !empty( $tax_obj->rest_base ) ? $tax_obj->rest_base : $tax_obj->name;
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/'.$this->rest_base,
			array(
				array(
					'methods'               => WP_REST_Server::READABLE,
					'callback'              => array( $this, 'get_items' ),
					'permission_callback'   => array( $this, 'get_items_permissions_check' ),
					'args'                  => $this->get_collection_params(),
					),
				array(
					'methods'               => WP_REST_Server::CREATABLE,
					'callback'              => array($this, 'create_item' ),
					'permission_callback'   => array( $this, 'create_item_permissions_check' ),
					'args'                  => $this->get_endpoint_args_for_item_schema (WP_REST_Server::CREATABLE ),
				),
				'schema'                    => array($this, 'get_public_item_schema' ),
			)
		);

		register_rest_route( $this->namespace, '/'.$this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'                => WP_REST_Server::READABLE,
					'callback'               => array( $this, 'get_item' ),
					'permission_callback'    => array( $this, 'get_item_permissions_check' ),
					'args'                   => array(
						'context'            => $this->get_context_param(
							array( 'default' => 'view' ) ),
					),
				),
				array( 
					'methods'                => WP_REST_Server::EDITABLE,
					'callback'               => array( $this, 'update_item' ),
					'permission_callback'    => array( $this, 'update_item_permissions_check' ),
					'args'                   => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'                => WP_REST_Server::DELETABLE,
					'callback'               => array( $this, 'delete_item' ),
					'permission_callback'    => array( $this, 'delete_item_permissions_check' ),
					'args'                   => array( 
						'force'              => array( 
							'default'        => false,
							'description'    => __( 'Required to be true, as resource does not support trashing.' ),
						),
					),
				),
				'schema'                     => array( $this, 'get_public_item_schema' ),
			)
		);
	}
		/**
	 * Prepare a single term output for response
	 *
	 * @param obj $item Term object
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $item, $request ) {

		$data = array(
			'id'           => (int) $item->term_id,
			'count'        => (int) $item->count,
			'description'  => $item->description,
			'link'         => get_term_link( $item ),
			'name'         => $item->name,
			'slug'         => $item->slug,
			'taxonomy'     => $item->taxonomy,
		);
		$schema = $this->get_item_schema();
		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $item->parent;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		// $response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original term object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( "rest_prepare_{$this->taxonomy}", $response, $item, $request );
	}
}