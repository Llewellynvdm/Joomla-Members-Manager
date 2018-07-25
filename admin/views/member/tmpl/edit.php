<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
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
$componentParams = JComponentHelper::getParams('com_membersmanager');
?>
<script type="text/javascript">
	// waiting spinner
	var outerDiv = jQuery('body');
	jQuery('<div id="loading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('components/com_membersmanager/assets/images/import.gif') 50% 15% no-repeat")
		.css("top", outerDiv.position().top - jQuery(window).scrollTop())
		.css("left", outerDiv.position().left - jQuery(window).scrollLeft())
		.css("width", outerDiv.width())
		.css("height", outerDiv.height())
		.css("position", "fixed")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.css("display", "none")
		.appendTo(outerDiv);
	jQuery('#loading').show();
	// when page is ready remove and show
	jQuery(window).load(function() {
		jQuery('#membersmanager_loader').fadeIn('fast');
		jQuery('#loading').hide();
	});
</script>
<div id="membersmanager_loader" style="display: none;">
<form action="<?php echo JRoute::_('index.php?option=com_membersmanager&layout=edit&id='.(int) $this->item->id.$this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

	<?php echo JLayoutHelper::render('member.details_above', $this); ?>
<div class="form-horizontal">

	<?php echo JHtml::_('bootstrap.startTabSet', 'memberTab', array('active' => 'details')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'memberTab', 'details', JText::_('COM_MEMBERSMANAGER_MEMBER_DETAILS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('member.details_left', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('member.details_right', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'memberTab', 'image', JText::_('COM_MEMBERSMANAGER_MEMBER_IMAGE', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo JLayoutHelper::render('member.image_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

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
</div>
</form>
</div>

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




<?php if ($formats = $componentParams->get('image_formats', null)) : ?>
jQuery(function($){
	var progressbar = $("#progressbar-profile-image"),
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

var select = UIkit2.uploadSelect($("#upload-select-profile-image"), settings),
	drop   = UIkit2.uploadDrop($("#upload-drop-profile-image"), settings);
});
jQuery('#profile-image-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
<?php if ($resize = $componentParams->get('crop_profile', null)) : ?>
	var sizeprofile = '(';
	<?php if ($width = $componentParams->get('profile_width', null)): ?>
		sizeprofile += 'width: <?php echo $width; ?>px';
	<?php else: ?>
		sizeprofile += 'width: <?php echo JText::_('COM_MEMBERSMANAGER_PROPORTIONALLY'); ?>';
	<?php endif; ?>
	<?php if ($height = $componentParams->get('profile_height', null)): ?>
		sizeprofile += '  height: <?php echo $height; ?>px';
	<?php else: ?>
		sizeprofile += '  height: <?php echo JText::_('COM_MEMBERSMANAGER_PROPORTIONALLY'); ?>';
	<?php endif; ?>
	sizeprofile += ')';
	sizeNotice = '<span data-uk-tooltip title="<?php echo JText::_('COM_MEMBERSMANAGER_THE_PROFILE_WILL_BE_CROPPED_TO_THIS_SIZE'); ?>">'+sizeprofile+'</span>';
	jQuery('#size-profile').html(sizeNotice);
<?php endif; ?>
<?php else: ?>
jQuery('#upload-drop-profile-image').html('<b><?php echo JText::_('COM_MEMBERSMANAGER_ALLOWED_IMAGE_FORMATS_ARE_NOT_SET_IN_THE_GLOBAL_SETTINGS_PLEASE_NOTIFY_YOUR_SYSTEM_ADMINISTRATOR'); ?></b>');
<?php endif; ?>

jQuery('#adminForm').on('change', '#jform_user_id',function (e)
{
	e.preventDefault();
	var user_id = jQuery("#jform_user_id").val();
	var showname = 1;
	// check if the user id was found
	if (!isSet(user_id)) {
		var user_id =$("#jform_user").val();
		var showname = 2;
	}
	getUser(user_id, showname);
});

jQuery(document).ready(function(){
  jQuery(window).load(function () {
    jQuery("body").css('background', 'transparent');
  });
});
jQuery('#adminForm').on('change', '#jform_token',function (e) {
	e.preventDefault();
	var tokenValue = jQuery('#jform_token').val();
	// check if this token value is used
	checkUnique(tokenValue, 'token', 1);
});
jQuery('#adminForm').on('change', '#jform_account',function (e) {
	e.preventDefault();
	var account = jQuery("#jform_account").val();
	if (1 == account || 4 == account) {
		jQuery('#user_info').show();
	} else {
		jQuery('#user_info').hide();	
	}
});
jQuery('#adminForm').on('change', '#jform_country',function (e) {
	e.preventDefault();
	getRegion();
});
var select_a_region = '<?php echo JText::_('COM_MEMBERSMANAGER_SELECT_A_REGION'); ?>';
var create_a_region = '<?php echo JText::_('COM_MEMBERSMANAGER_CREATE_A_REGION'); ?>';

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
	// set the link
	var link = '<?php echo MembersmanagerHelper::getFolderPath('url'); ?>';
	// build the return
	if (type === 'image') {
		var thePath = link+filename+'.'+fileFormat;
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+target+' '+type+'</button></div>';
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
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+target+' '+type+'</button>';
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
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+fileName+'</button>';
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
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_MEMBERSMANAGER_REMOVE'); ?> '+fileName+'</button>';
		return theplaceholder+thedownload+thedelete + '</div>';
	}
}

</script>
