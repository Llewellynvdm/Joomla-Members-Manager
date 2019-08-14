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

// the bucket for uikit 2 classes
$this->classes = array();

?>
<form action="<?php echo JRoute::_('index.php?option=com_membersmanager'); ?>" method="post" name="adminForm" id="adminForm">
<?php if ($this->params->get('members_display_type', false) == 3) : // List ?>
	<?php echo $this->loadTemplate('member_list'); ?>
<?php elseif ($this->params->get('members_display_type', false) == 2) :  // Table ?>
	<?php echo $this->loadTemplate('member_table'); ?>
<?php else : // Panels (default) ?>
	<?php echo $this->loadTemplate('member_panels'); ?>
<?php endif; ?>

<?php
// load the needed components for uikit 2
if (2 == $this->uikitVersion && MembersmanagerHelper::checkArray($this->classes))
{
	// load just in case.
	jimport('joomla.filesystem.file');
	$size = $this->params->get('uikit_min');
	$style = $this->params->get('uikit_style');
	// loading...
	foreach ($this->classes as $class)
	{
		foreach (MembersmanagerHelper::$uk_components[$class] as $name)
		{
			// check if the CSS file exists.
			if (JFile::exists(JPATH_ROOT.'/media/com_membersmanager/uikit-v2/css/components/'.$name.$style.$size.'.css'))
			{
				// load the css.
				$this->document->addStyleSheet(JURI::root(true) .'/media/com_membersmanager/uikit-v2/css/components/'.$name.$style.$size.'.css', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// check if the JavaScript file exists.
			if (JFile::exists(JPATH_ROOT.'/media/com_membersmanager/uikit-v2/js/components/'.$name.$size.'.js'))
			{
				// load the js.
				$this->document->addScript(JURI::root(true) .'/media/com_membersmanager/uikit-v2/js/components/'.$name.$size.'.js', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript', (MembersmanagerHelper::jVersion()->isCompatible('3.8.0')) ? array('type' => 'text/javascript', 'async' => 'async') : true);
			}
		}
	}
}
?>

<?php if (isset($this->items) && isset($this->pagination) && isset($this->pagination->pagesTotal) && $this->pagination->pagesTotal > 1): ?>
	<div class="pagination">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> <?php echo $this->pagination->getLimitBox(); ?></p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
