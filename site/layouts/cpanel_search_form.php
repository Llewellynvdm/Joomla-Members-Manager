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
<div class="control-group">
	<div class="controls"><?php echo $displayData->input;?></div>
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
