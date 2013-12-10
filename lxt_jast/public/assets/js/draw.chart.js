(function ( $ ) {
	"use strict";
		
	$.lxt_jast.drawChart = function() {
		var id = $(this).attr('id');
		var secs = id.split('_');
		var imgtype = $(this).attr('type');
		var title = $(this).attr('title');

		var ajaxObj = {action : $.lxt_jast.slug+'_getsurveyret'};
		ajaxObj.key = secs[4];
		ajaxObj.postid = secs[3];
									
		jQuery.post($.lxt_jast.ajaxurl, ajaxObj, function( resp ) {
			var data = [];
			try {
				var retObj = JSON.parse(resp);
			}catch(err){
				$('#'+id).html(resp);
				return;
			}

			
			if (imgtype.toLowerCase().indexOf("bar") !== -1){
				var total = retObj[$.lxt_jast.slug+'_total'];
				var ticks = [];
				for (var key in retObj)
					if (key != $.lxt_jast.slug+'_total') {
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
		                name: $.lxt_jast.choiceLabel,
		                data: data
		            }]
		        });
			}else{
				for (var key in retObj)
					if (key != $.lxt_jast.slug+'_total')
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
			            name: $.lxt_jast.choiceLabel,
			            data: data
			        }]
			    });
			}
		});
	};
}(jQuery));

