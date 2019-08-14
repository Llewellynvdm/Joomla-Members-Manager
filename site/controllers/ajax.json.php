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

/**
 * Membersmanager Ajax Controller
 */
class MembersmanagerControllerAjax extends JControllerLegacy
{
	public function __construct($config)
	{
		parent::__construct($config);
		// make sure all json stuff are set
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="getajax.json"');
		JResponse::setHeader("Access-Control-Allow-Origin", "*");
		// load the tasks 
		$this->registerTask('checkUnique', 'ajax');
		$this->registerTask('getPlaceHolders', 'ajax');
		$this->registerTask('getAnyPlaceHolders', 'ajax');
		$this->registerTask('uploadfile', 'ajax');
		$this->registerTask('removeFile', 'ajax');
		$this->registerTask('getUserDetails', 'ajax');
		$this->registerTask('getChartImageLink', 'ajax');
		$this->registerTask('searchMembers', 'ajax');
		$this->registerTask('getReport', 'ajax');
		$this->registerTask('getListMessages', 'ajax');
	}

	public function ajax()
	{
		$user 		= JFactory::getUser();
		$jinput 	= JFactory::getApplication()->input;
		// Check Token!
		$token 		= JSession::getFormToken();
		$call_token	= $jinput->get('token', 0, 'ALNUM');
		if($jinput->get($token, 0, 'ALNUM') || $token === $call_token)
		{
			$task = $this->getTask();
			switch($task)
			{
				case 'checkUnique':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$fieldValue = $jinput->get('field', NULL, 'WORD');
						$valueValue = $jinput->get('value', NULL, 'STRING');
						if($fieldValue && $valueValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->checkUnique($fieldValue, $valueValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getPlaceHolders':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$getTypeValue = $jinput->get('getType', NULL, 'WORD');
						if($getTypeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getPlaceHolders($getTypeValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getAnyPlaceHolders':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$getTypeValue = $jinput->get('getType', NULL, 'WORD');
						if($getTypeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getAnyPlaceHolders($getTypeValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'uploadfile':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$targetValue = $jinput->get('target', NULL, 'WORD');
						$typeValue = $jinput->get('type', NULL, 'WORD');
						if($targetValue && $typeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->uploadfile($targetValue, $typeValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'removeFile':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$filenameValue = $jinput->get('filename', NULL, 'CMD');
						$targetValue = $jinput->get('target', NULL, 'WORD');
						$flushValue = $jinput->get('flush', NULL, 'INT');
						$typeValue = $jinput->get('type', NULL, 'WORD');
						if($filenameValue && $targetValue && $flushValue && $typeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->removeFile($filenameValue, $targetValue, $flushValue, $typeValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getUserDetails':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$userValue = $jinput->get('user', NULL, 'INT');
						if($userValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getUserDetails($userValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getChartImageLink':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$imageValue = $jinput->get('image', NULL, 'STRING');
						if($imageValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getChartImageLink($imageValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'searchMembers':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$searchValue = $jinput->get('search', NULL, 'STRING');
						if($searchValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->searchMembers($searchValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getReport':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$keyValue = $jinput->get('key', NULL, 'STRING');
						if($keyValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getReport($keyValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getListMessages':
					try
					{
						$returnRaw = $jinput->get('raw', false, 'BOOLEAN');
						$keyValue = $jinput->get('key', NULL, 'STRING');
						if($keyValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getListMessages($keyValue);
						}
						else
						{
							$result = false;
						}
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(Exception $e)
					{
						if($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback."(".json_encode($e).");";
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
			}
		}
		else
		{
			if($callback = $jinput->get('callback', null, 'CMD'))
			{
				echo $callback."(".json_encode(false).");";
			}
			else
  			{
				echo "(".json_encode(false).");";
			}
		}
	}
}
