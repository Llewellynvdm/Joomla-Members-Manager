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
jimport('joomla.application.module.helper');

/**
 * Membersmanager View class for the Profile
 */
class MembersmanagerViewProfile extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null)
	{		
		// get combined params of both component and menu
		$this->app = JFactory::getApplication();
		$this->params = $this->app->getParams();
		$this->menu = $this->app->getMenu()->getActive();
		// get the user object
		$this->user = JFactory::getUser();
		// Initialise dispatcher.
		$dispatcher = JEventDispatcher::getInstance();
		// Initialise variables.
		$this->item = $this->get('Item');

		// Set the toolbar
		$this->addToolBar();

		// set the document
		$this->_prepareDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}
		// Process the content plugins.
		if (MembersmanagerHelper::checkObject($this->item))
		{
			JPluginHelper::importPlugin('content');
			// Setup Event Object.
			$this->item->event = new stdClass;
			// Check if item has params, or pass global params
			$params = (isset($this->item->params) && MembersmanagerHelper::checkJson($this->item->params)) ? json_decode($this->item->params) : $this->params;
			// onContentAfterTitle Event Trigger.
			$results = $dispatcher->trigger('onContentAfterTitle', array('com_membersmanager.member', &$this->item, &$params, 0));
			$this->item->event->onContentAfterTitle = trim(implode("\n", $results));
			// onContentBeforeDisplay Event Trigger.
			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_membersmanager.member', &$this->item, &$params, 0));
			$this->item->event->onContentBeforeDisplay = trim(implode("\n", $results));
			// onContentAfterDisplay Event Trigger.
			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_membersmanager.member', &$this->item, &$params, 0));
			$this->item->event->onContentAfterDisplay = trim(implode("\n", $results));
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_SITE.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new membersmanagerHeaderCheck;

		// Add View JavaScript File
		$this->document->addScript(JURI::root(true) . "/components/com_membersmanager/assets/js/profile.js", (MembersmanagerHelper::jVersion()->isCompatible("3.8.0")) ? array("version" => "auto") : "text/javascript");

		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		// Set script size.
		$size = $this->params->get('uikit_min');

		// Load uikit version.
		$this->uikitVersion = $this->params->get('uikit_version', 2);

		// Use Uikit Version 2
		if (2 == $this->uikitVersion)
		{
			// Set css style.
			$style = $this->params->get('uikit_style');

			// The uikit css.
			if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addStyleSheet(JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/uikit'.$style.$size.'.css', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// The uikit js.
			if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addScript(JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/uikit'.$size.'.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			}
		}
		// Use Uikit Version 3
		elseif (3 == $this->uikitVersion)
		{
			// The uikit css.
			if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addStyleSheet(JURI::root(true) .'/media/com_membersmanager/uikit-v3/css/uikit'.$size.'.css', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// The uikit js.
			if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addScript(JURI::root(true) .'/media/com_membersmanager/uikit-v3/js/uikit'.$size.'.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			}
		}
		
		// Add the JavaScript for JStore
		$this->document->addScript(JURI::root() .'media/com_membersmanager/js/jquery.json.min.js');
		$this->document->addScript(JURI::root() .'media/com_membersmanager/js/jstorage.min.js');
		// check if we should use browser storage
		$setBrowserStorage = $this->params->get('set_browser_storage', null);
		if ($setBrowserStorage)
		{
			// check what (Time To Live) we should use
			$storageTimeToLive = $this->params->get('storage_time_to_live', 'global');
			if ('global' == $storageTimeToLive)
			{
				// use the global session time
				$session = JFactory::getSession();
				// must have itin milliseconds
				$expire = ($session->getExpire()*60)* 1000;
			}
			else
			{
				// use the Membersmanager Global setting
				if (0 !=  $storageTimeToLive)
				{
					// this will convert the time into milliseconds
					$storageTimeToLive =  $storageTimeToLive * 1000;
				}
				$expire = $storageTimeToLive;
			}
		}
		else
		{
			// set to use no storage
			$expire = 10;
		}
		// Set the Time To Live To JavaScript
		$this->document->addScriptDeclaration("var expire = ". (int) $expire.";");
		// load the google lib
		$this->document->addScript('https://www.gstatic.com/charts/loader.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		// load the chart current
		$this->document->addScriptDeclaration("google.charts.load('current');");
		// add var key
		$this->document->addScriptDeclaration("var vastDevMod = '".$this->get('VDM')."';");
		// add JavaScript lang strings
		JText::script('COM_MEMBERSMANAGER_THERE_WAS_NO_REPORT_FOUND');
		JText::script('COM_MEMBERSMANAGER_THERE_WAS_NO_MESSAGES_FOUND'); 
		// add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/components/com_membersmanager/assets/css/profile.css', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// Set the Custom CSS script to view
		$this->document->addStyleDeclaration("
			.chartDiv {
			  margin:0 !important;
			}
			.chart {
			  width: 100%; 
			  min-height: 450px;
			}
		");
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// adding the joomla toolbar to the front
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR.'/includes/toolbar.php');

		// set help url for this view if found
		$help_url = MembersmanagerHelper::getHelpUrl('profile');
		if (MembersmanagerHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_MEMBERSMANAGER_HELP_MANAGER', false, $help_url);
		}
		// now initiate the toolbar
		$this->toolbar = JToolbar::getInstance();
	}

	/**
	 * Get the modules published in a position
	 */
	public function getModules($position, $seperator = '', $class = '')
	{
		// set default
		$found = false;
		// check if we aleady have these modules loaded
		if (isset($this->setModules[$position]))
		{
			$found = true;
		}
		else
		{
			// this is where you want to load your module position
			$modules = JModuleHelper::getModules($position);
			if ($modules)
			{
				// set the place holder
				$this->setModules[$position] = array();
				foreach($modules as $module)
				{
					$this->setModules[$position][] = JModuleHelper::renderModule($module);
				}
				$found = true;
			}
		}
		// check if modules were found
		if ($found && isset($this->setModules[$position]) && MembersmanagerHelper::checkArray($this->setModules[$position]))
		{
			// set class
			if (MembersmanagerHelper::checkString($class))
			{
				$class = ' class="'.$class.'" ';
			}
			// set seperating return values
			switch($seperator)
			{
				case 'none':
					return implode('', $this->setModules[$position]);
					break;
				case 'div':
					return '<div'.$class.'>'.implode('</div><div'.$class.'>', $this->setModules[$position]).'</div>';
					break;
				case 'list':
					return '<ul'.$class.'><li>'.implode('</li><li>', $this->setModules[$position]).'</li></ul>';
					break;
				case 'array':
				case 'Array':
					return $this->setModules[$position];
					break;
				default:
					return implode('<br />', $this->setModules[$position]);
					break;
				
			}
		}
		return false;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var, $sorten = false, $length = 40)
	{
		// use the helper htmlEscape method instead.
		return MembersmanagerHelper::htmlEscape($var, $this->_charset, $sorten, $length);
	}
}
