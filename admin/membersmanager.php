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
JHtml::_('behavior.tabstate');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_membersmanager'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
};

// Add CSS file for all pages
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_membersmanager/assets/css/admin.css');
$document->addScript('components/com_membersmanager/assets/js/admin.js');

// require helper files
JLoader::register('MembersmanagerHelper', __DIR__ . '/helpers/membersmanager.php'); 
JLoader::register('JHtmlBatch_', __DIR__ . '/helpers/html/batch_.php'); 

// Triger the Global Admin Event
MembersmanagerHelper::globalEvent($document);

// Get an instance of the controller prefixed by Membersmanager
$controller = JControllerLegacy::getInstance('Membersmanager');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
