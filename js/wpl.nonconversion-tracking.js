	
jQuery(document).ready(function($) {
	//alert(wplnct.admin_url);
	
	//record non conversion status
	var wp_lead_uid = jQuery.cookie("wp_lead_uid");	
	var wp_lead_id = jQuery.cookie("wp_lead_id");	
	var data_block = jQuery.parseJSON(jQuery.cookie('user_data_json'));
	var json = JSON.stringify(data_block);
	
	jQuery.ajax({
		type: 'POST',
		url: wplnct.admin_url,
		data: {
			action: 'wpl_track_user',
			wp_lead_uid: wp_lead_uid,
			wp_lead_id: wp_lead_id,
			json: json		
		},
		success: function(user_id){
				
			   },
		error: function(MLHttpRequest, textStatus, errorThrown){
				//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
				//die();
			}

	});
			
});