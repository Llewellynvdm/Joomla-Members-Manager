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
 * Onetomanycomponents Form Field class for the Membersmanager component
 */
class JFormFieldOnetomanycomponents extends JFormFieldList
{
	/**
	 * The onetomanycomponents field type.
	 *
	 * @var		string
	 */
	public $type = 'onetomanycomponents';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		
		$options = array();
		// get the component name
		$component = 'membersmanager';
		// set the element name
		$_component = 'com_membersmanager';
		// check if it is already set
		if (!class_exists('MembersmanagerHelper'))
		{
			// set the correct path focus
			$focus = JPATH_ADMINISTRATOR;
			// check if we are in the site area
			if (JFactory::getApplication()->isSite())
			{
				// set admin path
				$adminPath = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
				// change the focus
				$focus = JPATH_ROOT;
			}
			// set path based on focus
			$path = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
			// check if file exist, if not try admin again.
			if (file_exists($path))
			{
				// make sure to load the helper
				JLoader::register('MembersmanagerHelper', $path);
			}
			// fallback option
			elseif (isset($adminPath) && file_exists($adminPath))
			{
				// make sure to load the helper
				JLoader::register('MembersmanagerHelper', $adminPath);
			}
			else
			{
				// could not find this
				return false;
			}
		}
		// Get the components
		if (($components = MembersmanagerHelper::getAllComponents(2)) !== false)
		{
			// since used in multiple fields we need to test if this is a multi select or not
			$multiple = $this->getAttribute('multiple', false);
			if (!$multiple || $multiple === 'false')
			{
				$options[] = JHtml::_('select.option', '', JText::_('COM_MEMBERSMANAGER_SELECT_AN_OPTION'));
			}
			// now load the items
			foreach($components as $item)
			{
				$type = (isset($item->params->activate_membersmanager_assessment)) ? $item->params->assessment_type_name : JText::_('COM_MEMBERSMANAGER_INFO');
				$options[] = JHtml::_('select.option', $item->element, $item->name . ' (' . $type . ')');
			}
		}
		return $options;
	}
}
