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

// set the name and email based on account type
if (1 == $displayData->account || 4 == $displayData->account)
{
	$displayData->email = $displayData->user_email;
}

?>
<article class="uk-comment">
	<div class="uk-panel uk-panel-box">
		<?php echo JLayoutHelper::render('profileheader_uikit_two', $displayData); ?>
		<div class="uk-comment-body uk-grid">
			<div class="uk-width-medium-3-10">
				<?php echo JLayoutHelper::render('profiledetails_uikit_two', $displayData); ?>
			</div>
			<div class="uk-width-medium-7-10">
				<?php echo JLayoutHelper::render('profileassessment_uikit_two', $displayData); ?>
			</div>
		</div>
	</div>
</article>
