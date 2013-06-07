
function wpl_numKeys(obj)
{
    var count = 0;
    for(var key in obj)
    {
		if (obj.hasOwnProperty(key)) {
 
			for(var key_b in obj[key])
			{		
				//alert ('1');
				count++;
			}
		
		}
      
    }
    return count;
}

//alert(window.location);
var referrer = document.referrer;
var current_page =  window.location.href;
var current_page_parts = current_page.split('#');
current_page = current_page_parts[0];

var parts = location.hostname.split('.');
var subdomain = parts.shift();
var upperleveldomain = parts.join('.'); 

var data_block = jQuery.parseJSON(jQuery.cookie('user_data_json'));
console.log(data_block);


var date = new Date();
var year = date.getUTCFullYear(); 
var month = date.getMonth(); 
var day = date.getDay(); 
var hour = date.getHours(); 
var minute = date.getMinutes(); 
var second = date.getSeconds(); 
var datetime = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;
//alert(datetime);


if (typeof data_block =='object' && data_block)
{
	var count = wpl_numKeys(data_block);
	data_block.items.push(
		{ id : count+1,  current_page: current_page, timestamp: datetime, referrer: referrer}
	);
	
	jQuery.cookie('user_data_json', JSON.stringify(data_block),  { expires: 1, path: '/' });
} 
else
{
	data_block = {items: [
		{id: '1', current_page: current_page,timestamp: datetime,  referrer: referrer,  original_referrer: referrer},
	]};
	
	jQuery.cookie('user_data_json', JSON.stringify(data_block), { expires: 1, path: '/' });
}

function generate_wp_leads_uid(length) {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

    if (! length) {
        length = Math.floor(Math.random() * chars.length);
    }

    var str = '';
    for (var i = 0; i < length; i++) {
        str += chars[Math.floor(Math.random() * chars.length)];
    }
    return str;
}

