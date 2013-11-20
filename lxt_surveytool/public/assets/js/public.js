(function ( $ ) {
	"use strict";

	$(function () {
		var submit = lxt_surveytool_L10n.submit,
			cancel = lxt_surveytool_L10n.cancel;
		$('div.lxt_survey_dialog').each(function() {
			var allFields = $('.lxt_surveytool_field', this),
				ajaxurl = $(this).attr('ajaxurl');
			$(this).dialog({
				autoOpen: false,
				height: 300,
				width: 350,
				modal: true,
				buttons: {
					submit: function() {
						var ret = {};
						allFields.each(function() {
							ret[$(this).attr('name')] = '';
						});

						allFields.each(function() {
							if ( ( $(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox' ) && !$(this).prop('checked') )
								return;
							if (ret[$(this).attr('name')] !== '')
								ret[$(this).attr('name')] = ret[$(this).attr('name')]+","+$(this).val();
							else
								ret[$(this).attr('name')] = $(this).val();
						});

						ret.action = 'lxt_surveytool_savesurvey';
						
						jQuery.post(ajaxurl, ret, function( resp ) {
							alert(resp);
						});

						$(this).dialog('close');
					},
					cancel: function() {
						$(this).dialog('close');
					}
				},
				close: function() {
					$(allFields).not("[type='radio']").not("[type='checkbox']").val('');
				}
			});
		});
 
		$('a.lxt_survey_dialog_opener').each(function() {
			$(this).click(function() {
				$('#'+$(this).attr('dialog')).dialog("open");
			});
		});
	});	

}(jQuery));
