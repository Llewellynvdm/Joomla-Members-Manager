/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

/* JS Document */
// Get report based on id & element from Server
function getReport_server(report_key){
	var getUrl = JRouter("index.php?option=com_membersmanager&task=ajax.getReport&format=json");
	if(token.length > 0 && report_key.length > 0){
		var request = 'token='+token+'&key='+report_key;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
// get report to display
function getReport(id, element, report_key){
	// remove old data and add spinner
	jQuery('#setreport').html('');
	jQuery('#report-spinner').show();
	// get key
	var key = id+element;
	// first we see if we have local storage of this data
	var data = null; // jQuery.jStorage.get(key);
	if (!data) {
		getReport_server(report_key).done(function(result) {
			if(result.html){
				setReport(result.html);
				// store the data for next time
				jQuery.jStorage.set(key,result.html,{TTL: expire});
			} else if(result.error){
				// set an error if item date could not return
				setReport(result.error);
			} else {
				// set an error if item date could not return
				setReport(Joomla.JText._('COM_MEMBERSMANAGER_THERE_WAS_NO_REPORT_FOUND'));
			}
		});
	} else {
		setReport(data, key);
		// make sure to keep the Time To Live updated
		jQuery.jStorage.setTTL(key,expire);
	}
} 
// set the Report
function setReport(data) {
	// show data
	jQuery('#setreport').html(data);
	// hide spinner
	jQuery('#report-spinner').hide();
}