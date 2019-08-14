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
		$this->app_params = JComponentHelper::getParams('com_membersmanager');

	}

	// Used in member
	// allowed views
	protected $allowedViews = array('member');

	// allowed targets
	protected $targets = array('profile'); 

	// allowed types
	protected $types = array('image' => 'image');

	public function getUserDetails($user)
	{
		//first we check if this is an allowed query
		$view = $this->getViewID();
		if (isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			// since the connected member user can not be changed, check if this member has a user value set
			if (isset($view['a_id']) && $view['a_id'] > 0 && ($alreadyUser = MembersmanagerHelper::getVar($view['a_view'], $view['a_id'], 'id', 'user')) !== false && is_numeric($alreadyUser) && $alreadyUser > 0 && $user != $alreadyUser)
			{
				return false;
			}
			// return user details
			$user = JFactory::getUser($user);
			return array(
				'name' => $user->name,
				'username' => $user->username,
				'useremail' => $user->email
				);
		}
		return false;
	}

	// get placeholder header if available
	public function getPlaceHolderHeaders($component)
	{
		if ('com_membersmanager' === $component || 'com_corecomponent' === $component)
		{
			// just return the core placeholders
			return JText::_('COM_MEMBERSMANAGER_CORE_PLACEHOLDERS');
		}
		return MembersmanagerHelper::getComponentName($component);
	}

	// get chart image link
	public function getChartImageLink($image)
	{
		$view = $this->getViewID();
		// make sure we are in the (allowed) view
		if (isset($view['a_view']) && ($view['a_view'] === 'compose' || $view['a_view'] === 'profile'))
		{
			// build image name
			$imageName =  md5($image . 'jnst_f0r_dumm!es');
			// build image data
			$image =  explode('base64,', $image); unset($image[0]); $image = str_replace(' ', '+', implode('', $image));
			// validate Base64
			if (($image = MembersmanagerHelper::openValidBase64($image, null, false)) !== false)
			{
				// validate just png (for now)
				$png_binary_check = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";
				if (substr($image, 0, strlen($png_binary_check)) === $png_binary_check)
				{
					// build image path
					$imagepath = MembersmanagerHelper::getFolderPath('path', 'chartpath') . $imageName . '.png';
					// now write the file if not exists
					if (file_exists($imagepath) || MembersmanagerHelper::writeFile($imagepath, $image))
					{
						// build and return image link
						return array('link' => MembersmanagerHelper::getFolderPath('url', 'chartpath') . $imageName . '.png');
					}
				}
			}
		}
		return false;
	}

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
			// Get the basic encryption.
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
		$app = JFactory::getApplication();
		$input = $app->input;

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
		$config = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src = $userfile['tmp_name'];

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
		
		$config = JFactory::getConfig();
		// set Package Name
		$check['packagename'] = $archivename;
		
		// set directory
		$check['dir'] = $config->get('tmp_path'). '/' .$archivename;
		
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
		
		$config = JFactory::getConfig();
		$package = $config->get('tmp_path'). '/' .$package;

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
				// set view and id
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
				// set return if found
				if ($return = MembersmanagerHelper::get($vdm . '__return'))
				{
					if (MembersmanagerHelper::checkString($return))
					{
						$this->viewid[$call]['a_return'] = $return;
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
		// split at upper case
		$valueArray = (array) preg_split('/(?=[A-Z])/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
		// make string safe
		$value = MembersmanagerHelper::safeString(trim(implode(' ', $valueArray), '-'), 'L', '-', false, false);
		// get table and current ID
		$view = $this->getViewID();
		// check if it is unique
		if (MembersmanagerHelper::checkUnique($view['a_id'], $field, $value, $view['a_view']))
		{
			return array (
				'value' => $value,
				'message' => JText::sprintf('COM_MEMBERSMANAGER_GREAT_SS_IS_AVAILABLE', $field, $value),
				'status' => 'success');
		}
		return array (
			'message' => JText::sprintf('COM_MEMBERSMANAGER_BSB_IS_ALREADY_IN_USE_PLEASE_TRY_ANOTHER', $value),
			'status' => 'danger');
	}


	/**
	 * get any placeholder
	 *
	 * @param   string  $getType    Name get type
	 *
	 * @return  string  The html string of placeholders
	 *
	 */
	public function getAnyPlaceHolders($getType)
	{
		// check if we should add a header
		if (method_exists(__CLASS__, 'getPlaceHolderHeaders') && ($string = $this->getPlaceHolderHeaders($getType)) !== false)
		{
			$string = JText::_($string) . ' ';
			$header = '<h4>' . $string . '</h4>';
		}
		else
		{
			$string = '';
			$header = '';
		}
		// get the core component helper class & get placeholders
		if (($helperClass = MembersmanagerHelper::getHelperClass('membersmanager')) !== false &&  ($placeholders = $helperClass::getAnyPlaceHolders($getType)) !== false)
		{
			return '<div>' . $header . '<code style="display: inline-block; padding: 2px; margin: 3px;">' .
				implode('</code> <code style="display: inline-block; padding: 2px; margin: 3px;">', $placeholders) .
				'</code></div>';
		}
		// not found
		return '<div class="alert alert-error"><h4 class="alert-heading">' .
			$string . JText::_('COM_MEMBERSMANAGER_PLACEHOLDERS_NOT_FOUND') .
			'!</h4><div class="alert-message">' .
			JText::_('COM_MEMBERSMANAGER_THERE_WAS_AN_ERROR_PLEASE_TRY_AGAIN_LATER_IF_THIS_ERROR_CONTINUES_CONTACT_YOUR_SYSTEM_ADMINISTRATOR') .
			'</div></div>';
	}


	/**
	 * get the placeholder
	 *
	 * @param   string  $getType    Name get type
	 *
	 * @return  string  The html string of placeholders
	 *
	 */
	public function getPlaceHolders($getType)
	{
		// check if we should add a header
		if (method_exists(__CLASS__, 'getPlaceHolderHeaders') && ($string = $this->getPlaceHolderHeaders($getType)) !== false)
		{
			$string = JText::_($string) . ' ';
			$header = '<h4>' . $string . '</h4>';
		}
		else
		{
			$string = '';
			$header = '';
		}
		// get placeholders
		if ($placeholders = MembersmanagerHelper::getPlaceHolders($getType))
		{
			return '<div>' . $header . '<code style="display: inline-block; padding: 2px; margin: 3px;">' .
				implode('</code> <code style="display: inline-block; padding: 2px; margin: 3px;">', $placeholders) .
				'</code></div>';
		}
		// not found
		return '<div class="alert alert-error"><h4 class="alert-heading">' .
			$string . JText::_('COM_MEMBERSMANAGER_PLACEHOLDERS_NOT_FOUND') .
			'!</h4><div class="alert-message">' .
			JText::_('COM_MEMBERSMANAGER_THERE_WAS_AN_ERROR_PLEASE_TRY_AGAIN_LATER_IF_THIS_ERROR_CONTINUES_CONTACT_YOUR_SYSTEM_ADMINISTRATOR') .
			'</div></div>';
	}


	// Used in cpanel
	public function searchMembers($search)
	{
		// get user
		$user = JFactory::getUser();
		// get the session details
		$view = $this->getViewID();
		if (isset($view['a_id']) && $view['a_id'] > 0 && $user->id == $view['a_id'])
		{
			// start building the result set
			$result = array();
			// get the members
			$members = $this->getMembers($search, $user);
			// make sure we have some member values
			if (MembersmanagerHelper::checkArray($members))
			{
				$result[] = '<ul class="uk-list uk-list-striped">';
				$result[] = '<li>' . implode('</li><li>', $members) . '</li>';
				$result[] = '</ul>';
			}
			else
			{
				$result[] = '<p>' . JText::_('COM_MEMBERSMANAGER_NO_MEMBER_WAS_FOUND') . '...</p>';
			}
			return implode("\n", $result);
		}
		return false;
	}

	protected function getMembers($search, $user)
	{
		// get members from DB
		$this->searching($search, $user, $members, 1);
		// make sure we have members
		if (MembersmanagerHelper::checkArray($members))
		{
			// only sort if not email search
			if (strpos($search, '@') === false)
			{
				// little sorter based on name
				usort($members, function ($a, $b) use($search) {
					// first check if this is a name with space (basically hen name and surname is in one field = name)
					$length = strlen($search);
					$evalue = substr($a->name, 0, $length) == $search;
					$fvalue = substr($b->name, 0, $length) == $search;
					// if not found
					if (!$evalue && !$fvalue)
					{
						// best break up the string and search for each word
						$searchArray = (array) preg_split('/\s+/', $search);
						$name = array_shift($searchArray);
						$length = strlen($name);
						$avalue = substr($a->name, 0, $length) == $name;
						$bvalue = substr($b->name, 0, $length) == $name;
						// if the name is the same, use the surname (works only if second string is surname)
						if ($avalue == $bvalue)
						{
							if (MembersmanagerHelper::checkArray($searchArray))
							{
								// make sure we have surnames
								if (MembersmanagerHelper::checkString($a->surname) && MembersmanagerHelper::checkString($b->surname) )
								{
									$surname = implode(' ', $searchArray);
									$length = strlen($surname);
									$cvalue = substr($a->surname, 0, $length) == $surname;
									$dvalue = substr($b->surname, 0, $length) == $surname;
									// if the surname is the same
									if ($cvalue == $dvalue) return strcmp($a->surname, $b->surname);
									/*else*/ 
									if ($cvalue) return -1;
									if ($dvalue) return 1;
								}
								// try for longer name
								else
								{
									$name = implode(' ', $searchArray);
									$length = strlen($name);
									$cvalue = substr($a->name, 0, $length) == $name;
									$dvalue = substr($b->name, 0, $length) == $name;
									// if the surname is the same
									if ($cvalue == $dvalue) return strcmp($a->name, $b->name);
									/*else*/ 
									if ($cvalue) return -1;
									if ($dvalue) return 1;
								}
							}
							return strcmp($a->name, $b->name);
						}
						/*else*/ 
						if ($avalue) return -1;
						if ($bvalue) return 1;
					}
					else
					{
						/*else*/ 
						if ($evalue) return -1;
						if ($fvalue) return 1;
					}
				});
			}
			return (array) array_map( function ($member){
				// build the details
				$slug = (isset($member->token)) ? $member->id . ':' . $member->token : $member->id;
				$profile_uri = MembersmanagerHelperRoute::getProfileRoute($slug);
				$profile_link = JRoute::_($profile_uri);
				$name = (isset($member->user_name) && MembersmanagerHelper::checkString($member->user_name)) ? $member->user_name : $member->name;
				$surname = (isset($member->surname) && MembersmanagerHelper::checkString($member->surname)) ? ' ' . $member->surname : '';
				$email = (isset($member->user_email) && MembersmanagerHelper::checkString($member->user_email)) ? $member->user_email : $member->email;
				// build the link to the member
				return '<a href="' . $profile_link . '" title="' . JText::_('COM_MEMBERSMANAGER_OPEN') . ' ' . $name . $surname . ' ' . JText::_('COM_MEMBERSMANAGER_PROFILE') . '" >' . $name . $surname . ' ' . $email . ' (' . $member->token . ')</a> ' . 
					MembersmanagerHelper::getEditButton($member, 'member', 'members', '&return=' . urlencode(base64_encode($profile_uri)), 'com_membersmanager', null);
			}, $members);
		}
		return false;
	}

	protected function searching($search, $user, &$members, $action)
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select($db->quoteName(array('a.id','a.name','a.email','a.token','a.type','a.surname'),array('id','name','email','token','type','surname')));

		// From the membersmanager_item table
		$query->from($db->quoteName('#__membersmanager_member', 'a'));

		// From the users table.
		$query->select($db->quoteName(array('g.name','g.email'),array('user_name','user_email')));
		$query->join('LEFT', $db->quoteName('#__users', 'g') . ' ON (' . $db->quoteName('a.user') . ' = ' . $db->quoteName('g.id') . ')');

		// Filter by published state always (for now)
		$query->where('a.published = 1');

		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_membersmanager'))
		{
			// Join over the asset groups.
			$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// if we aready have members, stop them from being queried again
		if (MembersmanagerHelper::checkArray($members))
		{
			$ids = (array) array_map( function ($member){
				// return idies
				return $member->id;
			}, $members);
			// remove the members already loaded
			$query->where('a.id NOT IN (' . implode(',', $ids) . ')');
		}
		// check if they are searching for an id
		if (stripos($search, 'id:') === 0)
		{
			$query->where('a.id = ' . (int) substr($search, 3));
		}
		else
		{
			// best break up the string and search for each word
			$searchArray = (array) preg_split('/\s+/', $search);
			// start search bucket
			$searchBucket = array();
			// try to get it only
			if (1 == $action)
			{
				if (strpos($search, '@') !== false)
				{
					foreach ($searchArray as $i => $searchString)
					{
						$searchReady = $db->quote('%' . $db->escape($searchString) . '%');
						// only if string hints to be an email
						if (strpos($searchString, '@') !== false)
						{
							$searchBucket[] = 'g.email LIKE ' . $searchReady;
							$searchBucket[] = 'a.email LIKE ' . $searchReady;
							// remove from array since it was an email
							unset($searchArray[$i]);
							break;
						}
					}
				}
				// not email with more then one word
				if (count($searchArray) >= 2)
				{
					// get the name
					$searchBucket[] = 'a.name LIKE ' . $db->quote('%' . $db->escape(array_shift($searchArray)) . '%');
					$searchBucket[] = 'a.surname LIKE ' . $db->quote('%' . $db->escape( implode(' ', $searchArray) ) . '%');
				}
				// one word, so 
				else
				{
					$searchReady = $db->quote('%' . $db->escape($search) . '%');
					$searchBucket[] = 'a.name LIKE ' . $searchReady;
					$searchBucket[] = 'a.surname LIKE ' . $searchReady;
				}
			}
			elseif (2 == $action)
			{
				if (strpos($search, '@') !== false)
				{
					foreach ($searchArray as $i => $searchString)
					{
						// only if string hints to be an email
						if (strpos($searchString, '@') !== false)
						{
							// remove from array since it was an email
							unset($searchArray[$i]);
							break;
						}
					}
				}
				// not email with more then one word
				if (count($searchArray) >= 2)
				{
					// get the name
					$searchBucket[] = 'g.name LIKE ' . $db->quote('%' . $db->escape(array_shift($searchArray)) . '%');
					$searchBucket[] = 'a.surname LIKE ' . $db->quote('%' . $db->escape( implode(' ', $searchArray) ) . '%');
				}
				// one word, so 
				else
				{
					$searchReady = $db->quote('%' . $db->escape($search) . '%');
					$searchBucket[] = 'g.name LIKE ' . $searchReady;
					$searchBucket[] = 'a.surname LIKE ' . $searchReady;
				}
			}
			elseif (3 == $action)
			{
				if (strpos($search, '@') !== false)
				{
					foreach ($searchArray as $i => $searchString)
					{
						// only if string hints to be an email
						if (strpos($searchString, '@') !== false)
						{
							// remove from array since it was an email
							unset($searchArray[$i]);
							break;
						}
					}
				}
				// not email with more then one word
				if (count($searchArray) >= 2)
				{
					// get by token
					$searchBucket[] = 'a.token LIKE ' . $db->quote('%' . $db->escape( implode(' ', $searchArray) ) . '%');
				}
				// one word, so 
				else
				{
					$searchReady = $db->quote('%' . $db->escape($search) . '%');
					$searchBucket[] = 'a.token LIKE ' . $searchReady;
				}
			}
			else
			{
				if (count($searchArray) > 1)
				{
					foreach ($searchArray as $i => $searchString)
					{
						$searchReady = $db->quote('%' . $db->escape($searchString) . '%');
						// only if string hints to be an email
						if (strpos($searchString, '@') !== false)
						{
							$searchBucket[] = 'g.email LIKE ' . $searchReady;
							$searchBucket[] = 'a.email LIKE ' . $searchReady;
							// remove from array since it was an email
							unset($searchArray[$i]);
						}
						// only if string is longer then 3 characters
						elseif (strlen($searchString) > 3)
						{
							$searchBucket[] = 'a.name LIKE ' . $searchReady;
							$searchBucket[] = 'a.token LIKE ' . $searchReady;
							$searchBucket[] = 'a.surname LIKE ' . $searchReady;
							$searchBucket[] = 'g.name LIKE ' . $searchReady;
							// if this is the first string in array then remove since it is probably the name
							if ($i == 0)
							{
								unset($searchArray[$i]);
							}
						}
					}
					// load to surname as a whole
					if (MembersmanagerHelper::checkArray($searchArray))
					{
						$searchBucket[] = 'a.surname LIKE ' . $db->quote('%' . $db->escape( implode(' ', $searchArray) ) . '%');
					}
				}
				else
				{
					// the basic search
					$searchString = $db->quote('%' . $db->escape($search) . '%');
					$searchBucket[] = 'g.name LIKE ' . $searchString;
					$searchBucket[] = 'g.username LIKE ' . $searchString;
					// only if string hints to be an email
					if (strpos($searchString, '@') !== false)
					{
						$searchBucket[] = 'g.email LIKE ' . $searchString;
						$searchBucket[] = 'a.email LIKE ' . $searchString;
					}
					$searchBucket[] = 'a.name LIKE ' . $searchString;
					$searchBucket[] = 'a.surname LIKE ' . $searchString;
					$searchBucket[] = 'a.token LIKE ' . $searchString;
				}
			}
			// load the search bucket
			$query->where('('. implode(' OR ', $searchBucket) . ')');
		}
		// set query (to only get last 10)
		$db->setQuery($query, 0, 10);
		// get the members
		$_members = $db->loadObjectList();
		// did we get any
		if (MembersmanagerHelper::checkArray($_members) && ($_members = $this->permissionFilter($_members, $user)) !== false)
		{
			// merger the data
			$members = MembersmanagerHelper::mergeArrays(array($_members, $members));
			// if we have 8 or more we return
			if (count($members) >= 18)
			{
				return true;
			}
		}
		// increment
		$action++;
		// try again
		if ($action <= 4 )
		{
			$this->searching($search, $user, $members, $action);
		}
		return false;
	}

	protected function permissionFilter($members, &$user)
	{
		// if system admin, return all found
		if ($user->authorise('core.options', 'com_membersmanager'))
		{
			return $members;
		}
		// filter by access type
		$type_access = MembersmanagerHelper::getAccess($user);
		// filter to only these access types
		if (MembersmanagerHelper::checkArray($type_access))
		{
			// our little function to check if two arrays intersect on values
			$intersect = function ($a, $b) { $b = array_flip($b); foreach ($a as $v) { if (isset($b[$v])) return true; } return false; };
			// the new bucket
			$member_bucket = array();
			foreach ($members as $member)
			{
				// convert type json to array
				if (MembersmanagerHelper::checkJson($member->type))
				{
					$member->type = json_decode($member->type, true);
				}
				// convert type int to array
				if (is_numeric($member->type) && $member->type > 0)
				{
					$member->type = array($member->type);
				}
				// now check intersection
				if (MembersmanagerHelper::checkArray($member->type) && $intersect($member->type, $type_access))
				{
					$member_bucket[] = $member;
				}
			}
			// did we get any
			if (MembersmanagerHelper::checkArray($member_bucket))
			{
				return $member_bucket;
			}
		}
		return false;
	}


	// Used in profile
	public function getReport($key)
	{
		// first we check if this is an allowed query
		$view = $this->getViewID();
		if (isset($view['a_view']) && 'profile' === $view['a_view'])
		{
			// unlock the request
			if (($check = MembersmanagerHelper::unlock($key)) !== false &&
				MembersmanagerHelper::checkArray($check) &&
				isset($check['id']) && $check['id'] > 0 &&
				isset($check['element']) && MembersmanagerHelper::checkString($check['element']) &&
				isset($check['type']) && $check['type'] > 0 &&
				isset($check['account']) && $check['account'] > 0)
			{
				// now check if this component is active to this member
				if (($html = MembersmanagerHelper::getReport($check['id'], $check['element'])) !== false && MembersmanagerHelper::checkString($html))
				{
					return array('html' => $html);
				}
			}
		}
		return false;
	}

	public function getListMessages($key)
	{
		// first we check if this is an allowed query
		$view = $this->getViewID();
		if (isset($view['a_view']) && 'profile' === $view['a_view'])
		{
			// unlock the request
			if (($check = MembersmanagerHelper::unlock($key)) !== false &&
				MembersmanagerHelper::checkArray($check) &&
				isset($check['id']) && $check['id'] > 0 &&
				isset($check['return']) && MembersmanagerHelper::checkString($check['return']))
			{
				// now check if this component is active to this member
				if (($html = MembersmanagerHelper::communicate('list_messages', $check['id'], $check['return'])) !== false && MembersmanagerHelper::checkString($html))
				{
					return array('html' => $html);
				}
			}
		}
		return false;
	}
}
