<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
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
			<div><?php if (1 == $item->account_id || 3 == $item->account_id): ?>
			<?php if ($canDo->get('member.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo JFactory::getUser((int)$item->user)->name; ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'members.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo JFactory::getUser((int)$item->user)->name; ?>
			<?php endif; ?>
			<?php else: ?>
			<?php if ($canDo->get('member.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->name); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'members.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $this->escape($item->name); ?>
			<?php endif; ?>
			<?php endif; ?>
			<?php if (2 == $item->account_id || 3 == $item->account_id): ?>
			<br /><small><?php echo JText::_('COM_MEMBERSMANAGER_MAIN_MEMBER'); ?>: 
			<?php if ($this->user->authorise('member.edit', 'com_membersmanager.member.' . (int)$item->main_member)): ?>
				<a href="index.php?option=com_membersmanager&view=members&task=member.edit&id=<?php echo $item->main_member; ?>&ref=members"><?php echo JFactory::getUser((int)$item->main_member_user)->name; ?></a>
			<?php else: ?>
				<?php echo JFactory::getUser((int)$item->main_member_user)->name; ?>
			<?php endif; ?></small>
			<?php endif; ?>
			<?php if (MembersmanagerHelper::checkString($item->street)): ?>
			<br />
			<?php echo $this->escape($item->street); ?>
			<?php endif; ?>
			<?php if (MembersmanagerHelper::checkString($item->city)): ?>
			<br />
			<?php echo $this->escape($item->city); ?>
			<?php endif; ?>
			<?php if ($item->region > 0): ?>
			<br />
			<?php if ($this->user->authorise('region.edit', 'com_membersmanager.region.' . (int)$item->region)): ?>
				<a href="index.php?option=com_membersmanager&view=regions&task=region.edit&id=<?php echo $item->region; ?>&ref=members"><?php echo $this->escape($item->region_name); ?></a>
			<?php else: ?>
				<?php echo $this->escape($item->region_name); ?>
			<?php endif; ?>
			<?php endif; ?>
			<?php if ($item->country > 0): ?>
			<br />
			<?php if ($this->user->authorise('country.edit', 'com_membersmanager.country.' . (int)$item->country)): ?>
				<a href="index.php?option=com_membersmanager&view=countries&task=country.edit&id=<?php echo $item->country; ?>&ref=members"><?php echo $this->escape($item->country_name); ?></a>
			<?php else: ?>
				<?php echo $this->escape($item->country_name); ?>
			<?php endif; ?>
			<?php endif; ?>
			<?php if (MembersmanagerHelper::checkString($item->website)): ?>
			<br />
			<?php echo $this->escape($item->website); ?>
			<?php endif; ?>
			</div>
		</td>
		<td class="hidden-phone">
			<div><?php if (MembersmanagerHelper::checkString($item->landline_phone)): ?>
			<?php echo $this->escape($item->landline_phone); ?>
			<?php endif; ?>
			<?php if (MembersmanagerHelper::checkString($item->mobile_phone)): ?>
			<?php if (MembersmanagerHelper::checkString($item->landline_phone)): ?><br />
			<?php endif; ?>
			<?php echo $this->escape($item->mobile_phone); ?>
			<?php endif; ?>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if ($this->user->authorise('type.edit', 'com_membersmanager.type.' . (int)$item->type)): ?>
					<a href="index.php?option=com_membersmanager&view=types&task=type.edit&id=<?php echo $item->type; ?>&ref=members"><?php echo $this->escape($item->type_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->type_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="hidden-phone">
			<?php echo JText::_($item->account); ?>
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