<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

/**
 * Membersmanager Ajax Model
 */
class MembersmanagerModelAjax extends JModelList
{
	protected $app_params;
	
	public function __construct() 
	{		
		parent::__construct();		
		// get params
		$this->app_params	= JComponentHelper::getParams('com_membersmanager');
		
	}

	// Used in member
	// allowed views
	protected $allowedViews = array('member');

	// allowed targets
	protected $targets = array('profile'); 

	// allowed types
	protected $types = array('image' => 'image');
 
	// set some buckets
	protected $target;
	protected $targetType;
	protected $formatType;

	// set some defaults
	protected $formats = 
		array( 
			'image_formats' => array(
				1 => 'jpg',
				2 => 'jpeg',
				3 => 'gif',
				4 => 'png'),
			'document_formats' => array(
				1 => 'doc',
				2 => 'docx',
				3 => 'odt',
				4 => 'pdf',
				5 => 'csv',
				6 => 'xls',
				7 => 'xlsx',
				8 => 'ods',
				9 => 'ppt',
				10 => 'pptx',
				11 => 'pps',
				12 => 'ppsx',
				13 => 'odp',
				14 => 'zip'),
			'media_formats' => array(
				1 => 'mp3',
				2 => 'm4a',
				3 => 'ogg',
				4 => 'wav',
				5 => 'mp4',
				6 => 'm4v',
				7 => 'mov',
				8 => 'wmv',
				9 => 'avi',
				10 => 'mpg',
				11 => 'ogv',
				12 => '3gp',
				13 => '3g2'));

	// file details
	protected $fileName;
	protected $folderPath;
	protected $fullPath;
	protected $fileFormat;
	// return error if upload fails
	protected $errorMessage;
	// set uploading values
	protected $use_streams = false;
	protected $allow_unsafe = false;
	protected $safeFileOptions = array();

	public function uploadfile($target, $type)
	{
		// get the view values
		$view = $this->getViewID();
		if (in_array($target, $this->targets) && isset($this->types[$type]) && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			$this->target = (string) $target;
			$this->targetType = (string) $type;
			$this->formatType = (string) $this->types[$type];
			if ($package = $this->_getPackageFromUpload())
			{
				// now we move the file into place
				return $this->uploadNow($package, $view);
			}
			return array('error' => $this->errorMessage);
		}
		return array('error' => JText::_('COM_MEMBERSMANAGER_THERE_HAS_BEEN_AN_ERROR'));
	}

	protected function uploadNow($package, $view)
	{
		// set the package name to file name if found
		$name = $this->formatType;
		if (isset($package['packagename']))
		{
			$name = MembersmanagerHelper::safeString(str_replace('.'.$this->fileFormat, '', $package['packagename']), 'filename', '_', false);
		}
		$this->fileName = $this->target.'_'.$this->targetType.'_'.$this->fileFormat.'_'.MembersmanagerHelper::randomkey(20).'VDM'.$name;
		// set the folder path
		if ($this->formatType === 'document' || $this->formatType === 'media')
		{
			// get the folder path
			$this->folderPath = MembersmanagerHelper::getFolderPath('path', 'hiddenfilepath');
		}
		else
		{
			// get the file path
			$this->folderPath = MembersmanagerHelper::getFolderPath();
		}
		// set full path to the file
		$this->fullPath = $this->folderPath . $this->fileName . '.' . $this->fileFormat;
		// move to target folder
		if (JFile::move($package['dir'], $this->fullPath))
		{
			// do crop/resize if it is an image and cropping is set
			if ($this->formatType === 'image')
			{
				MembersmanagerHelper::resizeImage($this->fileName, $this->fileFormat, $this->target, $this->folderPath, $this->fullPath);
			}
			// Get the basic encription.
			$basickey = MembersmanagerHelper::getCryptKey('basic');
			$basic = null;
			// set link options
			$linkOptions = MembersmanagerHelper::getLinkOptions();
			// set link options
			if ($basickey)
			{
				// Get the encryption object.
				$basic = new FOFEncryptAes($basickey, 128);
			}
			// when it is documents we need to give file name in base64
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				// store the name
				$keyName = $this->fileName;
				if (MembersmanagerHelper::checkObject($basic))
				{
					// Get the encryption object.
					$localFile = MembersmanagerHelper::base64_urlencode($basic->encryptString($keyName));
				}
				else
				{
					// can not get the encryption object so only base64 encode
					$localFile = MembersmanagerHelper::base64_urlencode($keyName, true);
				}
			}
			// check if we must update the current item
			if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']))
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->targetType === 'image' || $this->targetType === 'document')
				{
					if ($linkOptions['lock'] && MembersmanagerHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($this->fileName);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $this->fileName;
					}
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					$this->fileName = $this->setFileNameArray('add', $basic, $view);
					if ($linkOptions['lock'] && MembersmanagerHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($this->fileName);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $this->fileName;
					}
					
				}
				JFactory::getDbo()->updateObject('#__membersmanager_'.$view['a_view'], $object, 'id');
			}
			elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
			{
				$this->fileName = array($this->fileName);
				$this->fileName =  '["'.implode('", "', $this->fileName).'"]';
			}
			// set the results
			$result = array('success' =>  $this->fileName, 'fileformat' => $this->fileFormat);
			// add some more values if document format type
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				$tokenLink = '';
				if ($linkOptions['lock'] == 0)
				{
					$localFile = MembersmanagerHelper::base64_urlencode($keyName, true);
				}
				if ($linkOptions['session'])
				{
					$tokenLink = '&token=' . JSession::getFormToken();
				}
				// if document
				if ($this->formatType === 'document')
				{
					$result['link'] = 'index.php?option=com_membersmanager&task=download.document&file=' . $localFile . $tokenLink;
				}
				// if media
				elseif ($this->formatType === 'media')
				{
					$result['link'] = 'index.php?option=com_membersmanager&task=download.media&file=' . $localFile . $tokenLink;
				}
				$result['key'] = $keyName;
			}
			return $result;
		}
		$this->remove($package['packagename']);
		return array('error' =>  JText::_('COM_MEMBERSMANAGER_THERE_HAS_BEEN_AN_ERROR'));
	}

	public function removeFile($oldFile, $target, $clearDB, $type)
	{
		// get view values
		$view = $this->getViewID();
		if (in_array($target, $this->targets) && isset($this->types[$type]) && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			$this->target = (string) $target;
			$this->targetType = (string) $type;
			$this->formatType = (string) $this->types[$type];
			$this->fileName = (string) $oldFile;
			if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']))
			{
				// get user to see if he has permission to upload
				$user = JFactory::getUser();
				if (!$user->authorise($view['a_view'].'.edit.'.$this->target.'_'.$this->targetType, 'com_membersmanager'))
				{
					return array('error' =>  JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_REMOVE_THIS_FILE'));
				}
			}
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				// get the file path
				$this->folderPath = MembersmanagerHelper::getFolderPath('path', 'hiddenfilepath');
			}
			else
			{
				// get the file path
				$this->folderPath = MembersmanagerHelper::getFolderPath();
			}
			// remove from the db if there is an id
			if ($clearDB == 1 && isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->targetType === 'image' || $this->targetType === 'document')
				{
					$object->{$this->target.'_'.$this->targetType} = '';
					JFactory::getDbo()->updateObject('#__membersmanager_'.$view['a_view'], $object, 'id');
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					// Get the basic encription.
					$basickey = MembersmanagerHelper::getCryptKey('basic');
					$basic = null;
					// set link options
					$linkOptions = MembersmanagerHelper::getLinkOptions();
					if ($linkOptions['lock'] && $basickey)
					{
						// Get the encryption object.
						$basic = new FOFEncryptAes($basickey, 128);
					}
					$fileNameArray = $this->setFileNameArray('remove', $basic, $view);
					if ($linkOptions['lock'] && MembersmanagerHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($fileNameArray);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $fileNameArray;
					}
					JFactory::getDbo()->updateObject('#__membersmanager_'.$view['a_view'], $object, 'id');
				}
			}
			// load the file class
			jimport('joomla.filesystem.file');
			// remove file with this filename
			$fileFormats = $this->formats[$this->formatType .'_formats'];
			foreach ($fileFormats as $fileFormat)
			{
				if (JFile::exists($this->folderPath . $this->fileName . '.' . $fileFormat))
				{
					// remove the file
					return JFile::delete($this->folderPath . $this->fileName . '.' . $fileFormat);
				}
			}
		}
		return array('error' => JText::_('COM_MEMBERSMANAGER_THERE_HAS_BEEN_AN_ERROR'));
	}

	protected function setFileNameArray($action, $basic, $view)
	{
		$curentFiles = MembersmanagerHelper::getVar($view['a_view'], $view['a_id'], 'id', $this->target.'_'.$this->targetType);
		// unlock if needed
		if ($basic && $curentFiles === base64_encode(base64_decode($curentFiles, true)))
		{
			// basic decrypt data banner_image.
			$curentFiles = rtrim($basic->decryptString($curentFiles), "\0");
		}
		// convert to array if needed
		if (MembersmanagerHelper::checkJson($curentFiles))
		{
			$curentFiles = json_decode($curentFiles, true);
		}
		// remove or add the file name
		if (MembersmanagerHelper::checkArray($curentFiles))
		{
			if ('add' === $action)
			{
				$curentFiles[] = $this->fileName;
			}
			else
			{
				if(($key = array_search($this->fileName, $curentFiles)) !== false)
				{
					unset($curentFiles[$key]);
				}
			}
		}
		elseif ('add' === $action)
		{
			$curentFiles = array($this->fileName);
		}
		else
		{
			$curentFiles = '';
		}
		// convert to json
		if (MembersmanagerHelper::checkArray($curentFiles))
		{
			return '["'.implode('", "', $curentFiles).'"]';
		}
		return '';
	}

	/**
	 * Works out an importation file from a HTTP upload
	 *
	 * @return file definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{		
		// Get the uploaded file information
		$app	= JFactory::getApplication();
		$input	= $app->input;

		// See JInputFiles::get.
		$userfiles = $input->files->get('files', null, 'array');
		
		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_WARNING_IMPORT_FILE_ERROR');
			return false;
		}

		// get the files from array
		$userfile = null;
		if (is_array($userfiles))
		{
			$userfile = array_values($userfiles)[0]; 
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_NO_IMPORT_FILE_SELECTED');
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_WARNING_IMPORT_UPLOAD_ERROR');
			return false;
		}

		// Build the appropriate paths
		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$p_file = JFile::upload($tmp_src, $tmp_dest, $this->use_streams, $this->allow_unsafe, $this->safeFileOptions);

		// Was the package downloaded?
		if (!$p_file)
		{
			$session = JFactory::getSession();
			$session->clear('package');
			$session->clear('dataType');
			$session->clear('hasPackage');
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_COULD_NOT_UPLOAD_THE_FILE');
			// was not uploaded
			return false;
		}

		// check that this is a valid file
		$package = $this->check($userfile['name']);

		return $package;
	}
	
	/**
	 * Check a file and verifies it as a allowed file format file
	 *
	 * @param   string  $archivename  The uploaded package filename or import directory
	 *
	 * @return  array  of elements
	 *
	 */
	protected function check($archivename)
	{
		// Clean the name
		$archivename = JPath::clean($archivename);
		// get file format
		$this->fileFormat = strtolower(pathinfo($archivename, PATHINFO_EXTENSION));
		// get fileFormat key
		$allowedFormats = array();
		if (in_array($this->fileFormat, $this->formats[$this->formatType .'_formats']))
		{
			// get allowed formats
			$allowedFormats = (array) $this->app_params->get($this->formatType.'_formats', null);
		}
		// check the extension
		if (!in_array($this->fileFormat, $allowedFormats))
		{
			// Cleanup the import files
			$this->remove($archivename);
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_DOES_NOT_HAVE_A_VALID_FILE_TYPE');
			return false;
		}

		// check permission if user
		$view = $this->getViewID();
		if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			// get user to see if he has permission to upload
			$user = JFactory::getUser();
			if (!$user->authorise($view['a_view'].'.edit.'.$this->target.'_'.$this->targetType, 'com_membersmanager'))
			{
				// Cleanup the import files
				$this->remove($archivename);
				$this->errorMessage = JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_UPLOAD_AN'.$this->targetType);
				return false;
			}
		}
		
		$config			= JFactory::getConfig();
		// set Package Name
		$check['packagename']	= $archivename;
		
		// set directory
		$check['dir']		= $config->get('tmp_path'). '/' .$archivename;
		
		return $check;
	}
	
	/**
	 * Clean up temporary uploaded file
	 *
	 * @param   string  $package    Name of the uploaded file
	 *
	 * @return  boolean  True on success
	 *
	 */
	protected function remove($package)
	{
		jimport('joomla.filesystem.file');
		
		$config		= JFactory::getConfig();
		$package	= $config->get('tmp_path'). '/' .$package;

		// Is the package file a valid file?
		if (is_file($package))
		{
			JFile::delete($package);
		}
		elseif (is_file(JPath::clean($package)))
		{
			// It might also be just a base filename
			JFile::delete(JPath::clean($package));
		}
	}


	protected $viewid = array();

	protected function getViewID($call = 'table')
	{
		if (!isset($this->viewid[$call]))
		{
			// get the vdm key
			$jinput = JFactory::getApplication()->input;
			$vdm = $jinput->get('vdm', null, 'WORD');
			if ($vdm) 
			{
				if ($view = MembersmanagerHelper::get($vdm))
				{
					$current = (array) explode('__', $view);
					if (MembersmanagerHelper::checkString($current[0]) && isset($current[1]) && is_numeric($current[1]))
					{
						// get the view name & id
						$this->viewid[$call] = array(
							'a_id' => (int) $current[1],
							'a_view' => $current[0]
						);
					}
				}
			}
		}
		if (isset($this->viewid[$call]))
		{
			return $this->viewid[$call];
		}
		return false;
	}

	public function checkUnique($field, $value)
	{
		if ($found = MembersmanagerHelper::getVar('organizer', $value, $field, 'id'))
		{
			$view = $this->getViewID();
			if (!isset($view['a_id']) || $found != $view['a_id'])
			{
				return true;
			}
		}
		return false;
	}

	public function getRegion($country)
	{
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName( array('a.id') ));
		$query->from($db->quoteName('#__membersmanager_region', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		// check for country and region
		$query->where($db->quoteName('a.country') . ' = '. (int) $country);
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadColumn();
		}
		return false;
	}

	public function getCreateUserFields($id)
	{
		$view = $this->getKey();
		$access = array(1 => 'member.access', 2 => 'other.access');
		if (1 == $id && isset($access[$view]) && JFactory::getUser()->authorise($access[$view], 'com_membersmanager'))
		{
			$fields = array();
			// start the block
			$fields[] = '<div id="user_info" >';

			// setup modal
			$name = "createUser";
			// load button
			$fields[] = '<div class="control-group">';
			$fields[] = '<div class="control-label"></div>';
			$fields[] = '<div class="controls"><a href="#modal-' . $name.'" data-toggle="modal" class="btn span3"><span class="icon-save-new"></span> '.JText::_('COM_MEMBERSMANAGER_CREATE_USER').'</a></div>';
			$fields[] = '</div>';

			$params = array();
			$params['title']  = JText::_("COM_MEMBERSMANAGER_CREATE_USER");
			$params['height'] = "500px";
			$params['width']  = "100%";

			// load modal
			$fields[] = JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params, $this->getCreateFields());

			// close the block
			$fields[] = '</div>';

			return implode("\n",$fields);
		}
		return false;
	}

	protected function getCreateFields()
	{
		// add dive to give padding
		$fields[] = '<div style="padding: 10px;">';
		// load name
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_NAME').'</label></div>';
		$fields[] = '<div class="controls"><input type="text" size="8" id="vdm_c_name" value="" placeholder="'.JText::_('COM_MEMBERSMANAGER_ADD_NAME').'"></div>';
		$fields[] = '</div>';

		// load username			
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_USERNAME').'</label></div>';
		$fields[] = '<div class="controls"><input type="text" size="8" id="vdm_c_username" value="" placeholder="'.JText::_('COM_MEMBERSMANAGER_ADD_USERNAME').'"></div>';
		$fields[] = '</div>';

		// load email		
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_EMAIL').'</label></div>';
		$fields[] = '<div class="controls"><input type="text" size="8" id="vdm_c_email" value="" placeholder="'.JText::_('COM_MEMBERSMANAGER_USERDOMAINCOM').'"></div>';
		$fields[] = '</div>';

		// load password field		
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_PASSWORD').'</label></div>';
		$fields[] = '<div class="controls"><input type="password" size="9" autocomplete="off" id="vdm_c_password" value="" aria-invalid="false"></div>';
		$fields[] = '</div>';

		$fields[] = '<div><small>'.JText::_('COM_MEMBERSMANAGER_USER_DETAILS_WILL_BE_EMAILED_DURING_CREATION_OF_THE_USER_ACCOUNT').'</small></div>';
		// close the div
		$fields[] = '</div>';

		$fields[] = '<div class="modal-footer">';
		$fields[] = '<button type="button" class="btn btn-default" data-dismiss="modal">'.JText::_('COM_MEMBERSMANAGER_CLOSE').'</button>';
		$fields[] = '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="createUser();"><span class="icon-save-new icon-white"></span> '.JText::_('COM_MEMBERSMANAGER_CREATE_USER').'</button>';
		$fields[] = '</div>';

		return implode("\n",$fields);
	}

	public function createUser($data)
	{
		$view = $this->getKey();
		$access = array(1 => 'member.edit.user', 2 => 'other.edit.user');
		if (isset($access[$view]) && JFactory::getUser()->authorise($access[$view], 'com_membersmanager'))
		{
			$data = json_decode($data, true);
			if (MembersmanagerHelper::checkArray($data))
			{
				$groups = array(1 => 'memberuser', 2 => 'otheruser');
				$keys = array('var' => 'name', 'uvar' => 'username', 'evar' => 'email', 'dvar' => 'password');
				$bucket = array();
				foreach($data as $key => $value)
				{
					if (isset($keys[$key]) && MembersmanagerHelper::checkString($value))
					{
						$bucket[$keys[$key]] = (string) $value;
						if ($keys[$key] == 'password')
						{
							$bucket['password2'] = (string) $value;
						}
					}
				}
				if (MembersmanagerHelper::checkArray($bucket) && count($bucket) == 5)
				{
					// now update user
					$returned = MembersmanagerHelper::createUser($bucket);
					if (is_numeric($returned))
					{
						if ((int) $returned > 0 && isset($groups[$view]))
						{
							$groups = $this->app_params->get($groups[$view], null);
							if ($groups)
							{
								// update the user groups
								JUserHelper::setUserGroups((int)$returned ,(array)$groups);
							}
						}
						$message = array();
						$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
						$message[] = '<div class="alert alert-success">';
						$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_SUCCESS').'</h4>';
						$message[] = '<div class="alert-message">'.JText::_('COM_MEMBERSMANAGER_USER_WAS_CREATED_SUCCESSFULLY_AND_THE_LOGIN_DETAILS_WAS_EMAILED_TO_THE_USER').'</div>';
						$message[] = '</div>';

						$notice = array();
						$notice[] = '<div id="user_info" ><button class="close" data-dismiss="alert" type="button">×</button>';
						$notice[] = '<div class="alert alert-success">';
						$notice[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_READY_TO_SELECT').'</h4>';
						$notice[] = '<div class="alert-message">'.JText::sprintf('COM_MEMBERSMANAGER_YOU_CAN_NOW_SELECT_BSB_THAT_YOU_JUST_CREATED_FROM_THE_USERS_LIST_IN_THE_ABOVE_FIELD_SIMPLY_CLICK_ON_THE_BLUE_USER_ICON', $bucket['name']).'</div>';
						$notice[] = '</div></div>';
						return array( 'html' => implode("\n",$notice), 'success' => implode("\n",$message));
					}
					else
					{
						$message = array();
						$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
						$message[] = '<div class="alert alert-error">';
						$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_ERROR_USER_NOT_CREATED').'</h4>';
						$message[] = '<div class="alert-message">'.$returned.'</div>';
						$message[] = '</div>';
						return array('error' => implode("\n",$message));
					}
				}
				else
				{
					$message = array();
					$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
					$message[] = '<div class="alert alert-error">';
					$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_ERROR_USER_NOT_UPDATED').'</h4>';
					$message[] = '<div class="alert-message">'.JText::_('COM_MEMBERSMANAGER_SOME_REQUIRED_VALUES_ARE_MISSING').'.</div>';
					$message[] = '</div>';
					return array('error' => implode("\n",$message));
				}
			}
		}
		return false;
	}

	public function getUser($id)
	{
		$user = JFactory::getUser($id);
		if ($user->id)
		{
			$fields = array();
			// start the block
			$fields[] = '<div id="user_info" >';

			$fields[] = $this->getUserFields($user);

			$view = $this->getKey();
			$access = array(1 => 'member.access', 2 => 'other.access');
			if (isset($access[$view]) && JFactory::getUser()->authorise($access[$view], 'com_membersmanager'))
			{
				// setup modal
				$name = "editUser";
				// load button
				$fields[] = '<div class="control-group">';
				$fields[] = '<div class="control-label"></div>';
				$fields[] = '<div class="controls"><a href="#modal-' . $name.'" data-toggle="modal" class="btn span3"><span class="icon-edit"></span> '.JText::_('COM_MEMBERSMANAGER_EDIT').'</a></div>';
				$fields[] = '</div>';

				$params = array();
				$params['title']  = "Edit User";
				$params['height'] = "500px";
				$params['width']  = "100%";
			
				// load modal
				$fields[] = JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params, $this->getUserFields($user, true));
			}

			// close the block
			$fields[] = '</div>';

			return implode("\n",$fields);
		}
		return false;
	}

	protected function getUserFields(&$user, $permission = false)
	{
		// set read only
		$readOnly = ' readonly="" class="readonly"';

		if($permission)
		{
			// add dive to give padding
			$fields[] = '<div style="padding: 10px;">';

			$readOnly = ' id="vdm_name"';

			// load name			
			$fields[] = '<div class="control-group">';
			$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_NAME').'</label></div>';
			$fields[] = '<div class="controls"><input type="text" size="8"'.$readOnly.' value="'.$user->name.'"></div>';
			$fields[] = '</div>';

			$readOnly = ' id="vdm_username"';
		}

		// load username			
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_USERNAME').'</label></div>';
		$fields[] = '<div class="controls"><input type="text" size="8"'.$readOnly.' value="'.$user->username.'"></div>';
		$fields[] = '</div>';

		if($permission)
		{
			$readOnly = ' id="vdm_email"';
		}	

		// load email		
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_EMAIL').'</label></div>';
		$fields[] = '<div class="controls"><input type="text" size="8"'.$readOnly.' value="'.$user->email.'"></div>';
		$fields[] = '</div>';

		if($permission)
		{
			$readOnly = ' id="vdm_password"';
			$password = $user->password;
		}
		else
		{
			$password = 'XXXXXXXXXXXXXXXXXX';
		}

		// load password field		
		$fields[] = '<div class="control-group">';
		$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_PASSWORD').'</label></div>';
		$fields[] = '<div class="controls"><input type="password" size="9" autocomplete="off"'.$readOnly.' value="'.$password.'" aria-invalid="false"></div>';
		$fields[] = '</div>';

		if($permission)
		{
			// close padding div
			$fields[] = '</div>';
			$fields[] = '<div class="modal-footer">';
			$fields[] = '<button type="button" class="btn btn-default" data-dismiss="modal">'.JText::_('COM_MEMBERSMANAGER_CLOSE').'</button>';
			$fields[] = '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="setUser();"><span class="icon-apply icon-white"></span> '.JText::_('COM_MEMBERSMANAGER_SAVE_USER_DETAILS').'</button>';
			$fields[] = '</div>';
		}

		if(!$permission)
		{
			// Registration Date
			$fields[] = '<div class="control-group">';
			$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_REGISTRATION_DATE').'</label></div>';
			$fields[] = '<div class="controls"><input type="text" size="8"'.$readOnly.' value="'.$user->registerDate.'"></div>';
			$fields[] = '</div>';

			// Last Visit Date
			$fields[] = '<div class="control-group">';
			$fields[] = '<div class="control-label"><label title="">'.JText::_('COM_MEMBERSMANAGER_LAST_VISIT_DATE').'</label></div>';
			$fields[] = '<div class="controls"><input type="text" size="8"'.$readOnly.' value="'.$user->lastvisitDate.'"></div>';
			$fields[] = '</div>';
		}
		return implode("\n",$fields);
	}

	public function setUser($id, $data)
	{
		$view = $this->getKey();
		$access = array(1 => 'member.edit.own', 2 => 'other.edit.own');
		$my = JFactory::getUser();
		if (isset($access[$view]) && $my->authorise($access[$view], 'com_membersmanager'))
		{
			$data = json_decode($data, true);
			if (MembersmanagerHelper::checkArray($data))
			{
				$keys = array('var' => 'name', 'uvar' => 'username', 'evar' => 'email', 'dvar' => 'password');
				$bucket = array();
				$bucket['id'] = $id;
				foreach($data as $key => $value)
				{
					if (isset($keys[$key]) && MembersmanagerHelper::checkString($value))
					{
						$bucket[$keys[$key]] = (string) $value;
						if ($keys[$key] == 'password')
						{
							$bucket['password2'] = (string) $value;
						}
					}
				}
				if (MembersmanagerHelper::checkArray($bucket) && count($bucket) == 6)
				{
					// check if current user is a supper admin
					$iAmSuperAdmin = $my->authorise('core.admin');
					if ($iAmSuperAdmin)
					{
						// add the user current groups
						$bucket['groups'] = JAccess::getGroupsByUser($id);
					}
					// now update user
					$done = MembersmanagerHelper::updateUser($bucket);
					if ($done == $id)
					{
						$message = array();
						$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
						$message[] = '<div class="alert alert-success">';
						$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_SUCCESS').'</h4>';
						$message[] = '<div class="alert-message">'.JText::_('COM_MEMBERSMANAGER_USER_WAS_UPDATED_SUCCESSFULLY').'.</div>';
						$message[] = '</div>';
						return array( 'html' => $this->getUser($id), 'success' => implode("\n",$message));
					}
					else
					{
						$message = array();
						$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
						$message[] = '<div class="alert alert-error">';
						$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_ERROR_USER_NOT_UPDATED').'</h4>';
						$message[] = '<div class="alert-message">'.$done.'</div>';
						$message[] = '</div>';
						return array('error' => implode("\n",$message));
					}
				}
				else
				{
					$message = array();
					$message[] = '<button class="close" data-dismiss="alert" type="button">×</button>';
					$message[] = '<div class="alert alert-error">';
					$message[] = '<h4 class="alert-heading">'.JText::_('COM_MEMBERSMANAGER_ERROR_USER_NOT_UPDATED').'</h4>';
					$message[] = '<div class="alert-message">'.JText::_('COM_MEMBERSMANAGER_SOME_REQUIRED_VALUES_ARE_MISSING').'.</div>';
					$message[] = '</div>';
					return array('error' => implode("\n",$message));
				}
			}
		}
		return false;
	}

	protected function getKey()
	{
		$viewData = $this->getViewID();
		$view = 0;
		if (isset($viewData['a_view']))
		{
			switch($viewData['a_view'])
			{
				case 'member':
					return 1;
				break;
				case 'other' :
					return 2;
				break;
			}
		}
		return 0;
	}

}
