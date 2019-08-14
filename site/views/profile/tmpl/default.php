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

JHtml::_('bootstrap.modal');
// check if this was directed here from a list view of member manager
$return_to = $this->app->input->get('return', null, 'base64');
if (!is_null($return_to) && \JUri::isInternal(base64_decode($return_to)))
{
	$return_to = base64_decode($return_to);
}

?>

<?php if ($this->user->id > 0 || 2 == $this->params->get('login_required', 1)): ?>
	<?php if (!MembersmanagerHelper::checkString($return_to)): ?>
		<?php if ($this->user->authorise('site.cpanel.access', 'com_membersmanager')): ?>
			<a class="uk-button uk-width-1-1 uk-button-primary uk-button-small uk-margin-small-bottom" href="<?php echo JRoute::_(MembersmanagerHelperRoute::getCpanelRoute()); ?>" title="<?php echo JText::_('COM_MEMBERSMANAGER_OPEN_CPANEL'); ?>">
				<?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?>
			</a>
		<?php endif; ?>
	<?php else: ?>
		<?php if ($this->user->authorise('site.cpanel.access', 'com_membersmanager')): ?>
			<div class="uk-button-group uk-width-1-1">
				<a class="uk-button uk-width-1-2 uk-button-primary uk-button-small uk-margin-small-bottom" href="<?php echo JRoute::_($return_to); ?>" title="<?php echo JText::_('COM_MEMBERSMANAGER_GO_BACK'); ?>">
					<?php echo JText::_('COM_MEMBERSMANAGER_BACK'); ?>
				</a>
				<a class="uk-button uk-width-1-2 uk-button-primary uk-button-small uk-margin-small-bottom" href="<?php echo JRoute::_(MembersmanagerHelperRoute::getCpanelRoute()); ?>" title="<?php echo JText::_('COM_MEMBERSMANAGER_OPEN_CPANEL'); ?>">
					<?php echo JText::_('COM_MEMBERSMANAGER_CPANEL'); ?>
				</a>
			</div>
		<?php else: ?>
			<a class="uk-button uk-width-1-1 uk-button-primary uk-button-small uk-margin-small-bottom" href="<?php echo JRoute::_($return_to); ?>" title="<?php echo JText::_('COM_MEMBERSMANAGER_GO_BACK'); ?>">
				<?php echo JText::_('COM_MEMBERSMANAGER_BACK'); ?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
	<?php if (2 == $this->params->get('login_required', 1) || MembersmanagerHelper::canAccessMember($this->item->id, $this->item->type, $this->user)): ?>
		<?php
			// remove main member if this user does not have access and login is required
			if (1 == $this->params->get('login_required', 1) && isset($this->item->main_member) && !MembersmanagerHelper::canAccessMember($this->item->main_member, null, $this->user))
			{
				unset($this->item->main_member);
			}
		?>
		<?php echo $this->loadTemplate('profiles'); ?>
		<script type="text/javascript">
			// token 
			var token = '<?php echo JSession::getFormToken(); ?>';
			
		function printMe(name, printDivId) {
			printWindow = window.open('','printwindow', "location=1,status=1,scrollbars=1");
			if(!printWindow)alert('<?php echo JText::_('COM_MEMBERSMANAGER_PLEASE_ENABLE_POPUPS_IN_YOUR_BROWSER_FOR_THIS_WEBSITE_TO_PRINT_THESE_DETAILS'); ?>');
			printWindow.document.write('<html moznomarginboxes mozdisallowselectionprint><head><title>'+name+'</title><link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>media/com_membersmanager/uikit-v2/css/uikit.css">');
			printWindow.document.write('<link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>media/com_membersmanager/css/A4.print.css">');
			//Print and cancel button
			printWindow.document.write('</head><body >');
			printWindow.document.write('<div class="uk-button-group uk-width-1-1 no-print"><button type="button" class="uk-button uk-width-1-2 uk-button-success" onclick="window.print(); window.close();" ><i class="uk-icon-print"></i> <?php echo JText::_('COM_MEMBERSMANAGER_PRINT_CLOSE'); ?></button>');
			printWindow.document.write('<button type="button" class="uk-button uk-width-1-2 uk-button-danger" onclick="window.close();"><i class="uk-icon-close"></i> <?php echo JText::_('COM_MEMBERSMANAGER_CLOSE'); ?></button></div><page size="A4">');
			printWindow.document.write(jQuery('#'+printDivId).html());
			printWindow.document.write('</page></body></html>');
			printWindow.document.close();
			printWindow.focus()
		}
			
<?php
	$app = JFactory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isSite())
	{
		echo 'var url = "'.JURI::root().'";';
	}
	else
	{
		echo 'var url = "";';
	}
?>
	return url+link;
} 
			
// nice little dot trick :)
jQuery(document).ready( function($) {
  var x=0;
  setInterval(function() {
	var dots = "";
	x++;
	for (var y=0; y < x%8; y++) {
		dots+=".";
	}
	$(".loading-dots").text(dots);
  } , 500);
});
		</script>
	<?php else: ?>
		<div class="uk-alert uk-alert-large uk-alert-danger" data-uk-alert="">
			<h2><?php echo JText::_('COM_MEMBERSMANAGER_NO_ACCESS'); ?></h2>
			<p><?php echo JText::_('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_ACCESS_TO_THIS_AREA'); ?></p>
		</div>
	<?php endif; ?>
<?php else: ?>
	<?php echo $this->loadTemplate('loginmodule'); ?>
<?php endif; ?>
