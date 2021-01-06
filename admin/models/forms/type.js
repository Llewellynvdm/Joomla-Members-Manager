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
jform_vvvvvvyvvy_required = false;

// Initial Script
jQuery(document).ready(function()
{
	var add_relationship_vvvvvvy = jQuery("#jform_add_relationship input[type='radio']:checked").val();
	vvvvvvy(add_relationship_vvvvvvy);
});

// the vvvvvvy function
function vvvvvvy(add_relationship_vvvvvvy)
{
	// set the function logic
	if (add_relationship_vvvvvvy == 1)
	{
		jQuery('#jform_communicate').closest('.control-group').show();
		jQuery('#jform_field_type').closest('.control-group').show();
		// add required attribute to field_type field
		if (jform_vvvvvvyvvy_required)
		{
			updateFieldRequired('field_type',0);
			jQuery('#jform_field_type').prop('required','required');
			jQuery('#jform_field_type').attr('aria-required',true);
			jQuery('#jform_field_type').addClass('required');
			jform_vvvvvvyvvy_required = false;
		}
		jQuery('#jform_edit_relationship').closest('.control-group').show();
		jQuery('#jform_type').closest('.control-group').show();
		jQuery('#jform_view_relationship').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_communicate').closest('.control-group').hide();
		jQuery('#jform_field_type').closest('.control-group').hide();
		// remove required attribute from field_type field
		if (!jform_vvvvvvyvvy_required)
		{
			updateFieldRequired('field_type',1);
			jQuery('#jform_field_type').removeAttr('required');
			jQuery('#jform_field_type').removeAttr('aria-required');
			jQuery('#jform_field_type').removeClass('required');
			jform_vvvvvvyvvy_required = true;
		}
		jQuery('#jform_edit_relationship').closest('.control-group').hide();
		jQuery('#jform_type').closest('.control-group').hide();
		jQuery('#jform_view_relationship').closest('.control-group').hide();
	}
}

// update fields required
function updateFieldRequired(name, status) {
	// check if not_required exist
	if (jQuery('#jform_not_required').length > 0) {
		var not_required = jQuery('#jform_not_required').val().split(",");

		if(status == 1)
		{
			not_required.push(name);
		}
		else
		{
			not_required = removeFieldFromNotRequired(not_required, name);
		}

		jQuery('#jform_not_required').val(fixNotRequiredArray(not_required).toString());
	}
}

// remove field from not_required
function removeFieldFromNotRequired(array, what) {
	return array.filter(function(element){
		return element !== what;
	});
}

// fix not required array
function fixNotRequiredArray(array) {
	var seen = {};
	return removeEmptyFromNotRequiredArray(array).filter(function(item) {
		return seen.hasOwnProperty(item) ? false : (seen[item] = true);
	});
}

// remove empty from not_required array
function removeEmptyFromNotRequiredArray(array) {
	return array.filter(function (el) {
		// remove ( 一_一) as well - lol
		return (el.length > 0 && '一_一' !== el);
	});
}

// the isSet function
function isSet(val)
{
	if ((val != undefined) && (val != null) && 0 !== val.length){
		return true;
	}
	return false;
} 
