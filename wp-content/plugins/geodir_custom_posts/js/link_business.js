// CPT Link Business

jQuery(document).ready(function() {
    jQuery('#geodir_link_cpt_business_autofill').bind("click", function() {
        var place_id = jQuery('select[name="geodir_link_cpt_business"]').val();
        var nonce = jQuery('input[name="geodir_link_cpt_business_nonce"]').val();
        if(place_id != '') {
            var ajax_url = geodir_cpt_link_alert_js_var.geodir_cpt_link_ajax_url;
            jQuery.post(ajax_url, {
                _wpnonce: nonce,
                auto_fill: "geodir_cpt_business_autofill",
                place_id: place_id
            }).done(function(data) {
                if(jQuery.trim(data) != '') {
                    var address = false;
                    var json = jQuery.parseJSON(data);
                    jQuery.each(json, function(i, item) {
                        if(item.key == 'text') {
                            if(item.value == false) item.value = '';
                            jQuery('input[name="' + i + '"]').val(item.value);
                        }
                        if(item.key == 'textarea') {
                            if(item.value == false) item.value = '';
                            jQuery('#' + i).val(item.value);
                            if(typeof tinymce != 'undefined') {
                                if(tinyMCE.get('content') && i == 'post_desc') {
                                    i = 'content';
                                    jQuery('#title').focus();
                                }
                                if(tinymce.editors.length > 0 && tinyMCE.get(i)) tinyMCE.get(i).setContent(item.value);
                            }
                        }
                        if(i == 'post_address') address = true;
                        if(i == 'post_city' || i == 'post_region' || i == 'post_country') {
                            if(jQuery("#" + i + " option:contains('" + item.value + "')").length == 0) {
                                jQuery("#" + i).append('<option value="' + item.value + '">' + item.value + '</option>');
                            }
                            jQuery('#' + i + ' option[value="' + item.value + '"]').attr("selected", true);
                            jQuery("#" + i).trigger("chosen:updated");
                        }
                        if(item.key == 'checkbox') {
                            var value = parseInt(item.value) > 0 ? 1 : 0;
                            jQuery('input[name="' + i + '"]').val(value);
                            var Ele = jQuery('input[name="' + i + '"][value="' + value + '"]');
                            if (jQuery(Ele).prop('type')!='checkbox') {
                                jQuery(Ele).closest('.geodir_form_row').find('input[type="checkbox"]').prop('checked', value);
                            }
                        }
                        if(item.key == 'radio') {
                            var value = item.value == false ? '' : item.value;
                            jQuery('input[name="' + i + '"][value="' + value + '"]').prop('checked', true);
                        }
                        if(item.key == 'select') {
                            var value = item.value == false ? '' : item.value;
                            jQuery('select[name="' + i + '"]').val(value);
                            jQuery('select[name="' + i + '"]').chosen().trigger("chosen:updated");
                        }
                        if(item.key == 'multiselect') {
                            var value = item.value == false ? '' : item.value;
                            var field_type = jQuery('[name="' + i + '[]"]').prop('type');
                            switch(field_type){
                                case 'checkbox':
                                case 'radio':
                                    jQuery('input[name="' + i + '[]"]').val(value);
                                    value = typeof value == 'object' && value ? value[0] : '';
                                    if (field_type == 'radio' && value != '') {
                                        jQuery('input[name="' + i + '[]"][value="' + value + '"]').prop('checked', true);
                                    }
                                    break;
                                default:
                                    jQuery('select[name="' + i + '[]"]').val(value);
                                    jQuery('select[name="' + i + '[]"]').chosen().trigger("chosen:updated");
                                    break;
                            }
                        }
                        if(item.key == 'datepicker' && item.value && item.value != '' ) {
                            jQuery('input[name="' + i + '"]').datepicker('setDate', item.value);
                        }
                        if(item.key == 'time' && item.value && item.value != '' ) {
                            jQuery('input[name="' + i + '"]').timepicker('setTime', new Date("January 1, 2015 "+item.value));
                        }
                        if(item.key == 'tags' && item.value && item.value != '' ) {
                            jQuery('input[name="post_tags"]').val(item.value);
                            jQuery('input[name="newtag[gd_event_tags]"]').val(item.value);
                        }
                    });
                    if(address) jQuery('#post_set_address_button').click();
                }
            });
        }
    });

    // now add an ajax function when value is entered in chose select text field
    geodir_link_cpt_business_chosen_ajax();
});

function geodir_link_cpt_business_chosen_ajax() {
    jQuery("select#geodir_link_cpt_business").each(function() {
        var curr_chosen = jQuery(this);
        var ajax_url = geodir_cpt_link_alert_js_var.geodir_cpt_link_ajax_url;
        var obj_name = curr_chosen.prop('name');
        var post_type = curr_chosen.data('post_type');
        var obbj_info = obj_name.split('_');
        listfor = obbj_info[1];
        if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined) {
            curr_chosen.ajaxChosen({
                    keepTypingMsg: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_KEEP_TYPE_TEXT,
                    lookingForMsg: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_LOOKING_FOR_TEXT,
                    type: 'GET',
                    url: ajax_url + '&task=geodir_cpt_link_fill_listings&post_type=' + post_type,
                    dataType: 'html',
                    success: function(data) {
                        curr_chosen.html(data).chosen().trigger("chosen:updated");
                    }
                }, null,
                {
                    no_results_text: geodir_cpt_link_alert_js_var.CPT_LINK_CHOSEN_NO_RESULTS_MATCH_TEXT,
                });
        }
    });
}