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

// dont load these in the list view
$notDisplay = array('id', 'created', 'modified');
// set last updated
if (isset($displayData['data']['created']))
{
	$updated = strtotime($displayData['data']['created']);
	// if it has been modified
	if (isset($displayData['data']['modified']) && MembersmanagerHelper::checkString($displayData['data']['modified']) && strpos($displayData['data']['modified'], '0000-00-00') === false)
	{
		$updated = strtotime($displayData['data']['modified']);
	}
}
else
{
	$updated = false;
}
$tracker = 0;

?>
<?php if (isset($displayData['data']) && MembersmanagerHelper::checkArray($displayData['data'])): ?>
	<ul class="uk-list uk-list-striped">
	<?php foreach ($displayData['data'] as $name => $value): ?>
		<?php if (!in_array($name, $notDisplay) && (MembersmanagerHelper::checkString($value) || is_numeric($value)) && $displayData['user']->authorise('form.view.' . $name, $displayData['com'] . '.form.' . (int) $displayData['data']['id'])): ?>
			<?php // build the label
				$LABLE = MembersmanagerHelper::safeString($displayData['com'], 'U') . '_FORM_' . MembersmanagerHelper::safeString($name, 'U') . '_LABEL';
				$label = JText::_($LABLE);
				// little workaround for now
				if ($LABLE === $label)
				{
					$label = MembersmanagerHelper::safeString($name, 'Ww');
				}
			?>
			<li><?php echo $label; ?>: <b><?php echo $value; ?></b></li>
			<?php $tracker++; ?>
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
	<?php if ($updated > 0 && $tracker > 0): ?>
		<br />
		<small><?php echo JText::_('COM_MEMBERSMANAGER_TIME_STAMP'); ?>: <?php echo MembersmanagerHelper::fancyDateTime($updated); ?></small>
	<?php else: ?>
		<small><?php echo JText::_('COM_MEMBERSMANAGER_NO_ACCESS_TO_VIEW_DETAILS'); ?>...</small>
	<?php endif; ?>
<?php else: ?>
	<small><?php echo JText::_('COM_MEMBERSMANAGER_NO_DETAILS_FOUND'); ?>...</small>
<?php endif; ?>
