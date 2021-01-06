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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule (Memberloginname) class for the Joomla Platform.
 */
class JFormRuleMemberloginname extends FormRule
{
	/**
	 * Method to test the username for uniqueness.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		// Get the database object and a new query object.
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username = ' . $db->quote($value));

		// Get the user ID if set.
		$userId = ($form instanceof Form && ($userId = $form->getValue('user'))) ? $userId : (($input instanceof Registry && ($userId = $input->get('user'))) ? $userId : 0);
		// if still not user is found get member id
		if ($userId == 0)
		{
			$memberId = ($form instanceof Form && ($memberId = $form->getValue('id'))) ? $memberId : (($input instanceof Registry && ($memberId = $input->get('id'))) ? $memberId : 0);
		}
		// get account type if needed
		if ($userId == 0 && $memberId > 0)
		{
			$accountId = ($form instanceof Form && ($accountId = $form->getValue('account'))) ? $accountId : (($input instanceof Registry && ($accountId = $input->get('account'))) ? $accountId : 0);
			// make sure these account is set
			if ($accountId == 0)
			{
				$accountId = MembersmanagerHelper::getVar('member', $memberId, 'id', 'account');
			}
		}
		// get user value if not set (due to permissions)
		if ($userId == 0 && $memberId > 0 && $accountId > 0 && (1 == $accountId || 4 == $accountId))
		{
			$userId = MembersmanagerHelper::getVar('member', $memberId, 'id', 'user');
		}
		$query->where($db->quoteName('id') . ' <> ' . (int) $userId);

		// Set and query the database.
		$db->setQuery($query);
		$duplicate = (bool) $db->loadResult();

		if ($duplicate)
		{
			return false;
		}

		return true;
	}
}
