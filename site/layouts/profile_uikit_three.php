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
<article class="uk-comment uk-comment-primary">
	<?php echo JLayoutHelper::render('profileheader_uikit_three', $displayData); ?>
	<div class="uk-comment-body" uk-grid>
		<div class="uk-width-1-3@m">
			<?php echo JLayoutHelper::render('profiledetails_uikit_three', $displayData); ?>
		</div>
		<div class="uk-width-expand@m">
			<?php echo JLayoutHelper::render('profileassessment_uikit_three', $displayData); ?>
		</div>
	</div>
</article>
