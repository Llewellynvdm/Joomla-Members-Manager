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

// switch hidden state
$hidden = ($displayData->setCharts) ? ' hidden' : '';

?>
<?php if ($displayData->_USER->id > 0): ?>
<div class="extra<?php echo $displayData->id; ?>"<?php echo $hidden; ?>>
<?php endif; ?>
<?php if ($displayData->setAssessment): ?>
<div class="uk-overflow-auto uk-height-small">
	<ul class="uk-list uk-list-striped">
	<?php foreach ($displayData->assessments as $name => $assessments): ?>
		<?php if (MembersmanagerHelper::checkArray($assessments, true)): ?>
			<li><b><?php echo $name; ?></b></li>
			<?php foreach ($assessments as $_nr => $values): ?>
				<?php if (MembersmanagerHelper::checkArray($values, true)): ?>
					<?php foreach ($values as $value): ?>
						<li>
							<?php 
								// set key
								$report_key = MembersmanagerHelper::lock(array(
									'id' => $value->id,
									'element' => $displayData->assessmentAvailable[$name][$_nr]->element,
									'type' => $displayData->type,
									'account' => $displayData->account));
							?>
							<span uk-icon="icon: chevron-right"></span>  <a href="#getreport" onclick="getReport(<?php echo (int) $value->id; ?>, '<?php echo $displayData->assessmentAvailable[$name][$_nr]->element; ?>', '<?php echo $report_key; ?>');" uk-toggle><?php echo $value->name; ?></a> (<?php echo MembersmanagerHelper::fancyDayTimeDate($value->created); ?>)
							<?php echo MembersmanagerHelper::getEditButton($value->id, 'form', 'form', '&return=' . $displayData->return_path, $displayData->assessmentAvailable[$name][$_nr]->element, null); ?>
						</li>
					<?php endforeach; ?>
				<?php elseif (MembersmanagerHelper::checkObject($values) && isset($values->created)): ?>
					<li>
						<?php 
							// set key
							$report_key = MembersmanagerHelper::lock(array(
								'id' => $values->id,
								'element' => $displayData->assessmentAvailable[$name][$_nr]->element,
								'type' => $displayData->type,
								'account' => $displayData->account));
						?>
						<span uk-icon="icon: chevron-right"></span>  <a href="#getreport" onclick="getReport(<?php echo (int) $values->id; ?>, '<?php echo $displayData->assessmentAvailable[$name][$_nr]->element; ?>', '<?php echo $report_key; ?>');" uk-toggle><?php echo $values->name; ?></a> (<?php echo MembersmanagerHelper::fancyDayTimeDate($values->created); ?>)
						<?php echo MembersmanagerHelper::getEditButton($values->id, 'form', 'form', '&return=' . $displayData->return_path, $displayData->assessmentAvailable[$name][$_nr]->element, null); ?>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php elseif (MembersmanagerHelper::checkObject($assessments) && isset($assessments->created)): ?>
			<li><b><?php echo $name; ?></b></li>
			<li>
				<?php 
					// set key
					$report_key = MembersmanagerHelper::lock(array(
						'id' => $assessments->id,
						'element' => $displayData->assessmentAvailable[$name]->element,
						'type' => $displayData->type,
						'account' => $displayData->account));
				?>
				<span uk-icon="icon: chevron-right"></span>  <a href="#getreport" onclick="getReport(<?php echo (int) $assessments->id; ?>, '<?php echo $displayData->assessmentAvailable[$name]->element; ?>', '<?php echo $report_key; ?>');" uk-toggle><?php echo $assessments->name; ?></a> (<?php echo MembersmanagerHelper::fancyDayTimeDate($assessments->created); ?>)
				<?php echo MembersmanagerHelper::getEditButton($assessments->id, 'form', 'form', '&return=' . $displayData->return_path, $displayData->assessmentAvailable[$name]->element, null); ?>
			</li>
		<?php else: ?>
			<li><?php echo JText::sprintf('COM_MEMBERSMANAGER_NO_S_FOUND_IN_S', MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS')), $name); ?></li>
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
</div>
<?php else: ?>
	<small><?php echo JText::sprintf('COM_MEMBERSMANAGER_NO_S_FOUND', MembersmanagerHelper::getButtonName('report', JText::_('COM_MEMBERSMANAGER_REPORTS'))); ?>...</small>
<?php endif; ?>
<?php if ($displayData->_USER->id > 0): ?>
</div>
<?php endif; ?>
