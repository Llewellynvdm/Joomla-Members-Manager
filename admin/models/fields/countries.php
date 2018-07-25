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

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Countries Form Field class for the Membersmanager component
 */
class JFormFieldCountries extends JFormFieldList
{
	/**
	 * The countries field type.
	 *
	 * @var		string
	 */
	public $type = 'countries';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	public function getOptions()
	{
		$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select($db->quoteName(array('a.id','a.name'),array('id','country_name')));
$query->from($db->quoteName('#__membersmanager_country', 'a'));
$query->where($db->quoteName('a.published') . ' = 1');
$query->order('a.name ASC');
$db->setQuery((string)$query);
$items = $db->loadObjectList();
$options = array();
if ($items)
{
	$options[] = JHtml::_('select.option', '', 'Select a country');
	foreach($items as $item)
	{
		$options[] = JHtml::_('select.option', $item->id, $item->country_name);
	}
}

return $options;
	}
}
