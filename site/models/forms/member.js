/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
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
		jQuery('#jform_user').closest('.control-group').show();
		if (jform_vvvvvvvvvv_required)
		{
			updateFieldRequired('user',0);
			jQuery('#jform_user').prop('required','required');
			jQuery('#jform_user').attr('aria-required',true);
			jQuery('#jform_user').addClass('required');
			jform_vvvvvvvvvv_required = false;
		}

	}
	else
	{
		jQuery('#jform_user').closest('.control-group').hide();
		if (!jform_vvvvvvvvvv_required)
		{
			updateFieldRequired('user',1);
			jQuery('#jform_user').removeAttr('required');
			jQuery('#jform_user').removeAttr('aria-required');
			jQuery('#jform_user').removeClass('required');
			jform_vvvvvvvvvv_required = true;
		}
	}
}

// the vvvvvvv Some function
function account_vvvvvvv_SomeFunc(account_vvvvvvv)
{
	// set the function logic
	if (account_vvvvvvv == 1 || account_vvvvvvv == 4)
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
		jQuery('#jform_email').closest('.control-group').show();
		if (jform_vvvvvvwvvw_required)
		{
			updateFieldRequired('email',0);
			jQuery('#jform_email').prop('required','required');
			jQuery('#jform_email').attr('aria-required',true);
			jQuery('#jform_email').addClass('required');
			jform_vvvvvvwvvw_required = false;
		}

		jQuery('#jform_name').closest('.control-group').show();
		if (jform_vvvvvvwvvx_required)
		{
			updateFieldRequired('name',0);
			jQuery('#jform_name').prop('required','required');
			jQuery('#jform_name').attr('aria-required',true);
			jQuery('#jform_name').addClass('required');
			jform_vvvvvvwvvx_required = false;
		}

	}
	else
	{
		jQuery('#jform_email').closest('.control-group').hide();
		if (!jform_vvvvvvwvvw_required)
		{
			updateFieldRequired('email',1);
			jQuery('#jform_email').removeAttr('required');
			jQuery('#jform_email').removeAttr('aria-required');
			jQuery('#jform_email').removeClass('required');
			jform_vvvvvvwvvw_required = true;
		}
		jQuery('#jform_name').closest('.control-group').hide();
		if (!jform_vvvvvvwvvx_required)
		{
			updateFieldRequired('name',1);
			jQuery('#jform_name').removeAttr('required');
			jQuery('#jform_name').removeAttr('aria-required');
			jQuery('#jform_name').removeClass('required');
			jform_vvvvvvwvvx_required = true;
		}
	}
}

// the vvvvvvw Some function
function account_vvvvvvw_SomeFunc(account_vvvvvvw)
{
	// set the function logic
	if (account_vvvvvvw == 2 || account_vvvvvvw == 3)
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
		jQuery('#jform_main_member').closest('.control-group').show();
		if (jform_vvvvvvxvvy_required)
		{
			updateFieldRequired('main_member',0);
			jQuery('#jform_main_member').prop('required','required');
			jQuery('#jform_main_member').attr('aria-required',true);
			jQuery('#jform_main_member').addClass('required');
			jform_vvvvvvxvvy_required = false;
		}

	}
	else
	{
		jQuery('#jform_main_member').closest('.control-group').hide();
		if (!jform_vvvvvvxvvy_required)
		{
			updateFieldRequired('main_member',1);
			jQuery('#jform_main_member').removeAttr('required');
			jQuery('#jform_main_member').removeAttr('aria-required');
			jQuery('#jform_main_member').removeClass('required');
			jform_vvvvvvxvvy_required = true;
		}
	}
}

// the vvvvvvx Some function
function account_vvvvvvx_SomeFunc(account_vvvvvvx)
{
	// set the function logic
	if (account_vvvvvvx == 3 || account_vvvvvvx == 4)
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

function removeFileCheck(clearServer, target, type){
	UIkit.modal.confirm('Are you sure you want to delete this '+target+'?', function(){ removeFile(clearServer, target, 1, type);	});
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
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.removeFile&format=json&vdm="+vastDevMod);
	if(token.length > 0 && target.length > 0 && type.length > 0){
		var request = 'token='+token+'&filename='+currentFileName+'&target='+target+'&flush='+flush+'&type='+type;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
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
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.checkUnique&format=json&vdm="+vastDevMod);
	if(token.length > 0 && value.length  > 0 && field.length > 0){
		var request = 'token='+token+'&value='+value+'&field='+field;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
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

// set regions that are on the page
regions = {};
var region = 0;
jQuery(document).ready(function($)
{
	jQuery("#jform_region option").each(function()
	{
		var key =  jQuery(this).val();
		var text =  jQuery(this).text();
		regions[key] = text;
	});
	region = jQuery('#jform_region').val();
	getRegion();
});

function getRegion_server(country){
	var getUrl = "index.php?option=com_membersmanager&task=ajax.getRegion&format=json";
	if(token.length > 0 && country > 0){
		var request = 'token='+token+'&country='+country;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function getRegion(){
	jQuery("#loading").show();
	// clear the selection
	jQuery('#jform_region').find('option').remove().end();
	jQuery('#jform_region').trigger('liszt:updated');
	// get country value if set
	var country = jQuery('#jform_country').val();
	getRegion_server(country).done(function(result) {
		setRegion(result);
		jQuery("#loading").hide();
		if (typeof regionButton !== 'undefined') {
			// ensure button is correct
			var region = jQuery('#jform_region').val();
			regionButton(region);
		}
	});
}
function setRegion(array){
	if (array) {
		jQuery('#jform_region').append('<option value="">'+select_a_region+'</option>');
		jQuery.each( array, function( i, id ) {
			if (id in regions) {
				jQuery('#jform_region').append('<option value="'+id+'">'+regions[id]+'</option>');
			}
			if (id == region) {
				jQuery('#jform_region').val(id);
			}
		});
	} else {
		jQuery('#jform_region').append('<option value="">'+create_a_region+'</option>');
	}
	jQuery('#jform_region').trigger('liszt:updated');
}

jQuery(document).ready(function($)
{
	var user_id = $("#jform_user_id").val();
	var showname = 1;
	// check if the user id was found
	if (!isSet(user_id)) {
		var user_id =$("#jform_user").val();
		var showname = 2;
	}
	getUser(user_id, showname);
});
function getUser_server(id, showname){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.getUser&format=json&vdm="+vastDevMod);
	if(token.length > 0 && id > 0 && showname > 0){
		var request = 'token='+token+'&id='+id+'&showname='+showname;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function getUser(id, showname){
	getUser_server(id, showname).done(function(result) {
		jQuery('#user_info').remove();
		if(result) {
			loadUser(result);
		} else {
			getCreateUserFields(1);
		}
	})
}
function getCreateUserFields_server(id){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.getCreateUserFields&format=json&vdm="+vastDevMod);
	if(token.length > 0 && id > 0){
		var request = 'token='+token+'&id='+id;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function getCreateUserFields(id) {
	getCreateUserFields_server(id).done(function(result) {
		jQuery('#user_info').remove();
		if(result) {
			loadUser(result);
		}
	});
}
// user values
var userArray = {};
function setUser_server(id){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.setUser&format=json&vdm="+vastDevMod);
	if (token.length > 0 && id > 0) {
		var request = 'token='+token+'&id='+id+'&data='+JSON.stringify(userArray);
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function setUser(){
	// get the id
	var id = jQuery("#jform_user_id").val();
	// check if the user id was found
	if (!isSet(id)) {
		var id = jQuery("#jform_user").val();
	}
	// get the values
	userArray['var'] = encodeURIComponent(jQuery("#vdm_name").val());
	userArray['uvar'] = encodeURIComponent(jQuery("#vdm_username").val());
	userArray['evar'] = encodeURIComponent(jQuery("#vdm_email").val());
	userArray['dvar'] = encodeURIComponent(jQuery("#vdm_password").val());
	// set the values
	setUser_server(id).done(function(result) {
		if(result.html) {
			jQuery('#user_info').remove();
			loadUser(result.html);
			jQuery('#system-message-container').html(result.success);
		} else if (result.error) {
			jQuery('#system-message-container').html(result.error);
		}
	});
}
// user values
var userCArray = {};
function createUser_server(){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.createUser&format=json&vdm="+vastDevMod);
	if (token.length > 0) {
		var request = 'token='+token+'&key=1&data='+JSON.stringify(userCArray);
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function createUser() {
	// get the values
	userCArray['var'] = encodeURIComponent(jQuery("#vdm_c_name").val());
	userCArray['uvar'] = encodeURIComponent(jQuery("#vdm_c_username").val());
	userCArray['evar'] = encodeURIComponent(jQuery("#vdm_c_email").val());
	userCArray['dvar'] = encodeURIComponent(jQuery("#vdm_c_password").val());
	// this takes long so show spinner
	jQuery("#loading").show();
	// set the values
	createUser_server().done(function(result) {
		if (result.html) {
			jQuery('#user_info').remove();
			loadUser(result.html);
			jQuery('#system-message-container').html(result.success);
		} else if (result.error) {
			jQuery('#system-message-container').html(result.error);
		}
		jQuery("#loading").hide();
	});
}
function loadUser(result){
	// first check the system type
	var account = jQuery("#jform_account").val();
	if (1 == account || 4 == account) {
		jQuery('#jform_user').closest('.span6').append(result);
	}
} 
