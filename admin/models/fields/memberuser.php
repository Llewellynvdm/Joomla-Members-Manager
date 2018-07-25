<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('user');

/**
 * Memberuser Form Field class for the Membersmanager component
 */
class JFormFieldMemberuser extends JFormFieldUser
{
	/**
	 * The memberuser field type.
	 *
	 * @var		string
	 */
	public $type = 'memberuser';

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		// set the groups array
$groups = JComponentHelper::getParams('com_membersmanager')->get('memberuser');
return $groups;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 *
	 * @since   1.6
	 */
	protected function getExcluded()
	{
		// To ensure that there is only one record per user
// Get a db connection.
$db = JFactory::getDbo();
// Create a new query object.
$query = $db->getQuery(true);
// Select all records from the #__membersmanager_member table from user column'.
$query->select($db->quoteName('user'));
$query->from($db->quoteName('#__membersmanager_member'));
$db->setQuery($query);
$db->execute();
$found = $db->getNumRows();
if ($found)
{
	// return all users already used
	return array_unique($db->loadColumn());
}
return null;
	}
}
