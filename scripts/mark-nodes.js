(function($){

$(document).ready(function () {
	
	var q=0;
	var audio		= $('.beep');
	var namesSpace 	= '.neverpo';
	var formStatus	= null;
	var hotkey_timer;
	var saving_timer;
	var init_cont_pos = 5;
	var init_cont_offset = 25;
	var np = 'neverpo';
	var sw = $('#neverpo-ruler #switch');
	var mk = $('.never-po-text-marker');
	var tm = $('#fs_1_translate_mode');
	var fc = $('#fs_translate_console [for="fs_1_all_blog"]');
	var pc = $('#fs_translate_console [for="fs_1_all_page"]');
	var ub = $('#fs_1_all_blog');
	var up = $('#fs_1_all_page');
	var mw = $('#neverpo_minimize_window');
	var tc = $('#neverpo_trans_container');
	var od = $('#neverpo_trans_container #origin_data');
	var td = $('#neverpo_trans_container #translated_data');
	var lc = $('#neverpo_trans_container #neverpo_wp_local');
    var ct = $('#cancel_translation');
	var st = $('#save_translation');
  //var safe_tags		= ["SCRIPT", "STYLE", "BYPASS"];
	var safe_tags		= ["SCRIPT", "STYLE", "BYPASS", "TEXTAREA"];
  //var safe_selectors  = ['#postdivrich *', '.bypass', '.bypass *'];
	var safe_selectors  = ['#postdivrich *', '.bypass', '.bypass *', '.widget-control-remove', '.widget-control-close', 'iframe'];
	var regexp_str		= /\S(.*)\S/g;
	
	/* FUNCTIONS */
	
	//NeverPo dictionary reading
	function get_definition( str ){
		if ( neverpo_dictionary != 'nothing' ) {
			if ( typeof neverpo_dictionary[ str ] != 'undefined' ) { return neverpo_dictionary[str] }
			else {return str} 
		}
		else {
			return str
		}
	};
	
	//NeverPo enforce translation
	function force_translation( area ){
		$(area).neverpo_replace_text( regexp_str, get_definition, np.toUpperCase() );
		$('.np-loader').remove();
		$('body').removeClass('np_translatable_page');
	};
	
	//NeverPo console features
	function play (event){
		
		audio[q].volume = 0.1;
		audio[q].play();
		if (q==audio.length-1) {q=0;} else {q=q+1};
	}
	
	//NeverPo operations spiner
	function waiter ( mode ){
		
		if ( mode == 'on'  ) { $('body').prepend('<div id="wait"></div>'); $('#wait').fadeIn(200); 	}
		if ( mode == 'off' ) { $('#wait'  ).fadeOut(400, function (){ $('#wait').remove(); });		}
	}
	
	//Check if any object is undefined
	function undefined_converter (data){
		if (typeof data == 'undefined'){
			return false;
		}
		else{
			return data;
		}
	}
	
	//Action for hover on tag NEVERPO
	function neverpo_mouseover_action (e){
		
		sw.addClass('led');
		$('.never-po-hotkey-reminder').addClass('never-po-text-marker-active');
		
		if (e.type == 'touchend'){
			e.preventDefault();
			
			mk.off('touchend' + namesSpace, key_push_parser);
			mk.one('touchend' + namesSpace, {
				source_el_target	: this,
				hovered_el_text 	: this.innerHTML,
				original_el_text	: this.neverpo_text_original,
				use_in_all_blogs	: this.use_in_all_blogs,
				use_in_all_pages	: this.use_in_all_pages,
			}, key_push_parser);
			
			return;
		}
		
		$( document ).on('keydown' + namesSpace, {
				source_el_target	: this,
				hovered_el_text 	: this.innerHTML,
				original_el_text	: this.neverpo_text_original,
				use_in_all_blogs	: this.use_in_all_blogs,
				use_in_all_pages	: this.use_in_all_pages,
			}, key_push_parser);
	}
	
	//Action for unhover on tag NEVERPO
	function neverpo_mouseout_action (){
		
		sw.removeClass('led');
		
		$( document ).off('keydown' + namesSpace, key_push_parser);
		$('.never-po-hotkey-reminder').removeClass('never-po-text-marker-active');
	}
	
	//Mark up page with tag NEVERPO
	function markup_translatable_nodes_INITSET (){
		
		var txtNodes = $('body *').not(safe_selectors.join (', ')).contents().filter(function () {
			if ( this.nodeType === 3 && this.data.match(regexp_str) && $.inArray(this.parentNode.tagName, safe_tags) === -1 ) { return true }
		});
		
		for (var i = 0; i < txtNodes.length; ++i) {
			$(txtNodes[i]).replaceWith( $( '<' + np + ' class="never-po-translate-marker">' + txtNodes[i].data + '</' + np + '>') );
		};
	}

	//Turn ON events on marked up nodes
	function markup_translatable_nodes_SET (){
		
		$('body').addClass("translate-mode");
		
		$('body').on('mouseover'	+ namesSpace,	np,	neverpo_mouseover_action	);
		$('body').on('mouseout' 	+ namesSpace,	np,	neverpo_mouseout_action		);
		
		$('body').on('touchend'		+ namesSpace,	np,	neverpo_mouseover_action	);
	}
	
	//Unset events and markup
	function markup_translatable_nodes_CLEAR () {
		
		$('body').removeClass("translate-mode");
		
		$('body').off('mouseover'	+ namesSpace,	np,	neverpo_mouseover_action	);
		$('body').off('mouseout'	+ namesSpace,	np,	neverpo_mouseout_action		);
		
		$('body').off('touchend'	+ namesSpace,	np,	neverpo_mouseover_action	);
		
		//Fully remove markup
		//var txtNodes = $('.never-po-translate-marker');
		//for (var i = 0; i < txtNodes.length; ++i) { $(txtNodes[i]).replaceWith( txtNodes[i].innerText ); };
	}
	
	//Button status for every operation state
	function buttons_ability() {
		
		if 	(td[0].value == ''	&& formStatus == 'draft' )					{ st.jQueryUIButton( 'disable' ); ct.jQueryUIButton( 'disable' );	}
		if	(td[0].value == ''	&& formStatus == 'saved' )					{ st.jQueryUIButton( 'disable' ); ct.jQueryUIButton( 'enable'  ); 	}
		if	(td[0].value != ''	&& formStatus == 'draft' )					{ st.jQueryUIButton( 'enable'  ); ct.jQueryUIButton( 'disable' );	}
		if	(td[0].value != ''	&& formStatus == 'saved' )					{ st.jQueryUIButton( 'enable'  ); ct.jQueryUIButton( 'enable'  );	}
	}
	
	//Manage button state
	function toggle_buttons_state (e, command){
		
		if (e){
			
			//nothing in original text?
			if 	( (e.type == 'keypress' || e.type == 'keyup') && od[0].value == '')	{ e.preventDefault();}
			//original text exist?
			else if	(e.type == 'keyup'	&& od[0].value != ''	 )					{ buttons_ability ();}
		}
		
		if (command) {
			
			if 	(command == 'ajax_start'	)		{ st.jQueryUIButton( 'disable' ); ct.jQueryUIButton( 'disable' ); mk.jQueryUITooltip('option', 'disabled', true  ); }
			if 	(command == 'ajax_saved'	)		{ st.jQueryUIButton( 'disable' ); ct.jQueryUIButton( 'enable'  ); mk.jQueryUITooltip('option', 'disabled', false ); }
			if 	(command == 'ajax_canceled'	)		{ st.jQueryUIButton( 'disable' ); ct.jQueryUIButton( 'disable' ); mk.jQueryUITooltip('option', 'disabled', false ); }

		}
	};
	
	//Paste action process on form
	function check_buffer (e){

		if 	( od[0].value == ''){ e.preventDefault(); return false; }
		else					{ buttons_ability();				}
	}
	
	//Hot key action on tags
	function key_push_parser (e){
		if( (e.shiftKey && (e.which == 66)) || e.type == 'touchend' ) {
			e.preventDefault();
			
			if  ( typeof e.data.original_el_text != 'undefined' ) {
				formStatus = 'saved';
				tc[0].neverpo_last_clicked = e.data.source_el_target;
				od[0].value = e.data.original_el_text.match(regexp_str);
				td[0].value = e.data.hovered_el_text.match(regexp_str);
				if ( e.data.use_in_all_blogs	===	1 ){ ub.attr('checked', true ).data('use_in_all_blogs',	1	);}
				else 								   { ub.attr('checked', false).data('use_in_all_blogs',	0	);}
				if ( e.data.use_in_all_pages	===	1 ){ up.attr('checked', true ).data('use_in_all_pages',	1	);}
				else 								   { up.attr('checked', false).data('use_in_all_pages',	0	);}
				ct.jQueryUIButton( 'enable'  );
				play();
			}
			else{
				formStatus = 'draft';
				ub.attr('checked', false).data('use_in_all_blogs',	0	);
				up.attr('checked', false).data('use_in_all_pages',	0	);
				tc[0].neverpo_last_clicked = e.data.source_el_target;
				od[0].value = $(e.data.source_el_target).text().match(regexp_str); //e.data.hovered_el_text.match(regexp_str);//
				td[0].value = '';
				ct.jQueryUIButton( 'disable'  );
				play();
			}
			if ( undefined_converter  ( tc.data('console_shown') ) === false )	{ sw.trigger( 'click' );																		}
			else																{ tc.animate(	{top: '-=5px'}, 100).animate(	{top: '+=5px'}, 200, function (){ td.focus();	})
			}
			
			$('.never-po-hotkey-reminder').removeClass('never-po-text-marker-active');
		}
	}
	
	//Swither handler
	function switch_force_for_all_blogs () {
		
		if ( $(this).is(':checked') )	{ $(this).data('use_in_all_blogs', 1	);}
		else							{ $(this).data('use_in_all_blogs', 0	);}
		
		if ( td[0].value != '' )		{st.jQueryUIButton('enable'				);}
	}
	
	//swither handler
	function switch_force_for_all_pages () {
		
		if ( $(this).is(':checked') )	{ $(this).data('use_in_all_pages', 1	);}
		else							{ $(this).data('use_in_all_pages', 0	);}
		
		if ( td[0].value != '' )		{st.jQueryUIButton('enable'				);}
	}

	//Trigger form visibility
	function switch_translate_form (event){
		
		event.stopPropagation();
		if ( tm[0].checked === true )	{ show_translate_form(); }
		else 							{ hide_translate_form(); }
	}
	
	//Get Height Width of hidden element - obj is ID
	function get_dimentions (obj) {
		
		obj.css({'display':'block', 'position':'absolute', 'left': '-100%', 'top': '-100%'});
		
		//We plus 15 px to width&height cause flex display may create scroll bar.		
		var height	= Math.ceil( parseFloat( obj[0].getBoundingClientRect().height	) + 15 );
		var width	= Math.ceil( parseFloat( obj[0].getBoundingClientRect().width	) + 15 );
		
		obj.data({'console_init_height'	: height	});
		obj.data({'console_init_width'	: width		});
		
		obj.css({'display':'', 'position':'', 'left': '', 'top': ''});		
	}
	
	//Calculate and set dimentions for object
	function set_dimentions (obj) {
		var winWidth	= $(window).innerWidth ();
		var winHeight	= $(window).innerHeight();
		
		obj.css({
			'max-width'	:	winWidth						+ 'px',
			'max-height':	winHeight						+ 'px',		
		})
	}
	
	//Initial form showing
	function show_translate_form (e){
		
		set_dimentions(tc);
		
		var winWidth	= $(window).innerWidth ();
		var winHeight	= $(window).innerHeight();
		
		var top		= winHeight <= tc.data('console_init_height' ) ? 0 : $(window).innerHeight();
		var left	= winWidth	<= tc.data('console_init_width'	 ) ? 0 : $(window).innerWidth()  - init_cont_pos - tc.data('console_init_width' );
		
		tc.css ({
			'top'		: top								+ 'px',
			'left'		: left								+ 'px',
			'width'		: tc.data('console_init_width')		+ 'px',
			'height'	: tc.data('console_init_height')	+ 'px',
			'display'	: 'flex',
		});
		
		tc.animate({
			top: $(window).innerHeight()  - init_cont_pos - init_cont_offset - tc.data('console_init_height')	+ 'px',
			}, 300,
			function() {
				tc_position_starter();
				tc.animate({
					top: '+=' + init_cont_offset + 'px'
				}, 300, function (){
					obj_rel_position (tc);
					markup_translatable_nodes_SET ();
					})
			});
	}

	//Final form hiding
	function hide_translate_form (e){
		
		markup_translatable_nodes_CLEAR ();
		
		if ( undefined_converter ( tc.data('console_shown') ) === true ){
			
			tc.animate({
				top: $(window).innerHeight() + 'px',
				}, 400,
				function() {
					tc.css('display','none');
					$( window ).off('resize' + namesSpace, tc_position_mode_switcher);
				});
		}
		else {
			sw.css( 'transform', 'translateY(50%)' );
		}
	}
	
	//Define if the object was snaped to window on screen
	function obj_rel_position (obj){

		var winWidth			= $(window).innerWidth ();
		var winHeight			= $(window).innerHeight();

		
		var currT	= obj.position().top;
		var currL	= obj.position().left;
		var currR	= obj.position().left	+ Math.ceil( obj.outerWidth (true) );
		var currB	= obj.position().top	+ Math.ceil( obj.outerHeight(true) );
		
		if (currT	=== 0			){var snapT	= true}	else	{var snapT	= false};
		if (currL	=== 0			){var snapL	= true}	else	{var snapL	= false};
		if (currR	=== winWidth	){var snapR	= true}	else	{var snapR	= false};
		if (currB	=== winHeight	){var snapB	= true}	else	{var snapB	= false};
		
		obj.data ({
			'console_shown'	: true,
			'geometria'	:{
				'snapT'	: snapT,
				'snapL'	: snapL,
				'snapR'	: snapR,
				'snapB'	: snapB,
			},
			'position'	:{
				'currT'	: currT,
				'currL'	: currL,
				'currR'	: currR,
				'currB'	: currB,
			}
		});
	}
	
	//Console drag animation
	function console_move (e){
		 
		if ( e.data.inout == 'in' && $(e.target).css('cursor') == 'move' ) {
			
			e.data.el.css ({
				'box-shadow'			: '0px 40px 60px 10px rgba(0,0,0,0.09), inset 0px 1px 0px 0px #d5d5d5,  inset -1px 0px 0px 0px #d5d5d5,  inset 1px 0px 0px 0px #d5d5d5, inset 0px -1px 0px 0px #d5d5d5',
				'border-color'			: '#bcbcbc',
			});
		}
		if ( e.data.inout == 'out' ){
			e.data.el.css ({
				'-webkit-box-shadow'	: '',
				'-moz-box-shadow'		: '',
				'box-shadow'			: '',
				'border'				: '',
			});
		}
	}
	
	//ajax request animation
	function server_request_animation (status){
		
		var lpgb = $('#loader_progress'); var lpgb_ui = $('#loader_progress .ui-progressbar-value');
		
		if ( status == 'start' ){
			waiter ('on');
			ct.jQueryUIButton({ disabled: true });
			st.jQueryUIButton({ disabled: true });
			lpgb.progressbar({ value: 2 });
			lpgb_ui.css('transition', 'width .6s linear');
			saving_timer = setInterval(function(){
				lpgb.progressbar(
					'value', lpgb.progressbar('value') + 1
					);
				}, 600)
		}
		
		if ( status == 'finish' ){
			if (saving_timer != null || saving_timer != 'undefined') {clearInterval ( saving_timer )};
			lpgb_ui.css('transition', 'width .5s linear');
			lpgb.progressbar({ value: 100 });
			setTimeout ( function () {
				lpgb_ui.css('transition', '');
				setTimeout ( function () {
					waiter ('off');
					lpgb.progressbar({ value: 0 });
					}, 200 );
			}, 700);
		}
	}
	
	//Transition animation
	function transition (obj){
		var dur = 200;
		obj.css('transition', 'top ' + dur + 'ms' + ', ' + 'bottom' + dur + 'ms' + ', ' + 'left ' + dur + 'ms' + ', ' + 'right ' + dur + 'ms');
		setTimeout ( function (){ obj.css('transition', ''); }, dur);
	}

	//Start timer on window resize
	function tc_position_starter () {
		
		var timeoutId;
		
		$(window).on('resize' + namesSpace, {
			timerID	: timeoutId,
		}, tc_position_mode_switcher );
	}
	
	//Check what object was resized
	function tc_position_mode_switcher (e){
		
		if ( e.target.id == tc[0].id ) { return; }
		
		if( e.data.timerID ){ clearTimeout( e.data.timerID ); }
		e.data.timerID = setTimeout(function(){
			set_dimentions(tc);
			find_container_pos ( tc );
		}, 150);
	}
	
	//Find new position
	function find_container_pos ( obj ) {
		
		if ( tc.data('console_shown') === false ){ return }
		
 			var snapT = obj.data('geometria').snapT;
			var snapL = obj.data('geometria').snapL;
			var snapR = obj.data('geometria').snapR;
			var snapB = obj.data('geometria').snapB;
			
			if ( snapT === true || snapL === true || snapR === true || snapB === true ){
				
				if (snapT === true)			{ set_container_pos (obj, 'top',	'top'		);}
				if (snapL === true)			{ set_container_pos (obj, 'left',	'left'		);}
				if (snapR === true)			{ set_container_pos (obj, 'right',	'right'		);}
				if (snapB === true)			{ set_container_pos (obj, 'bottom',	'bottom'	);}
			}
			
			var winWidth			= $(window).innerWidth ();
			var winHeight			= $(window).innerHeight();

			var curTop				= obj.position().top;
			var curLeft				= obj.position().left;
			var curRight			= obj.position().left	+ Math.ceil( obj.outerWidth (true) );
			var curBottom			= obj.position().top	+ Math.ceil( obj.outerHeight(true) );
			
			//Form can be putted (it is smaller) by height
			if ( winHeight > ( curBottom - curTop )){
			
				if ( curBottom	> winHeight && snapB === false	)	{ set_container_pos (obj, 'bottom','bottom'	);}
				if ( curTop		< 0			&& snapT === false	)	{ set_container_pos (obj, 'top',	'top'	);}
			}
			else   { transition (obj); obj.css('top', 0	+ 'px'	);}
			
			//Form can be putted (it is smaller) by width
			if ( winWidth > ( curRight - curLeft )){
			
				if ( curRight	> winWidth	&& snapR === false	)	{ set_container_pos (obj, 'right',	'right'	);}
				if ( curLeft	< 0			&& snapL === false	)	{ set_container_pos (obj, 'left',	'left'	);}
			}
			else   {transition (obj); obj.css('left', 0	+ 'px'	);}
		
	}
	
	//set console position
	function set_container_pos (obj, my, at){
		
		if ( my == 'top'	&& at == 'top' 		)	{ obj.position({my:	'top',		at:	'top',		of:	window,	using:	function (event, ui){transition (obj);obj.css('top',	event.top	+ 'px'	);	},}); 	}
		if ( my == 'left'	&& at == 'left'		)	{ obj.position({my:	'left',		at:	'left',		of:	window,	using:	function (event, ui){transition (obj);obj.css('left',	event.left	+ 'px'	);	},});	}
		if ( my == 'right'	&& at == 'right'	)	{ obj.position({my:	'right',	at:	'right',	of:	window,	using:	function (event, ui){transition (obj);obj.css('left',	event.left	+ 'px'	);	},});	}
		if ( my == 'bottom'	&& at == 'bottom'	)	{ obj.position({my:	'bottom',	at:	'bottom',	of:	window,	using:	function (event, ui){transition (obj);obj.css('top',	event.top	+ 'px'	);	},});	}
	}

	//form | tablet show switcher
	function trigger_elements (e){
		
		if ( e.data.mode == 'tablet' ){
			
			sw.css('display','block');
			tc.data({'last_known_top_pos': Math.ceil( parseInt (tc.css('top')) ) - 25 });
			
			tc.animate({top: '-=' + init_cont_offset + 'px'}, 300).animate({top: $(window).innerHeight() + 10 + 'px'}, 300,
				function (){
					$( window ).off('resize' + namesSpace, tc_position_mode_switcher);
					tc.css('display','none');
					sw.css( 'transform', 'translateY(0)' );
					tc.data({'console_shown': false})
				}
			);
		}
		
		if ( e.data.mode == 'console' ){
			
			var dur = parseFloat( sw.css('transition-duration'))*1000;

			tc_position_starter();
			tc.css('display','flex');
			sw.css( 'transform', 'translateY(50%)' );
			
			setTimeout( function () {
				
				tc.animate({top:  tc.data('last_known_top_pos')	+ 'px',}, 300).animate({top: '+=' + init_cont_offset + 'px'}, 300, function (){ sw.css('display',''); }).data({'console_shown': true});
			},dur)
		}
	}

	//ajax save data
	function save_translation (e){
	
	toggle_buttons_state(false, 'ajax_start');
	
	server_request_animation ('start');

 		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'neverpo_save_translation',
				translatable_pair: {
					'original_text'		: od[0].value,
					'translated_text'	: td[0].value,
					'current_screen_id'	: tc.data('screen'),
					'blogid'			: parseInt(tc.data('blogid')),
					'path'				: tc.data('path'),
					'use_in_all_blogs'	: ub.data('use_in_all_blogs'),
					'use_in_all_pages'	: up.data('use_in_all_pages'),
					}
				},
			success: function(data) {
				neverpo_dictionary = JSON.parse(data);
					if ( typeof tc[0].neverpo_last_clicked.neverpo_text_original != 'undefined' ){ tc[0].neverpo_last_clicked.innerText = tc[0].neverpo_last_clicked.neverpo_text_original; }
				force_translation( tc[0].neverpo_last_clicked );
				formStatus = 'saved';
				toggle_buttons_state(false, 'ajax_saved');
				server_request_animation ('finish');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){}
		});
	};
	
	//ajax erase data
	function cancel_translation (e){
		
	toggle_buttons_state(false, 'ajax_start');

	server_request_animation ('start');

 		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'neverpo_cancel_translation',
				translatable_pair: {
					'original_text'		: od[0].value,
					'translated_text'	: td[0].value,
					'current_screen_id'	: tc.data('screen'),
					'blogid'			: parseInt(tc.data('blogid')),
					'path'				: tc.data('path'),
					'use_in_all_blogs'	: ub.data('use_in_all_blogs'),
					'use_in_all_pages'	: up.data('use_in_all_pages'),
					}
				},
			success: function(data) {
				neverpo_dictionary = JSON.parse(data);
				tc[0].neverpo_last_clicked.innerText = tc[0].neverpo_last_clicked.neverpo_text_original;
				delete tc[0].neverpo_last_clicked.neverpo_text_original;
				od[0].value = '';
				td[0].value = '';
				ub.attr('checked', false).removeData('use_in_all_blogs');
				up.attr('checked', false).removeData('use_in_all_pages');
				formStatus = null;
				toggle_buttons_state(false, 'ajax_canceled');
				server_request_animation ('finish');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){}
		});
	};
	
	
	//INITIALIZING
	
	//Detect iFrames
	if ( window.frameElement !== null ) { 
	
		markup_translatable_nodes_INITSET ();
		force_translation('body *');
		
		return;
	
	}
	
	//User Mode
	if ( $(document).data ( 'mode' ) == 'translate' ) {
		
		markup_translatable_nodes_INITSET ();
		force_translation('body *');

	}
	
	//Admin Mode
	if ( $(document).data ( 'mode' ) == 'manage' ) {
	
		//set aliases
		$.widget.bridge( 'jQueryUIButton',  $.ui.button  );
		$.widget.bridge( 'jQueryUITooltip', $.ui.tooltip );
		
		//Mozilla browser cache
		if ( tm[0].checked === true )  { show_translate_form (); };

		//jQ draggable initializing	
		tc.draggable({
			drag			:function( event, ui){
							},
			start			:function (event, ui){
							},
			stop			:function (event, ui){
								obj_rel_position (tc);	
							},
			containment		: 'window',
		  //opacity			: 0.8,
			handle			: $('#neverpo_drag_window'),
			snap			: '#neverpo-ruler, #adminmenuback, #wpadminbar',
		  //snapMode		: 'inner',
			snapTolerance	: 10,
			scroll			: true,
		});
		
		//jQ resizable initializing
		tc.resizable({
			start			: function (event, ui){
								$('.never-po-text-marker-init').css('visibility','hidden');
							},
			stop			: function (event, ui){
								$('.never-po-text-marker-init').css('visibility', '' );
								obj_rel_position (tc);
							},
			containment: 'parent',
			handles: 'e, w, n, s, se',
		});
		
		//jQ buttons initializing
		ct.jQueryUIButton({disabled: true});
		st.jQueryUIButton({disabled: true});
		
		mk.jQueryUITooltip({
			position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
			content: tc.data('local').neverPoTextMarkerTitle,
			show: { easing: "easeInExpo", duration: 500 },
		});
		
		fc.jQueryUITooltip({
			position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
			content: tc.data('local').neverPoUseInAllBlogsTitle,
			show: { easing: "easeInExpo", duration: 1000 },
		});
		
		pc.jQueryUITooltip({
			position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
			content: tc.data('local').neverPoUseInAllPagesTitle,
			show: { easing: "easeInExpo", duration: 1000 },
		});

		lc.jQueryUITooltip({
			position: { my: "left-5 bottom-15", at: "left top", collision: "flipfit" },
			content: tc.data('local').neverPoLocal,
			show: { easing: "easeInExpo", duration: 500 },
		});
		
		//jQ progressbar initializing
		$('#loader_progress').progressbar();
		
		//everything initialized - get form init dimentions
		get_dimentions (tc);
		
		//Init translation
		markup_translatable_nodes_INITSET ();
		force_translation('body *');


		//Events
		tm.on('click'				+ namesSpace, switch_translate_form 					);
		ub.on('click'				+ namesSpace, switch_force_for_all_blogs				);
		up.on('click'				+ namesSpace, switch_force_for_all_pages				);	
		td.on('keypress'			+ namesSpace, toggle_buttons_state						);
		td.on('keyup'				+ namesSpace, toggle_buttons_state						);
		td.on('beforepaste paste'	+ namesSpace, check_buffer								);
		ct.on('click'				+ namesSpace, cancel_translation						);
		st.on('click'				+ namesSpace, save_translation							);
		sw.on('click'				+ namesSpace, {mode: 'console'}, trigger_elements		);
		mw.on('click'				+ namesSpace, {mode: 'tablet' }, trigger_elements		);
		tc.on('mousedown'			+ namesSpace, {el: tc, inout: 'in' }, 	console_move	);
		tc.on('mouseup'				+ namesSpace, {el: tc, inout: 'out'},	console_move	);
		
		//TO DO - translate after insert elements
		/* $("body").on("DOMNodeInserted", function(event) {
			
			if ( $(event.target).hasClass('wpb_content_element') ) {

			};
		});
		*/
		 
		//make title clickable
		$('#wp-admin-bar-neverpo_trnsmd_level_menu a').on('click' + namesSpace, function (e){
				e.preventDefault();
				tm.trigger( 'click' );
		})
	
	}
});
	
})(jQuery);