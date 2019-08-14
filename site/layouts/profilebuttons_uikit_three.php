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



?>
<?php if ($displayData->_USER->id > 0): ?>
	<div class="uk-button-group uk-width-1-1">
			<?php if (($communicate_url = MembersmanagerHelper::communicate('create_url', false, '&field=member&field_id=' . $displayData->id)) !== false) : ?>
				<button class="uk-button uk-width-1-3 uk-button-primary uk-button-small" type="button" uk-toggle="target: #assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>">
					<span uk-icon="icon: pencil"></span> <?php echo MembersmanagerHelper::getButtonName('forms', JText::_('COM_MEMBERSMANAGER_FORMS')); ?>
				</button>
				<button class="uk-button uk-width-1-3 uk-button-primary uk-button-small" type="button" uk-toggle="target: .extra<?php echo $displayData->id; ?>">
					<span uk-icon="icon: heart"></span> <?php echo MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS')); ?>
				</button>
				<a class="uk-button uk-width-1-3 uk-button-primary uk-button-small" href="<?php echo $communicate_url; ?>">
					<span uk-icon="icon: mail"></span> <?php echo MembersmanagerHelper::communicate('form_name', JText::_('COM_MEMBERSMANAGER_SEND_REPORT')); ?>
				</a>
			<?php else: ?>
				<button class="uk-button uk-width-1-2 uk-button-primary uk-button-small" type="button" uk-toggle="target: #assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>">
					<span uk-icon="icon: pencil"></span> <?php echo MembersmanagerHelper::getButtonName('forms', JText::_('COM_MEMBERSMANAGER_FORMS')); ?>
				</button>
				<button class="uk-button uk-width-1-2 uk-button-primary uk-button-small" type="button" uk-toggle="target: .extra<?php echo $displayData->id; ?>">
					<span uk-icon="icon: heart"></span> <?php echo MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS')); ?>
				</button>
			<?php endif; ?>
	</div>
	<br /><br />
<?php endif; ?>
