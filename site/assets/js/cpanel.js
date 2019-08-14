/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */


/* JS Document */
// the search method
function searchMembers(value){
	jQuery.get(path+value)
	.success(function(result) {
		// display result
		displaySearchResult(result);
	})
	.error(function(jqXHR, textStatus, errorThrown) { 
		// will add some sort of error message
		jQuery('#members_found').html('');
	});
}
function displaySearchResult(value){
	// clear old result and update with new
	jQuery('#members_found').html(value);
}