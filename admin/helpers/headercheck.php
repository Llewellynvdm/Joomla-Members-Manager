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

class membersmanagerHeaderCheck
{
	function js_loaded($script_name)
	{
		// UIkit check point
		if (strpos($script_name,'uikit') !== false)
		{
			$app            	= JFactory::getApplication();
			$getTemplateName  	= $app->getTemplate('template')->template;
			
			if (strpos($getTemplateName,'yoo') !== false)
			{
				return true;
			}
		}
		
		$document 	= JFactory::getDocument();
		$head_data 	= $document->getHeadData();
		foreach (array_keys($head_data['scripts']) as $script)
		{
			if (stristr($script, $script_name))
			{
				return true;
			}
		}

		return false;
	}
	
	function css_loaded($script_name)
	{
		// UIkit check point
		if (strpos($script_name,'uikit') !== false)
		{
			$app            	= JFactory::getApplication();
			$getTemplateName  	= $app->getTemplate('template')->template;
			
			if (strpos($getTemplateName,'yoo') !== false)
			{
				return true;
			}
		}
		
		$document 	= JFactory::getDocument();
		$head_data 	= $document->getHeadData();
		
		foreach (array_keys($head_data['styleSheets']) as $script)
		{
			if (stristr($script, $script_name))
			{
				return true;
			}
		}

		return false;
	}
}