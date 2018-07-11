<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * Types Controller
 */
class MembersmanagerControllerTypes extends JControllerAdmin
{
	protected $text_prefix = 'COM_MEMBERSMANAGER_TYPES';
	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Type', $prefix = 'MembersmanagerModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		
		return $model;
	}

	public function exportData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('type.export', 'com_membersmanager') && $user->authorise('core.export', 'com_membersmanager'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			JArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Types');
			// get the data to export
			$data = $model->getExportData($pks);
			if (MembersmanagerHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				MembersmanagerHelper::xls($data,'Types_'.$date->format('jS_F_Y'),'Types exported ('.$date->format('jS F, Y').')','types');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_MEMBERSMANAGER_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=types', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('type.import', 'com_membersmanager') && $user->authorise('core.import', 'com_membersmanager'))
		{
			// Get the import model
			$model = $this->getModel('Types');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (MembersmanagerHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('type_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'types');
				$session->set('dataType_VDM_IMPORTINTO', 'type');
				// Redirect to import view.
				$message = JText::_('COM_MEMBERSMANAGER_IMPORT_SELECT_FILE_FOR_TYPES');
				$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_MEMBERSMANAGER_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=types', false), $message, 'error');
		return;
	}  
}
