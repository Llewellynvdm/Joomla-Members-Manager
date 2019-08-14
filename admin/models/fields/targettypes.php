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
 * Targettypes Form Field class for the Membersmanager component
 */
class JFormFieldTargettypes extends JFormFieldList
{
	/**
	 * The targettypes field type.
	 *
	 * @var		string
	 */
	public $type = 'targettypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
				// load the db opbject
		$db = JFactory::getDBO();
		// start query
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.name'),array('id','target_type_name')));
		$query->from($db->quoteName('#__membersmanager_type', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.name ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			foreach($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->target_type_name);
			}
		}
		return $options;
	}
}
