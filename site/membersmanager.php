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

// Set the component css/js
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_membersmanager/assets/css/site.css');
$document->addScript('components/com_membersmanager/assets/js/site.js');

// Require helper files
JLoader::register('MembersmanagerHelper', dirname(__FILE__) . '/helpers/membersmanager.php'); 
JLoader::register('MembersmanagerHelperRoute', dirname(__FILE__) . '/helpers/route.php'); 

// Triger the Global Site Event
MembersmanagerHelper::globalEvent($document);

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Membersmanager
$controller = JControllerLegacy::getInstance('Membersmanager');

// Perform the request task
$jinput = JFactory::getApplication()->input;
$controller->execute($jinput->get('task', null, 'CMD'));

// Redirect if set by the controller
$controller->redirect();
