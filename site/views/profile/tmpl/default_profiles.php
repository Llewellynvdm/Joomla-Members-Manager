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
<?php if (isset($this->item) && MembersmanagerHelper::checkObject($this->item)): ?>
<?php if ($this->uikitVersion == 3) : ?>
<div class="uk-offcanvas-content">
<?php endif; ?>
<div class="uk-block">
	<?php $this->item->_USER = &$this->user; ?>
	<?php $this->item->_REFID = $this->item->id; ?>
	<?php $this->item->_UIKIT = $this->uikitVersion; ?>
	<?php $this->item->_IMAGELINK = MembersmanagerHelper::getFolderPath('url'); ?>
	<ul class="uk-comment-list">
		<li>
			<?php if ($this->uikitVersion == 3) : ?>
				<?php echo JLayoutHelper::render('profile_uikit_three', $this->item); ?>
			<?php else: ?>
				<?php echo JLayoutHelper::render('profile_uikit_two', $this->item); ?>
			<?php endif; ?>
			<?php if ($this->user->id > 0 && isset($this->item->idMain_memberMemberB) && MembersmanagerHelper::checkArray($this->item->idMain_memberMemberB)): ?>
			<ul>
				<?php foreach ($this->item->idMain_memberMemberB as $item): ?>
					<?php if (2 == $this->params->get('login_required', 1) || MembersmanagerHelper::canAccessMember($item->id, $item->type, $this->user)) : ?>
						<?php $item->_USER = &$this->user; ?>
						<?php $item->_REFID = $this->item->id; ?>
						<?php $item->_UIKIT = $this->uikitVersion; ?>
						<?php $item->_IMAGELINK = $this->item->_IMAGELINK; ?>
						<li>
							<?php if ($this->uikitVersion == 3) : ?>
								<?php echo JLayoutHelper::render('profile_uikit_three', $item); ?>
							<?php else: ?>
								<?php echo JLayoutHelper::render('profile_uikit_two', $item); ?>
							<?php endif; ?>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</li>
	</ul>
</div>
<?php if ($this->uikitVersion == 3) : ?>
</div>
<div id="getreport" class="uk-flex-top" uk-modal>
	<div class="uk-modal-dialog uk-modal-body">
	<button class="uk-modal-close-default" type="button" uk-close></button>
		<div class="setreport" uk-overflow-auto></div>
		<div class="report-spinner"><?php echo JText::_('COM_MEMBERSMANAGER_LOADING'); ?><span class="loading-dots"></span>.</div>
	</div>
</div>
<div id="getlistmessages" uk-offcanvas="mode: reveal; overlay: true">
	<div class="uk-offcanvas-bar">
		<button class="uk-offcanvas-close" type="button" uk-close></button>
		<ul class="setlistmessages uk-nav uk-nav-primary uk-nav-center uk-margin-auto-vertical"></ul>
		<div class="listmessages-spinner uk-panel"><?php echo JText::_('COM_MEMBERSMANAGER_LOADING'); ?><span class="loading-dots"></span>.</div>
	</div>
</div>
<?php else: ?>
<div id="getreport" class="uk-modal">
	<div class="uk-modal-dialog">
	<a class="uk-modal-close uk-close"></a>
		<div class="setreport"></div>
		<div class="report-spinner"><?php echo JText::_('COM_MEMBERSMANAGER_LOADING'); ?><span class="loading-dots"></span>.</div>
	</div>
</div>
<div id="getlistmessages" class="uk-offcanvas">
	<div class="uk-offcanvas-bar">
		<ul class="setlistmessages uk-nav uk-nav-offcanvas" data-uk-nav></ul>
		<div class="listmessages-spinner uk-panel"><?php echo JText::_('COM_MEMBERSMANAGER_LOADING'); ?><span class="loading-dots"></span>.</div>
	</div>
</div>
<?php endif; ?>
<?php else: ?>
	<h3><?php echo JText::_('COM_MEMBERSMANAGER_NO_PROFILE_FOUND'); ?></h3>
<?php endif; ?>
