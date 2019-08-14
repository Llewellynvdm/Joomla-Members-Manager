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



?>
<?php if (isset($displayData['relationships']) && MembersmanagerHelper::checkArray($displayData['relationships'])) : ?>
	<li>
		<ul class="uk-list">
		<?php $create = ''; //MembersmanagerHelper::getCreateButton('member', 'members', '&return=' . $displayData['return_path'], 'com_membersmanager', null); ?>
		<?php foreach ($displayData['relationships'] as $type => $linked_members): ?>
			<?php if (isset($displayData['relationshipTypes'][$type])): ?>
				<li><b><small><?php echo $displayData['relationshipTypes'][$type]->name; ?></small></b> <?php echo $create; ?></li>
				<?php foreach ($linked_members as $linked_member): ?>
					<?php $edit = MembersmanagerHelper::getEditButton($linked_member, 'member', 'member', '&return=' . $displayData['return_path'], 'com_membersmanager', null); ?>
					<li><?php echo MembersmanagerHelper::getMemberName($linked_member); ?> <?php echo $edit; ?></li>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</li>
<?php endif; ?>
