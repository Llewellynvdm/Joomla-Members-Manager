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
 * Membersmanager Model for Members
 */
class MembersmanagerModelMembers extends JModelList
{
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
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user = JFactory::getUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->initSet = true; 
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__membersmanager_member as a
		$query->select($db->quoteName(
			array('a.id'),
			array('id')));
		$query->from($db->quoteName('#__membersmanager_member', 'a'));

		// Filtering.

		$params = $this->app->getParams();
		// get the targeted types
		if (($target_type = $params->get('target_type', false)) === false)
		{
			return false;
		}
		// Check if $params->get('target_account', false) is an array with values.
		$array = $params->get('target_account', false);
		if (isset($array) && MembersmanagerHelper::checkArray($array))
		{
			$query->where('a.account IN (' . implode(',', $array) . ')');
		}
		else
		{
			return false;
		}
		// Check if MembersmanagerHelper::getMembersByType($target_type, $db) is an array with values.
		$array = MembersmanagerHelper::getMembersByType($target_type, $db);
		if (isset($array) && MembersmanagerHelper::checkArray($array))
		{
			$query->where('a.id IN (' . implode(',', $array) . ')');
		}
		else
		{
			return false;
		}
		$query->where('a.access IN (' . implode(',', $this->levels) . ')');
		// Get where a.published is 1
		$query->where('a.published = 1');
		$query->order('a.ordering ASC');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$user = JFactory::getUser();
		// check if this user has permission to access item
		if (!$user->authorise('site.members.access', 'com_membersmanager'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NOT_AUTHORISED_TO_VIEW_MEMBERS'), 'error');
			// redirect away to the home page if no access allowed.
			$app->redirect(JURI::root());
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_membersmanager', true);

		// Insure all item fields are adapted where needed.
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
			}
		}


		if (MembersmanagerHelper::checkArray($items))
		{
			// get the params from menu settings
			$params = $this->app->getParams();
			// get the linked components
			$components = $params->get('components', false);
			$__components = $params->get('many_components', false);
			// loop over the members an load the linked data
			foreach ($items as $nr => &$item)
			{
				// get id
				$id = $item->id;

				// get member details
				$item = (object) MembersmanagerHelper::getAnyFormDetails($id, 'id', 'com_membersmanager', 'placeholder', 'report', 'id', 'member', 1);
				// add company details
				if (($tmp = MembersmanagerHelper::getAnyCompanyDetails('com_membersmanager', 'placeholder')) !== false && MembersmanagerHelper::checkArray($tmp))
				{
					foreach ($tmp as $placeholder_key => $value)
					{
						$item->{$placeholder_key} = $value;
					}
					// not needed
					unset($tmp);
				}
				// also as related to the current logged in member
				$current_member_id = MembersmanagerHelper::getVar('member', JFactory::getUser()->get('id'), 'user', 'id', '=', 'membersmanager');
				if ($current_member_id && ($tmp = MembersmanagerHelper::getAnyFormDetails($current_member_id, 'id', 'com_membersmanager', 'placeholder', 'report', 'id', 'member', 1)) !== false && MembersmanagerHelper::checkArray($tmp))
				{
					foreach ($tmp as $placeholder_key => $value)
					{
						if (strpos($placeholder_key, '[member_') !== false)
						{
							$placeholder_key = str_replace('[member_', '[staff_', $placeholder_key);
							$item->{$placeholder_key} = $value;
						}
					}
					// not needed
					unset($tmp);
				}
				// now load the one to one component data
				if (MembersmanagerHelper::checkArray($components))
				{
					foreach ($components as $component)
					{
						// get values 
						if (($tmp = MembersmanagerHelper::getAnyFormDetails($id, 'member', $component, 'placeholder', 'report', 'member', 'form', 1)) !== false && MembersmanagerHelper::checkArray($tmp))
						{
							// add to the item array
							foreach ($tmp as $placeholder_key => $value)
							{
								// keep first data set
								if (!isset($item->{$placeholder_key}))
								{
									$item->{$placeholder_key} = $value;
								}
							}
							// not needed
							unset($tmp);
						}
						// also as related to the current logged in member
						if ($current_member_id && ($tmp = MembersmanagerHelper::getAnyFormDetails($current_member_id, 'member', $component, 'placeholder', 'report', 'member', 'form', 1)) !== false && MembersmanagerHelper::checkArray($tmp))
						{
							// add to the item array
							foreach ($tmp as $placeholder_key => $value)
							{
								if (strpos($placeholder_key, '[member_') !== false)
								{
									$placeholder_key = str_replace('[member_', '[staff_', $placeholder_key);
									// keep first data set
									if (!isset($item->{$placeholder_key}))
									{
										$item->{$placeholder_key} = $value;
									}
								}
							}
							// not needed
							unset($tmp);
						}
					}
				}
				// now load the one to many component data
				if (MembersmanagerHelper::checkArray($__components))
				{
					// start many data array
					foreach ($__components as $_component)
					{
						// rest buckets
						$main_template_0 = '';
						$main_template_1 = '';
						// set component name
						$component = $_component['component'];
						$qty = (isset($_component['qty']) &&  is_numeric($_component['qty'])) ?  $_component['qty'] : 0;
						// check if we have a main template
						if (MembersmanagerHelper::checkString($_component['main_template']))
						{
							if (strpos($_component['main_template'], '[load_items]') !==false)
							{
								$_tmp = (array) explode('[load_items]', $_component['main_template']);
								$main_template_0 = $_tmp[0];
								if (count($_tmp) >= 2)
								{
									$main_template_1 = $_tmp[1];
								}
							}
							else
							{
								$main_template_0 = $_component['main_template'];
							}
						}
						// get values 
						if (($_data = MembersmanagerHelper::getAnyFormDetails($id, 'member', $component, 'placeholder', 'report', 'member', 'form', $qty)) !== false && MembersmanagerHelper::checkArray($_data))
						{
							// start building this linked component view
							$item->{'[' . $_component['placeholder'] . ']'} = $main_template_0;
							// check the qty
							if (!isset($_data[0]))
							{
								$item->{'[' . $_component['placeholder'] . ']'} .= MembersmanagerHelper::setDynamicData($_component['item_template'], $_data);
							}
							else
							{
								foreach ($_data as $data_placeholders)
								{
									$item->{'[' . $_component['placeholder'] . ']'} .= MembersmanagerHelper::setDynamicData($_component['item_template'], $data_placeholders);
								}
							}
							$item->{'[' . $_component['placeholder'] . ']'} .= $main_template_1;
						}
						// remove if not set
						if (!isset($item->{'[' . $_component['placeholder'] . ']'}))
						{
							$item->{'[' . $_component['placeholder'] . ']'} = '';
						}
					}
				}
			}
		}

		// return items
		return $items;
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
}
