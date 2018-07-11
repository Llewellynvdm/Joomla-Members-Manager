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

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_membersmanager'))
{
	return JError::raiseWaring(404, JText::_('JERROR_ALERTNOAUTHOR'));
};

// Load cms libraries
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');
// Load joomla libraries without overwrite
JLoader::registerPrefix('J', JPATH_PLATFORM . '/joomla',false);

// Add CSS file for all pages
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_membersmanager/assets/css/admin.css');
$document->addScript('components/com_membersmanager/assets/js/admin.js');

// require helper files
JLoader::register('MembersmanagerHelper', dirname(__FILE__) . '/helpers/membersmanager.php'); 
JLoader::register('JHtmlBatch_', dirname(__FILE__) . '/helpers/html/batch_.php'); 

// Triger the Global Admin Event
MembersmanagerHelper::globalEvent($document);

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Membersmanager
$controller = JControllerLegacy::getInstance('Membersmanager');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
