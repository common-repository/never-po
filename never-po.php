<?php
/**
 * Plugin Name: Never POEDIT
 * Description: Enable translating text in administrator console on your WordPress site without using po&mo files.
 * Author: Temdo
 * Version: 1.0.6
 * License: GPLv2
 * Text Domain: neverpo
 * Domain Path: /languages
 */

/* class NeverPo {
 
}
$GLOBALS['NeverPo'] = new NeverPo; */

global $wpdb;
if(!defined('ABSPATH')) { die('Forbidden'); }

define('neverpo_latest_dbver',1);
define('NEVERPO_ROOT_PATH', dirname(__FILE__));
define('NEVERPO_ROOT_URL', plugins_url('', __FILE__));
define('NEVERPO_DICTIONARY_TABLE_NAME', $wpdb->base_prefix . "neverpo_dictionary");
define('NEVERPO_TOP_LEVEL_MENU_SLUG', 'neverpo-top-level-menu-page');

require_once(NEVERPO_ROOT_PATH . '/functions.php');

register_activation_hook( __FILE__, 'neverpo_create_tables' );

add_action( 'admin_init', function () {	add_filter( 'admin_body_class', 'neverpo_add_body_class', 20, 1); });

add_action('plugins_loaded',		'neverpo_lng_loads'					);	//text domain
add_action('admin_menu',			'neverpo_register_blog_page'		);	//Menu in standalone mode
add_action('network_admin_menu',	'neverpo_register_network_menupage'	);	//Menu in multisite mode
add_action('admin_bar_menu',		'neverpo_admin_bar_menu_create', 90	);	//Adminbar


add_action( 'admin_head', function () {

	neverpo_send_dictionary();
	
	if ( current_user_can( 'manage_options' ) )  {
		global $wpdb;

		echo '<audio preload="auto" class="beep" id="sound-0"><source src="' . NEVERPO_ROOT_URL . '/css/assets/media/sound-1.mp3"><source src="' . NEVERPO_ROOT_URL . '/assets/media/sound-1.wav"></audio>';
		echo '<audio preload="auto" class="beep" id="sound-1"><source src="' . NEVERPO_ROOT_URL . '/css/assets/media/sound-1.mp3"><source src="' . NEVERPO_ROOT_URL . '/assets/media/sound-1.wav"></audio>';
		?>
			<div id="neverpo-ruler">
				<div class="np-loader spinner4">
					<div class="dot1"></div>
					<div class="dot2"></div>
					<div class="bounce1"></div>
					<div class="bounce2"></div>
					<div class="bounce3"></div>
				</div>
				<div class="bypass" id="neverpo_trans_container">
					<div class="bypass" id="neverpo_trans_container_inner">
						<div id="neverpo_trans_container_header">
							<span id="neverpo_drag_window" class="dashicons dashicons-move"></span>
							<span id="neverpo_minimize_window" class="dashicons dashicons-arrow-down-alt2"></span>
							<span id="neverpo_minimize_window_caption"><?php echo __('Translation managment console', 'neverpo') ?></span>
							<span class="status_bar pointer-reminder dashicons dashicons-arrow-right"></span>
							<span class="bypass pointer-reminder"><?php echo __('Point mouse over ', 'neverpo') ?>
								<span class="bypass never-po-text-marker" title="jq-ui"><?php echo __(':text', 'neverpo') ?></span>
							</span>
							<span class="never-po-text-marker-init status_bar never-po-hotkey-reminder dashicons dashicons-arrow-right"></span>
							<span class="never-po-text-marker-init bypass never-po-hotkey-reminder"><?php echo __('Use Hot Key ', 'neverpo') ?>
								<span class="bypass never-po-text-marker"><?php echo __('Shift+B', 'neverpo') ?></span>
							</span>
						</div>
						<div id="neverpo_trans_container_body">
							<div id="loader_progress"></div>
							<div id="devider_hr"></div>
							<div id="neverpo_trans_area">
								<textarea id="origin_data" placeholder="<?php echo __('Point mouse over text with ":" markup at the beginning, then press "Shift + B".', 'neverpo') ?>" readonly="readonly"></textarea>
								<div id="dummy-v6px" style="min-height: 6px;"></div>
								<textarea id="translated_data" placeholder="<?php echo __('Enter translation - HTML Allowed!', 'neverpo') ?>"></textarea>
							</div>
						</div>
						<div id="neverpo_trans_container_footer">
							<div id="neverpo_trans_container_footer_left">
								<div class="neverpo_trans_container_footer_elem">
									<span id="neverpo_wp_local" title="jq-ui"><?php echo get_locale(); ?></span>
								</div>
								<div class="neverpo_trans_container_footer_elem">
									<span><?php echo __('All pages', 'neverpo'); ?></span>
												<div id="fs_translate_console" class="flipswitch">
													<input type="checkbox" name="flipswitch" class="flipswitch-cb" id="fs_1_all_page">
														<label class="flipswitch-label" for="fs_1_all_page" title="jq-ui">
															<div class="flipswitch-inner"></div>
															<div class="flipswitch-switch"></div>
														</label>
												</div>
								</div>
								<?php if ( is_multisite() ) {
											?>
											<div class="neverpo_trans_container_footer_elem">
												<span><?php echo __('All Blogs', 'neverpo'); ?></span>
												<div id="fs_translate_console" class="flipswitch">
													<input type="checkbox" name="flipswitch" class="flipswitch-cb" id="fs_1_all_blog">
														<label class="flipswitch-label" for="fs_1_all_blog" title="jq-ui">
															<div class="flipswitch-inner"></div>
															<div class="flipswitch-switch"></div>
														</label>
												</div>
											</div>
											<?php
										}
								?>
							</div>
							<div id="neverpo_trans_container_footer_right">
								<button id="cancel_translation"><span class="dashicons-before dashicons-no"><?php echo __('Clear', 'neverpo') ?></span></button>
								<button id="save_translation"><span class="dashicons-before dashicons-upload"><?php echo __('Save', 'neverpo') ?></span></button>
							</div>
						</div>
					</div>
				</div>
				<div id="switch" class="bypass"><span>Shift+B</span></div>
			</div>
			<script type="text/javascript">
				(function($){
					$(document).data({'mode'	: 'manage'});
					$('#neverpo_trans_container').data(
					{'local' 	:
						{
							'neverPoTextMarkerTitle'	: '<?php echo __('Text available for translation marked up with ":" at the beggining. You can translate only hole string. To do it - point mouse over such string and press "Shift + B".', 'neverpo'); ?>',
							'neverPoUseInAllBlogsTitle'	: '<?php echo __('Turn On this switcher and this translation will be applied for all blogs in WP multisite mode.', 'neverpo'); ?>',
							'neverPoUseInAllPagesTitle'	: '<?php echo __('Turn On this switcher and this translation will be applied for all pages of the current blog.', 'neverpo'); ?>',
							'neverPoLocal'				: '<?php echo __('This is Your current local. All saved translations will be applied ONLY in this local.', 'neverpo'); ?>',
						},
					'blogid'	: '<?php print_r ( get_current_blog_id() )?>',
					'path'		: '<?php if ( $_SERVER['QUERY_STRING'] != '' ) { echo  $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']; } else { echo $_SERVER['SCRIPT_NAME']; } ?>',
					'screen'	: '<?php print_r ( get_current_screen()->id ) ?>',
					});
				})(jQuery);
			</script>
		<?php
	}
	else {
		?>
			<div id="neverpo-ruler">
				<div class="np-loader spinner4">
					<div class="dot1"></div>
					<div class="dot2"></div>
					<div class="bounce1"></div>
					<div class="bounce2"></div>
					<div class="bounce3"></div>
				</div>
			</div>	
			<script type="text/javascript">
				(function($){
					$(document).data({'mode'	: 'translate'});
				})(jQuery);
			</script>
		<?php
	}
});
	
add_action('admin_enqueue_scripts',	function () {
	
	  //wp_register_style ('jq-ui-style',				NEVERPO_ROOT_URL .'/css/jquery-ui.css',													false						,	'1.0', 'screen'	);
		wp_register_style ('jq-ui-style-theme',			NEVERPO_ROOT_URL .'/css/jquery-ui.theme.css',											false						,	'1.0', 'screen'	);
		wp_register_style ('jq-ui-style-structure',		NEVERPO_ROOT_URL .'/css/jquery-ui.structure.css',										false						,	'1.0', 'screen'	);
		wp_register_style ('admin-over-all',			NEVERPO_ROOT_URL .'/css/admin-over-all.css',											array(
																																				'jq-ui-style-theme',
																																				'jq-ui-style-structure'
																																				)							,	'1.0', 'screen'	);
		
																																			
		wp_register_style ('tables-css',				NEVERPO_ROOT_URL .'/scripts/tables/DataTables-1.10.12/css/jquery.dataTables.min.css',	false						,	'1.0', 'screen'	);
		wp_register_style ('table-select-css',			NEVERPO_ROOT_URL .'/scripts/tables/Select-1.2.0/css/select.jqueryui.min.css', 			false						,	'1.0', 'screen'	); 
		wp_register_style ('table-buttons-css',			NEVERPO_ROOT_URL .'/scripts/tables/Buttons-1.2.2/css/buttons.dataTables.min.css', 		false						,	'1.0', 'screen'	); 
		wp_register_style ('table-responsive-css',		NEVERPO_ROOT_URL .'/scripts/tables/Responsive-2.1.0/css/responsive.jqueryui.min.css', 	false						,	'1.0', 'screen'	); 
		wp_register_style ('options-page-css',			NEVERPO_ROOT_URL .'/css/options-page.css', 												array(
																																				'tables-css',
																																				'table-select-css',
																																				'table-buttons-css',
																																				'table-responsive-css'
																																				)							,	'1.0', 'screen'	);
		
		wp_enqueue_script ("jquery-ui-core",																									array('jquery'						));
		wp_enqueue_script ("jquery-ui-resizable",																								array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-effects-core",																								array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-button",																									array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-tooltip",																									array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-progressbar",																								array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-draggable",																								array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-widget",																									array('jquery',	'jquery-ui-core'	));
		wp_enqueue_script ("jquery-ui-touch-punch",		NEVERPO_ROOT_URL .'/scripts/jquery.ui.touch-punch.min.js',								array('jquery',	'jquery-ui-core'	));
		
		wp_register_script("functions-js",				NEVERPO_ROOT_URL .'/scripts/functions-js.js',											array('jquery',	'jquery-ui-core'	));
		
		wp_register_script("tables-js",					NEVERPO_ROOT_URL .'/scripts/tables/DataTables-1.10.12/js/jquery.dataTables.min.js',		array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-jszip-js",			NEVERPO_ROOT_URL .'/scripts/tables/JSZip-2.5.0/jszip.min.js',							array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-select-js",			NEVERPO_ROOT_URL .'/scripts/tables/Select-1.2.0/js/dataTables.select.min.js',			array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-buttons-js",			NEVERPO_ROOT_URL .'/scripts/tables/Buttons-1.2.2/js/dataTables.buttons.min.js',			array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-buttons-colVis-js",	NEVERPO_ROOT_URL .'/scripts/tables/Buttons-1.2.2/js/buttons.colVis.min.js',				array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-buttons-html5-js",	NEVERPO_ROOT_URL .'/scripts/tables/Buttons-1.2.2/js/buttons.html5.min.js',				array('jquery',	'jquery-ui-core'	));
		wp_register_script("tables-responsive-js",		NEVERPO_ROOT_URL .'/scripts/tables/Responsive-2.1.0/js/dataTables.responsive.js',		array('jquery',	'jquery-ui-core'	));
		
		wp_register_script("options-page-js",			NEVERPO_ROOT_URL .'/scripts/options-page.js',											array(
																																				'tables-js',
																																				'tables-jszip-js',
																																				'tables-select-js',
																																				'tables-buttons-js',
																																				'tables-buttons-colVis-js',
																																				'tables-buttons-html5-js',
																																				'tables-responsive-js',
																																			
																																				)
																																			);
		wp_register_script('never-po',					NEVERPO_ROOT_URL .'/scripts/mark-nodes.js',												array(
																																				'jquery-ui-core',
																																				'jquery-ui-resizable',
																																				'jquery-ui-button',
																																				'jquery-ui-tooltip',
																																				'jquery-ui-progressbar',
																																				'jquery-ui-draggable',
																																				'jquery-ui-widget',
																																				'functions-js',
																																				)
																																			);
			wp_enqueue_style	( 'admin-over-all' 	);
			wp_enqueue_script	( 'never-po' 		);

});

add_action('wp_ajax_neverpo_save_translation',	'neverpo_save_translation' );

function neverpo_save_translation() {
	
	$data = array (
	
		'original_text'		=>				wp_unslash	( 	$_POST['translatable_pair']['original_text'		])	,
		'translated_text'	=>				wp_unslash	( 	$_POST['translatable_pair']['translated_text'	])	,
		'current_screen_id'	=>				wp_unslash	( 	$_POST['translatable_pair']['current_screen_id'	])	,
		'blogid'			=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['blogid'			]) ),
		'path'				=>				wp_unslash	(	$_POST['translatable_pair']['path'				])	,
		'use_in_all_blogs'	=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['use_in_all_blogs'	]) ),
		'use_in_all_pages'	=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['use_in_all_pages'	]) ),
	);
	
	neverpo_update_translation	( $data );
	
	$dic = neverpo_get_dictionary( true, $data['current_screen_id'] );
	
	echo json_encode( $dic );
	
	wp_die();
}

add_action('wp_ajax_neverpo_cancel_translation', 'neverpo_cancel_translation');

function neverpo_cancel_translation() {
	
	$data = array (
	
		'original_text'		=>				wp_unslash	( 	$_POST['translatable_pair']['original_text'		])	,
		'translated_text'	=>				wp_unslash	( 	$_POST['translatable_pair']['translated_text'	])	,
		'current_screen_id'	=>				wp_unslash	( 	$_POST['translatable_pair']['current_screen_id'	])	,
		'blogid'			=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['blogid'			]) ),
		'path'				=>				wp_unslash	(	$_POST['translatable_pair']['path'				])	,
		'use_in_all_blogs'	=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['use_in_all_blogs'	]) ),
		'use_in_all_pages'	=>	intval	(	wp_unslash	(	$_POST['translatable_pair']['use_in_all_pages'	]) ),
	);

	neverpo_delete_translation( $data );

	$dic = neverpo_get_dictionary( true, $data['current_screen_id'] );
	
	echo json_encode( $dic );
	
	wp_die();
}

add_action( 'upgrader_process_complete', 'on_update', 10, 2);

function on_update( $upgrader_object, $options ) {

	if( $options['action'] == 'update' && $options['type'] == 'plugin' ){

		$pbm = plugin_basename( __FILE__ );
		$path = plugin_basename( dirname( __FILE__ ) ) ;
			
		if( in_array($pbm, $options['plugins']) ){
			$action = 'update';
			include_once( dirname( __FILE__ ) . '/install.php' );
		}
	}
}