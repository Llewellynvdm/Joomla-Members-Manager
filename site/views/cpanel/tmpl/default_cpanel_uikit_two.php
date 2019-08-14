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
<div class="uk-grid">
	<?php if ($this->searchForm && $this->user->authorise('member.create', 'com_membersmanager')): ?>
		<div class="uk-width-medium-1-2">
			<?php echo JLayoutHelper::render('cpanel_search_form', $this->searchForm); ?>
		</div>
		<div class="uk-width-medium-1-2">
			<?php echo JLayoutHelper::render('cpanel_uikit_two_buttons', true) ?>
		</div>
	<?php elseif ($this->searchForm): ?>
		<?php echo JLayoutHelper::render('cpanel_search_form', $this->searchForm); ?>
	<?php elseif ($this->user->authorise('member.create', 'com_membersmanager')): ?>
		<?php echo JLayoutHelper::render('cpanel_uikit_two_buttons', true) ?>
	<?php endif; ?>
</div>
