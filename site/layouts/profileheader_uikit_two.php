<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */


// No direct access to this file
defined('JPATH_BASE') or die('Restricted access');


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
if (isset($displayData->type_name) && MembersmanagerHelper::checkString($displayData->type_name) && $displayData->_USER->authorise('member.view.type', 'com_membersmanager.member.' . (int) $displayData->id))
{
	$meta[] = $displayData->type_name;
}
// check if there is messages for this member
if (($number_messages = MembersmanagerHelper::communicate('message_number', false, $displayData->id)) !== false && $displayData->_USER->authorise('message.access', 'com_communicate'))
{
	// check if we have any mages for this member
	if ($number_messages > 0)
	{
		// set key
		$messages_key = MembersmanagerHelper::lock(array('id' => $displayData->id, 'return' => $displayData->return_path));
		// set the string
		if ($number_messages == 1)
		{
			$message_name = JText::_('COM_MEMBERSMANAGER_MESSAGE');
		}
		else
		{
			$message_name = JText::_('COM_MEMBERSMANAGER_MESSAGES');
		}
		// load the link
		if (2 == $displayData->_UIKIT)
		{
			$meta[] = '<a href="#getlistmessages" onclick="getListMessages(\'' . $messages_key . '\');" data-uk-offcanvas="{mode:\'reveal\'}">' . $message_name . '</a> (' . $number_messages . ')';
		}
		else
		{
			$meta[] = '<a href="#getlistmessages" onclick="getListMessages(\'' . $messages_key . '\');" uk-toggle>' . $message_name . '</a> (' . $number_messages . ')';
		}
	}
	else
	{
		$meta[] = JText::_('COM_MEMBERSMANAGER_NO_MESSAGES');
	}
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
		$displayData->main_member_email = $displayData->main_user_email;
	}
	// now make sure we have these set
	if (isset($displayData->main_member_name) && MembersmanagerHelper::checkString($displayData->main_member_name))
	{
		$meta[] = JText::_('COM_MEMBERSMANAGER_MAIN_MEMBER') . ': <a href="' . $displayData->main_member_profile_link . '" alt="' . $displayData->main_member_name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_MAIN_MEMBER_PROFILE') . '">' . $displayData->main_member_name . '</a>';
	}
	// set add profile link switch
	$addProfileLink = true;
}
// check if the edit button is to be added
$editButton = MembersmanagerHelper::getEditButton($displayData, 'member', 'members', '&ref=profile&refid=' . $displayData->_REFID . '&return=' . $displayData->return_path, 'com_membersmanager', null);
// set the header
$header = array();
$header[] = '<header class="uk-comment-header">';
if ($displayData->profile_image_link)
{
	// add link to profile image if loaded
	if ($addProfileLink)
	{
		$header[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
	}
	// add image
	$header[] = '<img class="uk-comment-avatar" src="' . $displayData->profile_image_link . '" alt="' . $displayData->name . '">';
	// close link if added
	if ($addProfileLink)
	{
		$header[] = '</a>';
	}
}
$header[] = '<h4 class="uk-comment-title">';
// add link to member name if set
if ($addProfileLink)
{
	$header[] = '<a href="' . $displayData->profile_link . '" alt="' . $displayData->name . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN_PROFILE') . '">';
}
// add member name
$header[] = $displayData->name;
// close link if added
if ($addProfileLink)
{
	$header[] = '</a>&nbsp;&nbsp;';
}
$header[] = $editButton . '</h4>';
$header[] = '<div class="uk-comment-meta">' . implode(' | ', $meta) . '</div>';
$header[] = '</header>';


?>
<?php echo implode(PHP_EOL, $header); ?>
