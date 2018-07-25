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
defined('_JEXEC') or die('Restricted access'); 

?>
<?php if (isset($this->item) && MembersmanagerHelper::checkObject($this->item)): ?>
<div class="uk-block">
	<?php $this->item->uikitVersion = $this->uikitVersion; ?>
	<?php $this->item->_USER = &$this->user; ?>
	<?php $this->item->_REFID = $this->item->id; ?>
	<?php $this->item->_IMAGELINK = MembersmanagerHelper::getFolderPath('url'); ?>
	<ul class="uk-comment-list">
		<li>
			<?php echo JLayoutHelper::render('profile', $this->item); ?>
			<?php if (isset($this->item->idMain_memberMemberB) && MembersmanagerHelper::checkArray($this->item->idMain_memberMemberB)): ?>
			<ul>
				<?php foreach ($this->item->idMain_memberMemberB as $item): ?>
					<?php $item->uikitVersion = $this->uikitVersion; ?>
					<?php $item->_USER = &$this->user; ?>
					<?php $item->_REFID = $this->item->id; ?>
					<?php $item->_IMAGELINK = $this->item->_IMAGELINK; ?>
					<li><?php echo JLayoutHelper::render('profile', $item); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</li>
	</ul>
</div>
<?php else: ?>
	<h3><?php echo JText::_('COM_MEMBERSMANAGER_NO_PROFILE_FOUND'); ?></h3>
<?php endif; ?>
