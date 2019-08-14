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
defined('JPATH_BASE') or die('Restricted access');


// get all the assessment Components
$displayData->assessmentAvailable = MembersmanagerHelper::getAssessmentAvaillable($displayData->type, $displayData->account);
$displayData->setAssessment = MembersmanagerHelper::checkArray($displayData->assessmentAvailable);
// get names and values if found
if ($displayData->setAssessment)
{
	// get assessment details
	$displayData->assessments = array();
	foreach ($displayData->assessmentAvailable as $_name => $assessment)
	{
		if (MembersmanagerHelper::checkArray($assessment))
		{
			$displayData->assessments[$_name]  = array();
			foreach ($assessment as $_nr => $assess)
			{
				if ($displayData->_USER->authorise('form.report.viewtab', $assess->element))
				{
					$displayData->assessments[$_name][$_nr] = MembersmanagerHelper::getAnyFormDetails($displayData->id, 'member', $assess->element, 'object', 'profile');
					if (MembersmanagerHelper::checkArray($displayData->assessments[$_name][$_nr]))
					{
						foreach ($displayData->assessments[$_name][$_nr] as $_pointer => &$value)
						{
							if (isset($value->name) && MembersmanagerHelper::checkString($value->name))
							{
								$value->name = $assess->name . ' - ' . $value->name;
							}
							else
							{
								$value->name = $assess->name;
							}
						}
					}
					elseif (MembersmanagerHelper::checkObject($displayData->assessments[$_name][$_nr]))
					{
						if (isset($displayData->assessments[$_name][$_nr]->name) && MembersmanagerHelper::checkString($displayData->assessments[$_name][$_nr]->name))
						{
							$displayData->assessments[$_name][$_nr]->name = $assess->name . ' - ' . $displayData->assessments[$_name][$_nr]->name;
						}
						else
						{
							$displayData->assessments[$_name][$_nr]->name = $assess->name;
						}
					}
				}
			}
		}
		elseif ($displayData->_USER->authorise('form.report.viewtab', $assessment->element)  && MembersmanagerHelper::checkObject($assessment) && isset($assessment->element))
		{
			$displayData->assessments[$_name] = MembersmanagerHelper::getAnyFormDetails($displayData->id, 'member', $assessment->element, 'object', 'profile');
			if (MembersmanagerHelper::checkObject($displayData->assessments[$_name]))
			{
				if (isset($displayData->assessments[$_name]->name) && MembersmanagerHelper::checkString($displayData->assessments[$_name]->name))
				{
					$displayData->assessments[$_name]->name = $assessment->name . ' - ' . $displayData->assessments[$_name]->name;
				}
				else
				{
					$displayData->assessments[$_name]->name = $assessment->name;
				}
			}
		}
	}
}

?>
<?php if ($displayData->setAssessment) : ?>
	<?php if (isset($displayData->type_name) && MembersmanagerHelper::checkString($displayData->type_name) && $displayData->_USER->authorise('member.view.type', 'com_membersmanager.member.' . (int) $displayData->id)) : ?>
		<h5><?php echo JText::sprintf('COM_MEMBERSMANAGER_ACCESS_S_S', MembersmanagerHelper::safeString($displayData->type_name, 'W'), implode(', ', (array) array_keys($displayData->assessmentAvailable))); ?></h5>
	<?php else: ?>
		<h5><?php echo JText::sprintf('COM_MEMBERSMANAGER_ACCESS_MEMBER_S', implode(', ', (array) array_keys($displayData->assessmentAvailable))); ?></h5>
	<?php endif; ?>
	<?php if ($displayData->_USER->id > 0): ?>
		<?php echo JLayoutHelper::render('profilebuttons_uikit_three', $displayData); ?>
		<?php echo JLayoutHelper::render('profileextra_uikit_three', $displayData); ?>
	<?php endif; ?>
	<?php echo JLayoutHelper::render('profilereports_uikit_three', $displayData); ?>
	<?php echo JLayoutHelper::render('profileassessmentselection_uikit_three', $displayData); ?>
<?php else: ?>
	<?php echo JLayoutHelper::render('profileextra_uikit_three', $displayData); ?>
<?php endif; ?>
