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
<!-- clear the batch values if cancel -->
<button class="btn" type="button" onclick="" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</button>
<!-- post the batch values if process -->
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('type.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>