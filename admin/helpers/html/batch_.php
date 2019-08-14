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
defined('JPATH_PLATFORM') or die;

/**
 * Utility class to render a list view batch selection options
 *
 * @since  3.0
 */
abstract class JHtmlBatch_
{
	/**
	 * ListSelection
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected static $ListSelection = array();

	/**
	 * Render the batch selection options.
	 *
	 * @return  string  The necessary HTML to display the batch selection options
	 *
	 * @since   3.0
	 */
	public static function render()
	{
		// Collect display data
		$data                 = new stdClass;
		$data->ListSelection  = static::getListSelection();

		// Create a layout object and ask it to render the batch selection options
		$layout    = new JLayoutFile('batchselection');
		$batchHtml = $layout->render($data);

		return $batchHtml;
	}

	/**
	 * Method to add a list selection to the batch modal
	 *
	 * @param   string  $label      Label for the menu item.
	 * @param   string  $name       Name for the filter. Also used as id.
	 * @param   string  $options    Options for the select field.
	 * @param   bool    $noDefault  Don't the label as the empty option
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function addListSelection($label, $name, $options, $noDefault = false)
	{
		array_push(static::$ListSelection, array('label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault));
	}

	/**
	 * Returns an array of all ListSelection
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getListSelection()
	{
		return static::$ListSelection;
	}
}
