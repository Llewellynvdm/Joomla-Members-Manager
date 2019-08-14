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

$ignore = array('id', 'created', 'modified');

?>
<?php if (($create_button = MembersmanagerHelper::getCreateButton('form', 'form', '&field=member&field_id=' . $displayData['id'] . '&return=' . $displayData['return_path'], $displayData['com'], null)) !== false && MembersmanagerHelper::checkString($create_button)): ?>
	<?php echo $create_button; ?>
<?php endif; ?>
<?php if (isset($displayData['data']) && MembersmanagerHelper::checkArray($displayData['data'])): ?>
	<ul class="uk-list uk-list-striped">
	<?php if (isset($displayData['data']['id'])): ?>
		<?php
			$stringArray = array_filter($displayData['data'], function($key) use($ignore) { if (in_array($key, $ignore)) { return false; } return true; }, ARRAY_FILTER_USE_KEY);
			$edit = MembersmanagerHelper::getEditButton($displayData['data']['id'], 'form', 'form', '&return=' . $displayData['return_path'], $displayData['com'], null);
		?>
		<?php if (MembersmanagerHelper::checkArray($stringArray)): ?>
			<li><?php echo implode(', ', $stringArray); echo $edit; ?></li>
		<?php else: ?>
			<li><?php echo MembersmanagerHelper::fancyDayTimeDate($displayData['data']['created']); echo $edit;?></li>				
		<?php endif; ?>
	<?php else: ?>
		<?php foreach ($displayData['data'] as $data): ?>
			<?php
				$stringArray = array_filter($data, function($key) use($ignore) { if (in_array($key, $ignore)) { return false; } return true; }, ARRAY_FILTER_USE_KEY);
				$edit = MembersmanagerHelper::getEditButton($data['id'], 'form', 'form', '&return=' . $displayData['return_path'], $displayData['com'], null);
			?>
			<?php if (MembersmanagerHelper::checkArray($stringArray)): ?>
				<li><?php echo implode(', ', $stringArray); echo $edit; ?></li>
			<?php else: ?>
				<li><?php echo MembersmanagerHelper::fancyDayTimeDate($data['created']); echo $edit;?></li>				
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	</ul>
<?php else: ?>
	<br /><small><?php echo JText::_('COM_MEMBERSMANAGER_NO_DETAILS_FOUND'); ?>...</small>
<?php endif; ?>
