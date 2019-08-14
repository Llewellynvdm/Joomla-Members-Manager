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


// get all the info Components
$infoAvailable = MembersmanagerHelper::getInfoAvaillable($displayData->type, $displayData->account, false);
$setInfo = MembersmanagerHelper::checkArray($infoAvailable);
// get names and values if found
if ($setInfo)
{
	// get the info names
	$infoNames = MembersmanagerHelper::getTypeInfosNames($displayData->type, $displayData->account, 'array');
	// get info details
	$infoDetails = array();
	foreach ($infoAvailable as $_nr => $info)
	{
		$infoDetails[$_nr] = MembersmanagerHelper::getAnyFormDetails($displayData->id, 'member', $info->element, 'array', 'profile');
	}
}
// get DB
$db = JFactory::getDBO();
// set relationships to false
$relationships = false;
// get the types of relationships available to this member
if (($relationshipTypes = MembersmanagerHelper::getRelationshipsByTypes($displayData->type, $db, false, true, false)) !== false)
{
	// load relationships
	$relationships = MembersmanagerHelper::getRelationshipsByMember($displayData->id, $db, 'member');
}

?>
<?php if ($setInfo || MembersmanagerHelper::checkArray($relationships)) : ?>
<ul class="uk-tab" data-uk-tab="{connect:'#member-info-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>'}">
	<?php if ($setInfo): ?>
		<li><a href=""><?php echo implode('</a></li><li><a href="">', $infoNames); ?></a></li>
	<?php endif; ?>
	<?php if (MembersmanagerHelper::checkArray($relationships)): ?>
		<li><a href=""><?php echo JText::_('COM_MEMBERSMANAGER_RELATIONSHIPS'); ?></a></li>
	<?php endif; ?>
</ul>
<ul id="member-info-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>" class="uk-switcher uk-margin">
	<?php if ($setInfo): ?>
		<?php foreach($infoDetails as $_nr => $infoDetail): ?>
			<?php if (isset($infoAvailable[$_nr]->params->membersmanager_relation_type) && 2 == $infoAvailable[$_nr]->params->membersmanager_relation_type): ?>
				<li><?php echo JLayoutHelper::render('many_list_name_value', array('id' => $displayData->id, 'data' => $infoDetail, 'com' => $infoAvailable[$_nr]->element, 'return_path' => $displayData->return_path)); ?></li>
			<?php else: ?>
				<li><?php echo JLayoutHelper::render('list_name_value', array('data' => $infoDetail, 'com' => $infoAvailable[$_nr]->element, 'user' => $displayData->_USER)); ?></li>			
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php echo JLayoutHelper::render('relation_list_name_value', array('id' => $displayData->id, 'relationshipTypes' => $relationshipTypes, 'relationships' => $relationships, 'return_path' => $displayData->return_path)); ?>
</ul>
<?php endif; ?>
