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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.calendar');
$componentParams = $this->params; // will be removed just use $this->params instead
?>
<?php echo $this->toolbar->render(); ?>
<form action="<?php echo JRoute::_('index.php?option=com_membersmanager&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

	<?php echo JLayoutHelper::render('member.membership_above', $this); ?>
<div class="form-horizontal">

	<?php echo JHtml::_('bootstrap.startTabSet', 'memberTab', array('active' => 'membership')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'memberTab', 'membership', JText::_('COM_MEMBERSMANAGER_MEMBER_MEMBERSHIP', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('member.membership_left', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('member.membership_right', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo MembersmanagerHelper::loadDynamicTabs($this->item, 'member', $this->referral); // Auto adding of bootstrap.addTab ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'memberTab'; ?>
	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('member.delete') || $this->canDo->get('member.edit.created_by') || $this->canDo->get('member.edit.state') || $this->canDo->get('member.edit.created')) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'memberTab', 'publishing', JText::_('COM_MEMBERSMANAGER_MEMBER_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('member.publishing', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('member.publlshing', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'memberTab', 'permissions', JText::_('COM_MEMBERSMANAGER_MEMBER_PERMISSION', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<fieldset class="adminform">
					<div class="adminformlist">
					<?php foreach ($this->form->getFieldset('accesscontrol') as $field): ?>
						<div>
							<?php echo $field->label; echo $field->input;?>
						</div>
						<div class="clearfix"></div>
					<?php endforeach; ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="member.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>

<script type="text/javascript">

// #jform_account listeners for account_vvvvvvv function
jQuery('#jform_account').on('keyup',function()
{
	var account_vvvvvvv = jQuery("#jform_account").val();
	vvvvvvv(account_vvvvvvv);

});
jQuery('#adminForm').on('change', '#jform_account',function (e)
{
	e.preventDefault();
	var account_vvvvvvv = jQuery("#jform_account").val();
	vvvvvvv(account_vvvvvvv);

});

// #jform_account listeners for account_vvvvvvw function
jQuery('#jform_account').on('keyup',function()
{
	var account_vvvvvvw = jQuery("#jform_account").val();
	vvvvvvw(account_vvvvvvw);

});
jQuery('#adminForm').on('change', '#jform_account',function (e)
{
	e.preventDefault();
	var account_vvvvvvw = jQuery("#jform_account").val();
	vvvvvvw(account_vvvvvvw);

});

// #jform_account listeners for account_vvvvvvx function
jQuery('#jform_account').on('keyup',function()
{
	var account_vvvvvvx = jQuery("#jform_account").val();
	vvvvvvx(account_vvvvvvx);

});
jQuery('#adminForm').on('change', '#jform_account',function (e)
{
	e.preventDefault();
	var account_vvvvvvx = jQuery("#jform_account").val();
	vvvvvvx(account_vvvvvvx);

});




<?php if ($formats = $this->params->get('image_formats', null)) : ?>
	<?php $uikitVersion = $this->params->get('uikit_version', 2);  // get Uikit Version ?>
	// set some details
	var sizeNotice = '';
	<?php if ($resize = $this->params->get('crop_profile', null)) : ?>
		var sizeprofile = '(';
		<?php if ($width = $this->params->get('profile_width', null)): ?>
			sizeprofile += 'width: <?php echo $width; ?>px';
		<?php else: ?>
			sizeprofile += 'width: <?php echo JText::_('COM_MEMBERSMANAGER_PROPORTIONALLY'); ?>';
		<?php endif; ?>
		<?php if ($height = $this->params->get('profile_height', null)): ?>
			sizeprofile += '  height: <?php echo $height; ?>px';
		<?php else: ?>
			sizeprofile += '  height: <?php echo JText::_('COM_MEMBERSMANAGER_PROPORTIONALLY'); ?>';
		<?php endif; ?>
		sizeprofile += ')';
		<?php if (2 == $uikitVersion) : ?>
			sizeNotice = '<span data-uk-tooltip title="<?php echo JText::_('COM_MEMBERSMANAGER_THE_PROFILE_WILL_BE_CROPPED_TO_THIS_SIZE'); ?>">'+sizeprofile+'</span>';
		<?php else: ?>
			sizeNotice = '<span uk-tooltip title="<?php echo JText::_('COM_MEMBERSMANAGER_THE_PROFILE_WILL_BE_CROPPED_TO_THIS_SIZE'); ?>">'+sizeprofile+'</span>';
		<?php endif; ?>
	<?php endif; ?>
	// load the UIKIT script
	<?php if (2 == $uikitVersion) : ?>
	// load uikit 2 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikittwo-profile-image-uploader').show();
		$('#uikitthree-profile-image-uploader').remove();
		$('#error-profile-image-uploader').remove();
		$('#size-profile').html(sizeNotice);
		$('#profile-image-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var progressbar = $("#uikittwo-progressbar-profile-image"),
		bar         = progressbar.find('.uk-progress-bar'),
		settings    = {

			action: JRouter('index.php?option=com_membersmanager&task=ajax.uploadfile&format=json&type=image&target=profile&raw=true&token='+token+'&vdm='+vastDevMod), // upload url

			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

			loadstart: function() {
				jQuery(".success-profile-image-8768").remove();
				bar.css("width", "0%").text("0%");
				progressbar.removeClass("uk-hidden");
			},

			progress: function(percent) {
				percent = Math.ceil(percent);
				bar.css("width", percent+"%").text(percent+"%");
			},

			allcomplete: function(response) {
				bar.css("width", "100%").text("100%");
				response = JSON.parse(response);
				setTimeout(function(){
					progressbar.addClass("uk-hidden");
				}, 250);
				if (response.error){
					alert(response.error);
				} else if (response.success) {
					// set the new file name and if another is found delete it
					setFilekey(response.success, response.fileformat, 'profile', 'image');
				}
			}
		};

		var select = UIkit2.uploadSelect($("#uikittwo-upload-select-profile-image"), settings),
		drop   = UIkit2.uploadDrop($("#uikittwo-upload-drop-profile-image"), settings);
	});
	<?php else: ?>
	// load uikit 3 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikitthree-profile-image-uploader').show();
		$('#uikittwo-profile-image-uploader').remove();
		$('#error-profile-image-uploader').remove();
		$('#size-profile').html(sizeNotice);
		$('#profile-image-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var bar = document.getElementById('uikitthree-progressbar-profile-image');
		UIkit.upload('#uikitthree-upload-profile-image', {

	 		url: JRouter('index.php?option=com_membersmanager&task=ajax.uploadfile&format=json&type=image&target=profile&raw=true&token='+token+'&vdm='+vastDevMod), // upload url
			multiple: true,
			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

	 		beforeSend: function (environment) {
				// console.log('beforeSend', arguments);

				// The environment object can still be modified here. 
				// var {data, method, headers, xhr, responseType} = environment;
			},
			beforeAll: function () {
	       			// console.log('beforeAll', arguments);
			},
			load: function () {
				// console.log('load', arguments);
			},
			error: function () {
				// console.log('error', arguments);
			},
			complete: function () {
				// console.log('complete', arguments);
			},

			loadStart: function (e) {
				jQuery(".success-profile-image-8768").remove();

				bar.removeAttribute('hidden');
				bar.max = e.total;
				bar.value = e.loaded;
			},

			progress: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			loadEnd: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			completeAll: function (response) {
				setTimeout(function () {
	 				bar.setAttribute('hidden', 'hidden');
				}, 250);
				// act upon the response
				if (response.response) {
					response = JSON.parse(response.response);
					if (response.error){
						alert(response.error);
					} else if (response.success) {
						// set the new file name and if another is found delete it
						setFilekey(response.success, response.fileformat, 'profile', 'image');
					}
				}
	  		}

		});
	});
	<?php endif; ?>
<?php else: ?>
	jQuery('#error-profile-image-uploader').html('<b><?php echo JText::_('COM_MEMBERSMANAGER_ALLOWED_IMAGE_FORMATS_ARE_NOT_SET_IN_THE_GLOBAL_SETTINGS_PLEASE_NOTIFY_YOUR_SYSTEM_ADMINISTRATOR'); ?></b>');
<?php endif; ?>
jQuery('#adminForm').on('change', '#jform_token',function (e) {
	e.preventDefault();
	var tokenValue = jQuery('#jform_token').val();
	// check if this token value is used
	checkUnique(tokenValue, 'token', 1);
});
jQuery('#adminForm').on('change', '#jform_user',function (e) {
	e.preventDefault();
	var userValue = jQuery('#jform_user').val();
	// check if this token value is used
	getUserDetails(userValue);
});

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
function JURI(link) {
	var url = "<?php echo JURI::root(); ?>";
	return url+link;
}

function getFile(filename, fileFormat, target, type){
	// set uikit version
	var uiVer = <?php echo (int) $this->params->get('uikit_version', 2); ?>;
	// set the link
	var link = '<?php echo MembersmanagerHelper::getFolderPath('url'); ?>';
	// build the return
	if (type === 'image') {
		var thePath = link+filename+'.'+fileFormat;
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+target+' '+type+'</button></div>';
		return '<img alt="'+target+' Image" src="'+thePath+'" /><br /><br />'+thedelete;
	} else if (type === 'images') {
		var imageNum = filename.length;
		if (imageNum == 1) {
			var gridClass = ' uk-grid-width-1-1';
			var perRow = 1;
		} else if (imageNum == 2) {
			var gridClass = ' uk-grid-width-1-2';
			var perRow = 2;
		} else {
			var gridClass = ' uk-grid-width-1-3';
			var perRow = 3;
		}
		var counter = 1;
		var imagesBox = '<div class="uk-grid'+gridClass+'">';
		jQuery.each(filename, function(i, item) {
			imagesBox += '<div class="uk-panel">';
			var fileFormat = item.split('_')[2];
			var thePath = link+item+'.'+fileFormat;
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+target+' '+type+'</button>';
			imagesBox += '<img alt="'+target+' Image" src="'+thePath+'" /><br /><br />'+thedelete; 
			if (perRow == counter) {
				counter = 0;
				if (imageNum == perRow) {
					imagesBox += '</div>';
				} else {
					imagesBox += '</div></div><div class="uk-grid'+gridClass+'">';
				}
			} else {
				imagesBox += '</div>';
			}
			counter++;
		});
		return imagesBox + '</div></div></div>';
	} else if (type === 'documents' || type === 'media') {
		var fileNum = filename.length;
		if (fileNum == 1) {
			var gridClass = ' uk-grid-width-1-1';
			var perRow = 1;
		} else if (fileNum == 2) {
			var gridClass = ' uk-grid-width-1-2';
			var perRow = 2;
		} else {
			var gridClass = ' uk-grid-width-1-3';
			var perRow = 3;
		}
		var counter = 1;
		var fileBox = '<div class="uk-grid'+gridClass+'">';
		jQuery.each(filename, function(i, item) {
			fileBox += '<div class="uk-panel">';
			var fileFormat = item.split('_')[2];
			// set the file name
			var fileName = item.split('VDM')[1]+'.'+fileFormat;
			// set the placeholder
			var theplaceholder = '<div class="uk-width-1-1"><div class="uk-panel uk-panel-box"><center><code>[DOCLINK='+fileName+']</code> <?php echo JText::_('COM_MEMBERSMANAGER_OR'); ?> <code>[DOCBUTTON='+fileName+']</code><br /><?php echo JText::_('COM_MEMBERSMANAGER_ADD_ONE_OF_THESE_PLACEHOLDERS_IN_TEXT_FOR_CUSTOM_DOWNLOAD_PLACEMENT'); ?>.</center></div></div>';
			// get the download link if set
			var thedownload = '';
			if (documentsLinks.hasOwnProperty(item)) {
				thedownload = '<a href="'+JRouter(documentsLinks[item])+'" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-success"><i class="uk-icon-download"></i> <?php echo JText::_('COM_MEMBERSMANAGER_DOWNLOAD'); ?> '+fileName+'</a>';
			}
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+fileName+'</button>';
			fileBox += theplaceholder+thedownload+thedelete; 
			if (perRow == counter) {
				counter = 0;
				if (fileNum == perRow) {
					fileBox += '</div>';
				} else {
					fileBox += '</div></div><div class="uk-grid'+gridClass+'">';
				}
			} else {
				fileBox += '</div>';
			}
			counter++;
		});
		return fileBox + '</div></div></div>';
	} else if (type === 'document') {
		var fileFormat = filename.split('_')[2];
		// set the file name
		var fileName = filename.split('VDM')[1]+'.'+fileFormat;
		// set the placeholder
		var theplaceholder = '<div class="uk-width-1-1"><div class="uk-panel uk-panel-box"><center><code>[DOCLINK='+fileName+']</code> <?php echo JText::_('COM_MEMBERSMANAGER_OR'); ?> <code>[DOCBUTTON='+fileName+']</code><br /><?php echo JText::_('COM_MEMBERSMANAGER_ADD_ONE_OF_THESE_PLACEHOLDERS_IN_TEXT_FOR_CUSTOM_DOWNLOAD_PLACEMENT'); ?>.</center></div></div>';
		// get the download link if set
		var thedownload = '';
		if (documentsLinks.hasOwnProperty(filename)) {
			thedownload = '<a href="'+JRouter(documentsLinks[filename])+'" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-success"><i class="uk-icon-download"></i> <?php echo JText::_('COM_MEMBERSMANAGER_DOWNLOAD'); ?> '+fileName+'</a>';
		}
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+fileName+'</button>';
		return theplaceholder+thedownload+thedelete + '</div>';
	}
}

</script>
