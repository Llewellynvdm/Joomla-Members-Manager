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
 * Allowedimageformats Form Field class for the Membersmanager component
 */
class JFormFieldAllowedimageformats extends JFormFieldList
{
	/**
	 * The allowedimageformats field type.
	 *
	 * @var		string
	 */
	public $type = 'allowedimageformats';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		
		// check if helper class already is set
		if (!class_exists('MembersmanagerHelper'))
		{
			// set the correct path focus
			$focus = JPATH_ADMINISTRATOR;
			// check if we are in the site area
			if (JFactory::getApplication()->isSite())
			{
				// set admin path
				$adminPath = $focus . '/components/com_membersmanager/helpers/membersmanager.php';
				// change the focus
				$focus = JPATH_ROOT;
			}
			// set path based on focus
			$path = $focus . '/components/com_membersmanager/helpers/membersmanager.php';
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
		// Start the options array
		$options = array();
		// Get the extensions list.
		$extensionList = MembersmanagerHelper::getFileExtensions('image', true);
		if (MembersmanagerHelper::checkArray($extensionList))
		{
			foreach($extensionList as $type => $extensions)
			{
				foreach($extensions as $extension)
				{
					$options[] = JHtml::_('select.option', $extension, $extension . ' [ ' . $type . ' ]');
				}
			}
		}
		return $options;
	}
}
