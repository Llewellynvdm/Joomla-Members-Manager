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

use Joomla\Registry\Registry;

/**
 * Membersmanager Type Model
 */
class MembersmanagerModelType extends JModelAdmin
{
	/**
	 * The tab layout fields array.
	 *
	 * @var      array
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'left' => array(
				'groups_target'
			),
			'right' => array(
				'groups_access'
			),
			'fullwidth' => array(
				'description'
			),
			'above' => array(
				'name',
				'alias'
			)
		),
		'advance' => array(
			'left' => array(
				'add_relationship',
				'type',
				'edit_relationship',
				'view_relationship'
			),
			'right' => array(
				'communicate',
				'field_type'
			)
		)
	);

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_MEMBERSMANAGER';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_membersmanager.type';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'type', $prefix = 'MembersmanagerTable', $config = array())
	{
		// add table path for when model gets used from other component
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_membersmanager/tables');
		// get instance of the table
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->params) && !is_array($item->params))
			{
				// Convert the params field to an array.
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry->toArray();
			}

			if (!empty($item->metadata))
			{
				// Convert the metadata field to an array.
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			if (!empty($item->view_relationship))
			{
				// Convert the view_relationship field to an array.
				$view_relationship = new Registry;
				$view_relationship->loadString($item->view_relationship);
				$item->view_relationship = $view_relationship->toArray();
			}

			if (!empty($item->edit_relationship))
			{
				// Convert the edit_relationship field to an array.
				$edit_relationship = new Registry;
				$edit_relationship->loadString($item->edit_relationship);
				$item->edit_relationship = $edit_relationship->toArray();
			}

			if (!empty($item->type))
			{
				// Convert the type field to an array.
				$type = new Registry;
				$type->loadString($item->type);
				$item->type = $type->toArray();
			}

			if (!empty($item->groups_target))
			{
				// JSON Decode groups_target.
				$item->groups_target = json_decode($item->groups_target,true);
			}

			if (!empty($item->groups_access))
			{
				// JSON Decode groups_access.
				$item->groups_access = json_decode($item->groups_access,true);
			}
			
			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_membersmanager.type');
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true, $options = array('control' => 'jform'))
	{
		// set load data option
		$options['load_data'] = $loadData;
		// Get the form.
		$form = $this->loadForm('com_membersmanager.type', 'type', $options);

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = JFactory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('type.edit.state', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.state', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if ($id != 0 && (!$user->authorise('type.edit.created_by', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.created_by', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('type.edit.created', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.created', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit Name access controls.
		if ($id != 0 && (!$user->authorise('type.edit.name', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.name', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('name', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('name', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('name'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'required', 'false');
			}
		}
		// Modify the form based on Edit Description access controls.
		if ($id != 0 && (!$user->authorise('type.edit.description', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.description', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('description', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('description', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('description'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('description', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('description', 'required', 'false');
			}
		}
		// Modify the form based on Edit Groups Target access controls.
		if ($id != 0 && (!$user->authorise('type.edit.groups_target', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.groups_target', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('groups_target', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('groups_target', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('groups_target'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('groups_target', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('groups_target', 'required', 'false');
			}
		}
		// Modify the form based on Edit Groups Access access controls.
		if ($id != 0 && (!$user->authorise('type.edit.groups_access', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.groups_access', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('groups_access', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('groups_access', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('groups_access'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('groups_access', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('groups_access', 'required', 'false');
			}
		}
		// Modify the form based on Edit Add Relationship access controls.
		if ($id != 0 && (!$user->authorise('type.edit.add_relationship', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.add_relationship', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('add_relationship', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('add_relationship', 'readonly', 'true');
			// Disable radio button for display.
			$class = $form->getFieldAttribute('add_relationship', 'class', '');
			$form->setFieldAttribute('add_relationship', 'class', $class.' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('add_relationship'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('add_relationship', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('add_relationship', 'required', 'false');
			}
		}
		// Modify the form based on Edit Field Type access controls.
		if ($id != 0 && (!$user->authorise('type.edit.field_type', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.field_type', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('field_type', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('field_type', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('field_type'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('field_type', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('field_type', 'required', 'false');
			}
		}
		// Modify the form based on Edit Communicate access controls.
		if ($id != 0 && (!$user->authorise('type.edit.communicate', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.communicate', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('communicate', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('communicate', 'readonly', 'true');
			// Disable radio button for display.
			$class = $form->getFieldAttribute('communicate', 'class', '');
			$form->setFieldAttribute('communicate', 'class', $class.' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('communicate'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('communicate', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('communicate', 'required', 'false');
			}
		}
		// Modify the form based on Edit View Relationship access controls.
		if ($id != 0 && (!$user->authorise('type.edit.view_relationship', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.view_relationship', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('view_relationship', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('view_relationship', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('view_relationship'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('view_relationship', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('view_relationship', 'required', 'false');
			}
		}
		// Modify the form based on Edit Edit Relationship access controls.
		if ($id != 0 && (!$user->authorise('type.edit.edit_relationship', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.edit_relationship', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('edit_relationship', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('edit_relationship', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('edit_relationship'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('edit_relationship', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('edit_relationship', 'required', 'false');
			}
		}
		// Modify the form based on Edit Type access controls.
		if ($id != 0 && (!$user->authorise('type.edit.type', 'com_membersmanager.type.' . (int) $id))
			|| ($id == 0 && !$user->authorise('type.edit.type', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('type', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('type', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('type'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'required', 'false');
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
		}
		return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	script files
	 */
	public function getScript()
	{
		return 'administrator/components/com_membersmanager/models/forms/type.js';
	}
    
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}

			$user = JFactory::getUser();
			// The record has been set. Check the record permissions.
			return $user->authorise('type.delete', 'com_membersmanager.type.' . (int) $record->id);
		}
		return false;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		$recordId = (!empty($record->id)) ? $record->id : 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('type.edit.state', 'com_membersmanager.type.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absense of better information, revert to the component permissions.
		return $user->authorise('type.edit.state', 'com_membersmanager');
	}
    
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	2.5
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		$user = JFactory::getUser();

		return $user->authorise('type.edit', 'com_membersmanager.type.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or $user->authorise('type.edit',  'com_membersmanager');
	}
    
	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (isset($table->name))
		{
			$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		}
		
		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}
		
		if (empty($table->id))
		{
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__membersmanager_type'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
			$table->modified_by = $user->id;
		}
        
		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_membersmanager.edit.type.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// check if the not_required field is set
		if (MembersmanagerHelper::checkString($data['not_required']))
		{
			$requiredFields = (array) explode(',',(string) $data['not_required']);
			$requiredFields = array_unique($requiredFields);
			// now change the required field attributes value
			foreach ($requiredFields as $requiredField)
			{
				// make sure there is a string value
				if (MembersmanagerHelper::checkString($requiredField))
				{
					// change to false
					$form->setFieldAttribute($requiredField, 'required', 'false');
					// also clear the data set
					$data[$requiredField] = '';
				}
			}
		}
		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqeFields()
	{
		return false;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}
		
		return true;
        }
    
	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user			= JFactory::getUser();
		$this->table			= $this->getTable();
		$this->tableClassName		= get_class($this->table);
		$this->contentType		= new JUcmType;
		$this->type			= $this->contentType->getTypeByTable($this->tableClassName);
		$this->canDo			= MembersmanagerHelper::getActions('type');
		$this->batchSet			= true;

		if (!$this->canDo->get('core.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}
        
		if ($this->type == false)
		{
			$type = new JUcmType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['move_copy']))
		{
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands, $pks, $contexts);

				if (is_array($result))
				{
					foreach ($result as $old => $new)
					{
						$contexts[$new] = $contexts[$old];
					}
					$pks = array_values($result);
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands, $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $values    The new values.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since 12.2
	 */
	protected function batchCopy($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user 		= JFactory::getUser();
			$this->table 		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= MembersmanagerHelper::getActions('type');
		}

		if (!$this->canDo->get('type.create') && !$this->canDo->get('type.batch'))
		{
			return false;
		}

		// get list of uniqe fields
		$uniqeFields = $this->getUniqeFields();
		// remove move_copy from array
		unset($values['move_copy']);

		// make sure published is set
		if (!isset($values['published']))
		{
			$values['published'] = 0;
		}
		elseif (isset($values['published']) && !$this->canDo->get('type.edit.state'))
		{
				$values['published'] = 0;
		}

		$newIds = array();
		// Parent exists so let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// only allow copy if user may edit this item.
			if (!$this->user->authorise('type.edit', $contexts[$pk]))
			{
				// Not fatal error
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
				continue;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}
			list($this->table->name, $this->table->alias) = $this->_generateNewTitle($this->table->alias, $this->table->name);

			// insert all set values
			if (MembersmanagerHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					if (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}

			// update all uniqe fields
			if (MembersmanagerHelper::checkArray($uniqeFields))
			{
				foreach ($uniqeFields as $uniqeField)
				{
					$this->table->$uniqeField = $this->generateUniqe($uniqeField,$this->table->$uniqeField);
				}
			}

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// TODO: Deal with ordering?
			// $this->table->ordering = 1;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch move items to a new category
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since 12.2
	 */
	protected function batchMove($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user		= JFactory::getUser();
			$this->table		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= MembersmanagerHelper::getActions('type');
		}

		if (!$this->canDo->get('type.edit') && !$this->canDo->get('type.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('type.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('type.edit', $contexts[$pk]))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// insert all set values.
			if (MembersmanagerHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					// Do special action for access.
					if ('access' === $key && strlen($value) > 0)
					{
						$this->table->$key = $value;
					}
					elseif (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}


			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input	= JFactory::getApplication()->input;
		$filter	= JFilterInput::getInstance();
        
		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
            
			$metadata = new JRegistry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}

		// Set the view_relationship items to data.
		if (isset($data['view_relationship']) && is_array($data['view_relationship']))
		{
			$view_relationship = new JRegistry;
			$view_relationship->loadArray($data['view_relationship']);
			$data['view_relationship'] = (string) $view_relationship;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty view_relationship
		elseif (!isset($data['view_relationship'])
			&& JFactory::getUser()->authorise('type.edit.view_relationship', 'com_membersmanager'))
		{
			// Set the empty view_relationship to data
			$data['view_relationship'] = '';
		}

		// Set the edit_relationship items to data.
		if (isset($data['edit_relationship']) && is_array($data['edit_relationship']))
		{
			$edit_relationship = new JRegistry;
			$edit_relationship->loadArray($data['edit_relationship']);
			$data['edit_relationship'] = (string) $edit_relationship;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty edit_relationship
		elseif (!isset($data['edit_relationship'])
			&& JFactory::getUser()->authorise('type.edit.edit_relationship', 'com_membersmanager'))
		{
			// Set the empty edit_relationship to data
			$data['edit_relationship'] = '';
		}

		// Set the type items to data.
		if (isset($data['type']) && is_array($data['type']))
		{
			$type = new JRegistry;
			$type->loadArray($data['type']);
			$data['type'] = (string) $type;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty type
		elseif (!isset($data['type'])
			&& JFactory::getUser()->authorise('type.edit.type', 'com_membersmanager'))
		{
			// Set the empty type to data
			$data['type'] = '';
		}

		// Set the groups_target string to JSON string.
		if (isset($data['groups_target']))
		{
			$data['groups_target'] = (string) json_encode($data['groups_target']);
		}

		// Set the groups_access string to JSON string.
		if (isset($data['groups_access']))
		{
			$data['groups_access'] = (string) json_encode($data['groups_access']);
		}
        
		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new JRegistry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the name for save as copy
		if ($input->get('task') === 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name)
			{
				list($name, $alias) = $this->_generateNewTitle($data['alias'], $data['name']);
				$data['name'] = $name;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['published'] = 0;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (int) $input->get('id') == 0)
		{
			if ($data['alias'] == null || empty($data['alias']))
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['name']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['name']);
				}

				$table = JTable::getInstance('type', 'membersmanagerTable');

				if ($table->load(array('alias' => $data['alias'])) && ($table->id != $data['id'] || $data['id'] == 0))
				{
					$msg = JText::_('COM_MEMBERSMANAGER_TYPE_SAVE_WARNING');
				}

				$data['alias'] = $this->_generateNewTitle($data['alias']);

				if (isset($msg))
				{
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		// Alter the uniqe field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other uniqe fields
			$uniqeFields = $this->getUniqeFields();
			if (MembersmanagerHelper::checkArray($uniqeFields))
			{
				foreach ($uniqeFields as $uniqeField)
				{
					$data[$uniqeField] = $this->generateUniqe($uniqeField,$data[$uniqeField]);
				}
			}
		}
		
		if (parent::save($data))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Method to generate a uniqe value.
	 *
	 * @param   string  $field name.
	 * @param   string  $value data.
	 *
	 * @return  string  New value.
	 *
	 * @since   3.0
	 */
	protected function generateUniqe($field,$value)
	{

		// set field value uniqe 
		$table = $this->getTable();

		while ($table->load(array($field => $value)))
		{
			$value = JString::increment($value);
		}

		return $value;
	}

	/**
	 * Method to change the title/s & alias.
	 *
	 * @param   string         $alias        The alias.
	 * @param   string/array   $title        The title.
	 *
	 * @return	array/string  Contains the modified title/s and/or alias.
	 *
	 */
	protected function _generateNewTitle($alias, $title = null)
	{

		// Alter the title/s & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			// Check if this is an array of titles
			if (MembersmanagerHelper::checkArray($title))
			{
				foreach($title as $nr => &$_title)
				{
					$_title = JString::increment($_title);
				}
			}
			// Make sure we have a title
			elseif ($title)
			{
				$title = JString::increment($title);
			}
			$alias = JString::increment($alias, 'dash');
		}
		// Check if this is an array of titles
		if (MembersmanagerHelper::checkArray($title))
		{
			$title[] = $alias;
			return $title;
		}
		// Make sure we have a title
		elseif ($title)
		{
			return array($title, $alias);
		}
		// We only had an alias
		return $alias;
	}
}
