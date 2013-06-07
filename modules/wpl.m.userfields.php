<?php

$wpleads_user_fields['first_name']['label'] = 'First Name';
$wpleads_user_fields['first_name']['name'] = 'wpleads_first_name';
$wpleads_user_fields['first_name']['priority'] = 1;
$wpleads_user_fields['first_name']['nature'] = "text-30";


$wpleads_user_fields['last_name']['label'] = 'Last Name';
$wpleads_user_fields['last_name']['name'] = 'wpleads_last_name';
$wpleads_user_fields['last_name']['priority'] = 2;
$wpleads_user_fields['last_name']['nature'] = "text-30";

$wpleads_user_fields['email']['label'] = 'Email';
$wpleads_user_fields['email']['name'] = 'wpleads_email_address';
$wpleads_user_fields['email']['priority'] = 3;
$wpleads_user_fields['email']['nature'] = "text-30";

//$wpleads_user_fields['ip_address']['priority'] = 99;
//$wpleads_user_fields['ip_address']['nature'] = "text-30-readonly";
//$wpleads_user_fields['ip_address']['value'] = get_post_meta( $post->ID , 'wpleads_ip_address', true );

$wpleads_user_fields['company_name']['label'] = 'Company Name';
$wpleads_user_fields['company_name']['name'] = 'wpleads_company_name';
$wpleads_user_fields['company_name']['priority'] = 4;
$wpleads_user_fields['company_name']['nature'] = "text-30";
// Maybe have global settings with if statements here: if website option off don't render this next option
$wpleads_user_fields['company_name']['label'] = 'Website';
$wpleads_user_fields['company_name']['name'] = 'wpleads_website';
$wpleads_user_fields['company_name']['priority'] = 4;
$wpleads_user_fields['company_name']['nature'] = "text-30";


$wpleads_user_fields['mobile_phone']['label'] = 'Mobile Phone';
$wpleads_user_fields['mobile_phone']['name'] = 'wpleads_mobile_phone';
$wpleads_user_fields['mobile_phone']['priority'] = 3;
$wpleads_user_fields['mobile_phone']['nature'] = "text-30";

$wpleads_user_fields['work_phone']['label'] = 'Work Phone';
$wpleads_user_fields['work_phone']['name'] = 'wpleads_work_phone';
$wpleads_user_fields['work_phone']['priority'] = 3;
$wpleads_user_fields['work_phone']['nature'] = "text-30";

$wpleads_user_fields['address_line_1']['label'] = 'Address';
$wpleads_user_fields['address_line_1']['name'] = 'wpleads_address_line_1';
$wpleads_user_fields['address_line_1']['priority'] = 5;
$wpleads_user_fields['address_line_1']['nature'] = "text-40";

$wpleads_user_fields['address_line_2']['label'] = 'Address Continued';
$wpleads_user_fields['address_line_2']['name'] = 'wpleads_address_line_2';
$wpleads_user_fields['address_line_2']['priority'] = 6;
$wpleads_user_fields['address_line_2']['nature'] = "text-40";

$wpleads_user_fields['city']['label'] = 'City';
$wpleads_user_fields['city']['name'] = 'wpleads_city';
$wpleads_user_fields['city']['priority'] = 7;
$wpleads_user_fields['city']['nature'] = "text-30";

$wpleads_user_fields['state']['label'] = 'State/Region';
$wpleads_user_fields['state']['name'] = 'wpleads_region_name';
$wpleads_user_fields['state']['priority'] = 8;
$wpleads_user_fields['state']['nature'] = "text-30";

$wpleads_user_fields['zip']['label'] = 'Zip-code';
$wpleads_user_fields['zip']['name'] = 'wpleads_zip';
$wpleads_user_fields['zip']['priority'] = 9;
$wpleads_user_fields['zip']['nature'] = "text-10";

$wpleads_user_fields['country']['label'] = 'Country';
$wpleads_user_fields['country']['name'] = 'wpleads_country_code';
$wpleads_user_fields['country']['priority'] = 10;
$wpleads_user_fields['country']['nature'] = "dropdown-country";

$wpleads_user_fields['websites']['label'] = 'Related Websites';
$wpleads_user_fields['websites']['name'] = 'wpleads_websites';
$wpleads_user_fields['websites']['priority'] = 11;
$wpleads_user_fields['websites']['nature'] = "links";

$wpleads_user_fields['notes']['label'] = 'Notes';
$wpleads_user_fields['notes']['name'] = 'wpleads_notes';
$wpleads_user_fields['notes']['priority'] = 99;
$wpleads_user_fields['notes']['nature'] = "textarea-5";



?>