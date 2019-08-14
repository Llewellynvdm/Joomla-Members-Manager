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
 * Membersmanager Member Model
 */
class MembersmanagerModelMember extends JModelAdmin
{
	/**
	 * The tab layout fields array.
	 *
	 * @var      array
	 */
	protected $tabLayoutFields = array(
		'membership' => array(
			'left' => array(
				'type',
				'name',
				'surname',
				'username',
				'email',
				'useremail',
				'password',
				'password_check',
				'main_member',
				'not_required',
				'profile_image'
			),
			'right' => array(
				'profile_image_uploader'
			),
			'above' => array(
				'token',
				'account',
				'user'
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
		// add table path for when model gets used from other component
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_membersmanager/tables');
		// get instance of the table
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

			if (!empty($item->type))
			{
				// Convert the type field to an array.
				$type = new Registry;
				$type->loadString($item->type);
				$item->type = $type->toArray();
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
				// set the vast development method key
				$this->vastDevMod = MembersmanagerHelper::randomkey(50);
				MembersmanagerHelper::set($this->vastDevMod, 'member__'.$id);
				MembersmanagerHelper::set('member__'.$id, $this->vastDevMod);
				// set a return value if found
				$jinput = JFactory::getApplication()->input;
				$return = $jinput->get('return', null, 'base64');
				MembersmanagerHelper::set($this->vastDevMod . '__return', $return);
			}
			// load values from user table
			if (isset($item->user) && $item->user > 0 && isset($item->account) && (1 == $item->account || 4 == $item->account))
			{
				// load values from user table
				$member = JFactory::getUser($item->user);
				// set the name
				$item->name = $member->name;
				// set the useremail
				$item->useremail = $member->email;
				// set the username
				$item->username = $member->username;
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
		$form = $this->loadForm('com_membersmanager.member', 'member', $options);

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
		// Modify the form based on View Name access controls.
		if ($id != 0 && (!$user->authorise('member.view.name', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.name', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('name', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('name')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'required', 'false');
				// Make sure
				$form->setValue('name', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('name');
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
			if (!($val = $form->getValue('email')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('email', 'required', 'false');
				// Make sure
				$form->setValue('email', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('email');
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
		// Modify the form based on View Account access controls.
		if ($id != 0 && (!$user->authorise('member.view.account', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.account', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('account', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('account')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('account', 'required', 'false');
				// Make sure
				$form->setValue('account', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('account');
			}
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
		// Modify the form based on View User access controls.
		if ($id != 0 && (!$user->authorise('member.view.user', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.user', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('user', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('user')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('user', 'required', 'false');
				// Make sure
				$form->setValue('user', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('user');
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
		// Modify the form based on View Token access controls.
		if ($id != 0 && (!$user->authorise('member.view.token', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.token', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('token', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('token')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('token', 'required', 'false');
				// Make sure
				$form->setValue('token', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('token');
			}
		}
		// Modify the form based on Edit Profile Image access controls.
		if ($id != 0 && (!$user->authorise('member.edit.profile_image', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.profile_image', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('profile_image', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('profile_image', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('profile_image'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('profile_image', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('profile_image', 'required', 'false');
			}
		}
		// Modify the from the form based on Profile Image access controls.
		if ($id != 0 && (!$user->authorise('member.access.profile_image', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.profile_image', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('profile_image');
		}
		// Modify the form based on View Profile Image access controls.
		if ($id != 0 && (!$user->authorise('member.view.profile_image', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.profile_image', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('profile_image', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('profile_image')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('profile_image', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('profile_image', 'required', 'false');
				// Make sure
				$form->setValue('profile_image', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('profile_image');
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
		// Modify the form based on View Main Member access controls.
		if ($id != 0 && (!$user->authorise('member.view.main_member', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.main_member', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('main_member', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('main_member')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('main_member', 'required', 'false');
				// Make sure
				$form->setValue('main_member', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('main_member');
			}
		}
		// Modify the form based on Edit Password Check access controls.
		if ($id != 0 && (!$user->authorise('member.edit.password_check', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.password_check', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('password_check', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('password_check', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('password_check'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('password_check', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('password_check', 'required', 'false');
			}
		}
		// Modify the from the form based on Password Check access controls.
		if ($id != 0 && (!$user->authorise('member.access.password_check', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.password_check', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('password_check');
		}
		// Modify the form based on View Password Check access controls.
		if ($id != 0 && (!$user->authorise('member.view.password_check', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.password_check', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('password_check', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('password_check')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('password_check', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('password_check', 'required', 'false');
				// Make sure
				$form->setValue('password_check', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('password_check');
			}
		}
		// Modify the form based on Edit Password access controls.
		if ($id != 0 && (!$user->authorise('member.edit.password', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.password', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('password', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('password', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('password'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('password', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('password', 'required', 'false');
			}
		}
		// Modify the from the form based on Password access controls.
		if ($id != 0 && (!$user->authorise('member.access.password', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.password', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('password');
		}
		// Modify the form based on View Password access controls.
		if ($id != 0 && (!$user->authorise('member.view.password', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.password', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('password', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('password')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('password', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('password', 'required', 'false');
				// Make sure
				$form->setValue('password', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('password');
			}
		}
		// Modify the form based on Edit Useremail access controls.
		if ($id != 0 && (!$user->authorise('member.edit.useremail', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.useremail', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('useremail', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('useremail', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('useremail'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('useremail', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('useremail', 'required', 'false');
			}
		}
		// Modify the from the form based on Useremail access controls.
		if ($id != 0 && (!$user->authorise('member.access.useremail', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.useremail', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('useremail');
		}
		// Modify the form based on View Useremail access controls.
		if ($id != 0 && (!$user->authorise('member.view.useremail', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.useremail', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('useremail', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('useremail')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('useremail', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('useremail', 'required', 'false');
				// Make sure
				$form->setValue('useremail', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('useremail');
			}
		}
		// Modify the form based on Edit Username access controls.
		if ($id != 0 && (!$user->authorise('member.edit.username', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.username', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('username', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('username', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('username'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('username', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('username', 'required', 'false');
			}
		}
		// Modify the from the form based on Username access controls.
		if ($id != 0 && (!$user->authorise('member.access.username', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.access.username', 'com_membersmanager')))
		{
			// Remove the field
			$form->removeField('username');
		}
		// Modify the form based on View Username access controls.
		if ($id != 0 && (!$user->authorise('member.view.username', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.username', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('username', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('username')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('username', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('username', 'required', 'false');
				// Make sure
				$form->setValue('username', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('username');
			}
		}
		// Modify the form based on Edit Surname access controls.
		if ($id != 0 && (!$user->authorise('member.edit.surname', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.edit.surname', 'com_membersmanager')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('surname', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('surname', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('surname'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('surname', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('surname', 'required', 'false');
			}
		}
		// Modify the form based on View Surname access controls.
		if ($id != 0 && (!$user->authorise('member.view.surname', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.surname', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('surname', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('surname')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('surname', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('surname', 'required', 'false');
				// Make sure
				$form->setValue('surname', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('surname');
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
		// Modify the form based on View Type access controls.
		if ($id != 0 && (!$user->authorise('member.view.type', 'com_membersmanager.member.' . (int) $id))
			|| ($id == 0 && !$user->authorise('member.view.type', 'com_membersmanager')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('type', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('type')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('type', 'required', 'false');
				// Make sure
				$form->setValue('type', null, '');
			}
			elseif (MembersmanagerHelper::checkArray($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we conver to json we get an error
				$form->removeField('type');
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
		// if this is a site area hide the user field
		if (JFactory::getApplication()->isSite() || $form->getValue('user'))
		{
			// Disable fields for being edited directly
			$form->setFieldAttribute('user', 'readonly', 'true');
			// only make hidden if site area
			if (JFactory::getApplication()->isSite())
			{
				$form->setFieldAttribute('user', 'type', 'hidden');
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
		// check if member is still linked to other sub members as a main member
		if (MembersmanagerHelper::checkArray($pks))
		{
			// get the application object
			$app = JFactory::getApplication();
			// now loop the ids
			foreach ($pks as $key => $pk)
			{
				// check if member still have sub accounts linked to it
				if (($found = MembersmanagerHelper::getVar('member', $pk, 'main_member', 'id')) !== false)
				{
					// set the name
					$name = MembersmanagerHelper::getMemberName($pk);
					// set a message
					$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_YOU_CAN_NOT_DELETE_BSB_FIRST_MOVE_ALL_SUB_ACCOUNTS_TO_NEW_MAIN_MEMBER', $name), 'Error');
					// remove for the list
					unset($pks[$key]);
				}
			}
		}
		if (!parent::delete($pks))
		{
			return false;
		}

		// we must also update all linked tables
		if (MembersmanagerHelper::checkArray($pks))
		{
			$seek = array('Info', 'Assessment');
			foreach ($seek as $area)
			{
				if (($components = MembersmanagerHelper::{'get' . $area . 'Components'}()) !== false)
				{
					foreach($components as $_component)
					{
						$component = str_replace('com_', '', $_component->element);
						$Component = MembersmanagerHelper::safeString($component, 'F');
						// get the linked IDs
						if (($ids = MembersmanagerHelper::getVars('form', $pks, 'member', 'id', 'IN', $component)) !== false && MembersmanagerHelper::checkArray($ids))
						{
							// get the model
							$_Model = MembersmanagerHelper::getModel('form', JPATH_ADMINISTRATOR . '/components/' . $_component->element, $Component);
							// do we have the model
							if ($_Model)
							{
								// change publish state
								$_Model->delete($ids);
							}
						}
					}
				}
			}
			// now loop the ids
			foreach ($pks as $key => $pk)
			{
				// make sure to remove the type_map
				MembersmanagerHelper::updateTypes($pk);
				// must still do the relationship clearing (TODO)
			}
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

		// we must also update all linked tables
		if (MembersmanagerHelper::checkArray($pks))
		{
			$seek = array('Info', 'Assessment');
			foreach ($seek as $area)
			{
				if (($components = MembersmanagerHelper::{'get' . $area . 'Components'}()) !== false)
				{
					foreach($components as $_component)
					{
						$component = str_replace('com_', '', $_component->element);
						$Component = MembersmanagerHelper::safeString($component, 'F');
						// get the linked IDs
						if (($ids = MembersmanagerHelper::getVars('form', $pks, 'member', 'id', 'IN', $component)) !== false && MembersmanagerHelper::checkArray($ids))
						{
							// get the model
							$_Model = MembersmanagerHelper::getModel('form', JPATH_ADMINISTRATOR . '/components/' . $_component->element, $Component);
							// do we have the model
							if ($_Model)
							{
								// change publish state
								$_Model->publish($ids, $value);
							}
						}
					}
				}
			}
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

			// Only for strings
			if (MembersmanagerHelper::checkString($this->table->name) && !is_numeric($this->table->name))
			{
				$this->table->name = $this->generateUniqe('name',$this->table->name);
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

		// get user object
		$user = JFactory::getUser();
		// set ID
		$id = (isset($data['id']) && $data['id'] > 0) ? $data['id'] : false;
		// little function to check user access	
		$checkUserAccess = function  ($permission) use($user, $id) {
			return (($id && $user->authorise('member.' . $permission, 'com_membersmanager.member.' . (int) $id)) || (!$id && $user->authorise('member.' . $permission, 'com_membersmanager')));
		};
		// make sure these type is set
		if ($id && !$checkUserAccess('edit.type'))
		{
			$data['type'] = MembersmanagerHelper::getVar('member', $id, 'id', 'type');
		}
		// make sure these account is set
		if ($id && !$checkUserAccess('edit.account'))
		{
			$data['account'] = MembersmanagerHelper::getVar('member', $id, 'id', 'account');
		}
		// get user value if not set (due to permissions)
		if ($id && isset($data['account']) && (1 == $data['account'] || 4 == $data['account']) && (!isset($data['user']) || $data['user'] == 0))
		{
			$data['user'] = MembersmanagerHelper::getVar('member', $id, 'id', 'user');
		}
		// check if this is a linked user (MUST STILL DO PERMISSIONS)
		if (isset($data['account']) && (1 == $data['account'] || 4 == $data['account']) && $checkUserAccess('edit.user'))
		{
			// get the application object
			$app = JFactory::getApplication();
			// check if member already exist
			if ($id && isset($data['user']) && $data['user'] > 0)
			{
				// do not allow user link to be changed (should have done this in the controller)
				if (($alreadyUser = MembersmanagerHelper::getVar('member', $id, 'id', 'user')) !== false && is_numeric($alreadyUser) && $alreadyUser > 0 && $alreadyUser != $data['user'])
				{
					$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_MEMBER_IS_ALREADY_LINKED_TO_AN_USER_THIS_CAN_NOT_BE_CHANGED_CONTACT_YOUR_SYSTEM_ADMINISTRATOR_IF_YOU_NEED_MORE_HELP'), 'Error');
					return false;
				}
			}
			// set bucket to update/create user
			$bucket = array();
			// set name
			$bucket['name'] = $data['name'];
			// set username
			$bucket['username'] = $data['username'];
			// set useremail
			$bucket['email'] = $data['useremail'];
			// start message bucket
			$message = array();
			// check if user already linked
			if (isset($data['user']) && is_numeric($data['user']) && $data['user'] > 0)
			{
				// set user ID
				$bucket['id'] = $data['user'];
				// get member user
				$memberUser = JFactory::getUser($bucket['id']);
				// get user exciting groups
				$bucket['groups'] = $memberUser->get('groups');
				// remove all groups part of members manager
				MembersmanagerHelper::removeMemberGroups($bucket['groups']);
				// load the user groups (TODO)
				if (($typeGroups = MembersmanagerHelper::getMemberGroupsByType($data['type'])) !== false)
				{
					$bucket['groups'] = MembersmanagerHelper::mergeArrays(array($bucket['groups'], $typeGroups));
				}
				// set password
				if (empty($data['password']) || empty($data['password_check']))
				{
					$bucket['password'] = JFactory::getUser($data['user'])->password;
					$bucket['password2'] = $bucket['password'];
				}
				else
				{
					$bucket['password'] = $data['password'];
					$bucket['password2'] = $data['password_check'];
				}
				// update exiting user
				$done = MembersmanagerHelper::updateUser($bucket);
				if (!is_numeric($done) || $done != $data['user'])
				{
					$app->enqueueMessage($done, 'Error');
					// we still check if user was created.... (TODO)
					if ($didCreate = JUserHelper::getUserId($bucket['username']))
					{
						$data['user'] = $didCreate;
					}
				}
			}
			else
			{
				// set password
				if (isset($data['password']) && isset($data['password_check']))
				{
					$bucket['password'] = $data['password'];
					$bucket['password2'] = $data['password_check'];
				}
				// create new user
				$done = MembersmanagerHelper::createUser($bucket);
				if (is_numeric($done))
				{
					// make sure to set the user value
					$data['user'] = $done;
					$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_MEMBER_WAS_CREATED_SUCCESSFULLY_AND_THE_LOGIN_DETAILS_WAS_EMAILED_TO_THE_MEMBER'), 'Success');
				}
				else
				{
					// set the error
					$app->enqueueMessage($done, 'Error');
					// we still check if user was created.... (TODO)
					if (($didCreate = JUserHelper::getUserId($bucket['username'])))
					{
						$data['user'] = $didCreate;
					}
				}
				// once we are sure we have an user ID
				if (isset($data['user']) && is_numeric($data['user']) && $data['user'] > 0)
				{
					// check if we have groups
					if (($typeGroups = MembersmanagerHelper::getMemberGroupsByType($data['type'])) !== false)
					{
						// update the user groups
						JUserHelper::setUserGroups((int) $data['user'], (array) $typeGroups);
					}
					else
					{
						// notice that the group was not set for this user
						$app->enqueueMessage(JText::_('COM_MEMBERSMANAGER_MEMBER_WAS_NOT_ADDED_TO_ANY_GROUPS_PLEASE_INFORM_YOUR_SYSTEM_ADMINISTRATOR'), 'Error');
					}
				}
				// the login member must always own it self for edit permissions
				$data['created_by'] = $data['user'];
			}
		}
		// if a sub account and not login access
		if (isset($data['account']) && 3 == $data['account'] && isset($data['main_member']) && $data['main_member'] > 0
			&& ($mainMemberUser = MembersmanagerHelper::getVar('member', $data['main_member'], 'id', 'user')) !== false && $mainMemberUser > 0)
		{
			// the main user must always own it self for edit permissions
			$data['created_by'] = $mainMemberUser;
		}
		// always clear out password!!
		unset($data['password']);
		unset($data['password_check']);
		// clear out user if error found
		if ((empty($data['user']) || $data['user'] == 0 || empty($data['account']) || (1 != $data['account'] && 4 != $data['account'])) && $checkUserAccess('edit.user') && $checkUserAccess('edit.account'))
		{
			// if not a linked account, then no user can be set
			$data['user'] = '';
			$data['username'] = '';
			$data['useremail'] = '';
		}
		// check if token is set
		if (empty($data['token']))
		{
			if (!isset($data['surname']))
			{
				// get a token
				$token = call_user_func(function($data) {
					// get the name of this member
					if (isset($data['account']) && (1 == $data['account'] || 4 == $data['account']) && isset($data['user']) && $data['user'] > 0)
					{
						return JFactory::getUser($data['user'])->name;
					}
					elseif (isset($data['name']) && MembersmanagerHelper::checkString($data['name']))
					{
						return $data['name'];
					}
					return MembersmanagerHelper::randomkey(8);
				}, $data);

			}
			else
			{
				// get a token
				$token = call_user_func(function($data) {
					// get the name of this member
					if (isset($data['account']) && (1 == $data['account'] || 4 == $data['account']) && isset($data['user']) && $data['user'] > 0)
					{
						return JFactory::getUser($data['user'])->name . ' ' . $data['surname'];
					}
					elseif (isset($data['name']) && MembersmanagerHelper::checkString($data['name']))
					{
						return $data['name'] . ' ' . $data['surname'];
					}
					return MembersmanagerHelper::randomkey(8);
				}, $data);
			}
			// split at upper case
			$tokenArray = (array) preg_split('/(?=[A-Z])/', trim($token), -1, PREG_SPLIT_NO_EMPTY);
			// make string safe
			$data['token'] = MembersmanagerHelper::safeString(trim(implode(' ', $tokenArray), '-'), 'L', '-', false, false);
			// get unique token
			while (!MembersmanagerHelper::checkUnique($id, 'token', $data['token'], 'member'))
			{
				$data['token'] = JString::increment($data['token'], 'dash');
			}
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
			&& JFactory::getUser()->authorise('member.edit.type', 'com_membersmanager')
			&& JFactory::getUser()->authorise('member.view.type', 'com_membersmanager'))
		{
			// Set the empty type to data
			$data['type'] = '';
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
