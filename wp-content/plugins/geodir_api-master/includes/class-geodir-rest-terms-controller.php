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
}