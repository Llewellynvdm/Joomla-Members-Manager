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
jform_vvvvvvyvvz_required = false;
jform_vvvvvvyvwa_required = false;
jform_vvvvvvyvwb_required = false;
jform_vvvvvvyvwc_required = false;

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
		if (jform_vvvvvvyvvz_required)
		{
			updateFieldRequired('field_type',0);
			jQuery('#jform_field_type').prop('required','required');
			jQuery('#jform_field_type').attr('aria-required',true);
			jQuery('#jform_field_type').addClass('required');
			jform_vvvvvvyvvz_required = false;
		}
		jQuery('#jform_edit_relationship').closest('.control-group').show();
		// add required attribute to edit_relationship field
		if (jform_vvvvvvyvwa_required)
		{
			updateFieldRequired('edit_relationship',0);
			jQuery('#jform_edit_relationship').prop('required','required');
			jQuery('#jform_edit_relationship').attr('aria-required',true);
			jQuery('#jform_edit_relationship').addClass('required');
			jform_vvvvvvyvwa_required = false;
		}
		jQuery('#jform_type').closest('.control-group').show();
		// add required attribute to type field
		if (jform_vvvvvvyvwb_required)
		{
			updateFieldRequired('type',0);
			jQuery('#jform_type').prop('required','required');
			jQuery('#jform_type').attr('aria-required',true);
			jQuery('#jform_type').addClass('required');
			jform_vvvvvvyvwb_required = false;
		}
		jQuery('#jform_view_relationship').closest('.control-group').show();
		// add required attribute to view_relationship field
		if (jform_vvvvvvyvwc_required)
		{
			updateFieldRequired('view_relationship',0);
			jQuery('#jform_view_relationship').prop('required','required');
			jQuery('#jform_view_relationship').attr('aria-required',true);
			jQuery('#jform_view_relationship').addClass('required');
			jform_vvvvvvyvwc_required = false;
		}
	}
	else
	{
		jQuery('#jform_communicate').closest('.control-group').hide();
		jQuery('#jform_field_type').closest('.control-group').hide();
		// remove required attribute from field_type field
		if (!jform_vvvvvvyvvz_required)
		{
			updateFieldRequired('field_type',1);
			jQuery('#jform_field_type').removeAttr('required');
			jQuery('#jform_field_type').removeAttr('aria-required');
			jQuery('#jform_field_type').removeClass('required');
			jform_vvvvvvyvvz_required = true;
		}
		jQuery('#jform_edit_relationship').closest('.control-group').hide();
		// remove required attribute from edit_relationship field
		if (!jform_vvvvvvyvwa_required)
		{
			updateFieldRequired('edit_relationship',1);
			jQuery('#jform_edit_relationship').removeAttr('required');
			jQuery('#jform_edit_relationship').removeAttr('aria-required');
			jQuery('#jform_edit_relationship').removeClass('required');
			jform_vvvvvvyvwa_required = true;
		}
		jQuery('#jform_type').closest('.control-group').hide();
		// remove required attribute from type field
		if (!jform_vvvvvvyvwb_required)
		{
			updateFieldRequired('type',1);
			jQuery('#jform_type').removeAttr('required');
			jQuery('#jform_type').removeAttr('aria-required');
			jQuery('#jform_type').removeClass('required');
			jform_vvvvvvyvwb_required = true;
		}
		jQuery('#jform_view_relationship').closest('.control-group').hide();
		// remove required attribute from view_relationship field
		if (!jform_vvvvvvyvwc_required)
		{
			updateFieldRequired('view_relationship',1);
			jQuery('#jform_view_relationship').removeAttr('required');
			jQuery('#jform_view_relationship').removeAttr('aria-required');
			jQuery('#jform_view_relationship').removeClass('required');
			jform_vvvvvvyvwc_required = true;
		}
	}
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
