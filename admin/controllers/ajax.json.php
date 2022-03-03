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
 * Membersmanager Ajax Controller
 */
class MembersmanagerControllerAjax extends JControllerLegacy
{
	public function __construct($config)
	{
		parent::__construct($config);
		// make sure all json stuff are set
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		// get the application
		$app = JFactory::getApplication();
		$app->setHeader('Content-Disposition','attachment;filename="getajax.json"');
		$app->setHeader('Access-Control-Allow-Origin', '*');
		// load the tasks 
		$this->registerTask('checkUnique', 'ajax');
		$this->registerTask('getPlaceHolders', 'ajax');
		$this->registerTask('getAnyPlaceHolders', 'ajax');
		$this->registerTask('uploadfile', 'ajax');
		$this->registerTask('removeFile', 'ajax');
		$this->registerTask('getUserDetails', 'ajax');
		$this->registerTask('getChartImageLink', 'ajax');
	}

	public function ajax()
	{
		// get the user for later use
		$user 		= JFactory::getUser();
		// get the input values
		$jinput 	= JFactory::getApplication()->input;
		// check if we should return raw
		$returnRaw	= $jinput->get('raw', false, 'BOOLEAN');
		// return to a callback function
		$callback	= $jinput->get('callback', null, 'CMD');
		// Check Token!
		$token 		= JSession::getFormToken();
		$call_token	= $jinput->get('token', 0, 'ALNUM');
		if($jinput->get($token, 0, 'ALNUM') || $token === $call_token)
		{
			// get the task
			$task = $this->getTask();
			switch($task)
			{
				case 'checkUnique':
					try
					{
						$fieldValue = $jinput->get('field', NULL, 'WORD');
						$valueValue = $jinput->get('value', NULL, 'STRING');
						if($fieldValue && $user->id != 0 && $valueValue)
						{
							$result = $this->getModel('ajax')->checkUnique($fieldValue, $valueValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$getTypeValue = $jinput->get('getType', NULL, 'WORD');
						if($getTypeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getPlaceHolders($getTypeValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$getTypeValue = $jinput->get('getType', NULL, 'WORD');
						if($getTypeValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getAnyPlaceHolders($getTypeValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$targetValue = $jinput->get('target', NULL, 'WORD');
						$typeValue = $jinput->get('type', NULL, 'WORD');
						if($targetValue && $user->id != 0 && $typeValue)
						{
							$result = $this->getModel('ajax')->uploadfile($targetValue, $typeValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$filenameValue = $jinput->get('filename', NULL, 'CMD');
						$targetValue = $jinput->get('target', NULL, 'WORD');
						$flushValue = $jinput->get('flush', NULL, 'INT');
						$typeValue = $jinput->get('type', NULL, 'WORD');
						if($filenameValue && $user->id != 0 && $targetValue && $flushValue && $typeValue)
						{
							$result = $this->getModel('ajax')->removeFile($filenameValue, $targetValue, $flushValue, $typeValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$userValue = $jinput->get('user', NULL, 'INT');
						if($userValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getUserDetails($userValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
						$chartValue = $jinput->get('chart', NULL, 'STRING');
						if($chartValue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getChartImageLink($chartValue);
						}
						else
						{
							$result = false;
						}
						if($callback)
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
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
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
			// return to a callback function
			if($callback)
			{
				echo $callback."(".json_encode(false).");";
			}
			elseif($returnRaw)
			{
				echo json_encode(false);
			}
			else
			{
				echo "(".json_encode(false).");";
			}
		}
	}
}
