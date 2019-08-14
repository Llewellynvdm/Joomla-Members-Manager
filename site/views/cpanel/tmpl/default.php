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

<?php if ($this->user->id > 0): ?>
	<?php if (MembersmanagerHelper::checkArray($this->access_types)) : ?>
		<?php echo $this->item->event->onContentBeforeDisplay; ?>
		<?php if (($staff_name = MembersmanagerHelper::getMemberName($this->item->id, $this->user->id, null, null, false)) !== false): ?>
			<?php $button_text = JText::sprintf('COM_MEMBERSMANAGER_WELCOME_S_UPDATE_YOUR_DETAILS', $staff_name); ?>
			<?php $staff_edit_button = MembersmanagerHelper::getEditTextButton($button_text, $this->item, 'member', 'members', '&return=' . urlencode(base64_encode((string) JUri::getInstance())), 'com_membersmanager', false, 'uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom', null); ?>
			<?php if (MembersmanagerHelper::checkString($staff_edit_button)): ?>
				<?php if (3 == $this->uikitVersion) : ?>
					<div uk-grid>
						<div class="uk-width-1-2@m">
							<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
						</div>
						<div class="uk-width-1-2@m">
							<?php echo $staff_edit_button; ?>
						</div>
					</div>
				<?php else: ?>
					<div class="uk-grid">
						<div class="uk-width-medium-1-2">
							<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
						</div>
						<div class="uk-width-medium-1-2">
							<?php echo $staff_edit_button; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
			<?php endif; ?>
		<?php else: ?>
			<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
		<?php endif; ?>
		<?php echo $this->item->event->onContentAfterTitle; ?>
		<?php if (3 == $this->uikitVersion) : ?>
			<?php echo $this->loadTemplate('cpanel_uikit_three'); ?>
		<?php else: ?>
			<?php echo $this->loadTemplate('cpanel_uikit_two'); ?>
		<?php endif; ?>
		<?php echo $this->item->event->onContentAfterDisplay; ?>
	<?php else: ?>
		<div class="uk-alert uk-alert-large uk-alert-danger" data-uk-alert="">
			<h2><?php echo JText::_('COM_MEMBERSMANAGER_NO_ACCESS'); ?></h2>
			<p><?php echo JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_ACCESS_TO_THIS_AREA'); ?></p>
		</div>
	<?php endif; ?>
<?php else: ?>
	<?php echo $this->loadTemplate('loginmodule'); ?>
<?php endif; ?>
