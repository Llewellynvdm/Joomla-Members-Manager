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
 * Types Model
 */
class MembersmanagerModelTypes extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
        {
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'a.name','name',
				'a.add_relationship','add_relationship'
			);
		}

		parent::__construct($config);
	}

	/**
	 * update/sync all the member types
	 *
	 * @return  bool true on success
	 */
	public function updateTypes()
	{
		if (($members = $this->getMembers()) !== false)
		{
			// set so defaults
			$bucket = array();
			$trigger = false;
			foreach ($members as $id => $types)
			{
				MembersmanagerHelper::updateTypes($id, $types);
			}
			return true;
		}
		JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NO_MEMBERS_ARE_SET_PLEASE_SET_SOME_AND_TRY_AGAIN'), 'warning');
		return false;
	}

	/**
	* Gets an array of members.
	 *
	 * @return  array  An array of members.
	 *
	 */
	protected function getMembers()
	{
		// get types that allow relationships
		$query = $this->_db->getQuery(true);
		$query->select(array('a.id', 'a.type'));
		$query->from('#__membersmanager_member AS a');
		$query->where($this->_db->quoteName('a.published') . ' >= 1');
		$this->_db->setQuery($query);
		$this->_db->execute();
		// only continue if we have member types and all relationship types
		if (($members = $this->_db->loadAssocList('id', 'type')) !== false && MembersmanagerHelper::checkArray($members))
		{
			return $members;
		}
		return false;
	}

	
	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}
		$name = $this->getUserStateFromRequest($this->context . '.filter.name', 'filter_name');
		$this->setState('filter.name', $name);

		$add_relationship = $this->getUserStateFromRequest($this->context . '.filter.add_relationship', 'filter_add_relationship');
		$this->setState('filter.add_relationship', $add_relationship);
        
		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);
        
		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);
        
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		// List state information.
		parent::populateState($ordering, $direction);
	}
	
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

		// set values to display correctly.
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				$access = (JFactory::getUser()->authorise('type.access', 'com_membersmanager.type.' . (int) $item->id) && JFactory::getUser()->authorise('type.access', 'com_membersmanager'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

				// decode groups_target
				$groups_targetArray = json_decode($item->groups_target, true);
				if (MembersmanagerHelper::checkArray($groups_targetArray))
				{
					$groups_targetNames = '';
					$counter = 0;
					foreach ($groups_targetArray as $groups_target)
					{
						if ($counter == 0)
						{
							$groups_targetNames .= MembersmanagerHelper::getGroupName($groups_target);
						}
						else
						{
							$groups_targetNames .= ', '.MembersmanagerHelper::getGroupName($groups_target);
						}
						$counter++;
					}
					$item->groups_target = $groups_targetNames;
				}
				// decode groups_access
				$groups_accessArray = json_decode($item->groups_access, true);
				if (MembersmanagerHelper::checkArray($groups_accessArray))
				{
					$groups_accessNames = '';
					$counter = 0;
					foreach ($groups_accessArray as $groups_access)
					{
						if ($counter == 0)
						{
							$groups_accessNames .= MembersmanagerHelper::getGroupName($groups_access);
						}
						else
						{
							$groups_accessNames .= ', '.MembersmanagerHelper::getGroupName($groups_access);
						}
						$counter++;
					}
					$item->groups_access = $groups_accessNames;
				}
			}
		}

		// set selection value to a translatable value
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert add_relationship
				$item->add_relationship = $this->selectionTranslation($item->add_relationship, 'add_relationship');
			}
		}

        
		// return items
		return $items;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return translatable string
	 */
	public function selectionTranslation($value,$name)
	{
		// Array of add_relationship language strings
		if ($name === 'add_relationship')
		{
			$add_relationshipArray = array(
				1 => 'COM_MEMBERSMANAGER_TYPE_YES',
				0 => 'COM_MEMBERSMANAGER_TYPE_NO'
			);
			// Now check if value is found in this array
			if (isset($add_relationshipArray[$value]) && MembersmanagerHelper::checkString($add_relationshipArray[$value]))
			{
				return $add_relationshipArray[$value];
			}
		}
		return $value;
	}
	
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = JFactory::getUser();
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the membersmanager_item table
		$query->from($db->quoteName('#__membersmanager_type', 'a'));

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_membersmanager'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.name LIKE '.$search.' OR a.groups_target LIKE '.$search.' OR a.groups_access LIKE '.$search.')');
			}
		}

		// Filter by Add_relationship.
		if ($add_relationship = $this->getState('filter.add_relationship'))
		{
			$query->where('a.add_relationship = ' . $db->quote($db->escape($add_relationship)));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'asc');	
		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get list export data.
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getExportData($pks)
	{
		// setup the query
		if (MembersmanagerHelper::checkArray($pks))
		{
			// Set a value to know this is exporting method.
			$_export = true;
			// Get the user object.
			$user = JFactory::getUser();
			// Create a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the membersmanager_type table
			$query->from($db->quoteName('#__membersmanager_type', 'a'));
			$query->where('a.id IN (' . implode(',',$pks) . ')');
			// Implement View Level Access
			if (!$user->authorise('core.options', 'com_membersmanager'))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}

			// Order the results by ordering
			$query->order('a.ordering  ASC');

			// Load the items
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$items = $db->loadObjectList();

				// set values to display correctly.
				if (MembersmanagerHelper::checkArray($items))
				{
					foreach ($items as $nr => &$item)
					{
						$access = (JFactory::getUser()->authorise('type.access', 'com_membersmanager.type.' . (int) $item->id) && JFactory::getUser()->authorise('type.access', 'com_membersmanager'));
						if (!$access)
						{
							unset($items[$nr]);
							continue;
						}

						// unset the values we don't want exported.
						unset($item->asset_id);
						unset($item->checked_out);
						unset($item->checked_out_time);
					}
				}
				// Add headers to items array.
				$headers = $this->getExImPortHeaders();
				if (MembersmanagerHelper::checkObject($headers))
				{
					array_unshift($items,$headers);
				}
				return $items;
			}
		}
		return false;
	}

	/**
	* Method to get header.
	*
	* @return mixed  An array of data items on success, false on failure.
	*/
	public function getExImPortHeaders()
	{
		// Get a db connection.
		$db = JFactory::getDbo();
		// get the columns
		$columns = $db->getTableColumns("#__membersmanager_type");
		if (MembersmanagerHelper::checkArray($columns))
		{
			// remove the headers you don't import/export.
			unset($columns['asset_id']);
			unset($columns['checked_out']);
			unset($columns['checked_out_time']);
			$headers = new stdClass();
			foreach ($columns as $column => $type)
			{
				$headers->{$column} = $column;
			}
			return $headers;
		}
		return false;
	}
	
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.name');
		$id .= ':' . $this->getState('filter.add_relationship');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to checkin all items left checked out longer then a set time.
	 *
	 * @return  a bool
	 *
	 */
	protected function checkInNow()
	{
		// Get set check in time
		$time = JComponentHelper::getParams('com_membersmanager')->get('check_in');

		if ($time)
		{

			// Get a db connection.
			$db = JFactory::getDbo();
			// reset query
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__membersmanager_type'));
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get Yesterdays date
				$date = JFactory::getDate()->modify($time)->toSql();
				// reset query
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = array(
					$db->quoteName('checked_out_time') . '=\'0000-00-00 00:00:00\'',
					$db->quoteName('checked_out') . '=0'
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('checked_out') . '!=0', 
					$db->quoteName('checked_out_time') . '<\''.$date.'\''
				);

				// Check table
				$query->update($db->quoteName('#__membersmanager_type'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}

		return false;
	}
}
