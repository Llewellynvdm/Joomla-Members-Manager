<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file

defined('JPATH_BASE') or die('Restricted access');

// set the name and email based on account type
if (1 == $displayData->account || 4 == $displayData->account)
{
	$displayData->name = $displayData->user_name;
	$displayData->email = $displayData->user_email;
}
// set the image path
if (MembersmanagerHelper::checkString($displayData->profile_image) && $displayData->_USER->authorise('member.view.profile_image', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$displayData->profile_image_link = MembersmanagerHelper::getImageLink($displayData, 'profile_image', 'name', $displayData->_IMAGELINK, false);
}
else
{
	$displayData->profile_image_link = false;
}
// build Meta
$meta = array();
// check if the type is to be set
if (MembersmanagerHelper::checkString($displayData->type_name) && $displayData->_USER->authorise('member.view.type', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$meta[] = $displayData->type_name;
}
// set add profile link switch
$addProfileLink = false;
// if this is a sub account load main account details
if ((3 == $displayData->account || 4 == $displayData->account) && $displayData->main_member > 0)
{
	// get main member account type
	$displayData->main_account = MembersmanagerHelper::getVar('member', $displayData->main_member, 'id', 'account');
	if (1 == $displayData->main_account && isset($displayData->main_user_name))
	{
		$displayData->main_member_name = $displayData->main_user_name;
		$displayData->main_member_email = $displayData->main_user_email;
	}
	// now make sure we have these set
	if (isset($displayData->main_member_name) && MembersmanagerHelper::checkString($displayData->main_member_name))
	{
		$meta[] = JText::_('COM_MEMBERSMANAGER_MAIN_MEMBER') . ': <a href="index.php?option=com_membersmanager&view=profile&id=' . $displayData->main_member . '" alt="' . $displayData->main_member_name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_MAIN_MEMBER_PROFILE') . '">' . $displayData->main_member_name . '</a>';
	}
	// get the view Link of sub members
	$displayData->profile_link = 'index.php?option=com_membersmanager&view=profile&id=' . $displayData->id;
	// set add profile link switch
	$addProfileLink = true;
}
// check if the edit button is to be added
$editButton = MembersmanagerHelper::getEditButton($displayData, 'member', 'members', '&ref=profile&refid=' . $displayData->_REFID, 'COM_MEMBERSMANAGER_YOU_WILL_BE_REDIRECTED_TO_AN_EDIT_VIEW_YOU_SURE_YOU_WANT_TO_CONTINUE');
// set the profile
$profile = array('<article class="uk-comment"><div class="uk-panel uk-panel-box">');
// set the uikit version 2 profile
if (2 == $displayData->uikitVersion)
{
	$profile[] = '<header class="uk-comment-header">';
	if ($displayData->profile_image_link)
	{
		// add link to profile image if loaded
		if ($addProfileLink)
		{
			$profile[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
		}
		// add image
		$profile[] = '<img class="uk-comment-avatar" src="' . $displayData->profile_image_link . '" alt="' . $displayData->name . '">';
		// close link if added
		if ($addProfileLink)
		{
			$profile[] = '</a>';
		}
	}
	$profile[] = '<h4 class="uk-comment-title">';
	// add link to member name if set
	if ($addProfileLink)
	{
		$profile[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
	}
	// add member name
	$profile[] = $displayData->name;
	// close link if added
	if ($addProfileLink)
	{
		$profile[] = '</a>&nbsp;&nbsp;';
	}
	$profile[] = $editButton . '</h4>';
	$profile[] = '<div class="uk-comment-meta">' . implode(' | ', $meta) . '</div>';
	$profile[] = '</header>';
}
else
{
	$profile[] = '<header class="uk-comment-header">';
	if ($displayData->profile_image_link)
	{
		// add link to profile image if loaded
		if ($addProfileLink)
		{
			$profile[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
		}
		$profile[] = '<div class="uk-width-auto"><img class="uk-comment-avatar" src="' . $displayData->profile_image_link . '" alt="' . $displayData->name . '"></div>';
		// close link if added
		if ($addProfileLink)
		{
			$profile[] = '</a>';
		}
	}
	$profile[] = '<div class="uk-width-expand">';
	$profile[] = '<h4 class="uk-comment-title uk-margin-remove">';
	// add link to member name if set
	if ($addProfileLink)
	{
		$profile[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
	}
	// add member name
	$profile[] = $displayData->name;
	// close link if added
	if ($addProfileLink)
	{
		$profile[] = '</a>&nbsp;&nbsp;';
	}
	$profile[] = $editButton . '</h4>';
	$profile[] = '<ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">';
	$profile[] = '<li>' . implode('</li><li>', $meta) . '</li>';
	$profile[] = '</ul></div>';
	$profile[] = '</header>';
}
// load the profile body
$profile[] = '<div class="uk-comment-body">';
$profile[] = '<ul class="uk-list uk-list-striped">';
// check if the email is to be set
if (MembersmanagerHelper::checkString($displayData->email) && $displayData->_USER->authorise('member.view.email', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_EMAIL') . ': <a href="mailto:' . $displayData->email . '" title="' . $displayData->name . ' email address">' . $displayData->email . '</a></li>';
}
// add only if mobile_phone is set
if (MembersmanagerHelper::checkString($displayData->mobile_phone) && $displayData->_USER->authorise('member.view.mobile_phone', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_MOBILE') . ': ' . $displayData->mobile_phone . '</li>';
}
// add only if landline_phone is set
if (MembersmanagerHelper::checkString($displayData->landline_phone) && $displayData->_USER->authorise('member.view.landline_phone', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_LANDLINE') . ': ' . $displayData->landline_phone . '</li>';
}
// add only if postal is set
if (MembersmanagerHelper::checkString($displayData->postal) && $displayData->_USER->authorise('member.view.postal', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_POSTAL') . ': ' . $displayData->postal . '</li>';
}
// add only if street is set
if (MembersmanagerHelper::checkString($displayData->street) && $displayData->_USER->authorise('member.view.street', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_STREET') . ': ' . $displayData->street . '</li>';
}
// add only if city is set
if (MembersmanagerHelper::checkString($displayData->city) && $displayData->_USER->authorise('member.view.city', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_CITY') . ': ' . $displayData->city . '</li>';
}
// add only if region_name is set
if (MembersmanagerHelper::checkString($displayData->region_name) && $displayData->_USER->authorise('member.view.region', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_REGION') . ': ' . $displayData->region_name . '</li>';
}
// add only if country_name is set
if (MembersmanagerHelper::checkString($displayData->country_name) && $displayData->_USER->authorise('member.view.country', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li>' . JText::_('COM_MEMBERSMANAGER_COUNTRY') . ': ' . $displayData->country_name . '</li>';
}
// add only if website is set
if (MembersmanagerHelper::checkString($displayData->website) && $displayData->_USER->authorise('member.view.website', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$profile[] = '<li><a href="' . $displayData->website . '" target="_blank" title="' . $displayData->name . ' website">' . $displayData->website . '</a></li>';
}
$profile[] = '</ul>';
$profile[] = '</div>';
$profile[] = '</div></article>';

?>
<?php echo implode(PHP_EOL, $profile); ?>
