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

use Joomla\Utilities\ArrayHelper;

/**
 * Members Model
 */
class MembersmanagerModelMembers extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
        {
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.access','access',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'a.account','account'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Load all the users found in Joomla into membersmanager
	 *
	 * @since   2.7.5
	 *
	 * @return  bool true on success
	 */
	public function importJoomlaUsers()
	{
		if (($types = $this->getMemberTypes()) !== false)
		{
			// get all already set users
			$active_users = (($users = MembersmanagerHelper::getVars('member', 1, 'published', 'user')) !== false) ? $users : array();
			// set so defaults
			$userBucket = array();
			$trigger = false;
			foreach ($types as $type => $groups)
			{
				$this->loadMembers($userBucket, $type, $groups, $active_users, $trigger);
				// trigger message to run import again
				if ($trigger)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_ONLY_ONE_THOUSAND_MEMBERS_CAN_BE_IMPORTED_AT_A_TIME_SINCE_YOU_HAVE_MORE_THEN_ONE_THOUSAND_USERS_YOU_WILL_NEED_TO_RUN_THE_IMPORT_AGAIN_UNTIL_YOU_SEE_A_GREEN_SUCCESS_MESSAGE'), 'warning');
					return false;
				}
			}
			// now insert the members in table
			if ($this->insertMembers($userBucket, $active_users) || !$trigger)
			{
				return true;
			}
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NO_USERS_WERE_FOUND_THAT_MATCH_THE_TARGET_GROUPS_SET_IN_THE_MEMBER_TYPES'), 'warning');
			return false;
		}
		JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_NO_MEMBER_TYPES_ARE_SET_PLEASE_SET_SOME_AND_TRY_AGAIN'), 'warning');
		return false;
	}

	/**
	* Gets an array of objects of types of members.
	 *
	 * @return  object[]  An array of results.
	 *
	 */
	protected function getMemberTypes()
	{
		// get types that allow relationships
		$query = $this->_db->getQuery(true);
		$query->select(array('a.id', 'a.groups_target'));
		$query->from('#__membersmanager_type AS a');
		$query->where($this->_db->quoteName('a.published') . ' >= 1');
		$this->_db->setQuery($query);
		$this->_db->execute();
		// only continue if we have member types and all relationship types
		if (($types = $this->_db->loadAssocList('id', 'groups_target')) !== false && MembersmanagerHelper::checkArray($types))
		{
			return $types;
		}
		return false;
	}

	/**
	* Load members
	 *
	 * @return  void
	 *
	 */
	protected function loadMembers(&$userBucket, &$type, &$groups, &$active_users, &$trigger)
	{
		if (!$trigger && MembersmanagerHelper::checkJson($groups))
		{
			$groups = (array) json_decode($groups, true);
			if (MembersmanagerHelper::checkArray($groups))
			{
				foreach ($groups as $group_id)
				{
					if (($users = JAccess::getUsersByGroup($group_id)) !== false && MembersmanagerHelper::checkArray($users))
					{
						foreach ($users as $user_id)
						{
							// make sure this user is not already set
							if (!in_array($user_id, $active_users))
							{
								if (!isset($userBucket[$user_id]))
								{
									$userBucket[$user_id] = array($type);
								}
								else
								{
									$userBucket[$user_id][] = $type;
								}
							}
							else
							{
								// we need to do something here (TODO)
							}
							// if at any time we hit the 1000 mark we must reset
							if (count($userBucket) >= 1000)
							{
								$trigger = true;
								return $this->insertMembers($userBucket, $active_users);
							}
						}
					}
				}
			}
		}
	}

	/**
	* Insert the members into the members table
	 *
	 * @return  void
	 *
	 */
	protected function insertMembers(&$userBucket, $users)
	{
		// check if we found users
		if (MembersmanagerHelper::checkArray($userBucket))
		{
			// Get a db connection.
			$db = JFactory::getDbo();
			$todayDate = JFactory::getDate()->toSql();
			// Create a new query object.
			$query = $db->getQuery(true);
			// Insert columns.
			$columns = array('user', 'token', 'name', 'username', 'useremail', 'account', 'type', 'created_by', 'created', 'published', 'access', 'version');
			// Prepare the insert query.
			$query->insert($db->quoteName('#__membersmanager_member'))->columns($db->quoteName($columns));
			// limiting counter
			$limiter = 0;
			foreach ($userBucket as $user_id => $values)
			{
				// set the type
				$type = new JRegistry;
				$type->loadArray($values);
				// get user
				$member = JFactory::getUser($user_id);
				// build unique token
				$token = MembersmanagerHelper::safeString($member->name, 'L', '-', false, false);
				while (!MembersmanagerHelper::checkUnique(0, 'token', $token, 'member'))
				{
					$token = JString::increment($token, 'dash');
				}
				// build member
				$values = array();
				$values[] = (int) $user_id;
				$values[] = $db->quote($token);
				$values[] = $db->quote($member->name);
				$values[] = $db->quote($member->username);
				$values[] = $db->quote($member->email);
				$values[] = 1;
				$values[] = $db->quote((string) $type);
				$values[] = (int) $user_id;
				$values[] = $db->quote($todayDate);
				$values[] = 1;
				$values[] = 1;
				$values[] = 1;

				// load values
				$query->values(implode(',', $values));
				// clear memory
				unset($userBucket[$user_id]);
				$limiter++;
				// check if we have 100 rows, the insert and start new
				if ($limiter >= 100)
				{
					// reset counter
					$limiter = 0;
					// run query
					$db->setQuery($query);
					$db->execute();
					// reset query
					$query = $db->getQuery(true);
					// Prepare the new insert query.
					$query->insert($db->quoteName('#__membersmanager_member'))->columns($db->quoteName($columns));
				}
				// make sure to update the user array that are active
				$users[] = $user_id;
			}
			// reset the bucket
			$userBucket = array();
			// only run if queries remain
			if ($limiter > 0)
			{
				$db->setQuery($query);
				$db->execute();
			}
			return true;
		}
		return false;
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Check if the form was submitted
		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$account = $this->getUserStateFromRequest($this->context . '.filter.account', 'filter_account');
		if ($formSubmited)
		{
			$account = $app->input->post->get('account');
			$this->setState('filter.account', $account);
		}

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
		// Check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

		// Set values to display correctly.
		if (MembersmanagerHelper::checkArray($items))
		{
			// Get the user object if not set.
			if (!isset($user) || !MembersmanagerHelper::checkObject($user))
			{
				$user = JFactory::getUser();
			}
			foreach ($items as $nr => &$item)
			{
				// Remove items the user can't access.
				$access = ($user->authorise('member.access', 'com_membersmanager.member.' . (int) $item->id) && $user->authorise('member.access', 'com_membersmanager'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

				// if linked to user get active name
				if (isset($item->user) && is_numeric($item->user) && $item->user > 0 && isset($item->user_name))
				{
					$item->name = $item->user_name;
				}
				// always add surname
				$item->name = $item->name . ' ' . $item->surname;
				// if linked to user get active name
				if (isset($item->user) && is_numeric($item->user) && $item->user > 0)
				{
					$item->email = JFactory::getUser($item->user)->email;
				}
				// convert type
				$item->type = MembersmanagerHelper::jsonToString($item->type, ', ', 'type', 'id', 'name');
			}
		}

		// set account value for later
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// keep account type value
				$item->account_id = $item->account;
			}
		}

		// set selection value to a translatable value
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert account
				$item->account = $this->selectionTranslation($item->account, 'account');
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
		// Array of account language strings
		if ($name === 'account')
		{
			$accountArray = array(
				1 => 'COM_MEMBERSMANAGER_MEMBER_MAIN_LOGIN',
				2 => 'COM_MEMBERSMANAGER_MEMBER_MAIN',
				3 => 'COM_MEMBERSMANAGER_MEMBER_SUB',
				4 => 'COM_MEMBERSMANAGER_MEMBER_SUB_LOGIN'
			);
			// Now check if value is found in this array
			if (isset($accountArray[$value]) && MembersmanagerHelper::checkString($accountArray[$value]))
			{
				return $accountArray[$value];
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
		$query->from($db->quoteName('#__membersmanager_member', 'a'));

		// From the users table.
		$query->select($db->quoteName('g.name','user_name'));
		$query->join('LEFT', $db->quoteName('#__users', 'g') . ' ON (' . $db->quoteName('a.user') . ' = ' . $db->quoteName('g.id') . ')');

		// From the membersmanager_member table.
		$query->select($db->quoteName('h.user','main_member_user'));
		$query->join('LEFT', $db->quoteName('#__membersmanager_member', 'h') . ' ON (' . $db->quoteName('a.main_member') . ' = ' . $db->quoteName('h.id') . ')');

		// From the membersmanager_type table.
		$query->select($db->quoteName('i.name','type_name'));
		$query->join('LEFT', $db->quoteName('#__membersmanager_type', 'i') . ' ON (' . $db->quoteName('a.type') . ' = ' . $db->quoteName('i.id') . ')');

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
		$_access = $this->getState('filter.access');
		if ($_access && is_numeric($_access))
		{
			$query->where('a.access = ' . (int) $_access);
		}
		elseif (MembersmanagerHelper::checkArray($_access))
		{
			// Secure the array for the query
			$_access = ArrayHelper::toInteger($_access);
			// Filter by the Access Array.
			$query->where('a.access IN (' . implode(',', $_access) . ')');
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
				$query->where('(a.name LIKE '.$search.' OR a.email LIKE '.$search.' OR a.account LIKE '.$search.' OR a.user LIKE '.$search.' OR a.token LIKE '.$search.' OR a.main_member LIKE '.$search.' OR a.useremail LIKE '.$search.' OR a.username LIKE '.$search.' OR a.surname LIKE '.$search.')');
			}
		}

		// Filter by Account.
		$_account = $this->getState('filter.account');
		if (is_numeric($_account))
		{
			if (is_float($_account))
			{
				$query->where('a.account = ' . (float) $_account);
			}
			else
			{
				$query->where('a.account = ' . (int) $_account);
			}
		}
		elseif (MembersmanagerHelper::checkString($_account))
		{
			$query->where('a.account = ' . $db->quote($db->escape($_account)));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');
		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get list export data.
	 *
	 * @param   array  $pks  The ids of the items to get
	 * @param   JUser  $user  The user making the request
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getExportData($pks, $user = null)
	{
		// setup the query
		if (($pks_size = MembersmanagerHelper::checkArray($pks)) !== false || 'bulk' === $pks)
		{
			// Set a value to know this is export method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
			$_export = true;
			// Get the user object if not set.
			if (!isset($user) || !MembersmanagerHelper::checkObject($user))
			{
				$user = JFactory::getUser();
			}
			// Create a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the membersmanager_member table
			$query->from($db->quoteName('#__membersmanager_member', 'a'));
			// The bulk export path
			if ('bulk' === $pks)
			{
				$query->where('a.id > 0');
			}
			// A large array of ID's will not work out well
			elseif ($pks_size > 500)
			{
				// Use lowest ID
				$query->where('a.id >= ' . (int) min($pks));
				// Use highest ID
				$query->where('a.id <= ' . (int) max($pks));
			}
			// The normal default path
			else
			{
				$query->where('a.id IN (' . implode(',',$pks) . ')');
			}
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

				// Get the medium encryption key.
				$mediumkey = MembersmanagerHelper::getCryptKey('medium');
				// Get the encryption object.
				$medium = new FOFEncryptAes($mediumkey);

				// Set values to display correctly.
				if (MembersmanagerHelper::checkArray($items))
				{
					foreach ($items as $nr => &$item)
					{
						// Remove items the user can't access.
						$access = ($user->authorise('member.access', 'com_membersmanager.member.' . (int) $item->id) && $user->authorise('member.access', 'com_membersmanager'));
						if (!$access)
						{
							unset($items[$nr]);
							continue;
						}

						// if linked to user get active name
						if (isset($item->user) && is_numeric($item->user) && $item->user > 0 && isset($item->user_name))
						{
							$item->name = $item->user_name;
						}
						// always add surname
						$item->name = $item->name . ' ' . $item->surname;
						// if linked to user get active name
						if (isset($item->user) && is_numeric($item->user) && $item->user > 0)
						{
							$item->email = JFactory::getUser($item->user)->email;
						}
						if ($mediumkey && !is_numeric($item->profile_image) && $item->profile_image === base64_encode(base64_decode($item->profile_image, true)))
						{
							// decrypt profile_image
							$item->profile_image = $medium->decryptString($item->profile_image);
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

				// set account value for later
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// keep account type value
				$item->account_id = $item->account;
			}
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
		$columns = $db->getTableColumns("#__membersmanager_member");
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
	 * Method to get data during an export request.
	 *
	 * @param   array  $pks  The ids of the items to get
	 * @param   JUser  $user  The user making the request
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getPrivacyExport($pks, $user = null)
	{
		// setup the query
		if (($pks_size = MembersmanagerHelper::checkArray($pks)) !== false || 'bulk' === $pks)
		{
			// Set a value to know this is privacy method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
			$_privacy = true;
			// Get the user object if not set.
			if (!isset($user) || !MembersmanagerHelper::checkObject($user))
			{
				$user = JFactory::getUser();
			}
			// Create a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the membersmanager_member table
			$query->from($db->quoteName('#__membersmanager_member', 'a'));
			// The bulk export path
			if ('bulk' === $pks)
			{
				$query->where('a.id > 0');
			}
			// A large array of ID's will not work out well
			elseif ($pks_size > 500)
			{
				// Use lowest ID
				$query->where('a.id >= ' . (int) min($pks));
				// Use highest ID
				$query->where('a.id <= ' . (int) max($pks));
			}
			// The normal default path
			else
			{
				$query->where('a.id IN (' . implode(',',$pks) . ')');
			}
			// Get global switch to activate text only export
			$export_text_only = JComponentHelper::getParams('com_membersmanager')->get('export_text_only', 0);
			// Add these queries only if text only is required
			if ($export_text_only)
			{

				// From the users table.
				$query->select($db->quoteName('g.name','user'));
				$query->join('LEFT', $db->quoteName('#__users', 'g') . ' ON (' . $db->quoteName('a.user') . ' = ' . $db->quoteName('g.id') . ')');

				// From the membersmanager_member table.
				$query->select($db->quoteName('h.user','main_member'));
				$query->join('LEFT', $db->quoteName('#__membersmanager_member', 'h') . ' ON (' . $db->quoteName('a.main_member') . ' = ' . $db->quoteName('h.id') . ')');

				// From the membersmanager_type table.
				$query->select($db->quoteName('i.name','type'));
				$query->join('LEFT', $db->quoteName('#__membersmanager_type', 'i') . ' ON (' . $db->quoteName('a.type') . ' = ' . $db->quoteName('i.id') . ')');
			}
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

				// Get the medium encryption key.
				$mediumkey = MembersmanagerHelper::getCryptKey('medium');
				// Get the encryption object.
				$medium = new FOFEncryptAes($mediumkey);

				// Set values to display correctly.
				if (MembersmanagerHelper::checkArray($items))
				{
					// Get the user object if not set.
					if (!isset($user) || !MembersmanagerHelper::checkObject($user))
					{
						$user = JFactory::getUser();
					}
					// Get global permissional control activation. (default is inactive)
					$strict_permission_per_field = JComponentHelper::getParams('com_membersmanager')->get('strict_permission_per_field', 0);

					foreach ($items as $nr => &$item)
					{
						// Remove items the user can't access.
						$access = ($user->authorise('member.access', 'com_membersmanager.member.' . (int) $item->id) && $user->authorise('member.access', 'com_membersmanager'));
						if (!$access)
						{
							unset($items[$nr]);
							continue;
						}

						// use permissional control if globally set.
						if ($strict_permission_per_field)
						{
							// set view permissional control for name value.
							if (isset($item->name) && (!$user->authorise('member.view.name', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.name', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->name = '';
							}
							// set access permissional control for email value.
							if (isset($item->email) && (!$user->authorise('member.access.email', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.email', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->email = '';
							}
							// set view permissional control for email value.
							if (isset($item->email) && (!$user->authorise('member.view.email', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.email', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->email = '';
							}
							// set view permissional control for account value.
							if (isset($item->account) && (!$user->authorise('member.view.account', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.account', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->account = '';
							}
							// set view permissional control for user value.
							if (isset($item->user) && (!$user->authorise('member.view.user', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.user', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->user = '';
							}
							// set view permissional control for token value.
							if (isset($item->token) && (!$user->authorise('member.view.token', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.token', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->token = '';
							}
							// set access permissional control for profile_image value.
							if (isset($item->profile_image) && (!$user->authorise('member.access.profile_image', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.profile_image', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->profile_image = '';
							}
							// set view permissional control for profile_image value.
							if (isset($item->profile_image) && (!$user->authorise('member.view.profile_image', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.profile_image', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->profile_image = '';
							}
							// set view permissional control for main_member value.
							if (isset($item->main_member) && (!$user->authorise('member.view.main_member', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.main_member', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->main_member = '';
							}
							// set access permissional control for password_check value.
							if (isset($item->password_check) && (!$user->authorise('member.access.password_check', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.password_check', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->password_check = '';
							}
							// set view permissional control for password_check value.
							if (isset($item->password_check) && (!$user->authorise('member.view.password_check', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.password_check', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->password_check = '';
							}
							// set access permissional control for password value.
							if (isset($item->password) && (!$user->authorise('member.access.password', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.password', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->password = '';
							}
							// set view permissional control for password value.
							if (isset($item->password) && (!$user->authorise('member.view.password', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.password', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->password = '';
							}
							// set access permissional control for useremail value.
							if (isset($item->useremail) && (!$user->authorise('member.access.useremail', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.useremail', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->useremail = '';
							}
							// set view permissional control for useremail value.
							if (isset($item->useremail) && (!$user->authorise('member.view.useremail', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.useremail', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->useremail = '';
							}
							// set access permissional control for username value.
							if (isset($item->username) && (!$user->authorise('member.access.username', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.access.username', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->username = '';
							}
							// set view permissional control for username value.
							if (isset($item->username) && (!$user->authorise('member.view.username', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.username', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->username = '';
							}
							// set view permissional control for surname value.
							if (isset($item->surname) && (!$user->authorise('member.view.surname', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.surname', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->surname = '';
							}
							// set view permissional control for type value.
							if (isset($item->type) && (!$user->authorise('member.view.type', 'com_membersmanager.member.' . (int) $item->id)
								|| !$user->authorise('member.view.type', 'com_membersmanager')))
							{
								// We JUST empty the value (do you have a better idea)
								$item->type = '';
							}
						}
						// if linked to user get active name
						if (isset($item->user) && is_numeric($item->user) && $item->user > 0 && isset($item->user_name))
						{
							$item->name = $item->user_name;
						}
						// always add surname
						$item->name = $item->name . ' ' . $item->surname;
						// if linked to user get active name
						if (isset($item->user) && is_numeric($item->user) && $item->user > 0)
						{
							$item->email = JFactory::getUser($item->user)->email;
						}
						if ($mediumkey && !is_numeric($item->profile_image) && $item->profile_image === base64_encode(base64_decode($item->profile_image, true)))
						{
							// decrypt profile_image
							$item->profile_image = $medium->decryptString($item->profile_image);
						}
						// convert type
						$item->type = MembersmanagerHelper::jsonToString($item->type, ', ', 'type', 'id', 'name');
					}
				}

				// set account value for later
		if (MembersmanagerHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// keep account type value
				$item->account_id = $item->account;
			}
		}
			// Add these translation only if text only is required
			if ($export_text_only)
			{

					// set selection value to a translatable value
					if (MembersmanagerHelper::checkArray($items))
					{
						foreach ($items as $nr => &$item)
						{
							// convert account
							$item->account = $this->selectionTranslation($item->account, 'account');
						}
					}

			}
				return json_decode(json_encode($items), true);
			}
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
		// Check if the value is an array
		$_access = $this->getState('filter.access');
		if (MembersmanagerHelper::checkArray($_access))
		{
			$id .= ':' . implode(':', $_access);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_access)
		 || MembersmanagerHelper::checkString($_access))
		{
			$id .= ':' . $_access;
		}
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.account');

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
			// Reset query.
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__membersmanager_member'));
			// Only select items that are checked out.
			$query->where($db->quoteName('checked_out') . '!=0');
			$db->setQuery($query, 0, 1);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get Yesterdays date.
				$date = JFactory::getDate()->modify($time)->toSql();
				// Reset query.
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

				// Check table.
				$query->update($db->quoteName('#__membersmanager_member'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}

		return false;
	}
}
