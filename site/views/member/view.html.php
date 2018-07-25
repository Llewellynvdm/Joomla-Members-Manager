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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Member View class
 */
class MembersmanagerViewMember extends JViewLegacy
{
	/**
	 * display method of View
	 * @return void
	 */
	public function display($tpl = null)
	{
		// Assign the variables
		$this->form 		= $this->get('Form');
		$this->item 		= $this->get('Item');
		$this->script 		= $this->get('Script');
		$this->state		= $this->get('State');
		// get action permissions
		$this->canDo		= MembersmanagerHelper::getActions('member',$this->item);
		// get input
		$jinput = JFactory::getApplication()->input;
		$this->ref 		= $jinput->get('ref', 0, 'word');
		$this->refid            = $jinput->get('refid', 0, 'int');
		$this->referral         = '';
		if ($this->refid)
		{
				// return to the item that refered to this item
				$this->referral = '&ref='.(string)$this->ref.'&refid='.(int)$this->refid;
		}
		elseif($this->ref)
		{
				// return to the list view that refered to this item
				$this->referral = '&ref='.(string)$this->ref;
		}

		// Set the toolbar
		$this->addToolBar();
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}


	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// adding the joomla edit toolbar to the front
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR.'/includes/toolbar.php');
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_MEMBERSMANAGER_MEMBER_NEW' : 'COM_MEMBERSMANAGER_MEMBER_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if ($this->refid || $this->ref)
		{
			if ($this->canDo->get('member.create') && $isNew)
			{
				// We can create the record.
				JToolBarHelper::save('member.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('member.edit'))
			{
				// We can save the record.
				JToolBarHelper::save('member.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				JToolBarHelper::cancel('member.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				JToolBarHelper::cancel('member.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('member.create'))
				{
					JToolBarHelper::apply('member.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('member.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::custom('member.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				JToolBarHelper::cancel('member.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('member.edit'))
				{
					// We can save the new record
					JToolBarHelper::apply('member.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('member.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('member.create'))
					{
						JToolBarHelper::custom('member.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('member.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('member.edit') && $canVersion)
				{
					JToolbarHelper::versions('com_membersmanager.member', $this->item->id);
				}
				if ($this->canDo->get('member.create'))
				{
					JToolBarHelper::custom('member.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				JToolBarHelper::cancel('member.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		JToolbarHelper::divider();
		// set help url for this view if found
		$help_url = MembersmanagerHelper::getHelpUrl('member');
		if (MembersmanagerHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_MEMBERSMANAGER_HELP_MANAGER', false, $help_url);
		}
		// now initiate the toolbar
		$this->toolbar = JToolbar::getInstance();
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if(strlen($var) > 30)
		{
    		// use the helper htmlEscape method instead and shorten the string
			return MembersmanagerHelper::htmlEscape($var, $this->_charset, true, 30);
		}
		// use the helper htmlEscape method instead.
		return MembersmanagerHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = ($this->item->id < 1);
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_($isNew ? 'COM_MEMBERSMANAGER_MEMBER_NEW' : 'COM_MEMBERSMANAGER_MEMBER_EDIT'));
		// we need this to fix the form display
		$this->document->addStyleSheet(JURI::root()."administrator/templates/isis/css/template.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript(JURI::root()."administrator/templates/isis/js/template.js", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		// the default style of this view
		$this->document->addStyleSheet(JURI::root()."components/com_membersmanager/assets/css/member.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// Add Ajax Token
		$this->document->addScriptDeclaration("var token = '".JSession::getFormToken()."';"); 
		// default javascript of this view
		$this->document->addScript(JURI::root().$this->script, (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		$this->document->addScript(JURI::root(). "components/com_membersmanager/views/member/submitbutton.js", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript'); 

		// add the style sheets
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/uikit.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/accordion.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/tooltip.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/notify.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/form-file.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/progress.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/placeholder.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2//css/components/upload.gradient.min.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// add JavaScripts
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/uikit.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/accordion.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/tooltip.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/lightbox.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/notify.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/upload.min.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// add var key
		$this->document->addScriptDeclaration("var vastDevMod = '".$this->get('VDM')."';");
		// need to add some language strings
		JText::script('COM_MEMBERSMANAGER_VALUE_ALREADY_TAKEN_PLEASE_TRY_AGAIN');
		JText::script('view not acceptable. Error');
	}
}
