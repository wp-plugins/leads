jQuery(document).ready(function () {
	jQuery('.row-actions').each(function() {
		var jQuerylist = jQuery(this);
		var jQueryfirstChecked = jQuerylist.parent().parent().find('.column-first-name');

		if ( !jQueryfirstChecked.html() )
			return;

		jQuerylist.appendTo(jQueryfirstChecked);
	}); 

	jQuery('.touchpoint-value').each(function() {
		var touch_val = jQuery(this).text();

		if ( touch_val === "0" ) {
			jQuery(this).parent().hide();
		}
		jQuery(this).find(".touchpoint-minute").show();
	}); 
	
	jQuery("#submitdiv .hndle").text("Update Lead Information");
	var html = '<a class="add-new-h2" href="edit.php?post_type=wp-lead">Back</a>';
	jQuery('.add-new-h2').before(html);

	//populate country
	jQuery('.wpleads-country-dropdown').val(jQuery('#hidden-country-value').val());
	
	jQuery('.add-new-link').live('click', function(e){
		var count = jQuery('#wpleads_websites-container .wpleads_link').size();
		var true_count = count+1;
		var html = '<input name="wpleads_websites['+count+']" class="wpleads_link" type="text" size="70" value="" />';
		jQuery('#wpleads_websites-container').append(html);
	});
	
	jQuery('.wpleads_remove_link').live('click',function(e){
		var this_id = jQuery(this).attr('id');
		jQuery('#wpleads_websites-'+this_id).remove();
	});
	jQuery('#wpleads_main_container input').each(
		
        function(){
   			// hide empty fields
          if( !jQuery(this).val() ) {
 			jQuery(this).parent().parent().hide().addClass('hidden-lead-fields');
            }
          
        }
    ); 
    if (jQuery('#wpleads-td-wpleads_websites').hasClass('hidden-lead-fields')) {
    jQuery('.wpleads_websites').hide().addClass('hidden-lead-fields');
	}
    jQuery("#show-hidden-fields").click(function() {
 	jQuery(".hidden-lead-fields").toggle();
 	jQuery("#add-notes").hide();
	});
	var notesarea = jQuery("#wpleads-td-wpleads_notes").text();
	if (notesarea === "") {
		jQuery("#wpleads-td-wpleads_notes textarea").hide().addClass('hidden-lead-fields');
		var expandnotes = "<span id='add-notes'>No Notes. Click here to add some.</span>";
		jQuery(expandnotes).appendTo(jQuery("#wpleads-td-wpleads_notes"));
		
	}
	  jQuery("#add-notes").click(function() {
 	jQuery("#wpleads-td-wpleads_notes textarea").toggle();
 	jQuery("#add-notes").hide();
	});

	 jQuery(".conversion-tracking-header").on("click", function(event){
var link = jQuery(this).find(".toggle-conversion-list");
var conversion_log = jQuery(this).parent().find(".conversion-session-view");
  conversion_log.toggle();
      if (jQuery(conversion_log).is(":visible")) {
                 link.text('-');                
            } else {
                 link.text('+');                
            }    
}); 
 	var textchange = jQuery("#timestamp").html().replace("Published", "Created");
  		jQuery('#timestamp').html(textchange);
	var pageviews = jQuery(".marker").size();
	var totalconversions = jQuery(".wpleads-conversion-tracking-table").size();
	jQuery("#p-view-total").text(pageviews);
	jQuery("#conversion-total").text(totalconversions);
	jQuery('h2 .nav-tab').eq(0).css("margin-left", "10px");
jQuery("#message.updated").text("Lead Updated").css("padding", "10px");
		jQuery('.wpleads-conversion-tracking-table').each(function() {
			var number_of_pages = jQuery(this).find('.lp-page-view-item').size();
			jQuery(this).find("#pages-view-in-session").text(number_of_pages);
			if (number_of_pages == 1) {
			   jQuery(this).find(".session-stats-header").hide();  
			   jQuery(this).find("#session-pageviews").hide();            
			   }      
		});	
		// view toggles
jQuery(".view-this-lead-session a").on("click", function(event){
var s_number = jQuery(this).attr("rel");
var correct_session = ".session_id_" + s_number;
console.log(correct_session);
jQuery(".conversion-session-view").hide();
jQuery(correct_session).show();
});	
jQuery('.conversion-date').each(function (i) {
var inew = i + 1;
var new_date = ".date_" + inew; 
var get_text = jQuery(new_date).text();
jQuery(this).append(" on " + get_text);
});	
// lead mapping 
var selectbox = jQuery('<select style="display:none" name="NOA" class="id_NOA"></select>'); 
jQuery("#raw-data-display").prepend(selectbox);
jQuery('.wpleads-th label').each(function(i) {
			// create select options
new_loop_val = i + 1;
var id_for_val = jQuery(this).parent().parent().attr("class");
var final_id = id_for_val.replace(" hidden-lead-fields","");
field_name_dirty = jQuery(this).text();
var field_name_clean = field_name_dirty.replace(":","");
var field_name_cleaner = field_name_clean.replace("/","");
jQuery(".id_NOA").append("<option value='" + final_id +"'>" + field_name_cleaner + "</option>");
		});	 
jQuery(".map-raw-field").on("click", function(event){
var count_of_fields = jQuery(this).parent().find(".possible-map-value").size();
var this_selected = jQuery(this).parent().find(".toggle-val").size();
console.log(this_selected);
if (this_selected === 1) {
jQuery(".toggle-val").addClass("re-do").removeClass("toggle-val");
jQuery(".re-do").addClass("toggle-val").removeClass("re-do");
}
if (count_of_fields === 1){
	jQuery(this).parent().find(".possible-map-value").addClass('toggle-val');
}	
jQuery(".map-active-class").removeClass("map-active-class");

jQuery(this).find(".apply-map").show();
jQuery(this).prepend(selectbox);
jQuery(selectbox).show();
jQuery(".map-hide").show();
jQuery(this).addClass("map-active-class");

}); 
var nonce_val = wp_lead_map.wp_lead_map_nonce;
jQuery(".apply-map").on('click', function () {
        var value_clicked = jQuery(this).parent().parent().find(".toggle-val").size();

        if (value_clicked === 0) {
            alert("You must select one of the values to map!");
        } else {

            var conf = confirm("Are you sure you want to update this field?");

            if (conf == true) {
                var map_val = jQuery(this).parent().find("select").val();
                var map_val_id = "#" + map_val;
                var toggle_value = jQuery(this).parent().parent().find('.toggle-val').text();
                //console.log(map_val_id);
                jQuery(map_val_id).val(toggle_value);
                jQuery(map_val_id).parent().parent().show();
                //   alert(map_val + " Updated as " + toggle_value + ". Make sure to Save the Post!"); 
                // define the bulk edit row
                var post_id = jQuery("#post_ID").val();
                var status = "Read";

                jQuery.ajax({
                    type: 'POST',
                    url: wp_lead_map.ajaxurl,
                    context: this,
                    data: {
                        action: 'wp_leads_raw_form_map_save',
                        meta_val: map_val,
                        mapped_field: toggle_value,
                        page_id: post_id,
                        nonce: nonce_val
                    },

                    success: function (data) {
                        var self = this;
                        //alert(data);
                        // jQuery('.lp-form').unbind('submit').submit();
                        var worked = '<span class="success-message-map">Success! ' + map_val + ' set to ' + toggle_value + '</span>';
                        var s_message = jQuery(self).parent();
                        jQuery(worked).appendTo(s_message);
                        //alert("Changes Saved!");
                    },

                    error: function (MLHttpRequest, textStatus, errorThrown) {
                        alert("Ajax not enabled");
                    }
                });

                return false;

            }
        }
        //alert(toggle_value);
    });

jQuery(".possible-map-value").on("click", function(event){
jQuery(".toggle-val").removeClass("toggle-val");
jQuery(this).toggleClass("toggle-val");
});

var null_lead_status = jQuery("#current-lead-status").text();

if (null_lead_status === "") {
var post_id = jQuery("#post_ID").val();
jQuery.ajax({
			type: 'POST',
			url: wp_lead_map.ajaxurl,
			context: this,
			data: {
				action: 'wp_leads_auto_mark_as_read',
				page_id: post_id,
				nonce: nonce_val
			},
			
			success: function(data){
				var self = this;
						//alert(data);
						// jQuery('.lp-form').unbind('submit').submit();
						var worked = '<span class="success-message-map" style="display: inline-block;margin-top: -1px;margin-left: 20px;padding:4px 25px 4px 20px;position: absolute;">This Lead has been marked as read/viewed.</span>';
		            	var s_message = jQuery("#lead-top-area");
						jQuery(worked).appendTo(s_message);	 
						// alert("This lead is marked as read.");
					   },

			error: function(MLHttpRequest, textStatus, errorThrown){
				alert("Error thrown not sure why");
				}
		});

}

});