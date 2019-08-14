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
 * Membersmanager Cpanel Model
 */
class MembersmanagerModelCpanel extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_membersmanager.cpanel';

	/**
	 * Model user data.
	 *
	 * @var        strings
	 */
	protected $user;
	protected $userId;
	protected $guest;
	protected $groups;
	protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		// Get the itme main id
		$id = $this->input->getInt('id', null);
		$this->setState('cpanel.id', $id);

		// Load the parameters.
		$params = $this->app->getParams();
		$this->setState('params', $params);
		parent::populateState();
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$this->user = JFactory::getUser();
		// check if this user has permission to access item
		if (!$this->user->authorise('site.cpanel.access', 'com_membersmanager'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NOT_AUTHORISED_TO_VIEW_CPANEL'), 'error');
			// redirect away to the home page if no access allowed.
			$app->redirect(JURI::root());
			return false;
		}
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->initSet = true;

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('cpanel.id');
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				// Get a db connection.
				$db = JFactory::getDbo();

				// Create a new query object.
				$query = $db->getQuery(true);

				// Get data
				$query->select('a.*');
				$query->from($db->quoteName('#__membersmanager_member', 'a'));
				// make sure we have a user
				if ($this->userId > 0)
				{
					$query->where('a.user = ' . (int) $this->userId);
				}
				else
				{
					return false;
				}

				// Reset the query using our newly populated query object.
				$db->setQuery($query);
				// Load the results as a stdClass object.
				$data = $db->loadObject();

				if (empty($data))
				{
					$app = JFactory::getApplication();
					// If no data is found redirect to default page and show warning.
					$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
					$app->redirect(JURI::root());
					return false;
				}

				// set data object to item.
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseWaring(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}


		if (empty((int) $this->userId))
		{
			$id = 0;
		}
		else
		{
			$id = (int) $this->userId;
		}
		// set the id and view name to session
		if ($vdm = MembersmanagerHelper::get('user__'.$id))
		{
			$this->vastDevMod = $vdm;
		}
		else
		{
			// set the vast development method key
			$this->vastDevMod = MembersmanagerHelper::randomkey(50);
			MembersmanagerHelper::set($this->vastDevMod, 'user__'.$id);
			MembersmanagerHelper::set('user__'.$id, $this->vastDevMod);
			// set a return value if found
			$jinput = JFactory::getApplication()->input;
			$return = $jinput->get('return', null, 'base64');
			MembersmanagerHelper::set($this->vastDevMod . '__return', $return);
		}

		return $this->_item[$pk];
	}

	/**
	 * Get the uikit needed components
	 *
	 * @return mixed  An array of objects on success.
	 *
	 */
	public function getUikitComp()
	{
		if (isset($this->uikitComp) && MembersmanagerHelper::checkArray($this->uikitComp))
		{
			return $this->uikitComp;
		}
		return false;
	}

	public function getVDM()
	{
		if (isset($this->vastDevMod))
		{
			return $this->vastDevMod;
		}
		return false;
	}
}
