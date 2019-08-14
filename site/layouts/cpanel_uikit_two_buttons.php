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
<a class="uk-button uk-button-success uk-width-1-1" href="<?php echo JURI::root(); ?>index.php?option=com_membersmanager&view=members&task=member.edit&return=<?php echo urlencode(base64_encode((string) JUri::getInstance())); ?>">
	<i class="uk-icon-plus"></i> <?php echo JText::_('COM_MEMBERSMANAGER_CREATE'); ?>
</a>
