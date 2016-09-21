<?php

class Geodir_REST_Listings_Controller extends WP_REST_Posts_Controller {
   protected $post_type;

    public function __construct( $post_type ) {
        $this->post_type    = $post_type;
        $this->namespace    = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
        $obj                = get_post_type_object( $post_type );
        $this->rest_base    = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                        'description'  => __( 'Whether to bypass trash and force deletion.' ),
                    ),
                ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }
    
    /**
     * Get the query params for collections of attachments.
     *
     * @return array
     */
    public function get_collection_params() {
        $params = parent::get_collection_params();
                
        // $sort_options = geodir_rest_get_listing_sorting( $this->post_type );
        // var_dump($sort_options); die();
        // $orderby            = array_keys( $sort_options['sorting'] );
        // $orderby_rendered   = $sort_options['sorting'];
        // $default_orderby    = $sort_options['default_sortby'];
        // $default_order      = $sort_options['default_sort'];

        $default_dist       = 250000;
        $default_lat        = 51.0530588;
        $default_lon        = -114.0625613;
        $default_sort_by    = 'nearest';
        $sort_by_options    = array(
            'newest', 
            'oldest', 
            'low_review', 
            'rating_count_asc',
            'high_review',
            'rating_count_desc',
            'low_rating',
            'high_rating',
            'featured',
            'nearest',
            'farthest',
            'random',
            'az',
            'high_views',
            'low_views',
            'expensive',
            'cheap'
        );

        $params['is_featured']   = array(
            'description'        => __( 'Limit response to featured resouces.' ),
            'default'            => null,
            'enum'               => array(0, 1),
            'validate_callback'  => 'rest_validate_request_arg',
        );           

        $params['sdist']    = array(
            'description'        => __( 'Limit response to resources within some distance from current position.' ),
            'type'               => 'integer',
            'default'            => $default_dist,
            'sanitize_callback'  => 'absint',
            'validate_callback'  => 'rest_validate_request_arg',
        );

        $params['sort_by']    = array(
            'description'        => __( 'retrieve result sort by some content.' ),
            'type'               => 'string',
            'default'            => $default_sort_by,
            'enum'               => $sort_by_options,
            'validate_callback'  => 'rest_validate_request_arg',
        );

        $params['sgeo_lat']    = array(
            'description'        => __( 'Pass lat of current position to server' ),
            'type'               => 'float',
            'default'            => $default_lat,
            'validate_callback'  => 'rest_validate_request_arg',
        );

        $params['sgeo_lon']    = array(
            'description'        => __( 'Pass lon of current position to server' ),
            'type'               => 'float',
            'default'            => $default_lon,
            'validate_callback'  => 'rest_validate_request_arg',
        );

        $params['context']['default'] = 'view';

        $params['after'] = array(
            'description'        => __( 'Limit response to resources published after a given ISO8601 compliant date.' ),
            'type'               => 'string',
            'format'             => 'date-time',
            'validate_callback'  => 'rest_validate_request_arg',
        );
        if ( post_type_supports( $this->post_type, 'author' ) ) {
            $params['author'] = array(
                'description'         => __( 'Limit result set to posts assigned to specific authors.' ),
                'type'                => 'array',
                'default'             => array(),
                'sanitize_callback'   => 'wp_parse_id_list',
                'validate_callback'   => 'rest_validate_request_arg',
            );
            $params['author_exclude'] = array(
                'description'         => __( 'Ensure result set excludes posts assigned to specific authors.' ),
                'type'                => 'array',
                'default'             => array(),
                'sanitize_callback'   => 'wp_parse_id_list',
                'validate_callback'   => 'rest_validate_request_arg',
            );
        }
        $params['before'] = array(
            'description'        => __( 'Limit response to resources published before a given ISO8601 compliant date.' ),
            'type'               => 'string',
            'format'             => 'date-time',
            'validate_callback'  => 'rest_validate_request_arg',
        );
        $params['exclude'] = array(
            'description'        => __( 'Ensure result set excludes specific ids.' ),
            'type'               => 'array',
            'default'            => array(),
            'sanitize_callback'  => 'wp_parse_id_list',
        );
        $params['include'] = array(
            'description'        => __( 'Limit result set to specific ids.' ),
            'type'               => 'array',
            'default'            => array(),
            'sanitize_callback'  => 'wp_parse_id_list',
        );
        if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
            $params['menu_order'] = array(
                'description'        => __( 'Limit result set to resources with a specific menu_order value.' ),
                'type'               => 'integer',
                'sanitize_callback'  => 'absint',
                'validate_callback'  => 'rest_validate_request_arg',
            );
        }
        $params['offset'] = array(
            'description'        => __( 'Offset the result set by a specific number of items.' ),
            'type'               => 'integer',
            'sanitize_callback'  => 'absint',
            'validate_callback'  => 'rest_validate_request_arg',
        );
//        $params['order'] = array(
//            'description'        => __( 'Order sort attribute ascending or descending.' ),
//            'type'               => 'string',
//            'default'            => $default_order,
//            'enum'               => array( 'asc', 'desc' ),
//            'validate_callback'  => 'rest_validate_request_arg',
//        );
        // $params['orderby'] = array(
        //     'description'        => __( 'Sort collection by object attribute.' ),
        //     'type'               => 'string',
        //     'default'            => $default_orderby,
        //     'enum'               => $orderby,
        //     'validate_callback'  => 'rest_validate_request_arg'
        // );
        // $params['orderby_rendered']    = array(
        //     'description'           => __( 'All sorting options for listings.' ),
        //     'type'                  => 'array',
        //     'enum'                  => $orderby_rendered
        // );

        $post_type_obj = get_post_type_object( $this->post_type );
        if ( $post_type_obj->hierarchical || 'attachment' === $this->post_type ) {
            $params['parent'] = array(
                'description'       => __( 'Limit result set to those of particular parent ids.' ),
                'type'              => 'array',
                'sanitize_callback' => 'wp_parse_id_list',
                'default'           => array(),
            );
            $params['parent_exclude'] = array(
                'description'       => __( 'Limit result set to all items except those of a particular parent id.' ),
                'type'              => 'array',
                'sanitize_callback' => 'wp_parse_id_list',
                'default'           => array(),
            );
        }

        $params['slug'] = array(
            'description'       => __( 'Limit result set to posts with a specific slug.' ),
            'type'              => 'string',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $params['status'] = array(
            'default'           => 'publish',
            'description'       => __( 'Limit result set to posts assigned a specific status.' ),
            'sanitize_callback' => 'sanitize_key',
            'type'              => 'string',
            'validate_callback' => array( $this, 'validate_user_can_query_private_statuses' ),
        );
        $params['filter'] = array(
            'description'       => __( 'Use WP Query arguments to modify the response; private query vars require appropriate authorization.' ),
        );

        $taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
        foreach ( $taxonomies as $taxonomy ) {
            $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

            $params[ $base ] = array(
                'description'       => sprintf( __( 'Limit result set to all items that have the specified term assigned in the %s taxonomy.' ), $base ),
                'type'              => 'array',
                'sanitize_callback' => 'wp_parse_id_list',
                'default'           => array(),
            );
        }
        return $params;
    }

    /**
     * Prepare links for the request.
     *
     * @param WP_Post $post Post object.
     * @return array Links for the given post.
     */
    protected function prepare_links( $post ) {
        $base = sprintf( '/%s/%s', $this->namespace, $this->rest_base );

        // Entity meta
        $links = array(
            'self' => array(
                'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
            ),
            'collection' => array(
                'href'   => rest_url( $base ),
            ),
            'about'      => array(
                'href'   => rest_url( '/wp/v2/types/' . $this->post_type ),
            ),
        );

        if ( ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'author' ) )
            && ! empty( $post->post_author ) ) {
            $links['author'] = array(
                'href'       => rest_url( '/wp/v2/users/' . $post->post_author ),
                'embeddable' => true,
            );
        };

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'comments' ) ) {
            $replies_url = rest_url( '/wp/v2/comments' );
            $replies_url = add_query_arg( 'post', $post->ID, $replies_url );
            $links['replies'] = array(
                'href'         => $replies_url,
                'embeddable'   => true,
            );
        }

        if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'revisions' ) ) {
            $links['version-history'] = array(
                'href' => rest_url( trailingslashit( $base ) . $post->ID . '/revisions' ),
            );
        }
        $post_type_obj = get_post_type_object( $post->post_type );
        if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) ) {
            $links['up'] = array(
                'href'       => rest_url( trailingslashit( $base ) . (int) $post->post_parent ),
                'embeddable' => true,
            );
        }

        // If we have a featured media, add that.
        if ( $featured_media = get_post_thumbnail_id( $post->ID ) ) {
            $image_url = rest_url( 'wp/v2/media/' . $featured_media );
            $links['https://api.w.org/featuredmedia'] = array(
                'href'       => $image_url,
                'embeddable' => true,
            );
        }
        if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ) ) ) {
            $attachments_url = rest_url( 'wp/v2/media' );
            $attachments_url = add_query_arg( 'parent', $post->ID, $attachments_url );
            $links['https://api.w.org/attachment'] = array(
                'href'       => $attachments_url,
            );
        }

        $taxonomies_controller = new Geodir_REST_Taxonomies_Controller;
        
        $taxonomies = get_object_taxonomies( $post->post_type );
        if ( ! empty( $taxonomies ) ) {
            $links['https://api.w.org/term'] = array();

            foreach ( $taxonomies as $tax ) {
                $taxonomy_obj = get_taxonomy( $tax );
                // Skip taxonomies that are not public.
                if ( empty( $taxonomy_obj->show_in_rest ) ) {
                    continue;
                }

                $tax_base = ! empty( $taxonomy_obj->rest_base ) ? $taxonomy_obj->rest_base : $tax;
                $terms_url = add_query_arg(
                    'post',
                    $post->ID,
                    rest_url( $this->namespace . '/' . $tax_base )
                );

                $links['https://api.w.org/term'][] = array(
                    'href'       => $terms_url,
                    'taxonomy'   => $tax,
                    'embeddable' => true,
                );
            }
        }

        return $links;
    }
    public function get_items( $request ) {
        global $sdist, $sgeo_lon, $sgeo_lat, $sort_by, $s, $is_featured;
        $args                         = array();
        $args['author__in']           = $request['author'];
        $args['author__not_in']       = $request['author_exclude'];
        $args['menu_order']           = $request['menu_order'];
        $args['offset']               = $request['offset'];
        $args['order']                = $request['order'];
        $args['orderby']              = $request['orderby'];
        $args['paged']                = $request['page'];
        $args['post__in']             = $request['include'];
        $args['post__not_in']         = $request['exclude'];
        $args['posts_per_page']       = $request['per_page'];
        $args['name']                 = $request['slug'];
        $args['post_parent__in']      = $request['parent'];
        $args['post_parent__not_in']  = $request['parent_exclude'];
        $args['post_status']          = $request['status'];
        $s                            = $args['s']           = $request['search'];
        $sdist                        = $args['sdist']       = $request['sdist'];
        $sgeo_lon                     = $args['sgeo_lon']    = $request['sgeo_lon'];
        $sgeo_lat                     = $args['sgeo_lat']    = $request['sgeo_lat'];
        $sort_by                      = $args['sort_by']     = $request['sort_by'];
        $is_featured                  = $args['is_featured'] = $request['is_featured']; 

        $args['date_query'] = array();
        // Set before into date query. Date query must be specified as an array of an array.
        if ( isset( $request['before'] ) ) {
            $args['date_query'][0]['before'] = $request['before'];
        }

        // Set after into date query. Date query must be specified as an array of an array.
        if ( isset( $request['after'] ) ) {
            $args['date_query'][0]['after'] = $request['after'];
        }

        if ( is_array( $request['filter'] ) ) {
            $args = array_merge( $args, $request['filter'] );
            unset( $args['filter'] );
        }

        // Force the post_type argument, since it's not a user input variable.
        $args['post_type'] = $this->post_type;

        $args = apply_filters( "rest_{$this->post_type}_query", $args, $request );
        $query_args = $this->prepare_items_query( $args, $request );

        $taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
        foreach ( $taxonomies as $taxonomy ) {
            $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

            if ( ! empty( $request[ $base ] ) ) {
                $query_args['tax_query'][] = array(
                    'taxonomy'         => $taxonomy->name,
                    'field'            => 'term_id',
                    'terms'            => $request[ $base ],
                    'include_children' => false,
                );
            }
        }

        add_action('pre_get_posts', array($this, 'ido_geodir_listing_loop_filter'), 1);
        
        // add_filter( 'posts_request', array($this,'my_posts_request_filter') );
        
        $posts_query = new WP_Query();
        $query_result = $posts_query->query( $query_args );
        // var_dump($query_result);
        remove_action('pre_get_posts', array($this, 'ido_geodir_listing_loop_filter'), 1);

        $posts = array();
        foreach ( $query_result as $post ) {
            if ( ! $this->check_read_permission( $post ) ) {
                continue;
            }
            $data = $this->prepare_item_for_response( $post, $request );
            $posts[] = $this->prepare_response_for_collection( $data );
        }

        $page = (int) $query_args['paged'];
        $total_posts = $posts_query->found_posts;

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count
            unset( $query_args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $query_args );
            $total_posts = $count_query->found_posts;
        }

        $max_pages = ceil( $total_posts / (int) $args['posts_per_page'] );

        $response = rest_ensure_response( $posts );
        $response->header( 'X-WP-Total', (int) $total_posts );
        $response->header( 'X-WP-TotalPages', (int) $max_pages );

        $request_params = $request->get_query_params();
        if ( ! empty( $request_params['filter'] ) ) {
            // Normalize the pagination params.
            unset( $request_params['filter']['posts_per_page'] );
            unset( $request_params['filter']['paged'] );
        }
        $base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

        if ( $page > 1 ) {
            $prev_page = $page - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $page ) {
            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }


    public function ido_geodir_listing_loop_filter($query) {
        
        global $plugin_prefix, $table, $views_counter_table, $wpdb;

        $geodir_post_type = $this->post_type;

        $table = $plugin_prefix . $geodir_post_type . '_detail';
        $views_counter_table = $wpdb->prefix."post_views";

        add_filter('posts_fields', array($this, 'ido_geodir_posts_fields'), 1);
        add_filter('posts_join', array($this, 'ido_geodir_posts_join'), 1);
        add_filter('posts_where', array($this, 'ido_searching_filter_where'), 1);
        add_filter('posts_orderby', array($this, 'ido_geodir_posts_orderby'), 1);

        return $query;
    }

    function ido_geodir_posts_fields($fields) {
        global $table,$sgeo_lon, $sgeo_lat, $views_counter_table;

        $fields .= ", " . $table . ".* ";
        $fields .= ", " . $views_counter_table . ".count ";

        $DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));

        $fields .= " , (" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($sgeo_lat) - ABS(" . $table . ".post_latitude)) * pi()/180 / 2), 2) +COS(ABS($sgeo_lat) * pi()/180) * COS( ABS(" . $table . ".post_latitude) * pi()/180) *POWER(SIN(($sgeo_lon - " . $table . ".post_longitude) * pi()/180 / 2), 2) )))as distance ";

        return $fields;
    }


    function ido_geodir_posts_join($join) {
        
        global $wpdb, $table, $views_counter_table;

        $join .= " INNER JOIN " . $table . " ON (" . $table . ".post_id = $wpdb->posts.ID)  ";
        $join .= " LEFT JOIN " . $views_counter_table ." ON (" . $views_counter_table . ".id = $wpdb->posts.ID) ";

        return $join;    
    }


    function ido_searching_filter_where($where) {
        global $wpdb, $table, $sdist, $sgeo_lat, $sgeo_lon, $s, $is_featured;

        $content_where = '';
        if ( $s != '' ) {
            $content_where = "AND ($wpdb->posts.post_content LIKE \"$s\" OR $wpdb->posts.post_content LIKE \"$s%\" OR $wpdb->posts.post_content LIKE \"% $s%\" OR $wpdb->posts.post_content LIKE \"%>$s%\" OR $wpdb->posts.post_content LIKE \"%\n$s%\") ";
        }

        if ( $is_featured === '0' || $is_featured === '1' ) {
            $content_where .= "AND " . $table . ".is_featured = " . $is_featured;
        }
            

        $lon1 = $sgeo_lon - $sdist / abs(cos(deg2rad($sgeo_lat)) * 69);
        $lon2 = $sgeo_lon + $sdist / abs(cos(deg2rad($sgeo_lat)) * 69);
        $lat1 = $sgeo_lat - ($sdist / 69);
        $lat2 = $sgeo_lat + ($sdist / 69);

        $rlon1 = is_numeric(min($lon1, $lon2)) ? min($lon1, $lon2) : '';
        $rlon2 = is_numeric(max($lon1, $lon2)) ? max($lon1, $lon2) : '';
        $rlat1 = is_numeric(min($lat1, $lat2)) ? min($lat1, $lat2) : '';
        $rlat2 = is_numeric(max($lat1, $lat2)) ? max($lat1, $lat2) : '';



        $where .= " $content_where
                 AND ( " . $table . ".post_latitude between $rlat1 and $rlat2 )
                 AND ( " . $table . ".post_longitude between $rlon1 and $rlon2 ) ";

//        $DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
//        $where .= " AND CONVERT((" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($sgeo_lat) - ABS(" . $table . ".post_latitude)) * pi()/180 / 2), 2) +COS(ABS($sgeo_lat) * pi()/180) * COS( ABS(" . $table . ".post_latitude) * pi()/180) *POWER(SIN(($sgeo_lon - " . $table . ".post_longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= " . $sdist;

        return $where;
    }

    function ido_geodir_posts_orderby($orderby) {
        global $wpdb, $table, $plugin_prefix, $sort_by, $s, $views_counter_table;

        switch ($sort_by):
            case 'newest':
                $orderby = "$wpdb->posts.post_date desc ";
                break;
            case 'oldest':
                $orderby = "$wpdb->posts.post_date asc ";
                break;
            case 'low_review':
            case 'rating_count_asc':
                $orderby = $table . ".rating_count ASC, " . $table . ".overall_rating ASC ";
                break;
            case 'high_review':
            case 'rating_count_desc':
                $orderby = $table . ".rating_count DESC, " . $table . ".overall_rating DESC ";
                break;
            case 'low_rating':
                $orderby = "( " . $table . ".overall_rating  ) ASC, " . $table . ".rating_count ASC  ";
                break;
            case 'high_rating':
                $orderby = " " . $table . ".overall_rating DESC, " . $table . ".rating_count DESC ";
                break;
            case 'featured':
                $orderby = $table . ".is_featured asc ";
                break;
            case 'nearest':
                $orderby = " distance asc ";
                break;
            case 'farthest':
                $orderby = " distance desc ";
                break;
            case 'random':
                $orderby = " rand() ";
                break;
            case 'az':
                $orderby = "$wpdb->posts.post_title asc ";
                break;
            case 'high_views':
                $orderby = "$views_counter_table.count desc ";
                break;
            case 'low_views':
                $orderby = "$views_counter_table.count asc ";
                break;
            case 'expensive':
                $orderby = $table . ".geodir_price desc ";
                break;
            case 'cheap':
                $orderby = $table . ".geodir_price asc ";
                break;
            default:

                break;
        endswitch;

        return $orderby;
    }


    protected function get_allowed_query_vars() {

        global $wp;

        /**
         * Filter the publicly allowed query vars.
         *
         * Allows adjusting of the default query vars that are made public.
         *
         * @param array  Array of allowed WP_Query query vars.
         */
        $valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

        $post_type_obj = get_post_type_object( $this->post_type );
        if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
            /**
             * Filter the allowed 'private' query vars for authorized users.
             *
             * If the user has the `edit_posts` capability, we also allow use of
             * private query parameters, which are only undesirable on the
             * frontend, but are safe for use in query strings.
             *
             * To disable anyway, use
             * `add_filter( 'rest_private_query_vars', '__return_empty_array' );`
             *
             * @param array $private_query_vars Array of allowed query vars for authorized users.
             * }
             */
            $private = apply_filters( 'rest_private_query_vars', $wp->private_query_vars );
            $valid_vars = array_merge( $valid_vars, $private );
        }
        // Define our own in addition to WP's normal vars.
        $rest_valid = array(
            'author__in',
            'author__not_in',
            'ignore_sticky_posts',
            'menu_order',
            'offset',
            'post__in',
            'post__not_in',
            'post_parent',
            'post_parent__in',
            'post_parent__not_in',
            'posts_per_page',
            'date_query',
            'sort_by',
            'sdist',
            'sgeo_lat',
            'sgeo_lon',
            'is_featured',
        );
        $valid_vars = array_merge( $valid_vars, $rest_valid );

        /**
         * Filter allowed query vars for the REST API.
         *
         * This filter allows you to add or remove query vars from the final allowed
         * list for all requests, including unauthenticated ones. To alter the
         * vars for editors only, {@see rest_private_query_vars}.
         *
         * @param array {
         *    Array of allowed WP_Query query vars.
         *
         *    @param string $allowed_query_var The query var to allow.
         * }
         */
        $valid_vars = apply_filters( 'rest_query_vars', $valid_vars );

        return $valid_vars;
    }

    function my_posts_request_filter( $input ) {
        print_r( $input );
        return $input;
    }

    function create_item( $request ) {
      // var_dump($request); die();
      add_action( "rest_insert_{$this->post_type}", array($this, "ido_create_geo_item"), 10, 3);
      $response = parent::create_item($request);
      remove_action("rest_insert_{$this->post_type}", array($this, "ido_create_geo_item"), 10, 3);
      return $response;
    }

    function ido_create_geo_item( $post, $request, $is_create = true ) {
        global $wpdb, $plugin_prefix;

        $table = $plugin_prefix . $this->post_type . '_detail';
        
        $params = $request->get_params();

        $post_id          = $post->ID;
        $post_title       = $post->post_title;
        $post_status      = $post->post_status;
        $post_tags        = isset($params['post_tags'])?$params['post_tags']:'';
        $gd_advertcategory= isset($params['gd_advertcategory'])?$params['gd_advertcategory']:'';
        $is_featured      = '0';
        $featured_image   = isset($params['featured_image'])?$params['featured_image']:'/default.jpg';
        $submit_time      = time();
        $submit_ip        = $_SERVER['REMOTE_ADDR'];
        $post_address     = isset($params['post_address'])?$params['post_zip']:'';
        $post_city        = 'Calgary';
        $post_region      = 'Region';
        $post_country     = 'Canada';
        $post_zip         = isset($params['post_zip'])?$params['post_zip']:'';
        $post_latitude    = isset($params['post_latitude'])?$params['post_latitude']:'';
        $post_longitude   = isset($params['post_longitude'])?$params['post_longitude']:'';
        $post_mapview     = isset($params['post_mapview'])?$params['post_mapview']:'ROADMAP';
        $geodir_contact   = isset($params['geodir_contact'])?$params['geodir_contact']:'';
        $geodir_email     = isset($params['geodir_email'])?$params['geodir_email']:'';
        $geodir_price     = isset($params['geodir_price'])?(int)$params['geodir_price']:0;

        $wpdb->query(
            $wpdb->prepare( 
                "INSERT INTO " . $table . "(`post_id`, `post_title`, `post_status`, `post_tags`, `gd_advertcategory`, `is_featured`, `featured_image`, `submit_time`, `submit_ip`, `post_address`, `post_city`,`post_region`, `post_country`, `post_zip`, `post_latitude`, `post_longitude`, `post_mapview`, `geodir_contact`, `geodir_email`, `geodir_price`)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d)", array($post_id, $post_title, $post_status, $post_tags, $gd_advertcategory, $is_featured, $featured_image, $submit_time,$submit_ip, $post_address, $post_city, $post_region, $post_country, $post_zip, $post_latitude, $post_longitude, $post_mapview, $geodir_contact, $geodir_email, $geodir_price)
            )
        );

        if ( isset( $params['post_image_ids'] ) ) {
            $post_image_ids         = $params['post_image_ids'];
            $post_images_ids_array  = explode ( ',' , $post_image_ids );

            $table = 'wp_geodir_attachments';
            foreach ($post_images_ids_array as $post_images_id) {
                $wpdb->query(
                    $wpdb->prepare( 
                        "UPDATE " . $table . " SET post_id = %d WHERE ID = %d", array($post_id, $post_images_id)
                    )
                );
            }
        }
    } 

}
