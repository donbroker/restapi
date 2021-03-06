<?php
/**
 * WP Rest API functions & actions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
 
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function geodir_rest_log( $log, $title = '', $file = '', $line = '', $exit = false ) {
    $should_log = apply_filters( 'geodir_rest_log', WP_DEBUG );
    
    if ( true === $should_log ) {
        $label = '';
        if ( $file && $file !== '' ) {
            $label .= basename( $file ) . ( $line ? '(' . $line . ')' : '' );
        }
        
        if ( $title && $title !== '' ) {
            $label = $label !== '' ? $label . ' ' : '';
            $label .= $title . ' ';
        }
        
        $label = $label !== '' ? trim( $label ) . ' : ' : '';
        
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( $label . print_r( $log, true ) );
        } else {
            error_log( $label . $log );
        }
        
        if ( $exit ) {
            exit;
        }
    }
}

/**
 * Alter allowed query vars for the REST API.
 *
 * This filter allows you to add or remove query vars from the allowed
 * list for all requests, including unauthenticated ones. To alter the
 * vars for editors only, {@see rest_private_query_vars}.
 *
 * @param array $query_vars List of allowed query vars.
 */
function geodir_rest_query_vars($query_vars) {
    $query_vars[] = 'is_gd_api';

    return $query_vars;
}
add_filter('rest_query_vars', 'geodir_rest_query_vars', 10, 1);

/**
 * Alter the query arguments for a request.
 *
 * This allows you to set extra arguments or defaults for a post
 * collection request.
 *
 * @param array $args Map of query var to query value.
 * @param WP_REST_Request $request Full details about the request.
 */
function geodir_rest_set_gd_var($args, $request) {
    if (empty($args['post_type'])) {
        return $args;
    }
        
    $gd_post_types = geodir_get_posttypes();

    if (in_array($args['post_type'], $gd_post_types)) {
        $args['is_gd_api'] = true;
    }

    return $args;
}
add_filter('rest_post_query', 'geodir_rest_set_gd_var', 10, 2);

/**
 * Filter the JOIN clause of the query.
 *
 * @since 1.5.4
 *
 * @param string   $join The JOIN clause of the query.
 * @param WP_Query &$this The WP_Query instance (passed by reference).
 */
function geodir_rest_posts_join($join, $wp_query) {	
    if (!(!empty($wp_query) && !empty($wp_query->query_vars['post_type']) && !empty($wp_query->query_vars['is_gd_api']))) {
        return $join;
    }

    $post_type = $wp_query->query_vars['post_type'];

    $gd_post_types = geodir_get_posttypes();

    if (in_array($post_type, $gd_post_types)) {	
        $join = geodir_rest_post_query_join($join, $post_type, $wp_query);
    }

    return $join;
}
add_filter('posts_join', 'geodir_rest_posts_join', 10, 2);

/**
 * Filter the SELECT clause of the query.
 *
 * @since 1.5.4
 *
 * @param string   $fields The SELECT clause of the query.
 * @param WP_Query &$this  The WP_Query instance (passed by reference).
 */
function geodir_rest_posts_fields($fields, $wp_query) {
    if (!(!empty($wp_query) && !empty($wp_query->query_vars['post_type']) && !empty($wp_query->query_vars['is_gd_api']))) {
        return $fields;
    }

    $post_type = $wp_query->query_vars['post_type'];

    $gd_post_types = geodir_get_posttypes();

    if (in_array($post_type, $gd_post_types)) {
        $fields = geodir_rest_post_query_fields($fields, $post_type, $wp_query);
    }

    return $fields;
}
add_filter('posts_fields', 'geodir_rest_posts_fields', 10, 2);

function geodir_rest_post_query_join($join, $post_type, $wp_query = array()) {
    global $wpdb;

    $table = geodir_rest_post_table($post_type);
        
    ########### WPML ###########
    if (function_exists('icl_object_id') && defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE) {
        $join .= " JOIN " . $wpdb->prefix . "icl_translations AS icl_t ON icl_t.element_id = " . $wpdb->posts . ".ID";
    }
    ########### WPML ###########

    $join .= " INNER JOIN " . $table . " ON (" . $table . ".post_id = " . $wpdb->posts . ".ID)";
    if ($post_type == 'gd_event') {
        $join .= " INNER JOIN " . EVENT_SCHEDULE ." ON (" . EVENT_SCHEDULE . ".event_id = " . $wpdb->posts . ".ID)";
    }

    $join = apply_filters('geodir_rest_post_query_join', $join, $post_type, $wp_query);

    return $join;
}

function geodir_rest_post_query_fields($fields, $post_type, $wp_query = array()) {
    $table = geodir_rest_post_table($post_type);
        
    $fields = $fields != '' ? $fields . ", " : '';
    $fields .= $table . ".*";
    if ($post_type == 'gd_event') {
        $fields .= ", " . EVENT_SCHEDULE . ".*";
    }
        
    $fields = apply_filters('geodir_rest_post_query_fields', $fields, $post_type, $wp_query);

	return $fields;
}

function geodir_rest_post_table($post_type) {
    global $plugin_prefix;

    $table = $plugin_prefix . $post_type . '_detail';

    return $table;
}

function geodir_rest_register_cpt_fields() {
    $gd_post_types = geodir_get_posttypes('array');

    if (!empty($gd_post_types)) {
        foreach ($gd_post_types as $post_type => $data) {
            geodir_rest_register_gd_data($post_type);
        }
    }
}
add_action('rest_api_init', 'geodir_rest_register_cpt_fields');

function geodir_rest_register_gd_data($post_type) {
    register_api_field($post_type,
        'gd_data',
        array(
            'get_callback' => 'geodir_rest_get_gd_data',
            'update_callback' => null,
            'schema' => null,
        )
    );
}

function geodir_rest_get_gd_data($object, $attribute, $args) {
    $post_id = $object['id'];
    $post_type = $object['type'];

    $custom_fields = geodir_rest_get_custom_fields($post_type, true, 'htmlvar_name');
    $taxonomy = $post_type . 'category';

    $date_format = geodir_default_date_format();
    $time_format = get_option('time_format');
    $search_date_format = array('dd','d','DD','mm','m','MM','yy'); // jQuery UI datepicker format
    $replace_date_format = array('d','j','l','m','n','F','Y'); // PHP date format

    $data = array();

    $gd_post_info = geodir_rest_gd_post_info($post_id);
    if (!empty($gd_post_info)) {
        $uploads = wp_upload_dir();
        
        foreach ($gd_post_info as $field => $value) {
            $field_data = array();
            
            $raw_value = $value != NULL ? $value : '';
            $rendered_value = '';
            
            if ($field == $taxonomy) {
                $values = explode(',', trim($raw_value, ','));
                
                if (!empty($values)) {
                    $rendered_value = array();
                    
                    foreach ($values as $term_id) {
                        $term = get_term_by('id', $term_id, $taxonomy);
                        
                        if (!empty($term)) {
                            $rendered_value[] = $term->name;
                        }
                    }
                }
            } else if ($field == 'default_category') {				
                if ((int)$raw_value > 0 && $term = get_term_by('id', (int)$raw_value, $taxonomy)) {
                    $rendered_value = $term->name;
                }
            } else if ($field == 'featured_image') {				
                if ($raw_value != '') {
                    $rendered_value = $uploads['baseurl'] . $raw_value;
                }
            } else if (in_array($field, $custom_fields)) {
                $field_info = geodir_rest_get_field_by_name($field, $post_type);
                
                if (!empty($field_info)) {
                    $extra_fields = $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
                    $option_values = $field_info->option_values;
                    $option_values_arr = $option_values != '' ? geodir_string_values_to_options($option_values) : NULL;
                    
                    switch ($field_info->field_type) {
                        case 'time':
                            if ($raw_value != '') {
                                $rendered_value = date_i18n($time_format, strtotime($raw_value));
                            }
                        break;
                        case 'datepicker':
                            if ($raw_value != '' && $raw_value != '0000-00-00') {							
                                $date_format = isset($extra_fields['date_format']) && $extra_fields['date_format'] != '' ? $extra_fields['date_format'] : $date_format;
                                $date_format = str_replace($search_date_format, $replace_date_format, $date_format);
                                
                                $rendered_value = $date_format == 'd/m/Y' ? str_replace('/', '-', $raw_value) : $raw_value; // PHP doesn't work well with dd/mm/yyyy format
                                $rendered_value = date_i18n($date_format, strtotime($rendered_value));
                            }
                        break;
                        case 'radio':
                            if ($raw_value == 't' || $raw_value == '1') {
                                $rendered_value =  __('Yes', 'geodirectory');
                            } else if ($raw_value == 'f' || $raw_value == '0') {
                                $rendered_value =  __('No', 'geodirectory');
                            }
                            
                            if (strpos($option_values, '/') !== false && !empty($option_values_arr)) {
                                foreach ($option_values_arr as $option_row) {
                                    $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                                    $option_value = isset($option_row['value']) ? $option_row['value'] : $option_label;
                                    
                                    if ($option_value == $raw_value && $option_label != '') {
                                        $rendered_value = __($option_label, 'geodirectory');
                                        break;
                                    }
                                }
                            }
                        break;
                        case 'checkbox':
                            if ((int)$raw_value == 1) {
                                $rendered_value = __('Yes', 'geodirectory');
                            }
                        break;
                        case 'select':
                            if ($raw_value != '') {
                                if (strpos($option_values, '/') !== false && !empty($option_values_arr)) {
                                    foreach ($option_values_arr as $option_row) {
                                        $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                                        $option_value = isset($option_row['value']) ? $option_row['value'] : $option_label;
                                        
                                        if ($option_value == $raw_value && $option_label != '') {
                                            $rendered_value = __($option_label, 'geodirectory');
                                            break;
                                        }
                                    }
                                }
                            }
                        break;
                        case 'multiselect':
                            if ($raw_value != '') {
                                $raw_value_arr = explode(',', $raw_value);
                                
                                $raw_values = array();
                                if (strpos($option_values, '/') !== false) {
                                    foreach ($option_values_arr as $option_row) {
                                        $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                                        $option_value = isset($option_row['value']) ? $option_row['value'] : $option_label;
                                        $option_label = $option_label == '' ? $option_value : $option_label;
                                        
                                        if (in_array($option_value, $raw_value_arr) && $option_label != '') {
                                            $raw_values[] = $option_label;
                                        }
                                    }
                                } else {
                                    $raw_values = $raw_value_arr;
                                }
                                
                                $rendered_value = array();
                                foreach ($raw_values as $label) {
                                    $rendered_value[] = __($label, 'geodirectory');
                                }
                            }
                        break;
                    }
                }
            }
            
            $field_data = $raw_value;
            
            if (!empty($rendered_value) && $rendered_value != $raw_value) {
                $field_data = array();
                $field_data['raw'] = $raw_value;
                $field_data['rendered'] = $rendered_value;
            }
            
            $data[$field] = $field_data;
        }

        $data = apply_filters('geodir_rest_get_gd_data', $data, $post_id);
    }

    return $data;
}

/**
 * Get post custom fields.
 *
 * @since 1.5.4
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int|string $post_id Optional. The post ID.
 * @return object|bool Returns full post details as an object. If no details returns false.
 */
function geodir_rest_gd_post_info($post_id = '') {
    global $wpdb, $plugin_prefix;

    $post_type = get_post_type($post_id);

    $all_postypes = geodir_get_posttypes();

    if (!in_array($post_type, $all_postypes)) {
        return false;
    }

    $table = $plugin_prefix . $post_type . '_detail';

    /**
     * Apply Filter to change Post info
     *
     * You can use this filter to change Post info.
     *
     * @since 1.5.4
     * @package GeoDirectory
     */
    $query = "SELECT pd.* FROM " . $table . " pd INNER JOIN " . $wpdb->posts . " as p ON p.ID = pd.post_id WHERE post_id = " . (int)$post_id . " AND p.post_type = '" . $post_type . "'";
    $row = $wpdb->get_row($query);

    return $row;

}

function geodir_rest_custom_gd_data($data, $post_id) {
    global $post, $geodir_date_time_format, $geodir_date_format, $geodir_time_format;

    $post_type = $post->post_type;
    if ($post_type == 'gd_event' && isset($post->event_date)) {
        $event_date = str_replace(' 00:00:00', '', $post->event_date);
        
        $start_datetime = $event_date . ' ' . $post->event_starttime;
        $end_datetime = $post->event_enddate . ' ' . $post->event_endtime;
        
        $date_format = $post->all_day ? $geodir_date_format : $geodir_date_time_format;
        
        $start_rendered = date_i18n($date_format, strtotime($start_datetime));
        $end_rendered = date_i18n($date_format, strtotime($end_datetime));
        
        $data['event_start_datetime'] = array('raw' => $start_datetime, 'rendered' => $start_rendered);
        $data['event_end_datetime'] = array('raw' => $end_datetime, 'rendered' => $end_rendered);
        $data['event_startdate'] = array('raw' => $event_date, 'rendered' => date_i18n($geodir_date_format, strtotime($event_date)));
        $data['event_enddate'] = array('raw' => $post->event_enddate, 'rendered' => date_i18n($geodir_date_format, strtotime($post->event_enddate)));
        $data['event_starttime'] = array('raw' => $post->event_starttime, 'rendered' => date_i18n($geodir_time_format, strtotime($post->event_starttime)));
        $data['event_endtime'] = array('raw' => $post->event_endtime, 'rendered' => date_i18n($geodir_time_format, strtotime($post->event_endtime)));
        $data['recurring'] = $post->recurring;
        $data['all_day'] = $post->all_day;
        $data['schedule_id'] = $post->schedule_id;
    }

    // post images
    $data['post_images'] = geodir_rest_get_post_images($post_id);

    if (is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php')) {
        // post package
        $package_info = geodir_post_package_info(array(), $post);
        
        if (!empty($package_info)) {
            $data['package_info'] = array(
                                        'package_id' => $package_info->pid,
                                        'package_title' => $package_info->title_desc,
                                        'package_amount' => $package_info->amount > 0 ? geodir_payment_price($package_info->amount) : 0,
                                    );
        }

        $payment_invoices = geodir_rest_get_payment_invoices($post_id);
        $data['payment_invoices'] = $payment_invoices;
    }
        
    return $data;
}
add_filter('geodir_rest_get_gd_data', 'geodir_rest_custom_gd_data', 0, 2);

function  geodir_rest_get_post_images($post_id) {
    $post_images = $post_images = geodir_get_images($post_id);
    $images = array();

    if (!empty($post_images)) {
        foreach ($post_images as $post_image) {
            $image = array();
            $image['src'] = $post_image->src;
            $image['title'] = $post_image->title;
            
            $images[] = $image;
        }
    }

    return $images;
}

function geodir_rest_get_payment_invoices($post_id) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM " . INVOICE_TABLE . " WHERE post_id = %d ORDER BY date desc", array($post_id));
    $rows = $wpdb->get_results($sql);

    $invoices = array();

    if (!empty($rows)) {
        $date_time_format = geodir_default_date_format() . ' ' . get_option('time_format');
        
        foreach ($rows as $row) {			
            $invoice_status = $row->status;
            if ( in_array(strtolower($invoice_status), array('paid', 'active', 'subscription-payment', 'free'))) {
                $invoice_status = 'confirmed';
            } else if (in_array(strtolower($invoice_status), array('unpaid'))) {
                $invoice_status = 'pending';
            }
            
            $invoice = array();
            $invoice['id'] = $row->id;
            $invoice['title'] = $row->post_title;
            $invoice['invoice_type'] = geodir_payment_invoice_type_name($row->invoice_type);
            $invoice['package_id'] = $row->package_id;
            $invoice['paid_amount'] = geodir_payment_price($row->paied_amount);
            $invoice['alive_days'] = $row->alive_days;
            $invoice['amount'] = geodir_payment_price($row->amount);
            $invoice['coupon'] = $row->coupon_code;
            $invoice['discount'] = $row->discount > 0 ? geodir_payment_price($row->discount) : 0;
            $invoice['payment_method'] = geodir_payment_method_title($row->paymentmethod);
            $invoice['date'] = $row->date != '0000-00-00 00:00:00' ? date_i18n($date_time_format, strtotime($row->date)) : '';
            $invoice['date_updated'] = $row->date_updated != '0000-00-00 00:00:00' ? date_i18n($date_time_format, strtotime($row->date_updated)) : '';
            $invoice['status'] = geodir_payment_status_name($invoice_status);
            
            $invoices[] = $invoice;
        }
    }

    return $invoices;
}

function geodir_rest_register_custom_fields($post_type) {
    register_api_field($post_type,
        'gd_custom_fields',
        array(
            'get_callback' => 'geodir_rest_get_custom_fields_values',
            'update_callback' => null,
            'schema' => null,
        )
    );
}

function geodir_rest_get_custom_fields_values($object, $attribute, $args) {	
    $post_id = $object['id'];
    $post_type = $object['type'];

    $custom_fields = geodir_rest_get_custom_fields($post_type);

    $data = array();
    if (!empty($custom_fields)) {
        $gd_post_info = geodir_get_post_info($post_id);
        
        foreach ($custom_fields as $field) {
            $field_name = $field->htmlvar_name;
            $admin_title = $field->admin_title;
            $site_title = $field->site_title;
            $field_type = $field->field_type;
            
            $title = $site_title != '' ? $site_title : $admin_title;
            
            $raw_value = isset($gd_post_info->$field_name) ? $gd_post_info->$field_name : '';
            
            switch ($field_type) {
                case 'checkbox':
                    $rendered_value = (int)$raw_value == 1 ? __('Yes', 'geodirectory') : __('No', 'geodirectory');
                break;
                case 'radio':
                    if ($raw_value == 't' || $raw_value == '1') {
                        $rendered_value =  __('Yes', 'geodirectory');
                    } else if ($raw_value == 'f' || $raw_value == '0') {
                        $rendered_value =  __('No', 'geodirectory');
                    } else {
                        $rendered_value = $raw_value;
                    }
                break;
                default:
                    $rendered_value = $raw_value;
                break;
            }
            
            $field_data = array();
            $field_data['title'] = $title;
            $field_data['type'] = $field_type;
            $field_data['raw'] = $raw_value;
            
            if ($rendered_value != $raw_value) {
                $field_data['rendered'] = $rendered_value;
            }
            
            $data[$field_name] = $field_data;
        }
    }

    return $data;
}

function geodir_rest_get_custom_fields($post_type, $name_only = false, $select = '*' ) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT " . $select . " FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s AND is_active='1' ORDER BY sort_order ASC", array( $post_type ) );
    $rows = $wpdb->get_results( $sql );

    if ($name_only && !empty($rows)) {
        $fields = array();
        
        foreach ($rows as $row) {
            $fields[] = $row->htmlvar_name;
        }
        
        $rows = $fields;
    }

    return $rows;
}

function geodir_rest_get_field_by_name($field_name, $post_type) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s AND htmlvar_name=%s AND is_active='1' ORDER BY sort_order ASC", array( $post_type, $field_name ) );
    $rows = $wpdb->get_row( $sql );

    return $rows;
}

function geodir_rest_comments_clauses( $clauses, $comment_query ) {
    global $wpdb;

    $is_reviewrating = fuction_exists('geodir_reviewrating_comments_shorting')?true:false;

    $comment_sorting = $comment_query->query_vars['orderby'];
    if ( $comment_sorting == 'comment_date' ) {
        $comment_sorting = $comment_query->query_vars['order'] == 'asc' ? 'oldest' : 'latest';
    }
     
    $_REQUEST['comment_sorting'] = $comment_sorting;

    if($is_reviewrating) {
        $review_sorting = geodir_reviewrating_comments_shorting( $clauses );
    } else {
        $review_sorting = array('orderby' => $clauses['orderby']);
    }

    $clauses['fields'] .= ", gdr.`id` AS review_id, gdr.`post_title`, gdr.`post_type`, gdr.`rating_ip`, gdr.`ratings`, gdr.`overall_rating`, gdr.`comment_images`, gdr.`wasthis_review`, gdr.`status` AS review_status, gdr.`post_status` AS review_post_status, gdr.`post_date`, gdr.`post_city` AS city, gdr.`post_region` AS region, gdr.`post_country` AS country, gdr.`post_latitude` AS latitude, gdr.`post_longitude` AS longitude, gdr.`comment_content` AS city";
    if($is_reviewrating){
        $clauses['fields'] .=", gdr.`read_unread`, gdr.`total_images`";
    }
    $clauses['join'] .= " LEFT JOIN " . GEODIR_REVIEW_TABLE . " AS gdr ON gdr.comment_id=" . $wpdb->comments . ".comment_ID";
    $clauses['orderby'] = str_replace( GEODIR_REVIEW_TABLE, 'gdr', $review_sorting['orderby'] );
    return $clauses;
}

function geodir_rest_get_comment( $comment ) {
    global $wpdb;

    $is_reviewrating = function_exists( 'geodir_reviewrating_comments_shorting' ) ? true : false;

    $fields = '';
    if ( $is_reviewrating ) {
        $fields = ", gdr.`read_unread`, gdr.`total_images`";
    }

    $query = "SELECT gdr.`id` AS review_id, gdr.`post_title`, gdr.`post_type`, gdr.`rating_ip`, gdr.`ratings`, gdr.`overall_rating`, gdr.`comment_images`, gdr.`wasthis_review`, gdr.`status` AS review_status, gdr.`post_status` AS review_post_status, gdr.`post_date`, gdr.`post_city` AS city, gdr.`post_region` AS region, gdr.`post_country` AS country, gdr.`post_latitude` AS latitude, gdr.`post_longitude` AS longitude, gdr.`comment_content` AS review " . $fields . " FROM " . GEODIR_REVIEW_TABLE . " AS gdr WHERE gdr.comment_id = " . (int)$comment->comment_ID;
    $review = $wpdb->get_row( $query );

    $comment = (object)array_merge((array)$comment, (array)$review);

    $review = $wpdb->get_row( $query );
    
    $comment = (object)array_merge((array)$comment, (array)$review);
    
    return $comment;
}

function geodir_rest_get_listing_sorting( $post_type ) {
    $sort_options = geodir_get_sort_options( $post_type );
    
    $sorting = array();
    
    $default_orderby = 'post_date';
    $default_order = 'desc';
    
    if ( !empty( $sort_options ) ) {
        foreach ( $sort_options as $sort ) {
            $sort = stripslashes_deep( $sort ); // strip slashes
            
            $field_name = $sort->htmlvar_name;
            $label = __( $sort->site_title, 'geodirectory' );
            
            if ( $sort->field_type == 'random' ) {
                $field_name = 'random';
            }
            
            if ( $sort->htmlvar_name == 'comment_count' ) {
                $field_name = 'rating_count';
            }
            
            if ( (int)$sort->is_default == 1 ) {
                $default_order = $sort->sort_desc ? 'desc' : 'asc';
                $default_orderby = $field_name . '_' . $default_order;
            }
            
            if ( $sort->sort_asc ) {
                $label = $sort->asc_title ? __( $sort->asc_title ) : $label;
                $sorting[$field_name . '_asc'] = $label;
            }
            
            if ( $sort->sort_desc ) {
                $label = $sort->desc_title ? __( $sort->desc_title ) : $label;
                $sorting[$field_name . '_desc'] = $label;
            }
            
            if ( $field_name == 'random' ) {
                $label = $sort->desc_title ? __( $sort->desc_title ) : $label;
                $sorting['random'] = $label;
            }
            
            $orderby[] = $field_name ;
        }
    }
    
    $default_orderby = apply_filters( 'geodir_rest_get_listing_sorting_default_orderby', $default_orderby, $post_type );
    $default_order = apply_filters( 'geodir_rest_get_listing_sorting_default_order', $default_order, $post_type );
    $sorting = apply_filters( 'geodir_rest_get_listing_sorting', $sorting, $post_type );

    return array( 'sorting' => $sorting, 'default_sortby' => $default_orderby, 'default_sort' => $default_order );
}

function geodir_rest_get_review_sorting() {
    $sorting = array(
                    'latest' => __( 'Latest', 'geodir_reviewratings' ),
                    'oldest' => __( 'Oldest', 'geodir_reviewratings' ),
                    'low_rating' => __( 'Lowest Rating', 'geodir_reviewratings' ),
                    'high_rating' => __( 'Highest Rating', 'geodir_reviewratings' )
               );
                   
    return apply_filters( 'geodir_reviews_rating_comment_shorting', $sorting );
}