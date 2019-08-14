/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */


Joomla.submitbutton = function(task)
{
	if (task == ''){
		return false;
	} else { 
		var action = task.split('.');
		if (action[1] == 'cancel' || action[1] == 'close' || document.formvalidator.isValid(document.getElementById("adminForm"))){
			Joomla.submitform(task, document.getElementById("adminForm"));
			return true;
		} else {
			alert(Joomla.JText._('type, some values are not acceptable.','Some values are unacceptable'));
			return false;
		}
	}
}