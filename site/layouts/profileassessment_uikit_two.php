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
				$displayData->assessments[$_name][$_nr] = MembersmanagerHelper::getAnyFormDetails($displayData->id, 'member', $assess->element, 'object', 'profile');
				if (MembersmanagerHelper::checkArray($displayData->assessments[$_name][$_nr]))
				{
					foreach ($displayData->assessments[$_name][$_nr] as $_pointer => &$value)
					{
						$value->name = $assess->name;
					}
				}
				elseif (MembersmanagerHelper::checkObject($displayData->assessments[$_name][$_nr]))
				{
					$displayData->assessments[$_name][$_nr]->name = $assess->name;
				}
			}
		}
		elseif (MembersmanagerHelper::checkObject($assessment) && isset($assessment->element))
		{
			$displayData->assessments[$_name] = MembersmanagerHelper::getAnyFormDetails($displayData->id, 'member', $assessment->element, 'object', 'profile');
			if (MembersmanagerHelper::checkObject($displayData->assessments[$_name]))
			{
				$displayData->assessments[$_name]->name = $assessment->name;
			}
		}
	}
}

?>
<?php if ($displayData->setAssessment) : ?>
	<?php if (MembersmanagerHelper::checkString($displayData->type_name) && $displayData->_USER->authorise('member.view.type', 'com_membersmanager.member.' . (int) $displayData->id)) : ?>
		<h5><?php echo JText::sprintf('COM_MEMBERSMANAGER_ACCESS_S_S', MembersmanagerHelper::safeString($displayData->type_name, 'W'), implode(', ', (array) array_keys($displayData->assessmentAvailable))); ?></h5>
	<?php else: ?>
		<h5><?php echo JText::sprintf('COM_MEMBERSMANAGER_ACCESS_MEMBER_S', implode(', ', (array) array_keys($displayData->assessmentAvailable))); ?></h5>
	<?php endif; ?>
	<div class="uk-button-group uk-width-1-1">
		<a class="uk-button uk-width-1-3 uk-button-primary uk-button-small" href="#assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>"  data-uk-offcanvas="{mode:'slide'}">
			<i class="uk-icon-check-square-o"></i> <?php echo JText::_('COM_MEMBERSMANAGER_FORMS'); ?>
		</a>
		<button class="uk-button uk-width-1-3 uk-button-primary uk-button-small" data-uk-toggle="{target:'.extra<?php echo $displayData->id; ?>'}">
			<i class="uk-icon-bar-chart"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REPORTS'); ?>
		</button>
		<a class="uk-button uk-width-1-3 uk-button-primary uk-button-small" href="#report<?php echo $displayData->id; ?>">
			<i class="uk-icon-paper-plane"></i> <?php echo JText::_('COM_MEMBERSMANAGER_SEND_REPORT'); ?>
		</a>
	</div>
	<br /><br />
	<?php echo JLayoutHelper::render('profileextra_uikit_two', $displayData); ?>
	<?php echo JLayoutHelper::render('profilereports_uikit_two', $displayData); ?>
	<div id="assess-<?php echo MembersmanagerHelper::safeString($displayData->id); ?>" class="uk-offcanvas">
		<div class="uk-offcanvas-bar uk-offcanvas-bar-flip">
			<ul class="uk-nav uk-nav-offcanvas" data-uk-nav>
				<?php foreach ($displayData->assessmentAvailable as $typeAssesmentName => $components) : ?>
					<?php if  (MembersmanagerHelper::checkArray($components) || MembersmanagerHelper::checkObject($components)): ?>
						<li class="uk-nav-header"><?php echo JText::sprintf('COM_MEMBERSMANAGER_S_OPTIONS', $typeAssesmentName); ?></li>
						<?php if  (MembersmanagerHelper::checkArray($components)): ?>
							<?php foreach ($components as $component) : ?>
								<li><a href="index.php?option=<?php echo $component->element; ?>&view=form&field=member&field_id=<?php echo $displayData->id; ?>&layout=edit&return=<?php echo $displayData->return_path; ?>"><?php echo $component->name; ?></a></li>
							<?php endforeach; ?>
						<?php elseif  (MembersmanagerHelper::checkObject($components)): ?>
							<?php if (($id = MembersmanagerHelper::getVar('form', $displayData->id, 'member', 'id', '=', str_replace("com_", "", $components->element))) !== false): ?>
								<?php if (($link = MembersmanagerHelper::getEditURL($id, 'form', 'form', '&return=' . $displayData->return_path, $components->element)) !== false): ?>
									<li><a href="<?php echo $link; ?>"><?php echo $components->name; ?></a></li>
								<?php endif; ?>
							<?php else: ?>
								<li><a href="index.php?option=<?php echo $components->element; ?>&view=form&field=member&field_id=<?php echo $displayData->id; ?>&layout=edit&return=<?php echo $displayData->return_path; ?>"><?php echo $components->name; ?></a></li>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div id="getreport<?php echo $displayData->id; ?>" class="uk-modal">
		<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>
			<div class="report-spinner"><?php echo JText::_('COM_MEMBERSMANAGER_LOADING'); ?><span class="loading-dots"></span>.</div>
			<div class="setreport"></div>
		</div>
	</div>
<?php else: ?>
	<?php echo JLayoutHelper::render('profileextra_uikit_two', $displayData); ?>
<?php endif; ?>
