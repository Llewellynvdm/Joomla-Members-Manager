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
 * Mainmembers Form Field class for the Membersmanager component
 */
class JFormFieldMainmembers extends JFormFieldList
{
	/**
	 * The mainmembers field type.
	 *
	 * @var		string
	 */
	public $type = 'mainmembers';

	/**
	 * Override to add new button
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// see if we should add buttons
		$set_button = $this->getAttribute('button');
		// get html
		$html = parent::getInput();
		// if true set button
		if ($set_button === 'true')
		{
			$button = array();
			$script = array();
			$button_code_name = $this->getAttribute('name');
			// get the input from url
			$app = JFactory::getApplication();
			$jinput = $app->input;
			// get the view name & id
			$values = $jinput->getArray(array(
				'id' => 'int',
				'view' => 'word'
			));
			// check if new item
			$ref = '';
			$refJ = '';
			if (!is_null($values['id']) && strlen($values['view']))
			{
				// only load referral if not new item.
				$ref = '&amp;ref=' . $values['view'] . '&amp;refid=' . $values['id'];
				$refJ = '&ref=' . $values['view'] . '&refid=' . $values['id'];
				// get the return value.
				$_uri = (string) JUri::getInstance();
				$_return = urlencode(base64_encode($_uri));
				// load return value.
				$ref .= '&amp;return=' . $_return;
				$refJ .= '&return=' . $_return;
			}
			// get button label
			$button_label = trim($button_code_name);
			$button_label = preg_replace('/_+/', ' ', $button_label);
			$button_label = preg_replace('/\s+/', ' ', $button_label);
			$button_label = preg_replace("/[^A-Za-z ]/", '', $button_label);
			$button_label = ucfirst(strtolower($button_label));
			// get user object
			$user = JFactory::getUser();
			// only add if user allowed to create member
			if ($user->authorise('member.create', 'com_membersmanager') && $app->isAdmin()) // TODO for now only in admin area.
			{
				// build Create button
				$button[] = '<a id="'.$button_code_name.'Create" class="btn btn-small btn-success hasTooltip" title="'.JText::sprintf('COM_MEMBERSMANAGER_CREATE_NEW_S', $button_label).'" style="border-radius: 0px 4px 4px 0px; padding: 4px 4px 4px 7px;"
					href="index.php?option=com_membersmanager&amp;view=member&amp;layout=edit'.$ref.'" >
					<span class="icon-new icon-white"></span></a>';
			}
			// only add if user allowed to edit member
			if ($user->authorise('member.edit', 'com_membersmanager') && $app->isAdmin()) // TODO for now only in admin area.
			{
				// build edit button
				$button[] = '<a id="'.$button_code_name.'Edit" class="btn btn-small hasTooltip" title="'.JText::sprintf('COM_MEMBERSMANAGER_EDIT_S', $button_label).'" style="display: none; padding: 4px 4px 4px 7px;" href="#" >
					<span class="icon-edit"></span></a>';
				// build script
				$script[] = "
					jQuery(document).ready(function() {
						jQuery('#adminForm').on('change', '#jform_".$button_code_name."',function (e) {
							e.preventDefault();
							var ".$button_code_name."Value = jQuery('#jform_".$button_code_name."').val();
							".$button_code_name."Button(".$button_code_name."Value);
						});
						var ".$button_code_name."Value = jQuery('#jform_".$button_code_name."').val();
						".$button_code_name."Button(".$button_code_name."Value);
					});
					function ".$button_code_name."Button(value) {
						if (value > 0) {
							// hide the create button
							jQuery('#".$button_code_name."Create').hide();
							// show edit button
							jQuery('#".$button_code_name."Edit').show();
							var url = 'index.php?option=com_membersmanager&view=members&task=member.edit&id='+value+'".$refJ."';
							jQuery('#".$button_code_name."Edit').attr('href', url);
						} else {
							// show the create button
							jQuery('#".$button_code_name."Create').show();
							// hide edit button
							jQuery('#".$button_code_name."Edit').hide();
						}
					}";
			}
			// check if button was created for member field.
			if (is_array($button) && count($button) > 0)
			{
				// Load the needed script.
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(implode(' ',$script));
				// return the button attached to input field.
				return '<div class="input-append">' .$html . implode('',$button).'</div>';
			}
		}
		return $html;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		
		// get the user
		$my = JFactory::getUser();
		// load the db opbject
		$db = JFactory::getDBO();
		// start the query
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.user','a.account','a.name','a.email','a.token'),array('id','main_member_user','account','name','email','token')));
		$query->from($db->quoteName('#__membersmanager_member', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->where($db->quoteName('a.account') . ' = 1 OR ' . $db->quoteName('a.account') . ' = 2');
		// check if current user is an admin
		if (!$my->authorise('core.options', 'com_membersmanager'))
		{
			// get user access groups
			if (($user_access_types =  MembersmanagerHelper::getAccess($my)) === false || !MembersmanagerHelper::checkArray($user_access_types))
			{
				return false;
			}
			//filter by type
			$query->join('LEFT', $db->quoteName('#__membersmanager_type_map', 't') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('t.member') . ')');
			$user_access_types = implode(',', $user_access_types);
			$query->where('t.type IN (' . $user_access_types . ')');
			// also filter by access (will keep an eye on this)
			$groups = implode(',', $my->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		$query->order('a.user ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		// get the input from url
		$jinput = JFactory::getApplication()->input;
		// get the id
		$id = $jinput->getInt('id', 0);
		if ($id > 0)
		{
			$main_member = MembersmanagerHelper::getVar('member', $id, 'id', 'main_member');
		}
		$options = array();
		if (MembersmanagerHelper::checkArray($items))
		{
			// only add if more then one value found
			if (count( (array) $items) > 1)
			{
				$options[] = JHtml::_('select.option', '', 'Select a main member');
			}
			foreach($items as $item)
			{
				// check if we current member
				if (isset($main_member) && $main_member == $item->id)
				{
					// remove ID
					$main_member = 0;
				}
				if ($item->account == 1)
				{
					$options[] = JHtml::_('select.option', $item->id, JFactory::getUser((int) $item->main_member_user)->name . ' ' . JFactory::getUser((int) $item->main_member_user)->email . ' ( ' . $item->token . ' )');
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->id, $item->name . ' ' . $item->email . ' ( ' . $item->token . ' )');
				}
			}
		}
		// add the current user (TODO this is not suppose to happen)
		if (isset($main_member) && $main_member > 0)
		{
			// load the current member manual
			$options[] = JHtml::_('select.option', (int) $main_member, MembersmanagerHelper::getMemberName($main_member));
		}
		return $options;
	}
}
