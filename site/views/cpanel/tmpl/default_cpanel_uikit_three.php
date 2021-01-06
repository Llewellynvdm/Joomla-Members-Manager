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
defined('_JEXEC') or die('Restricted access');

?>
<div uk-grid>
	<?php if ($this->searchForm && $this->user->authorise('member.create', 'com_membersmanager')): ?>
		<div class="uk-width-1-2@m">
			<?php if (MembersmanagerHelper::checkArray($this->cpanelAboveSearchModules)): ?>
				<?php foreach($this->cpanelAboveSearchModules as $module): ?>
					<?php echo JLayoutHelper::render('panelbox', $module); ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php echo JLayoutHelper::render('cpanel_search_form', $this->searchForm); ?>
			<?php if (MembersmanagerHelper::checkArray($this->cpanelBelowSearchModules)): ?>
				<?php foreach($this->cpanelBelowSearchModules as $module): ?>
					<?php echo JLayoutHelper::render('panelbox', $module); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="uk-width-1-2@m">
			<?php if (MembersmanagerHelper::checkArray($this->cpanelAboveCreateButtonModules)): ?>
				<?php foreach($this->cpanelAboveCreateButtonModules as $module): ?>
					<?php echo JLayoutHelper::render('panelbox', $module); ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php echo JLayoutHelper::render('cpanel_uikit_three_buttons', true) ?>
			<?php if (MembersmanagerHelper::checkArray($this->cpanelBelowCreateButtonModules)): ?>
				<?php foreach($this->cpanelBelowCreateButtonModules as $module): ?>
					<?php echo JLayoutHelper::render('panelbox', $module); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	<?php elseif ($this->searchForm): ?>
		<?php if (MembersmanagerHelper::checkArray($this->cpanelAboveSearchModules)): ?>
			<?php foreach($this->cpanelAboveSearchModules as $module): ?>
				<?php echo JLayoutHelper::render('panelbox', $module); ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php echo JLayoutHelper::render('cpanel_search_form', $this->searchForm); ?>
		<?php if (MembersmanagerHelper::checkArray($this->cpanelBelowSearchModules)): ?>
			<?php foreach($this->cpanelBelowSearchModules as $module): ?>
				<?php echo JLayoutHelper::render('panelbox', $module); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php elseif ($this->user->authorise('member.create', 'com_membersmanager')): ?>
		<?php if (MembersmanagerHelper::checkArray($this->cpanelAboveCreateButtonModules)): ?>
			<?php foreach($this->cpanelAboveCreateButtonModules as $module): ?>
				<?php echo JLayoutHelper::render('panelbox', $module); ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php echo JLayoutHelper::render('cpanel_uikit_three_buttons', true) ?>
		<?php if (MembersmanagerHelper::checkArray($this->cpanelBelowCreateButtonModules)): ?>
			<?php foreach($this->cpanelBelowCreateButtonModules as $module): ?>
				<?php echo JLayoutHelper::render('panelbox', $module); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
