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
 * Members Controller
 */
class MembersmanagerControllerMembers extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MEMBERSMANAGER_MEMBERS';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Member', $prefix = 'MembersmanagerModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function exportData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('member.export', 'com_membersmanager') && $user->authorise('core.export', 'com_membersmanager'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			JArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Members');
			// get the data to export
			$data = $model->getExportData($pks);
			if (MembersmanagerHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				MembersmanagerHelper::xls($data,'Members_'.$date->format('jS_F_Y'),'Members exported ('.$date->format('jS F, Y').')','members');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_MEMBERSMANAGER_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=members', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('member.import', 'com_membersmanager') && $user->authorise('core.import', 'com_membersmanager'))
		{
			// Get the import model
			$model = $this->getModel('Members');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (MembersmanagerHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('member_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'members');
				$session->set('dataType_VDM_IMPORTINTO', 'member');
				// Redirect to import view.
				$message = JText::_('COM_MEMBERSMANAGER_IMPORT_SELECT_FILE_FOR_MEMBERS');
				$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_MEMBERSMANAGER_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_membersmanager&view=members', false), $message, 'error');
		return;
	}

	public function importJoomlaUsers()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if user has the right
		$user = JFactory::getUser();
		// set redirect
		$redirect_url = JRoute::_('index.php?option=com_membersmanager&view=members', false);
		if($user->authorise('core.create', 'com_membersmanager'))
		{
			// get the model
			$model = $this->getModel('members');
			if ($model->importJoomlaUsers())
			{
				// set success message
				$message = '<h1>'.JText::_('COM_MEMBERSMANAGER_IMPORT_SUCCESS').'</h1>';
				$message .= '<p>'.JText::_('COM_MEMBERSMANAGER_ALL_THE_USERS_FOUND_IN_JOOMLA_WERE_SUCCESSFULLY_IMPORTED_INTO_RELATED_MEMBER_TYPE_RELATIONSHIPS').'</p>';
				$this->setRedirect($redirect_url, $message);
				return true;
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_CREATE_MEMBERS'), 'error');
		}
		$this->setRedirect($redirect_url);
		return false;
	}
}
