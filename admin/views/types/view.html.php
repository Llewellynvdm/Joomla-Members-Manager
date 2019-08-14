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
 * Membersmanager View class for the Types
 */
class MembersmanagerViewTypes extends JViewLegacy
{
	/**
	 * Types view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			MembersmanagerHelper::addSubmenu('types');
		}

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->user = JFactory::getUser();
		$this->listOrder = $this->escape($this->state->get('list.ordering'));
		$this->listDirn = $this->escape($this->state->get('list.direction'));
		$this->saveOrder = $this->listOrder == 'ordering';
		// set the return here value
		$this->return_here = urlencode(base64_encode((string) JUri::getInstance()));
		// get global action permissions
		$this->canDo = MembersmanagerHelper::getActions('type');
		$this->canEdit = $this->canDo->get('type.edit');
		$this->canState = $this->canDo->get('type.edit.state');
		$this->canCreate = $this->canDo->get('type.create');
		$this->canDelete = $this->canDo->get('type.delete');
		$this->canBatch = $this->canDo->get('core.batch');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
			// load the batch html
			if ($this->canCreate && $this->canEdit && $this->canState)
			{
				$this->batchDisplay = JHtmlBatch_::render();
			}
		}
		
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
		JToolBarHelper::title(JText::_('COM_MEMBERSMANAGER_TYPES'), 'archive');
		JHtmlSidebar::setAction('index.php?option=com_membersmanager&view=types');
		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

		if ($this->canCreate)
		{
			JToolBarHelper::addNew('type.add');
		}

		// Only load if there are items
		if (MembersmanagerHelper::checkArray($this->items))
		{
			if ($this->canEdit)
			{
				JToolBarHelper::editList('type.edit');
			}

			if ($this->canState)
			{
				JToolBarHelper::publishList('types.publish');
				JToolBarHelper::unpublishList('types.unpublish');
				JToolBarHelper::archiveList('types.archive');

				if ($this->canDo->get('core.admin'))
				{
					JToolBarHelper::checkin('types.checkin');
				}
			}

			// Add a batch button
			if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState)
			{
				// Get the toolbar object instance
				$bar = JToolBar::getInstance('toolbar');
				// set the batch button name
				$title = JText::_('JTOOLBAR_BATCH');
				// Instantiate a new JLayoutFile instance and render the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');
				// add the button to the page
				$dhtml = $layout->render(array('title' => $title));
				$bar->appendButton('Custom', $dhtml, 'batch');
			}

			if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
			{
				JToolbarHelper::deleteList('', 'types.delete', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($this->canState && $this->canDelete)
			{
				JToolbarHelper::trash('types.trash');
			}

			if ($this->canDo->get('core.export') && $this->canDo->get('type.export'))
			{
				JToolBarHelper::custom('types.exportData', 'download', '', 'COM_MEMBERSMANAGER_EXPORT_DATA', true);
			}
		}
		if ($this->user->authorise('type.update_types', 'com_membersmanager'))
		{
			// add Update Types button.
			JToolBarHelper::custom('types.updateTypes', 'wrench', '', 'COM_MEMBERSMANAGER_UPDATE_TYPES', false);
		}

		if ($this->canDo->get('core.import') && $this->canDo->get('type.import'))
		{
			JToolBarHelper::custom('types.importData', 'upload', '', 'COM_MEMBERSMANAGER_IMPORT_DATA', false);
		}

		// set help url for this view if found
		$help_url = MembersmanagerHelper::getHelpUrl('types');
		if (MembersmanagerHelper::checkString($help_url))
		{
				JToolbarHelper::help('COM_MEMBERSMANAGER_HELP_MANAGER', false, $help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_membersmanager');
		}

		if ($this->canState)
		{
			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
			);
			// only load if batch allowed
			if ($this->canBatch)
			{
				JHtmlBatch_::addListSelection(
					JText::_('COM_MEMBERSMANAGER_KEEP_ORIGINAL_STATE'),
					'batch[published]',
					JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('all' => false)), 'value', 'text', '', true)
				);
			}
		}

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_MEMBERSMANAGER_KEEP_ORIGINAL_ACCESS'),
				'batch[access]',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text')
			);
		}

		// Set Add Relationship Selection
		$this->add_relationshipOptions = $this->getTheAdd_relationshipSelections();
		// We do some sanitation for Add Relationship filter
		if (MembersmanagerHelper::checkArray($this->add_relationshipOptions) &&
			isset($this->add_relationshipOptions[0]->value) &&
			!MembersmanagerHelper::checkString($this->add_relationshipOptions[0]->value))
		{
			unset($this->add_relationshipOptions[0]);
		}
		// Only load Add Relationship filter if it has values
		if (MembersmanagerHelper::checkArray($this->add_relationshipOptions))
		{
			// Add Relationship Filter
			JHtmlSidebar::addFilter(
				'- Select '.JText::_('COM_MEMBERSMANAGER_TYPE_ADD_RELATIONSHIP_LABEL').' -',
				'filter_add_relationship',
				JHtml::_('select.options', $this->add_relationshipOptions, 'value', 'text', $this->state->get('filter.add_relationship'))
			);

			if ($this->canBatch && $this->canCreate && $this->canEdit)
			{
				// Add Relationship Batch Selection
				JHtmlBatch_::addListSelection(
					'- Keep Original '.JText::_('COM_MEMBERSMANAGER_TYPE_ADD_RELATIONSHIP_LABEL').' -',
					'batch[add_relationship]',
					JHtml::_('select.options', $this->add_relationshipOptions, 'value', 'text')
				);
			}
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_('COM_MEMBERSMANAGER_TYPES'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_membersmanager/assets/css/types.css", (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
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
		if(strlen($var) > 50)
		{
			// use the helper htmlEscape method instead and shorten the string
			return MembersmanagerHelper::htmlEscape($var, $this->_charset, true);
		}
		// use the helper htmlEscape method instead.
		return MembersmanagerHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
			'a.sorting' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.name' => JText::_('COM_MEMBERSMANAGER_TYPE_NAME_LABEL'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}

	protected function getTheAdd_relationshipSelections()
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('add_relationship'));
		$query->from($db->quoteName('#__membersmanager_type'));
		$query->order($db->quoteName('add_relationship') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results)
		{
			// get model
			$model = $this->getModel();
			$results = array_unique($results);
			$_filter = array();
			foreach ($results as $add_relationship)
			{
				// Translate the add_relationship selection
				$text = $model->selectionTranslation($add_relationship,'add_relationship');
				// Now add the add_relationship and its text to the options array
				$_filter[] = JHtml::_('select.option', $add_relationship, JText::_($text));
			}
			return $_filter;
		}
		return false;
	}
}
