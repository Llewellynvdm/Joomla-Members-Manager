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


// get templates
$body_template = $this->params->get('list_template', '');
$item_template = $this->params->get('list_item_template', '');
// if uikit 2 then we need to load some more components if needed
if (2 == $this->uikitVersion)
{
	$this->classes = MembersmanagerHelper::getUikitComp($body_template, $this->classes);
	$this->classes = MembersmanagerHelper::getUikitComp($item_template, $this->classes);
}
// check that it has the [panel_template] placeholder
if (strpos($body_template, '[load_items]') !==false)
{
	$body_template = explode('[load_items]', $body_template);
}
else
{
	// we may need to do some defaults here
	$body_template = array();
}

?>

<?php if (isset($body_template[0])) : ?>
	<?php echo $body_template[0]; ?>
<?php endif; ?>
<?php foreach ($this->items as $item): ?>
	<?php echo membersmanagerHelper::setDynamicData($item_template, (array) $item); ?>
<?php endforeach; ?>
<?php if (isset($body_template[1])) :?>
	<?php echo $body_template[1]; ?>
<?php endif; ?>
