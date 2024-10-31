(function($){

$(document).ready(function () {
	
	var local	= $('#neverpo-dic-table').data('local');
	
	$('#neverpo-dic-table').on( 'init.dt', function () {});
	$('#neverpo-dic-table').on( 'buttons-action.dt', function ( e, buttonApi, dataTable, node, config ) {
		if ( dataTable.rows({ selected: true }).count() > 0) {
			config.exportOptions.rows.selected = true;
		};
		console.log( 'Button '+buttonApi.text()+' was activated' );
	});

	$('#neverpo-dic-table').DataTable({
		
		/* 'columns': [
			{ "name": "ID",			},
			{ "name": "Screen", 	},
			{ "name": "Blog",		},
			{ "name": "Origin",		},
			{ "name": "Translated",	},
			{ "name": "Local",		}, 
		], */
		responsive: true,
		/* columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: -2 }
        ], */
		language: {
			emptyTable: local.emptyTable,
			select: {
				rows: {
					_: local.row_,
					0: local.row0,
					1: local.row1,
				},
			},
			paginate: {
				previous	: local.previous,
				next		: local.next,
			},
			search			: local.search,
			info			: local.info,
			infoEmpty		: local.infoEmpty,
		    lengthMenu		: local.lengthMenu,
			buttons: {
                copyTitle	: local.buttons.CopyButton.success,
                copyKeys	: local.buttons.CopyButton.copyKeys,
                copySuccess	: {
                    _: local.buttons.CopyButton.info__,
                    1: local.buttons.CopyButton.info_1,
                }
            }
		},
		dom: '<".bypass"lfr<".clear">Btip>',
		lengthMenu			: [ [20, 50, 100, -1], [20, 50, 100, local.lengthMenuAmount] ],
		aaSorting			: [],
		select				: true,
		initComplete		: function () {
 			this.api().columns().every( function () {
				var column = this;
				var select = $('<select><option value=""></option></select>')
					.appendTo( $(column.footer()).empty() )
					.on( 'change', function () {
						var val = $.fn.dataTable.util.escapeRegex(
							$(this).val()
						);

						column.search( val ? '^'+val+'$' : '', true, false ).draw();
					});
				column.data().unique().sort().each( function ( d, j ) {
					if ( /</.test( d ) ) {
						var d1 = $(d).text();
						select.append( '<option value="'+d1+'">'+d1+'</option>' )
					}
					else {
						select.append( '<option value="'+d+'">'+d+'</option>' )
					}
				});
				$('#neverpo-dic-table').css('opacity', '1');
			});
		},
        buttons: [
			{
				extend			: 'colvis',
				className		: 'ColumnsButton',
				text			: local.buttons.ColumnsButton.text,
			  //titleAttr		: local.buttons.ColumnsButton.title,
			},
			{
				extend			: 'copyHtml5',
				className		: 'CopyButton',
				text			: local.buttons.CopyButton.text,
			  //titleAttr		: local.buttons.CopyButton.title,
				exportOptions	:
				{
                    columns		: ':visible',
					rows		: { selected: false },
				},
			},
            {
                extend			: 'csvHtml5',
				className		: 'ExportCSVButton',
				text			: local.buttons.ExportCSVButton.text,
			  //titleAttr		: local.buttons.ExportCSVButton.title,
				title			: 'NeverPo Dictionary',
				exportOptions	:
				{
                    columns		: ':visible',
					rows		: { selected: false },
				},
                fieldSeparator	: ':',
                extension		: '.csv'
            },
            /* {
                className		: 'JSONPairButton',
				text			: local.buttons.JSONPairButton.text,
			  //titleAttr		: local.buttons.JSONPairButton.title,
				exportOptions	:
				{
                    columns		: ':visible',
					rows		: { selected: false },
				},
                action			: function ( e, dt, button, config ) {
                    var data = dt.buttons.exportData();
 
                    $.fn.dataTable.fileSave(
                        new Blob( [ JSON.stringify( data ) ] ),
                        'Export.json'
                    );
                }
            }, */
        ]
	});
	
	$('.ColumnsButton .tip').jQueryUITooltip({
		position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
		content: local.buttons.ColumnsButton.title,
		show: { easing: "easeInExpo", duration: 500 },
	});
	
	$('.CopyButton .tip').jQueryUITooltip({
		position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
		content: local.buttons.CopyButton.title,
		show: { easing: "easeInExpo", duration: 500 },
	});
	
	$('.ExportCSVButton .tip').jQueryUITooltip({
		position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
		content: local.buttons.ExportCSVButton.title,
		show: { easing: "easeInExpo", duration: 500 },
	});
	
	/* $('.JSONPairButton .tip').jQueryUITooltip({
		position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
		content: local.buttons.JSONPairButton.title,
		show: { easing: "easeInExpo", duration: 500 },
	}); */
});
	
})(jQuery);

//$('#neverpo-dic-table').on( 'column-sizing.dt', function ( e, settings ) {});