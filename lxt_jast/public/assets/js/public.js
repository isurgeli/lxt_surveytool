(function ( $ ) {
	"use strict";

	$(function () {
		var slug = wordpress_L10n.slug,
			ver = wordpress_L10n.ver,
			ajaxurl = wordpress_L10n.ajaxurl;
 
		$('.'+slug+'_popup_open').click( function(e) {
			e.preventDefault();
			var targetid = $(this).attr('target');
			$('#'+slug+'_popup_'+targetid).bPopup({
				modalClose: false,
				speed: 450,
				transition: 'slideDown',
				closeClass: slug+'_popup_close',
				loadUrl: ajaxurl+'?XDEBUG_SESSION_START=1',
				loadData: {action : slug+'_loadsurvey', id : $(this).attr('postid')},
				content: 'ajax',
				contentContainer:'#'+slug+'_popup_container_'+targetid,
				loadCallback: function() { 
					$('#'+slug+'_popup_container_'+targetid+' .'+slug+'_submit').click(function() {
						var allFields = $('#'+slug+'_popup_container_'+targetid+' .'+slug+'_qust[name]');
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
				
						var ajaxObj = {action : slug+'_savesurvey'};
						ajaxObj.email = ret.the_email;
						delete ret.the_email;
						ajaxObj.result = JSON.stringify(ret);
									
						jQuery.post(ajaxurl, ajaxObj, function( resp ) {
							alert(resp);
						});
				
						$('#'+slug+'_popup_'+targetid).bPopup().close();
					});
				}
			});
		});

		$('.'+slug+'_result_img').each( function() {
			var data = [
				['Heavy Industry', 12],['Retail', 9], ['Light Industry', 14], 
				['Out of home', 16],['Commuting', 7], ['Orientation', 9]
			];

			$.jqplot ($(this).attr('id'), [data], 
				{ 
					seriesDefaults: {
						// Make this a pie chart.
						renderer: $.jqplot.PieRenderer, 
						rendererOptions: {
							// Put data labels on the pie slices.
							// By default, labels show the percentage of the slice.
							showDataLabels: true
						}
					}, 
					legend: { show:true, location: 'e' }
				}
			);
		});
	});	
}(jQuery));
