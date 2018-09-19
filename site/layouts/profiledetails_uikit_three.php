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

?>
<?php if ($setInfo) : ?>
<div>
<?php if (count($infoNames) > 3) : ?>
	<ul class="uk-child-width-expand" uk-tab>
<?php else: ?>
	<ul uk-tab>
<?php endif; ?>
		<li class="uk-active"><a href="#"><?php echo implode('</a></li><li><a href="#">', $infoNames); ?></a></li>
	</ul>
	<ul class="uk-switcher uk-margin">
		<?php foreach($infoDetails as $_nr => $infoDetail): ?>
			<?php if (isset($infoAvailable[$_nr]->params->membersmanager_relation_type) && 2 == $infoAvailable[$_nr]->params->membersmanager_relation_type): ?>
				<li><?php echo JLayoutHelper::render('many_list_name_value', array('data' => $infoDetail, 'com' => $infoAvailable[$_nr]->element, 'user' => $displayData->_USER)); ?></li>
			<?php else: ?>
				<li><?php echo JLayoutHelper::render('list_name_value', array('data' => $infoDetail, 'com' => $infoAvailable[$_nr]->element, 'user' => $displayData->_USER)); ?></li>			
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
