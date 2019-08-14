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

// get the login module
$this->modules = $this->getModules('membersmanager-login', 'array');

?>
<?php if (MembersmanagerHelper::checkArray($this->modules)): ?>
	<?php foreach($this->modules as $module): ?>
		<?php echo JLayoutHelper::render('panelbox', $module); ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="uk-alert uk-alert-large">
		<h2><?php echo JText::_('COM_MEMBERSMANAGER_LOGIN_MODULE_POSITION'); ?></h2>
		<p><?php echo JText::sprintf('COM_MEMBERSMANAGER_PLEASE_PUBLISH_A_LOGIN_MODULE_TO_THIS_CODESLOGINCODE_POSITION_AND_INSURE_THAT_YOU_TARGET_THESE_PAGES_THIS_IS_POSSIBLE_IF_YOU_ADD_THE_MODULE_TO_ALL_PAGES_SINCE_THIS_MODULE_POSITION_SHOULD_ONLY_BE_AVAILABLE_IN_THIS_COMPONENT', 'membersmanager'); ?></p>
	</div>
<?php endif; ?>
