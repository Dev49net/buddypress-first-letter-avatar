/*
	Plugin: BuddyPress First Letter Avatar
	Plugin website: http://dev49.net
 */


/* BuddyPress First Letter Avatar */



var bpfla_data_attribute = bpfla_vars_data.img_data_attribute;
var bpfla_ajaxurl = bpfla_vars_data.ajaxurl;
var bpfla_nonce = bpfla_vars_data.wp_nonce;


jQuery(document).ready(function($){

	$('[' + bpfla_data_attribute + ']').each(function(){

		var gravatar_uri = $(this).attr(bpfla_data_attribute);
		var current_object = $(this); // assign this img to variable
		$(current_object).removeAttr(bpfla_data_attribute); // remove data attribute - not needed anymore

		var data = {
			'action' : 'bpfla_gravatar_verify',
			'verification' : bpfla_nonce,
			'gravatar_uri' : gravatar_uri
		};

		$.post(bpfla_ajaxurl, data, function(response){
			if (response.indexOf('1') >= 0){ // if the response contains '1'...
				$(current_object).attr('src', gravatar_uri); // replace image src with gravatar uri
			}
		});

	});

});
