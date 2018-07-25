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

use Joomla\Registry\Registry;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Membersmanager Member Model
 */
class MembersmanagerModelMember extends JModelAdmin
{    
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
	public $typeAlias = 'com_membersmanager.member';

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
	public function getTable($type = 'member', $prefix = 'MembersmanagerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getVDM()
	{
		return $this->vastDevMod;
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

			// Get the medium encryption.
			$mediumkey = MembersmanagerHelper::getCryptKey('medium');
			// Get the encryption object.
			$medium = new FOFEncryptAes($mediumkey);

			if (!empty($item->profile_image) && $mediumkey && !is_numeric($item->profile_image) && $item->profile_image === base64_encode(base64_decode($item->profile_image, true)))
			{
				// medium decrypt data profile_image.
				$item->profile_image = rtrim($medium->decryptString($item->profile_image), "\0");
			}


			if (empty($item->id))
			{
				$id = 0;
			}
			else
			{
				$id = $item->id;
			}			
			// set the id and view name to session
			if ($vdm = MembersmanagerHelper::get('member__'.$id))
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				$this->vastDevMod = MembersmanagerHelper::randomkey(50);
				MembersmanagerHelper::set($this->vastDevMod, 'member__'.$id);
				MembersmanagerHelper::set('member__'.$id, $this->vastDevMod);
			}
			
			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_membersmanager.member');
			}
		}

		return $item;
	} 

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_membersmanager.member', 'member', array('control' => 'jform', 'load_data' => $loadData));

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
		if ($id != 0 && (!$user->authorise('member.edit.state', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.state', 'com_membersmanager')))
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
		if ($id != 0 && (!$user->authorise('member.edit.created_by', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.created_by', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('member.edit.created', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.created', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit User access controls.
		if ($id != 0 && (!$user->authorise('member.edit.user', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.user', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('user', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('user', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('user'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'required', 'false');
			}
		}
		// Modify the from the form based on User access controls.
		if ($id != 0 && (!$user->authorise('member.access.user', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.user', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('user');
		}
		// Modify the form based on View User access controls.
		if ($id != 0 && (!$user->authorise('member.view.user', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.user', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('user', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('user'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'required', 'false');
			}
		}
		// Modify the form based on Edit Landline Phone access controls.
		if ($id != 0 && (!$user->authorise('member.edit.landline_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.landline_phone', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('landline_phone', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('landline_phone', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('landline_phone'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('landline_phone', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('landline_phone', 'required', 'false');
			}
		}
		// Modify the from the form based on Landline Phone access controls.
		if ($id != 0 && (!$user->authorise('member.access.landline_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.landline_phone', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('landline_phone');
		}
		// Modify the form based on View Landline Phone access controls.
		if ($id != 0 && (!$user->authorise('member.view.landline_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.landline_phone', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('landline_phone', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('landline_phone'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('landline_phone', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('landline_phone', 'required', 'false');
			}
		}
		// Modify the form based on Edit Type access controls.
		if ($id != 0 && (!$user->authorise('member.edit.type', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.type', 'com_membersmanager')))
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
		// Modify the from the form based on Type access controls.
		if ($id != 0 && (!$user->authorise('member.access.type', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.type', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('type');
		}
		// Modify the form based on View Type access controls.
		if ($id != 0 && (!$user->authorise('member.view.type', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.type', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('type', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('type'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'required', 'false');
			}
		}
		// Modify the form based on Edit Account access controls.
		if ($id != 0 && (!$user->authorise('member.edit.account', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.account', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('account', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('account', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('account'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'required', 'false');
			}
		}
		// Modify the from the form based on Account access controls.
		if ($id != 0 && (!$user->authorise('member.access.account', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.account', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('account');
		}
		// Modify the form based on View Account access controls.
		if ($id != 0 && (!$user->authorise('member.view.account', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.account', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('account', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('account'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'required', 'false');
			}
		}
		// Modify the form based on Edit Token access controls.
		if ($id != 0 && (!$user->authorise('member.edit.token', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.token', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('token', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('token', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('token'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'required', 'false');
			}
		}
		// Modify the from the form based on Token access controls.
		if ($id != 0 && (!$user->authorise('member.access.token', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.token', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('token');
		}
		// Modify the form based on View Token access controls.
		if ($id != 0 && (!$user->authorise('member.view.token', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.token', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('token', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('token'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'required', 'false');
			}
		}
		// Modify the form based on Edit Country access controls.
		if ($id != 0 && (!$user->authorise('member.edit.country', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.country', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('country', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('country', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('country'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('country', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('country', 'required', 'false');
			}
		}
		// Modify the from the form based on Country access controls.
		if ($id != 0 && (!$user->authorise('member.access.country', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.country', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('country');
		}
		// Modify the form based on View Country access controls.
		if ($id != 0 && (!$user->authorise('member.view.country', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.country', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('country', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('country'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('country', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('country', 'required', 'false');
			}
		}
		// Modify the form based on Edit Postalcode access controls.
		if ($id != 0 && (!$user->authorise('member.edit.postalcode', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.postalcode', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('postalcode', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('postalcode', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('postalcode'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('postalcode', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('postalcode', 'required', 'false');
			}
		}
		// Modify the from the form based on Postalcode access controls.
		if ($id != 0 && (!$user->authorise('member.access.postalcode', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.postalcode', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('postalcode');
		}
		// Modify the form based on View Postalcode access controls.
		if ($id != 0 && (!$user->authorise('member.view.postalcode', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.postalcode', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('postalcode', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('postalcode'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('postalcode', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('postalcode', 'required', 'false');
			}
		}
		// Modify the form based on Edit City access controls.
		if ($id != 0 && (!$user->authorise('member.edit.city', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.city', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('city', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('city', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('city'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('city', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('city', 'required', 'false');
			}
		}
		// Modify the from the form based on City access controls.
		if ($id != 0 && (!$user->authorise('member.access.city', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.city', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('city');
		}
		// Modify the form based on View City access controls.
		if ($id != 0 && (!$user->authorise('member.view.city', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.city', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('city', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('city'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('city', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('city', 'required', 'false');
			}
		}
		// Modify the form based on Edit Region access controls.
		if ($id != 0 && (!$user->authorise('member.edit.region', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.region', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('region', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('region', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('region'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('region', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('region', 'required', 'false');
			}
		}
		// Modify the from the form based on Region access controls.
		if ($id != 0 && (!$user->authorise('member.access.region', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.region', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('region');
		}
		// Modify the form based on View Region access controls.
		if ($id != 0 && (!$user->authorise('member.view.region', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.region', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('region', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('region'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('region', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('region', 'required', 'false');
			}
		}
		// Modify the form based on Edit Street access controls.
		if ($id != 0 && (!$user->authorise('member.edit.street', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.street', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('street', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('street', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('street'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('street', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('street', 'required', 'false');
			}
		}
		// Modify the from the form based on Street access controls.
		if ($id != 0 && (!$user->authorise('member.access.street', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.street', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('street');
		}
		// Modify the form based on View Street access controls.
		if ($id != 0 && (!$user->authorise('member.view.street', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.street', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('street', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('street'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('street', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('street', 'required', 'false');
			}
		}
		// Modify the form based on Edit Postal access controls.
		if ($id != 0 && (!$user->authorise('member.edit.postal', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.postal', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('postal', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('postal', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('postal'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('postal', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('postal', 'required', 'false');
			}
		}
		// Modify the from the form based on Postal access controls.
		if ($id != 0 && (!$user->authorise('member.access.postal', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.postal', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('postal');
		}
		// Modify the form based on View Postal access controls.
		if ($id != 0 && (!$user->authorise('member.view.postal', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.postal', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('postal', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('postal'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('postal', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('postal', 'required', 'false');
			}
		}
		// Modify the form based on Edit Mobile Phone access controls.
		if ($id != 0 && (!$user->authorise('member.edit.mobile_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.mobile_phone', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('mobile_phone', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('mobile_phone', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('mobile_phone'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('mobile_phone', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('mobile_phone', 'required', 'false');
			}
		}
		// Modify the from the form based on Mobile Phone access controls.
		if ($id != 0 && (!$user->authorise('member.access.mobile_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.mobile_phone', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('mobile_phone');
		}
		// Modify the form based on View Mobile Phone access controls.
		if ($id != 0 && (!$user->authorise('member.view.mobile_phone', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.mobile_phone', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('mobile_phone', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('mobile_phone'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('mobile_phone', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('mobile_phone', 'required', 'false');
			}
		}
		// Modify the form based on Edit Name access controls.
		if ($id != 0 && (!$user->authorise('member.edit.name', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.name', 'com_membersmanager')))
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
		// Modify the from the form based on Name access controls.
		if ($id != 0 && (!$user->authorise('member.access.name', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.name', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('name');
		}
		// Modify the form based on View Name access controls.
		if ($id != 0 && (!$user->authorise('member.view.name', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.name', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('name', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('name'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'required', 'false');
			}
		}
		// Modify the form based on Edit Website access controls.
		if ($id != 0 && (!$user->authorise('member.edit.website', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.website', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('website', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('website', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('website'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('website', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('website', 'required', 'false');
			}
		}
		// Modify the from the form based on Website access controls.
		if ($id != 0 && (!$user->authorise('member.access.website', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.website', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('website');
		}
		// Modify the form based on View Website access controls.
		if ($id != 0 && (!$user->authorise('member.view.website', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.website', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('website', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('website'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('website', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('website', 'required', 'false');
			}
		}
		// Modify the form based on Edit Email access controls.
		if ($id != 0 && (!$user->authorise('member.edit.email', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.email', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('email', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('email', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('email'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'required', 'false');
			}
		}
		// Modify the from the form based on Email access controls.
		if ($id != 0 && (!$user->authorise('member.access.email', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.email', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('email');
		}
		// Modify the form based on View Email access controls.
		if ($id != 0 && (!$user->authorise('member.view.email', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.email', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('email', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('email'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'required', 'false');
			}
		}
		// Modify the form based on Edit Main Member access controls.
		if ($id != 0 && (!$user->authorise('member.edit.main_member', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.main_member', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('main_member', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('main_member', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('main_member'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'required', 'false');
			}
		}
		// Modify the from the form based on Main Member access controls.
		if ($id != 0 && (!$user->authorise('member.access.main_member', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.main_member', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('main_member');
		}
		// Modify the form based on View Main Member access controls.
		if ($id != 0 && (!$user->authorise('member.view.main_member', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.main_member', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('main_member', 'type', 'hidden');
			// If there is no value continue.
			if (!$form->getValue('main_member'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'required', 'false');
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected field name
			$redirectedField = $jinput->get('ref', null, 'STRING');
			// Set redirected field value
			$redirectedValue = $jinput->get('refid', 0, 'INT');
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
		return 'administrator/components/com_membersmanager/models/forms/member.js';
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
			return $user->authorise('member.delete', 'com_membersmanager.member.' . (int) $record->id);
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
			$permission = $user->authorise('member.edit.state', 'com_membersmanager.member.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absense of better information, revert to the component permissions.
		return $user->authorise('member.edit.state', 'com_membersmanager');
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

		return $user->authorise('member.edit', 'com_membersmanager.member.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or $user->authorise('member.edit',  'com_membersmanager');
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
					->from($db->quoteName('#__membersmanager_member'));
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
		$data = JFactory::getApplication()->getUserState('com_membersmanager.edit.member.data', array());

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
		$this->canDo			= MembersmanagerHelper::getActions('member');
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
			$this->canDo		= MembersmanagerHelper::getActions('member');
		}

		if (!$this->canDo->get('member.create') && !$this->canDo->get('member.batch'))
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
		elseif (isset($values['published']) && !$this->canDo->get('member.edit.state'))
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
			if (!$this->user->authorise('member.edit', $contexts[$pk]))
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
			$this->canDo		= MembersmanagerHelper::getActions('member');
		}

		if (!$this->canDo->get('member.edit') && !$this->canDo->get('member.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('member.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('member.edit', $contexts[$pk]))
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

		// Get the medium encryption key.
		$mediumkey = MembersmanagerHelper::getCryptKey('medium');
		// Get the encryption object
		$medium = new FOFEncryptAes($mediumkey);

		// Encrypt data profile_image.
		if (isset($data['profile_image']) && $mediumkey)
		{
			$data['profile_image'] = $medium->encryptString($data['profile_image']);
		}
        
		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new JRegistry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
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
	 * Method to change the title
	 *
	 * @param   string   $title   The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 */
	protected function _generateNewTitle($title)
	{

		// Alter the title
		$table = $this->getTable();

		while ($table->load(array('title' => $title)))
		{
			$title = JString::increment($title);
		}

		return $title;
	}
}
