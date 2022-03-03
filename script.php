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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
JHTML::_('bootstrap.renderModal');

/**
 * Script File of Membersmanager Component
 */
class com_membersmanagerInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 */
	public function __construct(ComponentAdapter $parent) {}

	/**
	 * Called on installation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(ComponentAdapter $parent) {}

	/**
	 * Called on uninstallation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 */
	public function uninstall(ComponentAdapter $parent)
	{
		// Get Application object
		$app = JFactory::getApplication();

		// Get The Database object
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select ids from fields
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__fields'));
		// Where member context is found
		$query->where( $db->quoteName('context') . ' = '. $db->quote('com_membersmanager.member') );
		$db->setQuery($query);
		// Execute query to see if context is found
		$db->execute();
		$member_found = $db->getNumRows();
		// Now check if there were any rows
		if ($member_found)
		{
			// Since there are load the needed  member field ids
			$member_field_ids = $db->loadColumn();
			// Remove member from the field table
			$member_condition = array( $db->quoteName('context') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__fields'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove member add queued success message.
				$app->enqueueMessage(JText::_('The fields with type (com_membersmanager.member) context was removed from the <b>#__fields</b> table'));
			}
			// Also Remove member field values
			$member_condition = array( $db->quoteName('field_id') . ' IN ('. implode(',', $member_field_ids) .')');
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__fields_values'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member field values
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove member add queued success message.
				$app->enqueueMessage(JText::_('The fields values for member was removed from the <b>#__fields_values</b> table'));
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select ids from field groups
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__fields_groups'));
		// Where member context is found
		$query->where( $db->quoteName('context') . ' = '. $db->quote('com_membersmanager.member') );
		$db->setQuery($query);
		// Execute query to see if context is found
		$db->execute();
		$member_found = $db->getNumRows();
		// Now check if there were any rows
		if ($member_found)
		{
			// Remove member from the field groups table
			$member_condition = array( $db->quoteName('context') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__fields_groups'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove member add queued success message.
				$app->enqueueMessage(JText::_('The field groups with type (com_membersmanager.member) context was removed from the <b>#__fields_groups</b> table'));
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where member alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$member_found = $db->getNumRows();
		// Now check if there were any rows
		if ($member_found)
		{
			// Since there are load the needed  member type ids
			$member_ids = $db->loadColumn();
			// Remove member from the content type table
			$member_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove member items from the contentitem tag map table
			$member_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove member items from the ucm content table
			$member_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully removed member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the member items are cleared from DB
			foreach ($member_ids as $member_id)
			{
				// Remove member items from the ucm base table
				$member_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $member_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($member_condition);
				$db->setQuery($query);
				// Execute the query to remove member items
				$db->execute();

				// Remove member items from the ucm history table
				$member_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $member_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($member_condition);
				$db->setQuery($query);
				// Execute the query to remove member items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Member alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$member_found = $db->getNumRows();
		// Now check if there were any rows
		if ($member_found)
		{
			// Since there are load the needed  member type ids
			$member_ids = $db->loadColumn();
			// Remove Member from the content type table
			$member_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove Member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove Member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Member items from the contentitem tag map table
			$member_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove Member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully remove Member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Member items from the ucm content table
			$member_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_membersmanager.member') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($member_condition);
			$db->setQuery($query);
			// Execute the query to remove Member items
			$member_done = $db->execute();
			if ($member_done)
			{
				// If successfully removed Member add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.member) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Member items are cleared from DB
			foreach ($member_ids as $member_id)
			{
				// Remove Member items from the ucm base table
				$member_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $member_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($member_condition);
				$db->setQuery($query);
				// Execute the query to remove Member items
				$db->execute();

				// Remove Member items from the ucm history table
				$member_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $member_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($member_condition);
				$db->setQuery($query);
				// Execute the query to remove Member items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Type alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.type') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$type_found = $db->getNumRows();
		// Now check if there were any rows
		if ($type_found)
		{
			// Since there are load the needed  type type ids
			$type_ids = $db->loadColumn();
			// Remove Type from the content type table
			$type_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.type') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($type_condition);
			$db->setQuery($query);
			// Execute the query to remove Type items
			$type_done = $db->execute();
			if ($type_done)
			{
				// If successfully remove Type add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.type) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Type items from the contentitem tag map table
			$type_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.type') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($type_condition);
			$db->setQuery($query);
			// Execute the query to remove Type items
			$type_done = $db->execute();
			if ($type_done)
			{
				// If successfully remove Type add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.type) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Type items from the ucm content table
			$type_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_membersmanager.type') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($type_condition);
			$db->setQuery($query);
			// Execute the query to remove Type items
			$type_done = $db->execute();
			if ($type_done)
			{
				// If successfully removed Type add queued success message.
				$app->enqueueMessage(JText::_('The (com_membersmanager.type) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Type items are cleared from DB
			foreach ($type_ids as $type_id)
			{
				// Remove Type items from the ucm base table
				$type_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $type_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($type_condition);
				$db->setQuery($query);
				// Execute the query to remove Type items
				$db->execute();

				// Remove Type items from the ucm history table
				$type_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $type_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($type_condition);
				$db->setQuery($query);
				// Execute the query to remove Type items
				$db->execute();
			}
		}

		// If All related items was removed queued success message.
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_base</b> table'));
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_history</b> table'));

		// Remove membersmanager assets from the assets table
		$membersmanager_condition = array( $db->quoteName('name') . ' LIKE ' . $db->quote('com_membersmanager%') );

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__assets'));
		$query->where($membersmanager_condition);
		$db->setQuery($query);
		$type_done = $db->execute();
		if ($type_done)
		{
			// If successfully removed membersmanager add queued success message.
			$app->enqueueMessage(JText::_('All related items was removed from the <b>#__assets</b> table'));
		}

		// Get the biggest rule column in the assets table at this point.
		$get_rule_length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
		$db->setQuery($get_rule_length);
		if ($db->execute())
		{
			$rule_length = $db->loadResult();
			// Check the size of the rules column
			if ($rule_length < 5120)
			{
				// Revert the assets table rules column back to the default
				$revert_rule = "ALTER TABLE `#__assets` CHANGE `rules` `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.';";
				$db->setQuery($revert_rule);
				$db->execute();
				$app->enqueueMessage(JText::_('Reverted the <b>#__assets</b> table rules column back to its default size of varchar(5120)'));
			}
			else
			{

				$app->enqueueMessage(JText::_('Could not revert the <b>#__assets</b> table rules column back to its default size of varchar(5120), since there is still one or more components that still requires the column to be larger.'));
			}
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Membersmanager from the action_logs_extensions table
		$membersmanager_action_logs_extensions = array( $db->quoteName('extension') . ' = ' . $db->quote('com_membersmanager') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_logs_extensions'));
		$query->where($membersmanager_action_logs_extensions);
		$db->setQuery($query);
		// Execute the query to remove Membersmanager
		$membersmanager_removed_done = $db->execute();
		if ($membersmanager_removed_done)
		{
			// If successfully remove Membersmanager add queued success message.
			$app->enqueueMessage(JText::_('The com_membersmanager extension was removed from the <b>#__action_logs_extensions</b> table'));
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Membersmanager Member from the action_log_config table
		$member_action_log_config = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.member') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_log_config'));
		$query->where($member_action_log_config);
		$db->setQuery($query);
		// Execute the query to remove com_membersmanager.member
		$member_action_log_config_done = $db->execute();
		if ($member_action_log_config_done)
		{
			// If successfully removed Membersmanager Member add queued success message.
			$app->enqueueMessage(JText::_('The com_membersmanager.member type alias was removed from the <b>#__action_log_config</b> table'));
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Membersmanager Type from the action_log_config table
		$type_action_log_config = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_membersmanager.type') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_log_config'));
		$query->where($type_action_log_config);
		$db->setQuery($query);
		// Execute the query to remove com_membersmanager.type
		$type_action_log_config_done = $db->execute();
		if ($type_action_log_config_done)
		{
			// If successfully removed Membersmanager Type add queued success message.
			$app->enqueueMessage(JText::_('The com_membersmanager.type type alias was removed from the <b>#__action_log_config</b> table'));
		}
		// little notice as after service, in case of bad experience with component.
		echo '<h2>Did something go wrong? Are you disappointed?</h2>
		<p>Please let me know at <a href="mailto:llewellyn@joomlacomponentbuilder.com">llewellyn@joomlacomponentbuilder.com</a>.
		<br />We at Joomla Component Builder are committed to building extensions that performs proficiently! You can help us, really!
		<br />Send me your thoughts on improvements that is needed, trust me, I will be very grateful!
		<br />Visit us at <a href="https://www.joomlacomponentbuilder.com/" target="_blank">https://www.joomlacomponentbuilder.com/</a> today!</p>';
	}

	/**
	 * Called on update
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(ComponentAdapter $parent){}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, ComponentAdapter $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// is redundant or so it seems ...hmmm let me know if it works again
		if ($type === 'uninstall')
		{
			return true;
		}
		// the default for both install and update
		$jversion = new JVersion();
		if (!$jversion->isCompatible('3.8.0'))
		{
			$app->enqueueMessage('Please upgrade to at least Joomla! 3.8.0 before continuing!', 'error');
			return false;
		}
		// do any updates needed
		if ($type === 'update')
		{
		}
		// do any install needed
		if ($type === 'install')
		{
		}
		// check if the PHPExcel stuff is still around
		if (File::exists(JPATH_ADMINISTRATOR . '/components/com_membersmanager/helpers/PHPExcel.php'))
		{
			// We need to remove this old PHPExcel folder
			$this->removeFolder(JPATH_ADMINISTRATOR . '/components/com_membersmanager/helpers/PHPExcel');
			// We need to remove this old PHPExcel file
			File::delete(JPATH_ADMINISTRATOR . '/components/com_membersmanager/helpers/PHPExcel.php');
		}
		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, ComponentAdapter $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// We check if we have dynamic folders to copy
		$this->setDynamicF0ld3rs($app, $parent);
		// set the default component settings
		if ($type === 'install')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the member content type object.
			$member = new stdClass();
			$member->type_title = 'Membersmanager Member';
			$member->type_alias = 'com_membersmanager.member';
			$member->table = '{"special": {"dbtable": "#__membersmanager_member","key": "id","type": "Member","prefix": "membersmanagerTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$member->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","email":"email","account":"account","user":"user","token":"token","profile_image":"profile_image","main_member":"main_member","password_check":"password_check","password":"password","useremail":"useremail","username":"username","surname":"surname","type":"type"}}';
			$member->router = 'MembersmanagerHelperRoute::getMemberRoute';
			$member->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/member.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","profile_image"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","account","user","main_member"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "user","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "main_member","targetTable": "#__membersmanager_member","targetColumn": "id","displayColumn": "user"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$member_Inserted = $db->insertObject('#__content_types', $member);

			// Create the type content type object.
			$type = new stdClass();
			$type->type_title = 'Membersmanager Type';
			$type->type_alias = 'com_membersmanager.type';
			$type->table = '{"special": {"dbtable": "#__membersmanager_type","key": "id","type": "Type","prefix": "membersmanagerTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$type->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","description":"description","groups_target":"groups_target","groups_access":"groups_access","add_relationship":"add_relationship","field_type":"field_type","communicate":"communicate","view_relationship":"view_relationship","edit_relationship":"edit_relationship","type":"type","alias":"alias"}}';
			$type->router = 'MembersmanagerHelperRoute::getTypeRoute';
			$type->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/type.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","add_relationship","field_type","communicate"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "view_relationship","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "edit_relationship","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$type_Inserted = $db->insertObject('#__content_types', $type);


			// Install the global extenstion assets permission.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = array(
				$db->quoteName('rules') . ' = ' . $db->quote('{"site.members.access":{"1":1}}'),
			);
			// Condition.
			$conditions = array(
				$db->quoteName('name') . ' = ' . $db->quote('com_membersmanager')
			);
			$query->update($db->quoteName('#__assets'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			// Install the global extension params.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = array(
				$db->quoteName('params') . ' = ' . $db->quote('{"autorName":"Llewellyn van der Merwe","autorEmail":"llewellyn@joomlacomponentbuilder.com","default_accesslevel":"1","placeholder_prefix":"member","members_display_type":"1","many_components":"0","login_required":"1","crop_profile":"1","profile_height":"300","profile_width":"200","dynamic_salt":"1->!,3->E,4->A,6->b,9->d","country":"Namibia","check_in":"-1 day","save_history":"1","history_limit":"10","uikit_version":"2","uikit_load":"1","uikit_min":"","uikit_style":""}'),
			);
			// Condition.
			$conditions = array(
				$db->quoteName('element') . ' = ' . $db->quote('com_membersmanager')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			// Get the biggest rule column in the assets table at this point.
			$get_rule_length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
			$db->setQuery($get_rule_length);
			if ($db->execute())
			{
				$rule_length = $db->loadResult();
				// Check the size of the rules column
				if ($rule_length <= 14720)
				{
					// Fix the assets table rules column size
					$fix_rules_size = "ALTER TABLE `#__assets` CHANGE `rules` `rules` TEXT NOT NULL COMMENT 'JSON encoded access control. Enlarged to TEXT by JCB';";
					$db->setQuery($fix_rules_size);
					$db->execute();
					$app->enqueueMessage(JText::_('The <b>#__assets</b> table rules column was resized to the TEXT datatype for the components possible large permission rules.'));
				}
			}
			echo '<a target="_blank" href="https://www.joomlacomponentbuilder.com/" title="Members Manager">
				<img src="components/com_membersmanager/assets/images/vdm-component.jpg"/>
				</a>';

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the membersmanager action logs extensions object.
			$membersmanager_action_logs_extensions = new stdClass();
			$membersmanager_action_logs_extensions->extension = 'com_membersmanager';

			// Set the object into the action logs extensions table.
			$membersmanager_action_logs_extensions_Inserted = $db->insertObject('#__action_logs_extensions', $membersmanager_action_logs_extensions);

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the member action log config object.
			$member_action_log_config = new stdClass();
			$member_action_log_config->type_title = 'MEMBER';
			$member_action_log_config->type_alias = 'com_membersmanager.member';
			$member_action_log_config->id_holder = 'id';
			$member_action_log_config->title_holder = 'name';
			$member_action_log_config->table_name = '#__membersmanager_member';
			$member_action_log_config->text_prefix = 'COM_MEMBERSMANAGER';

			// Set the object into the action log config table.
			$member_Inserted = $db->insertObject('#__action_log_config', $member_action_log_config);

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the type action log config object.
			$type_action_log_config = new stdClass();
			$type_action_log_config->type_title = 'TYPE';
			$type_action_log_config->type_alias = 'com_membersmanager.type';
			$type_action_log_config->id_holder = 'id';
			$type_action_log_config->title_holder = 'name';
			$type_action_log_config->table_name = '#__membersmanager_type';
			$type_action_log_config->text_prefix = 'COM_MEMBERSMANAGER';

			// Set the object into the action log config table.
			$type_Inserted = $db->insertObject('#__action_log_config', $type_action_log_config);
		}
		// do any updates needed
		if ($type === 'update')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the member content type object.
			$member = new stdClass();
			$member->type_title = 'Membersmanager Member';
			$member->type_alias = 'com_membersmanager.member';
			$member->table = '{"special": {"dbtable": "#__membersmanager_member","key": "id","type": "Member","prefix": "membersmanagerTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$member->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","email":"email","account":"account","user":"user","token":"token","profile_image":"profile_image","main_member":"main_member","password_check":"password_check","password":"password","useremail":"useremail","username":"username","surname":"surname","type":"type"}}';
			$member->router = 'MembersmanagerHelperRoute::getMemberRoute';
			$member->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/member.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","profile_image"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","account","user","main_member"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "user","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "main_member","targetTable": "#__membersmanager_member","targetColumn": "id","displayColumn": "user"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

			// Check if member type is already in content_type DB.
			$member_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($member->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$member->type_id = $db->loadResult();
				$member_Updated = $db->updateObject('#__content_types', $member, 'type_id');
			}
			else
			{
				$member_Inserted = $db->insertObject('#__content_types', $member);
			}

			// Create the type content type object.
			$type = new stdClass();
			$type->type_title = 'Membersmanager Type';
			$type->type_alias = 'com_membersmanager.type';
			$type->table = '{"special": {"dbtable": "#__membersmanager_type","key": "id","type": "Type","prefix": "membersmanagerTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$type->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","description":"description","groups_target":"groups_target","groups_access":"groups_access","add_relationship":"add_relationship","field_type":"field_type","communicate":"communicate","view_relationship":"view_relationship","edit_relationship":"edit_relationship","type":"type","alias":"alias"}}';
			$type->router = 'MembersmanagerHelperRoute::getTypeRoute';
			$type->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/type.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","add_relationship","field_type","communicate"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "view_relationship","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "edit_relationship","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

			// Check if type type is already in content_type DB.
			$type_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($type->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$type->type_id = $db->loadResult();
				$type_Updated = $db->updateObject('#__content_types', $type, 'type_id');
			}
			else
			{
				$type_Inserted = $db->insertObject('#__content_types', $type);
			}


			echo '<a target="_blank" href="https://www.joomlacomponentbuilder.com/" title="Members Manager">
				<img src="components/com_membersmanager/assets/images/vdm-component.jpg"/>
				</a>
				<h3>Upgrade to Version 2.0.6 Was Successful! Let us know if anything is not working as expected.</h3>';

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the membersmanager action logs extensions object.
			$membersmanager_action_logs_extensions = new stdClass();
			$membersmanager_action_logs_extensions->extension = 'com_membersmanager';

			// Check if membersmanager action log extension is already in action logs extensions DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_logs_extensions'));
			$query->where($db->quoteName('extension') . ' LIKE '. $db->quote($membersmanager_action_logs_extensions->extension));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the action logs extensions table if not found.
			if (!$db->getNumRows())
			{
				$membersmanager_action_logs_extensions_Inserted = $db->insertObject('#__action_logs_extensions', $membersmanager_action_logs_extensions);
			}

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the member action log config object.
			$member_action_log_config = new stdClass();
			$member_action_log_config->id = null;
			$member_action_log_config->type_title = 'MEMBER';
			$member_action_log_config->type_alias = 'com_membersmanager.member';
			$member_action_log_config->id_holder = 'id';
			$member_action_log_config->title_holder = 'name';
			$member_action_log_config->table_name = '#__membersmanager_member';
			$member_action_log_config->text_prefix = 'COM_MEMBERSMANAGER';

			// Check if member action log config is already in action_log_config DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_log_config'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($member_action_log_config->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$member_action_log_config->id = $db->loadResult();
				$member_action_log_config_Updated = $db->updateObject('#__action_log_config', $member_action_log_config, 'id');
			}
			else
			{
				$member_action_log_config_Inserted = $db->insertObject('#__action_log_config', $member_action_log_config);
			}

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the type action log config object.
			$type_action_log_config = new stdClass();
			$type_action_log_config->id = null;
			$type_action_log_config->type_title = 'TYPE';
			$type_action_log_config->type_alias = 'com_membersmanager.type';
			$type_action_log_config->id_holder = 'id';
			$type_action_log_config->title_holder = 'name';
			$type_action_log_config->table_name = '#__membersmanager_type';
			$type_action_log_config->text_prefix = 'COM_MEMBERSMANAGER';

			// Check if type action log config is already in action_log_config DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_log_config'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($type_action_log_config->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$type_action_log_config->id = $db->loadResult();
				$type_action_log_config_Updated = $db->updateObject('#__action_log_config', $type_action_log_config, 'id');
			}
			else
			{
				$type_action_log_config_Inserted = $db->insertObject('#__action_log_config', $type_action_log_config);
			}
		}
		return true;
	}

	/**
	 * Remove folders with files
	 * 
	 * @param   string   $dir     The path to folder to remove
	 * @param   boolean  $ignore  The folders and files to ignore and not remove
	 *
	 * @return  boolean   True in all is removed
	 * 
	 */
	protected function removeFolder($dir, $ignore = false)
	{
		if (Folder::exists($dir))
		{
			$it = new RecursiveDirectoryIterator($dir);
			$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			// remove ending /
			$dir = rtrim($dir, '/');
			// now loop the files & folders
			foreach ($it as $file)
			{
				if ('.' === $file->getBasename() || '..' ===  $file->getBasename()) continue;
				// set file dir
				$file_dir = $file->getPathname();
				// check if this is a dir or a file
				if ($file->isDir())
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					Folder::delete($file_dir);
				}
				else
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					File::delete($file_dir);
				}
			}
			// delete the root folder if not ignore found
			if (!$this->checkArray($ignore))
			{
				return Folder::delete($dir);
			}
			return true;
		}
		return false;
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input	array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 */
	protected function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && ($nr = count((array)$array)) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return $this->checkArray($array, false);
			}
			return $nr;
		}
		return false;
	}

	/**
	 * Method to set/copy dynamic folders into place (use with caution)
	 *
	 * @return void
	 */
	protected function setDynamicF0ld3rs($app, $parent)
	{
		// get the instalation path
		$installer = $parent->getParent();
		$installPath = $installer->getPath('source');
		// get all the folders
		$folders = Folder::folders($installPath);
		// check if we have folders we may want to copy
		$doNotCopy = array('media','admin','site'); // Joomla already deals with these
		if (count((array) $folders) > 1)
		{
			foreach ($folders as $folder)
			{
				// Only copy if not a standard folders
				if (!in_array($folder, $doNotCopy))
				{
					// set the source path
					$src = $installPath.'/'.$folder;
					// set the destination path
					$dest = JPATH_ROOT.'/'.$folder;
					// now try to copy the folder
					if (!Folder::copy($src, $dest, '', true))
					{
						$app->enqueueMessage('Could not copy '.$folder.' folder into place, please make sure destination is writable!', 'error');
					}
				}
			}
		}
	}
}
