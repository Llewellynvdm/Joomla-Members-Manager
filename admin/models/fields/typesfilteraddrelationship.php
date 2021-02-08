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

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Typesfilteraddrelationship Form Field class for the Membersmanager component
 */
class JFormFieldTypesfilteraddrelationship extends JFormFieldList
{
	/**
	 * The typesfilteraddrelationship field type.
	 *
	 * @var		string
	 */
	public $type = 'typesfilteraddrelationship';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
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
		$_filter = array();
		$_filter[] = JHtml::_('select.option', '', '- ' . JText::_('COM_MEMBERSMANAGER_FILTER_SELECT_ALLOW_RELATIONSHIPS') . ' -');

		if ($results)
		{
			// get typesmodel
			$model = MembersmanagerHelper::getModel('types');
			$results = array_unique($results);
			foreach ($results as $add_relationship)
			{
				// Translate the add_relationship selection
				$text = $model->selectionTranslation($add_relationship,'add_relationship');
				// Now add the add_relationship and its text to the options array
				$_filter[] = JHtml::_('select.option', $add_relationship, JText::_($text));
			}
		}
		return $_filter;
	}
}
