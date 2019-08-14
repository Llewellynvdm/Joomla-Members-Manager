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

$edit = "index.php?option=com_membersmanager&view=members&task=member.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
	<?php
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
		$userChkOut = JFactory::getUser($item->checked_out);
		$canDo = MembersmanagerHelper::getActions('member',$item,'members');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
		<?php if ($canDo->get('member.edit.state')): ?>
			<?php
				if ($this->saveOrder)
				{
					$iconClass = ' inactive';
				}
				else
				{
					$iconClass = ' inactive tip-top" hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
				}
			?>
			<span class="sortable-handler<?php echo $iconClass; ?>">
				<i class="icon-menu"></i>
			</span>
			<?php if ($this->saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5"
				value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
			<?php endif; ?>
		<?php else: ?>
			&#8942;
		<?php endif; ?>
		</td>
		<td class="nowrap center">
		<?php if ($canDo->get('member.edit')): ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					<?php else: ?>
						&#9633;
					<?php endif; ?>
				<?php else: ?>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				<?php endif; ?>
		<?php else: ?>
			&#9633;
		<?php endif; ?>
		</td>
		<td class="nowrap">
			<div>
			<?php if ($canDo->get('member.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->name); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'members.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
			<?php if (3 == $item->account_id || 4 == $item->account_id): ?>
				<br /><small><?php echo JText::_('COM_MEMBERSMANAGER_MAIN_MEMBER'); ?>:
				<?php if ($this->user->authorise('member.edit', 'com_membersmanager.member.' . (int)$item->main_member)): ?>
					<a href="index.php?option=com_membersmanager&view=members&task=member.edit&id=<?php echo $item->main_member; ?>&ref=members">
						<?php echo MembersmanagerHelper::getMemberName($item->main_member, $item->main_member_user); ?>
					</a>
				<?php else: ?>
					<?php echo MembersmanagerHelper::getMemberName($item->main_member, $item->main_member_user); ?>
				<?php endif; ?>
				</small>
			<?php endif; ?>
			</div>
		</td>
		<td class="hidden-phone">
			<?php echo $this->escape($item->email); ?>
		</td>
		<td class="hidden-phone">
			<div>
			<?php echo $this->escape($item->type); ?>: <b>
			<?php echo JText::_($item->account); ?></b><br />
<em>
			<?php echo $this->escape($item->token); ?></em>
			</div>
		</td>
		<td class="center">
		<?php if ($canDo->get('member.edit.state')) : ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'members.', true, 'cb'); ?>
					<?php else: ?>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'members.', false, 'cb'); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'members.', true, 'cb'); ?>
				<?php endif; ?>
		<?php else: ?>
			<?php echo JHtml::_('jgrid.published', $item->published, $i, 'members.', false, 'cb'); ?>
		<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>