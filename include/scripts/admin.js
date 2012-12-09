// <---- Custom Additions end here.
// Photonic additions begin ----->

$j = jQuery.noConflict();
$j(document).ready(function() {
	var tabs = $j("#photonic-options").tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});
	tabs.tabs('select', '#' + Photonic_Admin_JS.category);

	$j('.photonic-border-options input[type="text"], .photonic-border-options select').change(function(event) {
		var thisId = event.currentTarget.id;
		thisId = thisId.substring(0, thisId.indexOf('-'));
		var edges = new Array('top', 'right', 'bottom', 'left');
		var border = '';
		for (var x in edges) {
			var edge = edges[x];
			var thisName = thisId + '-' + edge;
			border += edge + '::';
			border += 'color=' + $j("#" + thisName + "-color").val() + ';' +
					'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
					'style=' + $j("#" + thisName + "-style").val() + ';' +
					'border-width=' + $j("#" + thisName + "-border-width").val() + ';' +
					'border-width-type=' + $j("#" + thisName + "-border-width-type").val() + ';';
			border += '||';
		}
		$j('#' + thisId).val(border);
	});

	$j('.photonic-border-options input[type="radio"]').change(function(event) {
		var thisId = event.currentTarget.name;
		thisId = thisId.substring(0, thisId.indexOf('-'));
		var edges = new Array('top', 'right', 'bottom', 'left');
		var border = '';
		for (var x in edges) {
			var edge = edges[x];
			var thisName = thisId + '-' + edge;
			border += edge + '::';
			border += 'color=' + $j("#" + thisName + "-color").val() + ';' +
					'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
					'style=' + $j("#" + thisName + "-style").val() + ';' +
					'border-width=' + $j("#" + thisName + "-border-width").val() + ';' +
					'border-width-type=' + $j("#" + thisName + "-border-width-type").val() + ';';
			border += '||';
		}
		$j('#' + thisId).val(border);
	});

	$j('.photonic-background-options input[type="text"], .photonic-background-options select').change(function(event) {
		var thisName = event.currentTarget.id;
		thisName = thisName.substring(0, thisName.indexOf('-'));
		$j("#" + thisName).val('color=' + $j("#" + thisName + "-bgcolor").val() + ';' +
			'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
			'image=' + $j("#" + thisName + "-bgimg").val() + ';' +
			'position=' + $j("#" + thisName + "-position").val() + ';' +
			'repeat=' + $j("#" + thisName + "-repeat").val() + ';' +
			'trans=' + $j("#" + thisName + "-trans").val() + ';'
		);
	});

	$j('.photonic-background-options input[type="radio"]').change(function(event) {
		var thisName = event.currentTarget.name;
		thisName = thisName.substring(0, thisName.indexOf('-'));
		$j("#" + thisName).val('color=' + $j("#" + thisName + "-bgcolor").val() + ';' +
			'colortype=' + $j("input[name=" + thisName + "-colortype]:checked").val() + ';' +
			'image=' + $j("#" + thisName + "-bgimg").val() + ';' +
			'position=' + $j("#" + thisName + "-position").val() + ';' +
			'repeat=' + $j("#" + thisName + "-repeat").val() + ';' +
			'trans=' + $j("#" + thisName + "-trans").val() + ';'
		);
	});

	$j('.photonic-padding-options input[type="text"], .photonic-padding-options select').change(function(event) {
		var thisId = event.currentTarget.id;
		thisId = thisId.substring(0, thisId.indexOf('-'));
		var edges = new Array('top', 'right', 'bottom', 'left');
		var padding = '';
		for (var x in edges) {
			var edge = edges[x];
			var thisName = thisId + '-' + edge;
			padding += edge + '::';
			padding += 'padding=' + $j("#" + thisName + "-padding").val() + ';' +
					'padding-type=' + $j("#" + thisName + "-padding-type").val() + ';';
			padding += '||';
		}
		$j('#' + thisId).val(padding);
	});

	//AJAX Upload
	$j('.image_upload_button').live('click', function() {
		var clickedObject = $j(this);
		var thisID = $j(this).attr('id');

		new AjaxUpload(thisID, {
			action: ajaxurl,
			name: thisID,
			debug: true,
			data: {
				action: "photonic_admin_upload_file",
				type: "upload",
				data: thisID
			},
			autoSubmit: true,
			responseType: false,
			onSubmit: function(file, extension) {
				clickedObject.text('Uploading ...');
				this.disable();
			},
			onComplete: function(file, response) {
				clickedObject.text('Upload Image');
				this.enable(); // enable upload button

				// If there was an error
				var buildReturn;
				if (response.search('Upload Error') > -1) {
					buildReturn = '<span class="upload-error">' + response + '</span>';
					$j(".upload-error").remove();
					clickedObject.parent().after(buildReturn);
				}
				else {
					thisID = thisID.substring(7);
					buildReturn = '<div id="photonic-preview-' + thisID + '"><img class="hidden photonic-option-image" id="image_' + thisID + '" src="' + response + '" alt="" /></div>';
					$j(".upload-error").remove();
					$j("#image_" + thisID).remove();
					clickedObject.parent().after(buildReturn);
					$j('img#image_' + thisID).fadeIn();
					clickedObject.next('span').fadeIn();
					var text_field = $j("#"+thisID);
					text_field.val(response);
					clickedObject.fadeOut();
					text_field.change();
				}
			}
		});
	});

	//AJAX Remove (clear option value)
	$j('.image_reset_button').live('click', function() {
		var clickedObject = $j(this);
		var thisID = $j(this).attr('id');
		var image_Id = thisID.substring(6);

		var data = {
			action: 'photonic_admin_upload_file',
			type: 'image_reset',
			data: thisID
		};

		$j.post(ajaxurl, data, function(response) {
			//var image_to_remove = $j('#image_' + image_Id);
			var image_to_remove = $j('.photonic-wrap').find("#photonic-preview-" + image_Id);
			var button_to_hide = $j('.photonic-wrap').find('#reset_' + image_Id);
			var button_to_show = $j('.photonic-wrap').find('#upload_' + image_Id);
			image_to_remove.fadeOut(500, function() {
				clickedObject.remove();
			});
			button_to_hide.fadeOut();
			button_to_show.fadeIn();
			var text_field = $j('.photonic-wrap').find("#"+image_Id);
			text_field.val('');
			text_field.change();
		});

		return false;
	});

	$j('.photonic-button-bar').draggable();

	$j('.photonic-button-toggler a').live('click', function() {
		var thisClass = this.className;
		thisClass = thisClass.substr(24);
		var dialogClass = '.photonic-button-bar-' + thisClass;
		$j(dialogClass).slideToggle();
		return false;
	});

	$j('.photonic-button-bar a').click(function() {
		var thisParent = $j(this).parent().parent();
		thisParent.slideToggle();
		return false;
	});

	$j("#photonic-options h3").each(function() {
		var text = $j(this).text();
		if (text == '') {
			$j(this).remove();
		}
	});

	$j('.suffusion-options-form').submit(function(event) {
		var field = suffusion_submit_button;
		var value = field.val();

		if (value.substring(0, 5) == 'Reset') {
			if (!confirm("This will reset your configurations to the original values!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
		else if (value.substring(0, 6) == 'Delete') {
			if (!confirm("This will delete all your Photonic configuration options!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
	});
});