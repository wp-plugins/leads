jQuery(document).ready(function($) {

	if (jQuery('.wp-list-table').length > 0)
	{
		
		// Script for leads list page
		jQuery('.row-actions').each(function() {
			var jQuerylist = jQuery(this);
			var jQueryfirstChecked = jQuerylist.parent().parent().find('.column-first-name');

			if ( !jQueryfirstChecked.html() )
				return;

			jQuerylist.appendTo(jQueryfirstChecked);
		}); 
		
		jQuery("body").on('click', '.lead-grav-img', function () {
			var checked = jQuery(this).parent().parent().find(".check-column input").is(':checked');
			if (checked === false) {
				jQuery(this).parent().parent().find(".check-column input").attr("checked", "checked");
			} else {
				jQuery(this).parent().parent().find(".check-column input").removeAttr('checked');
			}
			
   		});

		jQuery('.column-status').each(
			
			function(){
				// hide empty fields
			  if( jQuery(this).text() == "" ) {
				jQuery(this).text('New Lead');
				jQuery(this).parent().css("background-color", "#e2ffc9");
				}
				if( jQuery(this).text() == "New Lead" ) {
		  jQuery(this).parent().css("background-color", "#e2ffc9");
				} 
			   if( jQuery(this).text() == "Lost" ) {
				jQuery(this).parent().css("background-color", "#ffe2e2");
				}
				if( jQuery(this).text() == "Read" ) {
		  jQuery(this).parent().css("background-color", "#f2f2f2");
				}
			   if( jQuery(this).text() == "Contacted" ) {
				jQuery(this).parent().css("background-color", "#fcf7d1");
				} 
				
			}
		); 
		
		jQuery(".submitdelete").text("Delete");
		var textchange = jQuery("li.publish").html().replace("Published", "Live");
			jQuery('li.publish').html(textchange);

		  jQuery('.date.column-date').each(function() {
		 var textchange2 = jQuery(this).html().replace("Published", "");
		  jQuery(this).html(textchange2);   
		}); 
	  

		var mark_as_read = '<span class="mark-viewed" title="Mark lead as viewed">Mark as read</span><span class="mark-viewed-undo">Undo</span>';
		//jQuery(mark_as_read).appendTo(".row-actions");
		jQuery(mark_as_read).appendTo(".edit");
		  
		jQuery('.mark-viewed, .mark-viewed-undo').each(function() {
		   var this_lead = jQuery(this).parent().parent().parent().parent().attr("id");
		   var lead_id = this_lead.replace("post-","");
		   jQuery(this).attr("id", lead_id);

		  }); 

		jQuery('.type-wp-lead').each(function() {
		var current_status = jQuery(this).find(".status").text();
		  if( current_status === "Read" ) {
		jQuery(this).find(".mark-viewed").hide();
			jQuery(this).find(".mark-viewed-undo").text("Mark as new").show();
		   }
		  }); 

		$( '.mark-viewed' ).on( 'click', function() {

			 // define the bulk edit row
			var post_id = jQuery(this).attr("id");
			var status = "Read";
		  
			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  context: this,
			  data: {
				action: 'wp_leads_mark_as_read_save',
				j_rules: status,
				page_id: post_id
			  },
			  
			  success: function(data){
				var self = this;
					//alert(data);
					// jQuery('.lp-form').unbind('submit').submit();
					jQuery(self).hide();
					jQuery(self).parent().find(".mark-viewed-undo").show();
					jQuery(self).parent().parent().parent().parent().css("background-color", "#f2f2f2");
					jQuery(self).parent().parent().parent().parent().find(".status").text("Read");
					//alert("Changes Saved! Refresh the page to see your changes");
					 },

			  error: function(MLHttpRequest, textStatus, errorThrown){
				alert("Ajax not enabled");
				}
			});
			
			return false;
		  
		  });

		$( '.mark-viewed-undo' ).on( 'click', function() {

			 // define the bulk edit row
			var post_id = jQuery(this).attr("id");
			var status = "Read";
		  
			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  context: this,
			  data: {
				action: 'wp_leads_mark_as_read_undo',
				j_rules: status,
				page_id: post_id
			  },
			  
			  success: function(data){
				var self = this;
					//alert(data);
					// jQuery('.lp-form').unbind('submit').submit();
					jQuery(self).hide();
					jQuery(self).parent().find(".mark-viewed").show();
					jQuery(self).parent().parent().parent().parent().css("background-color", "#e2ffc9");
					jQuery(self).parent().parent().parent().parent().find(".status").text("New Lead");
					//alert("Changes Saved! Refresh the page to see your changes");
					 },

			  error: function(MLHttpRequest, textStatus, errorThrown){
				alert("Ajax not enabled");
				}
			});
			
			return false;
		  
		  });
	}
});
