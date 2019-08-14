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

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Memberuser Form Field class for the Membersmanager component
 */
class JFormFieldMemberuser extends JFormFieldList
{
	/**
	 * The memberuser field type.
	 *
	 * @var		string
	 */
	public $type = 'memberuser';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		// load the db object
		$db = JFactory::getDBO();
		// get the input from url
		$jinput = JFactory::getApplication()->input;
		// get the id
		$id = $jinput->getInt('id', 0);
		if ($id > 0)
		{
			$user = MembersmanagerHelper::getVar('member', $id, 'id', 'user');
		}
		// get all ready used users IDs
		$users = MembersmanagerHelper::getVars('member', array('1','4'), 'account', 'user');
		if (isset($user) && $user > 0 && MembersmanagerHelper::checkArray($users))
		{
			// remove from users array
			if (($key = array_search($user, $users)) !== false)
			{
				unset($users[$key]);
			}
		}
		// function to setup the group array
		$getGroups = function ($groups) {
			// convert to array
			if (MembersmanagerHelper::checkJson($groups))
			{
				return (array) json_decode($groups, true);
			}
			elseif (is_numeric($groups))
			{
				return array($groups);
			}
			return false;
		};
		// get the user
		$my = JFactory::getUser();
		// start query
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.name'),array('id','user_name')));
		$query->from($db->quoteName('#__users', 'a'));
		$query->where($db->quoteName('a.block') . ' = 0');
		// only load user not already used
		if (isset($users) && MembersmanagerHelper::checkArray($users))
		{
			$query->where($db->quoteName('a.id') . ' NOT IN (' . implode(', ', $users) . ')');
		}
		// check if current user is a supper admin
		if (!$my->authorise('core.admin'))
		{
			// get user access groups
			$user_access_groups =  MembersmanagerHelper::getAccess($my, 2);
			// user must have access
			if (isset($user_access_groups) && MembersmanagerHelper::checkArray($user_access_groups))
			{
				// filter my user access groups
				$query->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = a.id');
				$query->where('map.group_id IN (' . implode(',', $user_access_groups) . ')');
			}
			elseif ($id > 0)
			{
				// load this member only
				$query->where($db->quoteName('a.id') . ' = ' . (int) $id);
			}
			else
			{
				return false;
			}
		}
		$query->order('a.name ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			// only add if more then one value found
			if (count( (array) $items) > 1)
			{
				$options[] = JHtml::_('select.option', '', 'Select a user');
			}
			// build the options
			foreach($items as $item)
			{
				// check if we current member
				if (isset($user) && $user == $item->id)
				{
					// remove ID
					$user = 0;
				}
				$options[] = JHtml::_('select.option', $item->id, $item->user_name);
			}
		}
		// add the current user (TODO this is not suppose to happen)
		if (isset($user) && $user > 0)
		{
			// load the current member manual
			$options[] = JHtml::_('select.option', (int) $user, JFactory::getUser($user)->name);
		}
		return $options;
	}
}
