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
 * Type View class
 */
class MembersmanagerViewType extends JViewLegacy
{
	/**
	 * display method of View
	 * @return void
	 */
	public function display($tpl = null)
	{
		// set params
		$this->params = JComponentHelper::getParams('com_membersmanager');
		// Assign the variables
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');
		$this->state = $this->get('State');
		// get action permissions
		$this->canDo = MembersmanagerHelper::getActions('type', $this->item);
		// get input
		$jinput = JFactory::getApplication()->input;
		$this->ref = $jinput->get('ref', 0, 'word');
		$this->refid = $jinput->get('refid', 0, 'int');
		$return = $jinput->get('return', null, 'base64');
		// set the referral string
		$this->referral = '';
		if ($this->refid && $this->ref)
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string)$this->ref . '&refid=' . (int)$this->refid;
		}
		elseif($this->ref)
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string)$this->ref;
		}
		// check return value
		if (!is_null($return))
		{
			// add the return value
			$this->referral .= '&return=' . (string)$return;
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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_MEMBERSMANAGER_TYPE_NEW' : 'COM_MEMBERSMANAGER_TYPE_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (MembersmanagerHelper::checkString($this->referral))
		{
			if ($this->canDo->get('type.create') && $isNew)
			{
				// We can create the record.
				JToolBarHelper::save('type.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('type.edit'))
			{
				// We can save the record.
				JToolBarHelper::save('type.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				JToolBarHelper::cancel('type.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				JToolBarHelper::cancel('type.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('type.create'))
				{
					JToolBarHelper::apply('type.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('type.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::custom('type.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				JToolBarHelper::cancel('type.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('type.edit'))
				{
					// We can save the new record
					JToolBarHelper::apply('type.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('type.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('type.create'))
					{
						JToolBarHelper::custom('type.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('type.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('type.edit') && $canVersion)
				{
					JToolbarHelper::versions('com_membersmanager.type', $this->item->id);
				}
				if ($this->canDo->get('type.create'))
				{
					JToolBarHelper::custom('type.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				JToolBarHelper::cancel('type.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		JToolbarHelper::divider();
		// set help url for this view if found
		$help_url = MembersmanagerHelper::getHelpUrl('type');
		if (MembersmanagerHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_MEMBERSMANAGER_HELP_MANAGER', false, $help_url);
		}
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
		$this->document->setTitle(JText::_($isNew ? 'COM_MEMBERSMANAGER_TYPE_NEW' : 'COM_MEMBERSMANAGER_TYPE_EDIT'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_membersmanager/assets/css/type.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript(JURI::root() . $this->script, (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		$this->document->addScript(JURI::root() . "administrator/components/com_membersmanager/views/type/submitbutton.js", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript'); 

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
		JText::script('view not acceptable. Error');
	}
}
