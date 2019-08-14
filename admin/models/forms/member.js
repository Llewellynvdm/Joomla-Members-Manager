/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */


// Some Global Values
jform_vvvvvvvvvv_required = false;
jform_vvvvvvwvvw_required = false;
jform_vvvvvvwvvx_required = false;
jform_vvvvvvxvvy_required = false;

// Initial Script
jQuery(document).ready(function()
{
	var account_vvvvvvv = jQuery("#jform_account").val();
	vvvvvvv(account_vvvvvvv);

	var account_vvvvvvw = jQuery("#jform_account").val();
	vvvvvvw(account_vvvvvvw);

	var account_vvvvvvx = jQuery("#jform_account").val();
	vvvvvvx(account_vvvvvvx);
});

// the vvvvvvv function
function vvvvvvv(account_vvvvvvv)
{
	if (isSet(account_vvvvvvv) && account_vvvvvvv.constructor !== Array)
	{
		var temp_vvvvvvv = account_vvvvvvv;
		var account_vvvvvvv = [];
		account_vvvvvvv.push(temp_vvvvvvv);
	}
	else if (!isSet(account_vvvvvvv))
	{
		var account_vvvvvvv = [];
	}
	var account = account_vvvvvvv.some(account_vvvvvvv_SomeFunc);


	// set this function logic
	if (account)
	{
		jQuery('#jform_main_member').closest('.control-group').show();
		// add required attribute to main_member field
		if (jform_vvvvvvvvvv_required)
		{
			updateFieldRequired('main_member',0);
			jQuery('#jform_main_member').prop('required','required');
			jQuery('#jform_main_member').attr('aria-required',true);
			jQuery('#jform_main_member').addClass('required');
			jform_vvvvvvvvvv_required = false;
		}
	}
	else
	{
		jQuery('#jform_main_member').closest('.control-group').hide();
		// remove required attribute from main_member field
		if (!jform_vvvvvvvvvv_required)
		{
			updateFieldRequired('main_member',1);
			jQuery('#jform_main_member').removeAttr('required');
			jQuery('#jform_main_member').removeAttr('aria-required');
			jQuery('#jform_main_member').removeClass('required');
			jform_vvvvvvvvvv_required = true;
		}
	}
}

// the vvvvvvv Some function
function account_vvvvvvv_SomeFunc(account_vvvvvvv)
{
	// set the function logic
	if (account_vvvvvvv == 3 || account_vvvvvvv == 4)
	{
		return true;
	}
	return false;
}

// the vvvvvvw function
function vvvvvvw(account_vvvvvvw)
{
	if (isSet(account_vvvvvvw) && account_vvvvvvw.constructor !== Array)
	{
		var temp_vvvvvvw = account_vvvvvvw;
		var account_vvvvvvw = [];
		account_vvvvvvw.push(temp_vvvvvvw);
	}
	else if (!isSet(account_vvvvvvw))
	{
		var account_vvvvvvw = [];
	}
	var account = account_vvvvvvw.some(account_vvvvvvw_SomeFunc);


	// set this function logic
	if (account)
	{
		jQuery('#jform_password_check').closest('.control-group').show();
		jQuery('#jform_useremail').closest('.control-group').show();
		// add required attribute to useremail field
		if (jform_vvvvvvwvvw_required)
		{
			updateFieldRequired('useremail',0);
			jQuery('#jform_useremail').prop('required','required');
			jQuery('#jform_useremail').attr('aria-required',true);
			jQuery('#jform_useremail').addClass('required');
			jform_vvvvvvwvvw_required = false;
		}
		jQuery('#jform_username').closest('.control-group').show();
		// add required attribute to username field
		if (jform_vvvvvvwvvx_required)
		{
			updateFieldRequired('username',0);
			jQuery('#jform_username').prop('required','required');
			jQuery('#jform_username').attr('aria-required',true);
			jQuery('#jform_username').addClass('required');
			jform_vvvvvvwvvx_required = false;
		}
		jQuery('#jform_password').closest('.control-group').show();
		jQuery('#jform_user').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_password_check').closest('.control-group').hide();
		jQuery('#jform_useremail').closest('.control-group').hide();
		// remove required attribute from useremail field
		if (!jform_vvvvvvwvvw_required)
		{
			updateFieldRequired('useremail',1);
			jQuery('#jform_useremail').removeAttr('required');
			jQuery('#jform_useremail').removeAttr('aria-required');
			jQuery('#jform_useremail').removeClass('required');
			jform_vvvvvvwvvw_required = true;
		}
		jQuery('#jform_username').closest('.control-group').hide();
		// remove required attribute from username field
		if (!jform_vvvvvvwvvx_required)
		{
			updateFieldRequired('username',1);
			jQuery('#jform_username').removeAttr('required');
			jQuery('#jform_username').removeAttr('aria-required');
			jQuery('#jform_username').removeClass('required');
			jform_vvvvvvwvvx_required = true;
		}
		jQuery('#jform_password').closest('.control-group').hide();
		jQuery('#jform_user').closest('.control-group').hide();
	}
}

// the vvvvvvw Some function
function account_vvvvvvw_SomeFunc(account_vvvvvvw)
{
	// set the function logic
	if (account_vvvvvvw == 1 || account_vvvvvvw == 4)
	{
		return true;
	}
	return false;
}

// the vvvvvvx function
function vvvvvvx(account_vvvvvvx)
{
	if (isSet(account_vvvvvvx) && account_vvvvvvx.constructor !== Array)
	{
		var temp_vvvvvvx = account_vvvvvvx;
		var account_vvvvvvx = [];
		account_vvvvvvx.push(temp_vvvvvvx);
	}
	else if (!isSet(account_vvvvvvx))
	{
		var account_vvvvvvx = [];
	}
	var account = account_vvvvvvx.some(account_vvvvvvx_SomeFunc);


	// set this function logic
	if (account)
	{
		jQuery('#jform_email').closest('.control-group').show();
		// add required attribute to email field
		if (jform_vvvvvvxvvy_required)
		{
			updateFieldRequired('email',0);
			jQuery('#jform_email').prop('required','required');
			jQuery('#jform_email').attr('aria-required',true);
			jQuery('#jform_email').addClass('required');
			jform_vvvvvvxvvy_required = false;
		}
	}
	else
	{
		jQuery('#jform_email').closest('.control-group').hide();
		// remove required attribute from email field
		if (!jform_vvvvvvxvvy_required)
		{
			updateFieldRequired('email',1);
			jQuery('#jform_email').removeAttr('required');
			jQuery('#jform_email').removeAttr('aria-required');
			jQuery('#jform_email').removeClass('required');
			jform_vvvvvvxvvy_required = true;
		}
	}
}

// the vvvvvvx Some function
function account_vvvvvvx_SomeFunc(account_vvvvvvx)
{
	// set the function logic
	if (account_vvvvvvx == 2 || account_vvvvvvx == 3)
	{
		return true;
	}
	return false;
}

// update required fields
function updateFieldRequired(name,status)
{
	var not_required = jQuery('#jform_not_required').val();

	if(status == 1)
	{
		if (isSet(not_required) && not_required != 0)
		{
			not_required = not_required+','+name;
		}
		else
		{
			not_required = ','+name;
		}
	}
	else
	{
		if (isSet(not_required) && not_required != 0)
		{
			not_required = not_required.replace(','+name,'');
		}
	}

	jQuery('#jform_not_required').val(not_required);
}

// the isSet function
function isSet(val)
{
	if ((val != undefined) && (val != null) && 0 !== val.length){
		return true;
	}
	return false;
}


jQuery(document).ready(function($)
{
	var tokenValue = jQuery('#jform_token').val();
	// check if this token value is used
	checkUnique(tokenValue, 'token', 0);
	// load the profile image if it is set
	var profile = $('#jform_profile_image').val();
	if (profile.length > 20)
	{
		setFile(profile, false, 'profile', 'image')
	}
});
function getUserDetails(user){
	getUserDetails_server(user).done(function(result) {
		if (result) {
			setUserDetails(result);
		}
	});
}
function getUserDetails_server(user){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.getUserDetails&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && user > 0){
		var request = 'token='+token+'&user='+user;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}
function setUserDetails(result){
	if (result.name) {
		for (var key in result) {
			if (result.hasOwnProperty(key)) {
				jQuery('#jform_' + key).val(result[key]);
			}
		}
	}
}

function setFilekey(filename, fileFormat, target, type){
	var currentFileName = jQuery("#jform_"+target+"_"+type).val();
	if (currentFileName.length > 20 && (type === 'image' || type === 'document')){
		// remove file from server
		removeFile_server(currentFileName, target, 2, type);
	}
	// set new key
	if ((filename.length > 20 && (type === 'image' || type === 'document')) || (isJsonString(filename) && (type === 'images' || type === 'documents' || type === 'media'))){
		if((type === 'images' || type === 'documents' || type === 'media') && jQuery("#jform_id").val() == 0 && isJsonString(currentFileName)) {
			var newA = jQuery.parseJSON(currentFileName);
			var newB = jQuery.parseJSON(filename);
			var filename = JSON.stringify(jQuery.merge(newA, newB));
		}
		jQuery("#jform_"+target+"_"+type).val(filename);
		// set the FILE
		return setFile(filename, fileFormat, target, type);
	}
	return false;
}

function setFile(filename, fileFormat, target, type){
	if (type === 'image' || type === 'document') {
		if (!target) {
			target = filename.split('_')[0];
		}
		if (!type) {
			type = filename.split('_')[1];
		}
		if (!fileFormat) {
			fileFormat = filename.split('_')[2];
		}
		var isAre = 'is';
	} else if ((type === 'images' || type === 'documents' || type === 'media') && isJsonString(filename) ) {
		filename = jQuery.parseJSON(filename);
		if (!target) {
			target = filename[0].split('_')[0];
		}
		if (!type) {
			type = filename[0].split('_')[1];
		}
		var isAre = 'are';
	} else {
		return false;
	}
	// set icon
	if (type === 'images' || type === 'image') {
		var icon = 'file-image-o';
	} else {
		var icon = 'file';
	}
	var thenotice = '<div class="success-'+target+'-'+type+'-8768"><div class="uk-alert uk-alert-success" data-uk-alert><p class="uk-text-center"><span class="uk-text-bold uk-text-large"><i class="uk-icon-'+icon+'"></i> Your '+target+' '+type+' '+isAre+' set </span> </p></div>';
	var thefile = getFile(filename, fileFormat, target, type);
	jQuery("."+target+"_"+type+"_uploader").append(thenotice+thefile);
	// all is done
	return true;
}

function removeFileCheck(clearServer, target, type, uiVer){
	if (3 == uiVer) {
		UIkit.modal.confirm('Are you sure you want to delete this '+target+'?').then(function(){ removeFile(clearServer, target, 1, type); });
	} else {
		UIkit2.modal.confirm('Are you sure you want to delete this '+target+'?', function(){ removeFile(clearServer, target, 1, type); });
	}
}

function removeFile(clearServer, target, flush, type){
	if ((clearServer.length > 20 && (type === 'image' || type === 'document')) || (clearServer.length > 1 && (type === 'images' || type === 'documents' || type === 'media'))){
		// remove file from server
		removeFile_server(clearServer, target, flush, type);
	}
	jQuery(".success-"+target+"-"+type+"-8768").remove();	
	// remove locally 
	if (clearServer.length > 20 && (type === 'image' || type === 'document')) {
		// remove the file
		jQuery("#jform_"+target+"_"+type).val('');
	} else if (clearServer.length > 20 && (type === 'images' || type === 'documents' || type === 'media')) {
		// get the old values
		var filenames = jQuery("#jform_"+target+"_"+type).val();
		if (isJsonString(filenames)) {
			filenames = jQuery.parseJSON(filenames);
			// remove the current file from those values
			filenames = jQuery.grep(filenames, function(value) {
				return value != clearServer;
			});
			if (typeof filenames == 'object' && !jQuery.isEmptyObject(filenames)) {
				// set the new values
				var filename = JSON.stringify(filenames);
				jQuery("#jform_"+target+"_"+type).val(filename);
				setFile(filename, 0, target, type);
			} else {
				jQuery("#jform_"+target+"_"+type).val('');
			}
		} else {
			jQuery("#jform_"+target+"_"+type).val('');
		}
	}
}

function removeFile_server(currentFileName, target, flush, type){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.removeFile&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && target.length > 0 && type.length > 0){
		var request = 'token='+token+'&filename='+currentFileName+'&target='+target+'&flush='+flush+'&type='+type;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}
function isJsonString(str) {
       if (typeof str != 'string') {
              str = JSON.stringify(str);
       }
       try {
               var json = jQuery.parseJSON(str);
       } catch(err) {
               return false;
       }   
       if (typeof json == 'object' && isEmpty(json)) {
              return false;
       } else if(typeof json == 'object') {
              return true;
       }
	return false;
}
function isEmpty(obj) {
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }
    return true;
}


function checkUnique_server(value, field){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.checkUnique&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && value.length  > 0 && field.length > 0){
		var request = 'token='+token+'&value='+value+'&field='+field;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}
function checkUnique(value, field, show){
	// check that we have a value 
	if (value.length > 1) {
		checkUnique_server(value, field).done(function(result) {
			if(result.value && result.message){
				// show notice that functioName is okay
				if (show) {
					UIkit2.notify({message: result.message, timeout: 5000, status: result.status, pos: 'top-right'});
				}
				jQuery('#jform_'+field).val(result.value);
			} else if(result.message){
				// show notice that functionName is not okay
				if (show) {
					UIkit2.notify({message: result.message, timeout: 5000, status: result.status, pos: 'top-right'});
				}
				jQuery('#jform_'+field).val('');
			} else {
				// set an error that message was not send
				if (show) {
					UIkit2.notify({message: Joomla.JText._('COM_MEMBERSMANAGER_VALUE_ALREADY_TAKEN_PLEASE_TRY_AGAIN'), timeout: 5000, status: 'danger', pos: 'top-right'});
				}
				jQuery('#jform_'+field).val('');
			}
		});
	}
} 
