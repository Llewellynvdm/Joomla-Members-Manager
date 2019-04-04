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
	<?php echo $this->item->event->onContentBeforeDisplay; ?>
	<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
	<?php echo $this->item->event->onContentAfterTitle; ?>
	<?php if (3 == $this->uikitVersion) : ?>
		<?php echo $this->loadTemplate('cpanel_uikit_three'); ?>
	<?php else: ?>
		<?php echo $this->loadTemplate('cpanel_uikit_two'); ?>
	<?php endif; ?>
	<?php echo $this->item->event->onContentAfterDisplay; ?>
<?php else: ?>
	<?php echo $this->loadTemplate('loginmodule'); ?>
<?php endif; ?>
