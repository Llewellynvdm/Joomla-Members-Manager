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
			<a class="uk-button uk-width-1-3 uk-button-primary uk-button-small" href="#assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>"  data-uk-offcanvas="{mode:'reveal'}">
				<i class="uk-icon-check-square-o"></i> <?php echo MembersmanagerHelper::getButtonName('forms', JText::_('COM_MEMBERSMANAGER_FORMS')); ?>
			</a>
			<button class="uk-button uk-width-1-3 uk-button-primary uk-button-small" data-uk-toggle="{target:'.extra<?php echo $displayData->id; ?>'}">
				<i class="uk-icon-bar-chart"></i> <?php echo MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS')); ?>
			</button>
			<a class="uk-button uk-width-1-3 uk-button-primary uk-button-small" href="<?php echo $communicate_url; ?>">
				<i class="uk-icon-paper-plane"></i> <?php echo MembersmanagerHelper::communicate('form_name', JText::_('COM_MEMBERSMANAGER_SEND_REPORT')); ?>
			</a>
		<?php else : ?>
			<a class="uk-button uk-width-1-2 uk-button-primary uk-button-small" href="#assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>"  data-uk-offcanvas="{mode:'slide'}">
				<i class="uk-icon-check-square-o"></i> <?php echo MembersmanagerHelper::getButtonName('forms', JText::_('COM_MEMBERSMANAGER_FORMS')); ?>
			</a>
			<button class="uk-button uk-width-1-2 uk-button-primary uk-button-small" data-uk-toggle="{target:'.extra<?php echo $displayData->id; ?>'}">
				<i class="uk-icon-bar-chart"></i> <?php echo MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS')); ?>
			</button>
		<?php endif; ?>
	</div>
	<br /><br />
<?php endif; ?>
