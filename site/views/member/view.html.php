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
		// set params
		$this->params = JComponentHelper::getParams('com_membersmanager');
		// Assign the variables
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');
		$this->state = $this->get('State');
		// get action permissions
		$this->canDo = MembersmanagerHelper::getActions('member', $this->item);
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
		// adding the joomla edit toolbar to the front
		JLoader::register('JToolbarHelper', JPATH_ADMINISTRATOR.'/includes/toolbar.php');
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_MEMBERSMANAGER_MEMBER_NEW' : 'COM_MEMBERSMANAGER_MEMBER_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (MembersmanagerHelper::checkString($this->referral))
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
		// only add the ISIS template css & js if needed (default is 1 = true)
		// you can override this in the global component options
		// just add a (radio yes/no field) with a name called add_isis_template
		// to your components config area
		if ($this->params->get('add_isis_template', 1))
		{
			// we need this to fix the form display (TODO)
			$this->document->addStyleSheet(JURI::root() . "administrator/templates/isis/css/template.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addScript(JURI::root() . "administrator/templates/isis/js/template.js", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		}
		// the default style of this view
		$this->document->addStyleSheet(JURI::root()."components/com_membersmanager/assets/css/member.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// Add Ajax Token
		$this->document->addScriptDeclaration("var token = '".JSession::getFormToken()."';");
		// default javascript of this view
		$this->document->addScript(JURI::root(). $this->script, (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		$this->document->addScript(JURI::root(). "components/com_membersmanager/views/member/submitbutton.js", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript'); 

		// get Uikit Version
		$this->uikitVersion = $this->params->get('uikit_version', 2);
		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		$isAdmin = JFactory::getApplication()->isClient('administrator');
		// Set script size.
		$size = $this->params->get('uikit_min');
		// Use Uikit Version 2
		if (2 == $this->uikitVersion && ($isAdmin || $uikit != 2))
		{
			// Set css style.
			$style = $this->params->get('uikit_style');
			// only load if needed
			if ($isAdmin || $uikit != 3)
			{
				// add the style sheets
				$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/uikit' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// add the style sheets
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/accordion' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/tooltip' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/notify' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/form-file' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/progress' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/placeholder' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v2//css/components/upload' . $style . $size . '.css' , (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			// only load if needed
			if ($isAdmin || $uikit != 3)
			{
				// add JavaScripts
				$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/uikit' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			}
			// add JavaScripts
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/accordion' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/tooltip' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/lightbox' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/notify' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/upload' . $size . '.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		}
		// Use Uikit Version 3
		elseif (3 == $this->uikitVersion && ($isAdmin || $uikit != 2))
		{
			// add the style sheets
			$this->document->addStyleSheet( JURI::root(true) .'/media/com_membersmanager/uikit-v3/css/uikit'.$size.'.css', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			// add JavaScripts
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v3/js/uikit'.$size.'.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			// add icons
			$this->document->addScript( JURI::root(true) .'/media/com_membersmanager/uikit-v3/js/uikit-icons'.$size.'.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		}
		// add var key
		$this->document->addScriptDeclaration("var vastDevMod = '" . $this->get('VDM') . "';");
		// when this is a create view
		if ((empty($this->item->id) || $this->item->id == 0) && !$isAdmin)
		{
			// update button
			$update_button = 'jQuery(document).ready(function($){';
			$update_button .= '$(\'#toolbar-save button\').attr("onClick", "Joomla.submitbutton(\'member.saveprofile\');");';
			$update_button .= '$(\'#toolbar-save button\').removeClass("btn btn-small button-new btn-success");';
			$update_button .= '$(\'#toolbar-save button\').addClass("btn btn-small button-new btn-success");';
			$update_button .= '$(\'#toolbar-save button\').html(\'<span class="icon-new icon-white" aria-hidden="true"></span> Create\');';
			$update_button .= '});';
			$this->document->addScriptDeclaration($update_button);
		}
		elseif (!$isAdmin && isset($this->item->id) && $this->item->id > 0)
		{
			// update button
			$update_button = 'jQuery(document).ready(function($){';
			$update_button .= '$(\'#toolbar-save button\').attr("onClick", "Joomla.submitbutton(\'member.saveprofile\');");';
			$update_button .= '});';
			$this->document->addScriptDeclaration($update_button);
		}
		// need to add some language strings
		JText::script('COM_MEMBERSMANAGER_VALUE_ALREADY_TAKEN_PLEASE_TRY_AGAIN');
		JText::script('view not acceptable. Error');
	}
}
