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

?>

<?php if ($this->user->id > 0): ?>
	<h1><?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?></h1>
	<div class="uk-grid">
		<div class="uk-width-medium-1-2">
			<?php if ($this->searchForm): ?>
				<div class="control-group">
					<div class="controls"><?php echo $this->searchForm->input;?></div>
				</div>
				<div id="members_found"></div>
				<script type="text/javascript">
				jQuery(function($) {
					$('#member_search').keyup(function(e) {
						var value = $(this).val();
						searchMembers(value);
					});
				});
				</script>
			<?php endif; ?>
		</div>
		<div class="uk-width-medium-1-2">
			<a class="uk-button uk-button-success uk-width-1-1" href="<?php echo JURI::root(); ?>index.php?option=com_membersmanager&view=members&task=member.edit&return=<?php echo urlencode(base64_encode((string) JUri::getInstance())); ?>">
				<i class="uk-icon-plus"></i> <?php echo JText::_('COM_MEMBERSMANAGER_CREATE'); ?>
			</a>
		</div>
	</div>
<?php else: ?>
	<?php echo $this->loadTemplate('loginmodule'); ?>
<?php endif; ?>
