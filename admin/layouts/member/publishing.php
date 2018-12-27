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

$app = JFactory::getApplication();
$form = $displayData->getForm();

$fields = $displayData->get('fields') ?: array(
	'user',
	'created',
	'created_by',
	'modified',
	'modified_by'
);

$hiddenFields = $displayData->get('hidden_fields') ?: array();

foreach ($fields as $field)
{
	$field = is_array($field) ? $field : array($field);
	foreach ($field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hiddenFields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			echo $form->renderField($f);
			break;
		}
	}
}
