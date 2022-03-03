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
					// check if the file exist
					if (file_exists($imagepath))
					{
						// build and return image link
						return array('link' => MembersmanagerHelper::getFolderPath('url', 'chartpath') . $imageName . '.png');
					}
					// now write the file if not exists
					if (MembersmanagerHelper::writeFile($imagepath, $image))
					{
						// now check if this type of image is allowed to be uploaded
						if (JHelperMedia::canUpload($imagepath, 'com_membersmanager'))
						{
							// build and return image link
							return array('link' => MembersmanagerHelper::getFolderPath('url', 'chartpath') . $imageName . '.png');
						}
						// load the file class
						jimport('joomla.filesystem.file');
						// remove the image
						JFile::delete($imagepath);
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
			if (($package = $this->_getPackageFromUpload()) !== false)
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
		$this->fileName = $this->target . '_' . $this->targetType . '_' . $this->fileFormat . '_' . MembersmanagerHelper::randomkey(20) . 'VDM' . $name;
		// set the folder path
		if ($this->formatType === 'file' || $this->formatType === 'document' || $this->formatType === 'media')
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
			$encryption = null;
			$expertmode = false;
			// basic encryption of these format types
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				// Get the basic encryption.
				$encryptionkey = MembersmanagerHelper::getCryptKey('basic');
			}
			// medium encryption of these format types
			elseif ($this->formatType === 'file')
			{
				// check if we have expert Mode
				if (method_exists('MembersmanagerHelper', 'encrypt'))
				{
					$expertmode = true;
				}
				else
				{
					// Get the medium encryption.
					$encryptionkey = MembersmanagerHelper::getCryptKey('medium');
				}
			}
			// set link options
			if (isset($encryptionkey) && $encryptionkey)
			{
				// Get the encryption object.
				$encryption = new FOFEncryptAes($encryptionkey, 128);
			}
			// when it is documents we need to give file name in base64
			if ($this->formatType === 'file' || $this->formatType === 'document' || $this->formatType === 'media')
			{
				// store the name
				$keyName = $this->fileName;
				if (MembersmanagerHelper::checkObject($encryption) || $expertmode)
				{
					// also encrypt the actual content of the file
					if ($this->formatType === 'file')
					{
						// add notice to name that file is encrypted
						$this->fileName = $keyName =  '.' . $this->fileName;
						$securefullPath = $this->folderPath . $this->fileName;
						// also encrypt the actual content of the file
						if ($expertmode)
						{
							MembersmanagerHelper::writeFile($securefullPath, wordwrap(MembersmanagerHelper::encrypt(file_get_contents($this->fullPath)), 128, "\n", true));
						}
						else
						{
							MembersmanagerHelper::writeFile($securefullPath, wordwrap($encryption->encryptString(file_get_contents($this->fullPath)), 128, "\n", true));
						}
						// remove the original
						jimport('joomla.filesystem.file');
						JFile::delete($this->fullPath);
					}
					// Get the encryption object.
					if ($expertmode)
					{
						$localFile = MembersmanagerHelper::base64_urlencode(MembersmanagerHelper::encrypt($keyName, false), true);
					}
					else
					{
						$localFile = MembersmanagerHelper::base64_urlencode($encryption->encryptString($keyName));
					}
				}
				else
				{
					// can not get the encryption object so only base64 encode
					$localFile = MembersmanagerHelper::base64_urlencode($keyName, true);
				}
			}
			// check if we must update the current item
			if (isset($view['a_id']) && $view['a_id'] > 0)
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->formatType === 'file' || $this->targetType === 'image' || $this->targetType === 'document')
				{
					if (MembersmanagerHelper::checkObject($encryption) || $expertmode)
					{
						// Get the encryption object.
						if ($expertmode)
						{
							$object->{$this->target . '_' . $this->targetType} = MembersmanagerHelper::encrypt($this->fileName);
						}
						else
						{
							$object->{$this->target . '_' . $this->targetType} = $encryption->encryptString($this->fileName);
						}
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target . '_' . $this->targetType} = $this->fileName;
					}
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					$this->fileName = $this->setFileNameArray('add', $encryption, $view);
					if (MembersmanagerHelper::checkObject($encryption))
					{
						// Get the encryption object.
						$object->{$this->target . '_' . $this->targetType} = $encryption->encryptString($this->fileName);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target . '_' . $this->targetType} = $this->fileName;
					}
				}
				JFactory::getDbo()->updateObject('#__membersmanager_' . (string) $view['a_view'], $object, 'id');
			}
			elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
			{
				$this->fileName = array($this->fileName);
				$this->fileName =  '["'.implode('", "', $this->fileName).'"]';
			}
			// set the results
			$result = array('success' =>  $this->fileName, 'fileformat' => $this->fileFormat);
			// add some more values if document format type
			if ($this->formatType === 'file' || $this->formatType === 'document' || $this->formatType === 'media')
			{
				// set link options
				$linkOptions = MembersmanagerHelper::getLinkOptions();
				// do not lock file for link unless lock is set
				if ($linkOptions['lock'] == 0)
				{
					$localFile = MembersmanagerHelper::base64_urlencode($keyName, true);
				}
				$tokenLink = '';
				if ($linkOptions['session'])
				{
					$tokenLink = '&' . JSession::getFormToken() . '=1';
				}
				// if document or file
				if ($this->formatType === 'file' || $this->formatType === 'document')
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
			// check permissions
			if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']))
			{
				// get user to see if he has permission to upload
				$user = JFactory::getUser();
				if (!$user->authorise($view['a_view'] . '.edit.'. $this->target . '_' . $this->targetType, 'com_membersmanager'))
				{
					return array('error' =>  JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_REMOVE_THIS_FILE'));
				}
			}
			if ($this->formatType === 'file' || $this->formatType === 'document' || $this->formatType === 'media')
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
			if ($clearDB == 1 && isset($view['a_id']) && $view['a_id'] > 0)
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->formatType === 'file' || $this->targetType === 'image' || $this->targetType === 'document')
				{
					$object->{$this->target . '_' . $this->targetType} = '';
					JFactory::getDbo()->updateObject('#__membersmanager_' . $view['a_view'], $object, 'id');
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					// Get the basic encription.
					$encryptionkey = MembersmanagerHelper::getCryptKey('basic');
					$encryption = null;
					if ($encryptionkey)
					{
						// Get the encryption object.
						$encryption = new FOFEncryptAes($encryptionkey, 128);
					}
					$fileNameArray = $this->setFileNameArray('remove', $encryption, $view);
					if (MembersmanagerHelper::checkObject($encryption))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $encryption->encryptString($fileNameArray);
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
			// check if this is a locked file
			if (substr($this->fileName, 0, 1) === '.' && JFile::exists($this->folderPath . $this->fileName))
			{
				// remove the file
				return JFile::delete($this->folderPath . $this->fileName);
			}
			else
			{
				// set formats
				$this->formats = MembersmanagerHelper::getFileExtensions($this->formatType);
				foreach ($this->formats as $fileFormat)
				{
					if (JFile::exists($this->folderPath . $this->fileName . '.' . $fileFormat))
					{
						// remove the file
						return JFile::delete($this->folderPath . $this->fileName . '.' . $fileFormat);
					}
				}
			}
		}
		return array('error' => JText::_('COM_MEMBERSMANAGER_THERE_HAS_BEEN_AN_ERROR'));
	}

	protected function setFileNameArray($action, $encryption, $view)
	{
		$curentFiles = MembersmanagerHelper::getVar($view['a_view'], $view['a_id'], 'id', $this->target.'_'.$this->targetType);
		// unlock if needed
		if ($encryption && $curentFiles === base64_encode(base64_decode($curentFiles, true)))
		{
			// decrypt data image.
			$curentFiles = rtrim($encryption->decryptString($curentFiles), "\0");
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
		// set formats
		$this->formats = MembersmanagerHelper::getFileExtensions($this->formatType);
		// Clean the name
		$archivename = JPath::clean($archivename);
		// get file format
		$this->fileFormat = strtolower(pathinfo($archivename, PATHINFO_EXTENSION));
		// get fileFormat key
		$allowedFormats = array();
		if (in_array($this->fileFormat, $this->formats))
		{
			// get allowed formats
			$allowedFormats = (array) $this->app_params->get($this->formatType.'_formats', array());
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
		// get user to see if he has permission to upload
		$user = JFactory::getUser();
		if (!$user->authorise($view['a_view'] . '.edit.' . $this->target . '_' . $this->targetType, 'com_membersmanager'))
		{
			// Cleanup the import files
			$this->remove($archivename);
			$this->errorMessage = JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_UPLOAD_AN' . $this->targetType);
			return false;
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
				// set GUID if found
				if (($guid = MembersmanagerHelper::get($vdm . '__guid')) !== false && method_exists('MembersmanagerHelper', 'validGUID'))
				{
					if (MembersmanagerHelper::validGUID($guid))
					{
						$this->viewid[$call]['a_guid'] = $guid;
					}
				}
				// set return if found
				if (($return = MembersmanagerHelper::get($vdm . '__return')) !== false)
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

}
