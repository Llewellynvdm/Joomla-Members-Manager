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

/**
 * Membersmanager Import Controller
 */
class MembersmanagerControllerImport extends JControllerLegacy
{
	/**
	 * Import an spreadsheet.
	 *
	 * @return  void
	 */
	public function import()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('import');
		if ($model->import())
		{
			$cache = JFactory::getCache('mod_menu');
			$cache->clean();
			// TODO: Reset the users acl here as well to kill off any missing bits
		}

		$app = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_membersmanager.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_membersmanager&view=import', false);
		}
		else
		{
			// wipe out the user state when we're going to redirect
			$app->setUserState('com_membersmanager.redirect_url', '');
			$app->setUserState('com_membersmanager.message', '');
			$app->setUserState('com_membersmanager.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}
}
