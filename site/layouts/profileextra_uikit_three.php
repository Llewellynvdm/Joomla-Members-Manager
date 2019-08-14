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


// set default chart switch to false
$displayData->setCharts = false;
// get names and values if found
if ($displayData->setAssessment)
{
	// get Document
	$document = JFactory::getDocument();
	// Chart Target Types
	$targets = array(2,3);
	// get chart details
	$displayData->charts = array();
	$_y = MembersmanagerHelper::safeString($displayData->id);
	foreach ($displayData->assessmentAvailable as $_name => $assessment)
	{
		// set the chart array per/assessment type
		$displayData->charts[$_name] = array();
		if (MembersmanagerHelper::checkArray($assessment))
		{
			foreach ($assessment as $_nr => $assess)
			{
				if ($displayData->_USER->authorise('form.report.viewtab', $assess->element))
				{
					foreach ($targets as $target)
					{
						if (($carts = MembersmanagerHelper::getAnyAvailableCharts(null, $target, $assess->element)) !== false)
						{
							foreach ($carts as $key => $cartData)
							{
								if (($dataTable = MembersmanagerHelper::getAnyMultiChartDataTable($displayData->id, $target, $key, $assess->element)) !== ''
									&& ($code = MembersmanagerHelper::getAnyChartCode($key . $_y, $dataTable, $cartData['details'], 'profile', $assess->element)) !== false)
								{
									// load code
									$displayData->charts[$_name][] = $code;
									// add script to document
									$document->addScriptDeclaration($code['script']);
									// set loading of charts
									$displayData->setCharts = true;
								}
							}
						}
					}
				}
			}
		}
		elseif ($displayData->_USER->authorise('form.report.viewtab', $assessment->element) && MembersmanagerHelper::checkObject($assessment) && isset($assessment->element))
		{
			foreach ($targets as $target)
			{
				if (($carts = MembersmanagerHelper::getAnyAvailableCharts(null, $target, $assess->element)) !== false)
				{
					foreach ($carts as $key => $cartData)
					{
						if (($dataTable = MembersmanagerHelper::getAnyMultiChartDataTable($displayData->id, $target, $key, $assessment->element)) !== ''
							&& ($code = MembersmanagerHelper::getAnyChartCode($key . $_y, $dataTable, $cartData['details'], 'profile', $assessment->element)) !== false)
						{
							// load code
							$displayData->charts[$_name][] = $code;
							// add script to document
							$document->addScriptDeclaration($code['script']);
							// set loading of charts
							$displayData->setCharts = true;
						}
					}
				}
			}
		}
	}
}
// switch hidden state
$hidden = ($displayData->setCharts) ? '' : ' hidden';

?>
<div class="extra<?php echo $displayData->id; ?>"<?php echo $hidden; ?>>
<?php if ($displayData->setCharts): ?>
	<?php foreach ($displayData->charts as $name => $codes): ?>
		<?php foreach ($codes as $code): ?>
			<a href="#getreport" onclick="loadTheChartInModal(<?php echo $code['function_name']; ?>, '<?php echo $code['id_name']; ?>')" class="uk-thumbnail uk-thumbnail-small <?php echo $code['id_name']; ?>_target" uk-toggle><?php echo $code['div']; ?></a>
		<?php endforeach; ?>
	<?php endforeach; ?>
<?php else: ?>
	<div uk-alert>
		<p><?php echo JText::_('COM_MEMBERSMANAGER_NOT_ENOUGH_DATA'); ?>.</p>
	</div>
<?php endif; ?>
</div>
