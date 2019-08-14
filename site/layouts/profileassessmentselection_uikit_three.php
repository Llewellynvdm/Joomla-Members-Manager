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
<div id="assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>" uk-offcanvas="mode: reveal; overlay: true; flip: true">
	<div class="uk-offcanvas-bar uk-flex uk-flex-column">
		<ul class="uk-nav uk-nav-primary uk-nav-center uk-margin-auto-vertical">
			<?php foreach ($displayData->assessmentAvailable as $typeAssesmentName => $components) : ?>
				<?php if  (MembersmanagerHelper::checkArray($components) || MembersmanagerHelper::checkObject($components)): ?>
					<li class="uk-nav-header"><?php echo JText::sprintf('COM_MEMBERSMANAGER_S_OPTIONS', $typeAssesmentName); ?></li>
					<?php if  (MembersmanagerHelper::checkArray($components)): ?>
						<?php $has = 0; ?>
						<?php foreach ($components as $component) : ?>
							<?php if ($displayData->_USER->authorise('form.create', $component->element)): ?>
								<li><a href="index.php?option=<?php echo $component->element; ?>&view=form&field=member&field_id=<?php echo $displayData->id; ?>&layout=edit&return=<?php echo $displayData->return_path; ?>"><?php echo $component->name; ?></a></li>
								<?php $has++; ?>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ($has == 0): ?>
							<li><a href="#"><?php echo JText::_('COM_MEMBERSMANAGER_NO_FORMS_FOUND'); ?></a></li>
						<?php endif; ?>
					<?php elseif  (MembersmanagerHelper::checkObject($components)): ?>
						<?php $has = 0; ?>
						<?php if (($id = MembersmanagerHelper::getVar('form', $displayData->id, 'member', 'id', '=', str_replace("com_", "", $components->element))) !== false): ?>
							<?php if (($link = MembersmanagerHelper::getEditURL($id, 'form', 'form', '&return=' . $displayData->return_path, $components->element)) !== false): ?>
								<li><a href="<?php echo $link; ?>"><?php echo $components->name; ?></a></li>
								<?php $has++; ?>
							<?php endif; ?>
						<?php else: ?>
							<?php if ($displayData->_USER->authorise('form.create', $components->element)): ?>
								<li><a href="index.php?option=<?php echo $components->element; ?>&view=form&field=member&field_id=<?php echo $displayData->id; ?>&layout=edit&return=<?php echo $displayData->return_path; ?>"><?php echo $components->name; ?></a></li>
								<?php $has++; ?>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ($has == 0): ?>
							<li><a href="#"><?php echo JText::_('COM_MEMBERSMANAGER_NO_FORMS_FOUND'); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
