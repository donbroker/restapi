<?php
/**
 * Contains functions for updating the plugin from the GeoDirectory server.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
//set_site_transient('update_plugins', null);

// Define the update url
if (!defined('GD_UPDATE_URL')) define('GD_UPDATE_URL', 'http://wpgeodirectory.com/');

$gd_api_url = 'http://wpgeodirectory.com/updates/';
$plugin_slug = basename(dirname(__FILE__));

if (!function_exists('gd_add_plugin_to_update_list')) {
    //set global value;
    $gd_update_addons_list = array();
    /**
     * Add addons to a global array for later processing.
     *
     * @param string $textdomain The textdomain of the addon.
     * @param string $name The name of the addon shown on update screen.
     * @param string $version The addon version number.
     * @param string $download_id The GD download id.
     * @param string $slug The slug of the addon file.
     * @param string $notes Notes, these appear under the licence text box on update screen.
     */
    function gd_add_plugin_to_update_list($textdomain, $name, $version, $download_id, $slug, $notes = '')
    {
        global $gd_update_addons_list;

        $gd_update_addons_list[$textdomain] = array(
            'textdomain' => $textdomain,
            'name' => $name,
            'version' => $version,
            'download_id' => $download_id,
            'slug' => $slug,
            'notes' => $notes,
        );
    }
}

if (!function_exists('gd_hook_addons_licences')) {
    /**
     * Add licences to GD licence screen.
     */
    function gd_hook_addons_licences($licences)
    {
        global $gd_update_addons_list;

        if (!empty($gd_update_addons_list)) {
            foreach ($gd_update_addons_list as $addon) {
                $licences[$addon['textdomain']] = array(
                    'name' => $addon['name'],
                    'slug' => $addon['textdomain'],
                    'download_id' => $addon['download_id'],
                    'notes' => $addon['notes']

                );
            }
        }
        return $licences;
    }

    add_filter('geodir_licences', 'gd_hook_addons_licences', 10, 1);
}


if (!function_exists('gd_addon_call_updater')) {
    /**
     * Check for addons updates.
     */
    function gd_addon_call_updater()
    {
        global $gd_update_addons_list;

        $licence_keys = get_option('geodir_licence_keys');

        if (!empty($gd_update_addons_list)) {
            $update_array = array();
            foreach ($gd_update_addons_list as $addon) {
                $license_key = (isset($licence_keys[$addon['textdomain']]['licence']) && $licence_keys[$addon['textdomain']]['licence']) ?
                    $licence_keys[$addon['textdomain']]['licence'] : '';
                $slug = plugin_basename($addon['slug']);
                $name = basename($addon['slug'], '.php');

                // setup the updater
                $update_array[$slug] = array(
                    'slug' => $name,                      // the addon slug
                    'version' => $addon['version'],       // current version number
                    'license' => $license_key,            // license key (used get_option above to retrieve from DB)
                    'item_id' => $addon['download_id']    // id of this addon on GD site
                );
            }

            // setup the updater
            new GD_Plugin_Updater(GD_UPDATE_URL, $update_array);
        }
    }

    add_action('admin_init', 'gd_addon_call_updater', 0);
}


if (!function_exists('gd_prepare_request')) {
    /**
     * Prepare api request.
     *
     * @since 1.0.0
     *
     * @global string $wp_version WordPress version.
     *
     * @param string $action The type of information being requested from the Plugin Install API.
     * @param object|array $args Plugin API arguments.
     * @return array
     */
    function gd_prepare_request($action, $args)
    {
        global $wp_version;

        return array(
            'body' => array(
                'action' => $action,
                'request' => serialize($args),
                'api-key' => md5(get_bloginfo('url'))
            ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
            'sslverify' => false // this is needed for some old old servers
        );
    }
}


if (!function_exists('gd_plugin_upgrade_errors')) {
    /**
     * Set plugin upgrade errors.
     *
     * @since 1.0.0
     *
     * @global object $wpdb WordPress Database object.
     *
     * @param bool $false Whether to bail without returning the package. Default false.
     * @param string $src The package file url.
     * @param object $Uthis The WP_Upgrader instance.
     * @return mixed
     */
    function gd_plugin_upgrade_errors($false, $src, $Uthis)
    {
        global $wpdb;
        $Uthis->strings['no_package'] = $Uthis->strings['no_package'] . ' ' . __('GeoDirectory: Please make sure your licence keys are active GD>Auto Updates.', 'geodirectory');


        return $false;
    }
}


if (is_admin()) {

    // Take over the update check
    add_filter('pre_set_site_transient_update_plugins', 'gd_check_for_messages');

    add_filter('upgrader_pre_download', 'gd_plugin_upgrade_errors', 10, 3);

    add_filter('geodir_settings_tabs_array', 'geodir_adminpage_auto_update', 5);

    add_action('geodir_admin_option_form', 'geodir_auto_update_tab_content', 5);

}


if (!function_exists('geodir_adminpage_auto_update')) {
    /**
     * Adds auto updates tab to geodirectory settings.
     *
     * @since 1.0.0
     *
     * @param array $tabs Geodirectory settings page tab list.
     * @return array Modified Tabs list
     */
    function geodir_adminpage_auto_update($tabs)
    {

        $tabs['auto_update_fields'] = array(
            'label' => __('Auto Updates / Licensing', 'geodirectory')
        );

        return $tabs;
    }
}

if (!function_exists('geodir_auto_update_tab_content')) {
    /**
     * Adds content to auto updates tab.
     *
     * @since 1.0.0
     *
     * @param string $tab Geodirectory settings page tab name.
     */
    function geodir_auto_update_tab_content($tab)
    {

        switch ($tab) {

            case 'auto_update_fields':

                geodir_auto_update_setting_fields();

                break;

        }

    }
}

if (!function_exists('geodir_auto_update_setting_fields')) {
    /**
     * Adds setting fields to auto updates tab.
     *
     * @since 1.0.0
     *
     * @global object $wpdb WordPress Database object.
     */
    function geodir_auto_update_setting_fields()
    {
        global $wpdb;
        ?>

        <div class="inner_content_tab_main">
            <div class="gd-content-heading active">
                <h3><?php _e('Enter your GeoDirectory licence keys here to allow you to update plugins from dashboard', 'geodirectory'); ?></h3>

                <table class="form-table">
                    <?php
                    $licences = apply_filters('geodir_licences', '');

                    $licence_keys = get_option('geodir_licence_keys');
                    //print_r($licence_keys);

                    ?>
                    <tbody>

                    <?php
                    foreach ($licences as $licence) {
                        ?>
                        <tr valign="top">
                            <th scope="row"
                                class="titledesc"><?php echo $licence['name']; ?></th>
                            <td class="forminp">
                                <?php $key = isset($licence_keys[$licence['slug']]['licence']) ? $licence_keys[$licence['slug']]['licence'] : ''; ?>
                                <input data-id="<?php echo $licence['download_id']; ?>"
                                       name="<?php echo $licence['slug']; ?>"
                                       id="gd_update_<?php echo $licence['slug']; ?>" type="text"
                                       style=" min-width:300px;"
                                       value="<?php echo $key; ?>" <?php if ($key) {
                                    echo "disabled='disabled'";
                                } ?>>

                                <?php if (isset($licence_keys[$licence['slug']]) && $licence_keys[$licence['slug']]['status'] == 'valid') { ?>
                                    <button class="gd-licence-deactivate"
                                            type="button"><?php _e('Deactivate Licence', 'geodirectory'); ?></button>
                                <?php } else { ?>
                                    <button class="button-primary gd-licence-activate"
                                            type="button"><?php _e('Activate Licence', 'geodirectory'); ?></button>
                                <?php } ?>
                                <i class="fa fa-cog fa-spin" style="display: none;"></i>
                            <span
                                class="description"><?php _e($licence['notes'], 'geodirectory'); ?></span>

                            </td>
                        </tr>

                    <?php }?>

                    </tbody>
                </table>


                <p class="submit" style="margin-top:10px;">

                    <input type="hidden" name="subtab" id="last_tab"/>
                </p>

            </div>
        </div>

        <script>

            function geodir_activate_deactivate_keys() {

                //unbind clicks so we can later bind again and not have double actions.
                jQuery('.gd-licence-activate').unbind("click");
                jQuery('.gd-licence-deactivate').unbind("click");

                jQuery('.gd-licence-activate').click(function () {

                    var slug = jQuery(this).prev('input').attr('name');
                    this_var = this; // assign this to a global this_var so we can use it later in ajax response function
                    var licence = jQuery(this).prev('input').val();
                    var download_id = jQuery(this).prev('input').attr("data-id");

                    jQuery(this).next('.fa-cog').show();
                    jQuery(this).prop('disabled', true);
                    jQuery.post(
                        ajaxurl,
                        {
                            'action': 'geodir_activate_deactivate_license',
                            'type': 'geodir_activate_license',
                            'download_id': download_id,
                            'licence': licence,
                            'slug': slug,
                            '_wpnonce': '<?php  echo wp_create_nonce( 'activate_license' );?>'
                        },
                        function (response) {
                            jQuery(this_var).next('.fa-cog').hide();
                            if (!response) {
                                alert('Error');
                                return;
                            }
                            var parsedJson = jQuery.parseJSON(response);
                            console.log(parsedJson);

                            if (parsedJson.license == 'valid') {
                                alert('Licence activated, expires: ' + parsedJson.expires);
                                jQuery(this_var).text('<?php _e('Deactivate Licence','geodirectory'); ?>');
                                jQuery(this_var).prev('input').prop('disabled', true);
                                jQuery(this_var).removeClass('button-primary gd-licence-activate');
                                jQuery(this_var).addClass('gd-licence-deactivate');
                                geodir_activate_deactivate_keys();

                            } else if (parsedJson.license == 'invalid' && parsedJson.error == 'license_not_activable') {
                                alert('<?php _e('You can not use your membership licence here, use the item licence.','geodirectory'); ?>');
                            } else {
                                alert('<?php _e('Licence not activated! Please renew or seek support.','geodirectory'); ?>');
                            }
                            jQuery(this_var).prop('disabled', false);
                        }
                    );
                });

                jQuery('.gd-licence-deactivate').click(function () {

                    if (confirm("<?php _e('Are you sure?','geodirectory'); ?>")) {

                        var slug = jQuery(this).prev('input').attr('name');
                        this_var = this; // assign this to a global this_var so we can use it later in ajax response function
                        var licence = jQuery(this).prev('input').val();
                        var download_id = jQuery(this).prev('input').attr("data-id");
                        jQuery(this).prop('disabled', true);
                        jQuery(this).next('.fa-cog').show();
                        jQuery.post(
                            ajaxurl,
                            {
                                'action': 'geodir_activate_deactivate_license',
                                'type': 'geodir_deactivate_license',
                                'download_id': download_id,
                                'licence': licence,
                                'slug': slug,
                                '_wpnonce': '<?php  echo wp_create_nonce( 'deactivate_license' );?>'
                            },
                            function (response) {
                                jQuery(this_var).next('.fa-cog').hide();
                                if (!response) {
                                    alert('Error');
                                    return;
                                }
                                var parsedJson = jQuery.parseJSON(response);

                                if (parsedJson.license == 'deactivated') {
                                    alert('Licence deactivated');
                                    jQuery(this_var).text('Activate Licence');
                                    jQuery(this_var).prev('input').prop('disabled', false);
                                    jQuery(this_var).prev('input').val('');
                                    jQuery(this_var).removeClass('gd-licence-deactivate');
                                    jQuery(this_var).addClass('button-primary gd-licence-activate');
                                    geodir_activate_deactivate_keys();
                                } else {
                                    alert('<?php _e('Licence not deactivated! Please seek support.','geodirectory'); ?>');
                                    jQuery(this_var).text('Activate Licence');
                                    jQuery(this_var).prev('input').prop('disabled', false);
                                    jQuery(this_var).prev('input').val('');
                                    jQuery(this_var).removeClass('gd-licence-deactivate');
                                    jQuery(this_var).addClass('button-primary gd-licence-activate');
                                    geodir_activate_deactivate_keys();
                                }
                                jQuery(this_var).prop('disabled', false);
                            }
                        );
                    }
                });
            }

            jQuery(document).ready(function () {
                geodir_activate_deactivate_keys();
            });

        </script>

        <style>
            .gd-licence-deactivate {
            }
        </style>

    <?php

    }
}

if (!function_exists('geodir_activate_deactivate_license')) {
    /**
     * Activate or Deactivate license.
     *
     * @since 1.0.0
     */
    function geodir_activate_deactivate_license()
    {

        // listen for our activate button to be clicked
        if (isset($_POST['type']) && $_POST['type'] == 'geodir_deactivate_license') {
            $action = 'deactivate_license';
        } elseif (isset($_POST['type']) && $_POST['type'] == 'geodir_activate_license') {
            $action = 'activate_license';
        } else {
            return;
        }

        // run a quick security check
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], $action))
            return; // get out if we didn't click the Activate button

        // retrieve the license from the database
        $licence = sanitize_text_field($_REQUEST['licence']);
        $item_id = sanitize_text_field($_REQUEST['download_id']);
        $slug = sanitize_text_field($_REQUEST['slug']);


        // data to send in our API request
        $api_params = array(
            'edd_action' => $action,
            'license' => $licence,
            'item_id' => $item_id, // the name of our product in EDD
            'url' => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post(GD_UPDATE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));


        // make sure the response came back okay
        if (is_wp_error($response))
            return false;

        if ($action == 'deactivate_license') {
            $licence = '';
        }

        // decode the license data
        $licence_data_json = wp_remote_retrieve_body($response);
        $licence_data = json_decode($licence_data_json);

        // $license_data->license will be either "deactivated" or "failed"

        $licence_keys = get_option('geodir_licence_keys');
        $licence_keys[$slug] = array('licence' => $licence, 'status' => $licence_data->license);

        if ($licence_data->license == 'deactivated' || $licence_data->license == 'failed') {
            update_option('geodir_licence_keys', $licence_keys);
            echo $licence_data_json;
            die();
        } elseif ($licence_data->license == 'valid') {
            update_option('geodir_licence_keys', $licence_keys);
            echo $licence_data_json;
            die();
        }


        echo '0';
        die();
    }

    add_action('wp_ajax_geodir_activate_deactivate_license', 'geodir_activate_deactivate_license');
}


#################################################
########## CHECK FOR GD MESSAGES ################
#################################################

if (!function_exists('gd_check_for_messages')) {
    /**
     * Check plugin upgrade messages and update into the db.
     *
     * @since 1.0.0
     *
     * @global string $gd_api_url The API url where the plugin can check for update.
     * @global string $plugin_slug The plugin slug to check for update.
     * @global object $wpdb WordPress Database object.
     *
     * @param object $checked_data Checked plugin data.
     * @return object
     */
    function gd_check_for_messages($checked_data)
    {
        global $gd_api_url, $plugin_slug, $wpdb;
        $gd_arr = array();
        if (empty($checked_data->checked)) {
            return $checked_data;
        } else {
            foreach ($checked_data->checked as $key => $value) {// build an array of installed GD plugins and versions
                if (strpos($key, 'geodir_') !== false) {
                    $pieces = explode("/", $key);
                    $gd_arr[$pieces[0]] = array("ver" => $value, "last" => get_option($pieces[0] . "_last"));
                }
            }

            $gd_arr['geodirectory'] = array("ver" => GEODIRECTORY_VERSION, "last" => get_option("geodirectory_last"));// add core
            $gd_arr['geodirectory_general'] = array("ver" => '', "last" => get_option("geodirectory_general_last"));// add general messages


            $uname = get_option('gd_update_uname');
            $request_args = array(
                'plugins' => $gd_arr,
                'version' => GEODIRECTORY_VERSION,
                'site' => home_url(),
                'user' => $uname,
            );


            $request_string = gd_prepare_request('message_check', $request_args);
            // Start checking for an update
            $raw_response = wp_remote_post($gd_api_url, $request_string);

            if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
                $response = unserialize($raw_response['body']);

            if (!empty($response)) {// Feed the message into a wp_option

                $gd_msg = get_option('geodir_messages');

                if (is_array($gd_msg)) {
                    $result = $response + $gd_msg;
                } else {
                    $result = $response;
                }

                foreach ($result as $key => $res) {// check the notification is for the correct version if not remove it
                    if (empty($res['ver'])) $res['ver'] = 0;
                    if ($res['ver'] <= $gd_arr[$res['plugin']]['ver']) {

                    } else {
                        unset($result[$key]);
                    }
                }


                if(is_array($result) && !empty($result)){
                 $result = array_unique($result,SORT_REGULAR);
                }
                update_option('geodir_messages', $result);

            }

        }
        return $checked_data;
    }
}

if (!function_exists('geodir_show_message')) {
    /**
     * Adds messaged to the admin screen from the GeoDirectory server.
     *
     * @since 1.0.0
     *
     * @param string $message Message string.
     * @param string $msg_type Message type.
     * @param string $plugin Plugin name.
     * @param string $timestamp Timestamp.
     * @param string $js Extra js.
     * @param string $css Extra css.
     */
    function geodir_show_message($message, $msg_type = 'update-nag', $plugin, $timestamp, $js = '', $css = '')
    {
        /*
        $msg_type = error
        $msg_type = updated fade
        $msg_type = update-nag
        */


        echo '<div id="' . $timestamp . '" class="' . $msg_type . '">';
        echo '<span class="gd-remove-noti" onclick="gdRemoveNotification(\'' . $plugin . '\',\'' . $timestamp . '\');" ><i class="fa fa-times"></i></span>';
        echo "<img class='gd-icon-noti' src='" . plugin_dir_url('') . "geodirectory/geodirectory-assets/images/favicon.ico' > ";
        echo "$message";
        echo "</div>";

        ?>
        <script>
            function gdRemoveNotification($plugin, $timestamp) {

                jQuery('#' + $timestamp).css("background-color", "red");
                jQuery('#' + $timestamp).fadeOut("slow");
                // This does the ajax request
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        'action': 'geodir_remove_notification',
                        'plugin': $plugin,
                        'timestamp': $timestamp
                    },
                    success: function (data) {
                        // This outputs the result of the ajax request
                        //alert(data);
                    },
                    error: function (errorThrown) {
                        console.log(errorThrown);
                    }
                });

            }
            <?php echo $js;// extra js if needed?>
        </script>
        <style>
            .gd-icon-noti {
                float: left;
                margin-top: 10px;
                margin-right: 5px;
            }

            .update-nag .gd-icon-noti {
                margin-top: 2px;
            }

            .gd-remove-noti {
                float: right;
                margin-top: -20px;
                margin-right: -20px;
                color: #FF0000;
                cursor: pointer;
            }

            .updated .gd-remove-noti, .error .gd-remove-noti {
                float: right;
                margin-top: -10px;
                margin-right: -17px;
                color: #FF0000;
                cursor: pointer;
            }

            <?php echo $css;// extra styles if needed?>
        </style>
    <?php

    }
}

if (!function_exists('geodir_admin_messages')) {
    /**
     * Get the admin messaged from the options and calls the function to disaplay them.
     *
     * @since 1.0.0
     *
     * @global object $wpdb WordPress Database object.
     */
    function geodir_admin_messages()
    {
        global $wpdb;
        $gd_msg = get_option('geodir_messages');
        if (empty($gd_msg)) {
            return;
        }
        foreach ($gd_msg as $msg) {
            geodir_show_message($msg['msg'], $msg['type'], $msg['plugin'], $msg['timestamp'], $msg['js'], $msg['css']);
        }

    }
}
add_action('admin_notices', 'geodir_admin_messages');


if (!function_exists('geodir_remove_notification')) {
    /**
     * Remove GeoDirectory admin messages messages.
     *
     * @since 1.0.0
     *
     * @global object $wpdb WordPress Database object.
     */
    function geodir_remove_notification()
    {
        global $wpdb;
        // The $_REQUEST contains all the data sent via ajax
        if (isset($_POST)) {


            $gd_msg = get_option('geodir_messages');
            foreach ($gd_msg as $key => $msg) {
                if ($msg['plugin'] == $_POST['plugin'] && $msg['timestamp'] == $_POST['timestamp']) {
                    update_option($msg['plugin'] . '_last', current_time('timestamp', 1));
                    unset($gd_msg[$key]);
                }
            }
            update_option('geodir_messages', $gd_msg);

        }

        // Always die in functions echoing ajax content
        die();
    }
}

add_action('wp_ajax_geodir_remove_notification', 'geodir_remove_notification');


#################################################
########## CHECK FOR UPDATES CLASS ##############
#################################################
class GD_Plugin_Updater
{
    private $api_url = '';
    private $update_array = array();
    private $update_slugs_array = array();
    private $update_names_array = array();

    /**
     * Class constructor.
     *
     * @uses plugin_basename()
     * @uses hook()
     *
     * @param string $_api_url The URL pointing to the custom API endpoint.
     * @param array $_api_data Optional data to send with API calls.
     */
    public function __construct($_api_url, $_api_data = array())
    {
        $this->api_url = trailingslashit($_api_url);
        $this->update_array = $_api_data;
        if (!empty($_api_data)) {
            foreach ($_api_data as $name => $plugin) {
                $this->update_slugs_array[] = $plugin['slug'];
                $this->update_names_array[] = $name;
            }
        }
        // Set up hooks.
        $this->init();
        add_action('admin_init', array($this, 'show_changelog'));
    }

    /**
     * Set up WordPress filters to hook into WP's update process.
     *
     * @uses add_filter()
     *
     * @return void
     */
    public function init()
    {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugins_api_filter'), 10, 3);

        foreach ($this->update_array as $file => $plugin) {
            remove_action('after_plugin_row_' . $file, 'wp_plugin_update_row', 10, 2);
            add_action('after_plugin_row_' . $file, array($this, 'show_update_notification'), 10, 2);
        }

    }

    /**
     * Check for Updates at the defined API endpoint and modify the update array.
     *
     * This function dives into the update API just when WordPress creates its update array,
     * then adds a custom API call and injects the custom plugin data retrieved from the API.
     * It is reassembled from parts of the native WordPress plugin update code.
     * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
     *
     * @uses api_request()
     *
     * @param array|stdClass $_transient_data Update array build by WordPress.
     * @return array|stdClass Modified update array with custom plugin data.
     */
    public function check_update($_transient_data)
    {
        global $pagenow;
        if (!is_object($_transient_data)) {
            $_transient_data = new stdClass;
        }
        if ('plugins.php' == $pagenow && is_multisite()) {
            return $_transient_data;
        }
        $update_names_array = $this->update_names_array;// we need the first key of the update array, for speed and php complanace foreach is fastest/best
        if (empty($_transient_data->response) || empty($_transient_data->response[$update_names_array[0]])) {
            $version_info = $this->api_request('plugin_latest_version', $this->update_array);
            if (empty($version_info)) {
                return $_transient_data;
            }
            $_transient_data = self::process_update_transient_data($version_info,$_transient_data);
        }
        return $_transient_data;
    }

    /**
     * Process the transient info and return the data.
     *
     * @param array|stdClass $_transient_data Update array build by WordPress.
     * @return array|stdClass Modified update array with custom plugin data.
     */
    public function process_update_transient_data($version_info,$_transient_data){

        $update_array = $this->update_array;
        foreach ($version_info as $name => $plugin_info) {
            if (version_compare($update_array[$name]['version'], $plugin_info->new_version, '<')) {
                $_transient_data->response[$name] = $plugin_info;
            }
            $_transient_data->checked[$name] = $update_array[$name]['version'];
        }
        $_transient_data->last_checked = time();

        return $_transient_data;
    }

    /**
     * show update notification row -- needed for multisite subsites, because WP won't tell you otherwise!
     *
     * @param string $file
     */
    public function show_update_notification($file)
    {
        $update_names_array = $this->update_names_array;
        if (!current_user_can('update_plugins') || !is_multisite() || !in_array($file, $update_names_array)) {
            return;
        }
        // Remove our filter on the site transient
        remove_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'), 10);
        $update_cache = get_site_transient('update_plugins');
        $update_cache = is_object($update_cache) ? $update_cache : new stdClass();

        if (empty($update_cache->response) || empty($update_cache->response[$file])) {
            $version_info = self::set_single_cache($file,$update_cache);
        } else {
            $version_info = $update_cache->response[$file];
        }

        // Restore our filter
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));

        self::output_update_notification($file,$update_cache,$version_info);
    }

    /**
     * Set the cache plugin data to transient if needed.
     *
     * @param string $file The file location of the plugin in the plugins folder.
     * @param stdClass $update_cache The cached update object.
     * @return false|object|void
     */
    public function set_single_cache($file,$update_cache){
        $plugin = $this->update_array[$file];
        $cache_key = md5('edd_plugin_' . sanitize_key($file) . '_version_info');
        $version_info = get_transient($cache_key);

        if (false === $version_info) {
            $version_info = $this->api_request('plugin_latest_version', array('slug' => $plugin['slug']));
            set_transient($cache_key, $version_info, 3600);
        }

        if (!is_object($version_info)) {
            return;
        }

        if (version_compare($plugin['version'], $version_info->new_version, '<')) {
            $update_cache->response[$file] = $version_info;
        }
        $update_cache->last_checked = time();
        $update_cache->checked[$file] = $plugin['version'];

        set_site_transient('update_plugins', $update_cache);

        return $version_info;
    }

    /**
     * Output the HTML for showing the update version details on multisite.
     *
     * @param string $file The file location of the plugin in the plugins folder.
     * @param stdClass $update_cache The cached update object.
     * @param array|stdClass $version_info The version info for the plugins.
     */
    public function output_update_notification($file,$update_cache,$version_info){
        $plugin = $this->update_array[$file];
        if (!empty($update_cache->response[$file]) && version_compare($plugin['version'], $version_info->new_version, '<')) {
            // build a plugin list row, with update notification
            $wp_list_table = _get_list_table('WP_Plugins_List_Table');
            echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';

            $changelog_link = self_admin_url('index.php?edd_sl_action=view_plugin_changelog&plugin=' . $file . '&slug=' . $plugin['slug'] . '&TB_iframe=true&width=772&height=911');

            if (empty($version_info->download_link)) {
                printf(
                    __('There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a>.', 'geodirectory'),
                    esc_html($version_info->name),
                    esc_url($changelog_link),
                    esc_html($version_info->new_version)
                );
            } else {
                printf(
                    __('There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.', 'geodirectory'),
                    esc_html($version_info->name),
                    esc_url($changelog_link),
                    esc_html($version_info->new_version),
                    esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file))
                );
            }
            echo '</div></td></tr>';
        }
    }


    /**
     * Updates information on the "View version x.x details" page with custom data.
     *
     * @uses api_request()
     *
     * @param mixed $_data
     * @param string $_action
     * @param object $_args
     * @return object $_data
     */
    public function plugins_api_filter($_data, $_action = '', $_args = null)
    {
        if ($_action != 'plugin_information' || !isset($_args->slug) || (!in_array($_args->slug, $this->update_slugs_array))) {
            return $_data;
        }

        $to_send = array(
            'slug' => $_args->slug,
            'is_ssl' => is_ssl(),
            'fields' => array(
                'banners' => false, // These will be supported soon hopefully
                'reviews' => false
            )
        );

        $api_response = $this->api_request('plugin_information', $to_send);

        if (false !== $api_response) {
            $_data = $api_response;
        }
        return $_data;
    }


    /**
     * Disable SSL verification in order to prevent download update failures
     *
     * @param array $args
     * @param string $url
     * @return object $array
     */
    public function http_request_args($args, $url)
    {
        // If it is an https request and we are performing a package download, disable ssl verification
        if (strpos($url, 'https://') !== false && strpos($url, 'edd_action=package_download')) {
            $args['sslverify'] = false;
        }
        return $args;
    }

    /**
     * Calls the API and, if successfull, returns the object delivered by the API.
     *
     * @uses get_bloginfo()
     * @uses wp_remote_post()
     * @uses is_wp_error()
     *
     * @param string $_action The requested action.
     * @param array $_data Parameters for the API action.
     * @return false|object
     */
    private function api_request($_action, $_data)
    {
        if ($this->api_url == home_url()) {
            return false; // Don't allow a plugin to ping itself
        }

        $update_array = $this->update_array;
        $single = false;
        if (isset($_data['slug'])) {
            foreach ($update_array as $slug => $plugin) {
                if ($_data['slug'] == $plugin['slug']) {
                    $update_array = array();
                    $update_array[$slug] = $plugin;
                    $single = true;
                }
            }
        }
        $api_params = array(
            'edd_action' => 'get_version',
            'update_array' => $update_array,
            'url' => home_url()
        );

        $request = wp_remote_post($this->api_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (!is_wp_error($request)) {
            $request = json_decode(wp_remote_retrieve_body($request));
            $request = self::unserialize_response($request,$single);
            return $request;
        }
        return false;
    }

    /**
     * Unserialize the api response if needed for sections.
     *
     * @param array $response The response from the update api request.
     * @param boolean $single If the request if for a single or multiple plugins.
     * @return mixed
     */
    public function unserialize_response($response,$single){

        foreach ($response as $rslug => $rplugin) {
            $response->{$rslug}->sections = maybe_unserialize($response->{$rslug}->sections);
            if ($single) {
                $response = $response->{$rslug};
            }
        }
        return $response;
    }

    /**
     * Show the changelog on multisite.
     */
    public function show_changelog()
    {
        if (empty($_REQUEST['edd_sl_action']) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] || empty($_REQUEST['slug'])) {
            return;
        }

        if (!current_user_can('update_plugins')) {
            wp_die(__('You do not have permission to install plugin updates', 'geodirectory'), __('Error', 'geodirectory'), array('response' => 403));
        }

        $response = $this->api_request('plugin_latest_version', array('slug' => $_REQUEST['slug']));

        if ($response && isset($response->sections['changelog'])) {
            echo '<div style="background:#fff;padding:10px;">' . $response->sections['changelog'] . '</div>';
        }
        exit;
    }

}