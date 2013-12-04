(function ( $ ) {
	"use strict";

	$(function () {
		var slug = wordpress_L10n.slug,
			ver = wordpress_L10n.ver,
			ajaxurl = wordpress_L10n.ajaxurl,
			choiceLabel = wordpress_L10n.choiceLabel;
 
		$('.'+slug+'_popup_open').click( function(e) {
			e.preventDefault();
			var targetid = $(this).attr('target');
			var postid = $(this).attr('postid');

			$('#'+slug+'_popup_'+targetid).bPopup({
				modalClose: false,
				speed: 450,
				transition: 'slideDown',
				closeClass: slug+'_popup_close',
				loadUrl: ajaxurl,
				loadData: {action : slug+'_loadsurvey', id : postid},
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
						ajaxObj.postid = postid;
						ajaxObj.key = '1';
									
						jQuery.post(ajaxurl, ajaxObj, function( resp ) {
							alert(resp);
						});
				
						$('#'+slug+'_popup_'+targetid).bPopup().close();
					});
				}
			});
		});

		$('.'+slug+'_result_img').each( function() {
			var id = $(this).attr('id');
			var secs = id.split('_');
			var imgtype = $(this).attr('type');
			var title = $(this).text();

			var ajaxObj = {action : slug+'_getsurveyret'};
			ajaxObj.key = secs[4];
			ajaxObj.postid = secs[3];
									
			jQuery.post(ajaxurl, ajaxObj, function( resp ) {
				var data = [];
				var retObj = JSON.parse(resp);

				
				if (imgtype.toLowerCase().indexOf("bar") !== -1){
					var total = retObj[slug+'_total'];
					var ticks = [];
					for (var key in retObj)
						if (key != slug+'_total') {
							data.push(Math.round(retObj[key]/total*100));
							ticks.push(key);
						}

					$('#'+id).highcharts({
			            chart: {
			                type: 'bar'
			            },
			            title: {
			                text: title
			            },
			            xAxis: {
			                categories: ticks,
			                title: {
			                    text: null
			                }
			            },
			            yAxis: {
			                min: 0,
			                title: {
			                    text: '%',
			                    align: 'high'
			                },
			                labels: {
			                    overflow: 'justify'
			                }
			            },
			            tooltip: {
			                valueSuffix: '%'
			            },
			            plotOptions: {
			                bar: {
			                    dataLabels: {
			                        enabled: true
			                    }
			                }
			            },
			            legend: {
			                layout: 'vertical',
			                align: 'right',
			                verticalAlign: 'top',
			                x: -40,
			                y: 100,
			                floating: true,
			                borderWidth: 1,
			                backgroundColor: '#FFFFFF',
			                shadow: true
			            },
			            credits: {
			                enabled: false
			            },
			            series: [{
			                name: choiceLabel,
			                data: data
			            }]
			        });
				}else{
					for (var key in retObj)
						if (key != slug+'_total')
							data.push(new Array(key, retObj[key]));

					$('#'+id).highcharts({
				        chart: {
				            plotBackgroundColor: null,
				            plotBorderWidth: null,
				            plotShadow: false
				        },
				        title: {
				            text: title
				        },
				        tooltip: {
				    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
				        },
				        plotOptions: {
				            pie: {
				                allowPointSelect: true,
				                cursor: 'pointer',
				                dataLabels: {
				                    enabled: true,
				                    color: '#000000',
				                    connectorColor: '#000000',
				                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
				                }
				            }
				        },
				        series: [{
				            type: 'pie',
				            name: choiceLabel,
				            data: data
				        }]
				    });
				}
			});
		});
	});	
}(jQuery));
