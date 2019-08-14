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

JHTML::_('behavior.modal');

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
	public function __construct(JAdapterInstance $parent) {}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $parent) {}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $parent)
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove member add queued success message.
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
				// If succesfully remove Member add queued success message.
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
				// If succesfully remove Member add queued success message.
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
				// If succesfully remove Member add queued success message.
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
				// If succesfully remove Type add queued success message.
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
				// If succesfully remove Type add queued success message.
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
				// If succesfully remove Type add queued success message.
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
			// If succesfully remove membersmanager add queued success message.
			$app->enqueueMessage(JText::_('All related items was removed from the <b>#__assets</b> table'));
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
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $parent){}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, JAdapterInstance $parent)
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
		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, JAdapterInstance $parent)
	{
		// get application
		$app = JFactory::getApplication();
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
			$member->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","email":"email","account":"account","user":"user","token":"token","profile_image":"profile_image","not_required":"not_required","main_member":"main_member","password_check":"password_check","password":"password","useremail":"useremail","username":"username","surname":"surname","type":"type"}}';
			$member->router = 'MembersmanagerHelperRoute::getMemberRoute';
			$member->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/member.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","profile_image","not_required"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","account","user","main_member"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "user","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "main_member","targetTable": "#__membersmanager_member","targetColumn": "id","displayColumn": "user"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

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

			// Install the global extenstion params.
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

			echo '<a target="_blank" href="https://www.joomlacomponentbuilder.com/" title="Members Manager">
				<img src="components/com_membersmanager/assets/images/vdm-component.jpg"/>
				</a>';
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
			$member->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","email":"email","account":"account","user":"user","token":"token","profile_image":"profile_image","not_required":"not_required","main_member":"main_member","password_check":"password_check","password":"password","useremail":"useremail","username":"username","surname":"surname","type":"type"}}';
			$member->router = 'MembersmanagerHelperRoute::getMemberRoute';
			$member->content_history_options = '{"formFile": "administrator/components/com_membersmanager/models/forms/member.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","profile_image","not_required"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","account","user","main_member"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "user","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "main_member","targetTable": "#__membersmanager_member","targetColumn": "id","displayColumn": "user"},{"sourceColumn": "type","targetTable": "#__membersmanager_type","targetColumn": "id","displayColumn": "name"}]}';

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
		}
		return true;
	}
}
