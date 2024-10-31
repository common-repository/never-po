<?php

/** 
* Load textdomain 'neverpo'
*/
function neverpo_lng_loads() {
	 load_plugin_textdomain( 'neverpo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/** 
* Check if needed tables exist.
* If not - create them
* Also if db version becomes old - update them
*/
function neverpo_create_tables() {
	
	$action = 'activate';
	include_once( dirname( __FILE__ ) . '/install.php' );
    $dbversion=get_option("neverpo_latest_dbver");
	
    if($dbversion < neverpo_latest_dbver )
    {
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');

        $sql="CREATE TABLE " . NEVERPO_DICTIONARY_TABLE_NAME." (
        entry_id int AUTO_INCREMENT,
		current_screen_id MEDIUMTEXT NOT NULL,
		current_blog_id MEDIUMTEXT NOT NULL,
		path MEDIUMTEXT NOT NULL,
        use_in_all_blogs INT(11) NOT NULL,
		use_in_all_pages INT(11) NOT NULL,
        original_text MEDIUMTEXT NOT NULL,
        translated_text MEDIUMTEXT NOT NULL,
		local MEDIUMTEXT NOT NULL,
        PRIMARY KEY  (entry_id)
        ) COLLATE utf8_general_ci;";
        dbDelta($sql);

        update_site_option ("neverpo_latest_dbver"	,neverpo_latest_dbver);
    }
}

function neverpo_send_dictionary () {
	global $wpdb;
	$dic = neverpo_get_dictionary( false );

	if ( $dic != null ) 	{ ?><script type="text/javascript">var neverpo_dictionary = <?php echo json_encode( $dic ); ?></script><?php }
	else					{ ?><script type="text/javascript">var neverpo_dictionary = 'nothing';</script><?php }
}

function neverpo_get_dictionary ( $ajax, $current_screen_id = null) {
	
	global $wpdb;
	
	if ( $ajax === true	) {$screen_id = $current_screen_id;			}
	if ( $ajax === false) {$screen_id = get_current_screen()->id;	}

	$page_dictionary_db = $wpdb->get_results(
	   "SELECT original_text, translated_text, use_in_all_blogs, use_in_all_pages 
		FROM ".NEVERPO_DICTIONARY_TABLE_NAME." 
		WHERE (local = '".get_locale()."' AND current_screen_id = '".$screen_id."' AND current_blog_id = ".get_current_blog_id().") 
		OR	  (local = '".get_locale()."' AND current_screen_id = '".$screen_id."' AND use_in_all_blogs = 1) 
		OR	  (local = '".get_locale()."' AND use_in_all_pages = 1 AND current_blog_id = ".get_current_blog_id().") 
		OR	  (local = '".get_locale()."' AND use_in_all_pages = 1 AND use_in_all_blogs = 1);"
		);
	
	if ( count( $page_dictionary_db ) > 0 ){
		foreach ($page_dictionary_db as $key){
			$page_dictionary[ $key->original_text ] = 
				array
				(
					'translated_text'	=> $key->translated_text,
					'use_in_all_blogs'	=> intval($key->use_in_all_blogs),
					'use_in_all_pages' 	=> intval($key->use_in_all_pages)
				);
		}
		return $page_dictionary;

	}
	else { return 'nothing'; }
}

/* function neverpo_count_records ( $original_text, $current_screen_id, $use_in_blog_id, $use_in_all_pages ) {
	
	global $wpdb;
			
		$count= $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".NEVERPO_DICTIONARY_TABLE_NAME." 
		WHERE original_text='".$original_text."' 
		AND current_screen_id='".$current_screen_id."' 
		AND local = '".get_locale()."'"));	
	
	return $count;
} */

function neverpo_select_all_records () {

	global $wpdb;
		
	$dictionary= $wpdb->get_results("SELECT entry_id, current_screen_id, current_blog_id, path, use_in_all_blogs, use_in_all_pages, original_text, translated_text, local FROM ".NEVERPO_DICTIONARY_TABLE_NAME );

	return $dictionary;
}

//UPDATE
function neverpo_update_translation ( $data ) {
	
	global $wpdb;
	
	if ( $data['use_in_all_pages'] === 0 && $data['use_in_all_blogs'] === 0 )	{
		
		$query = $wpdb->update( NEVERPO_DICTIONARY_TABLE_NAME,
			array (
			'translated_text'	=>	$data['translated_text'],
			'use_in_all_blogs'	=>	$data['use_in_all_blogs'],
			'use_in_all_pages'	=>	$data['use_in_all_pages']
			),
			array (
			'original_text'		=>	$data['original_text'],
			'current_screen_id'	=>	$data['current_screen_id'],
			'current_blog_id'	=>	$data['blogid'],
			'local' 			=> 	get_locale()
			)
		);
		if ( $query === 0 ) { neverpo_insert_translation ( $data ); };
	}
	else{
		
		if ( $data['use_in_all_pages'] === 1 && $data['use_in_all_blogs'] === 0 ){ neverpo_delete_all_pages	( $data );										}
		if ( $data['use_in_all_pages'] === 0 && $data['use_in_all_blogs'] === 1 ){ neverpo_delete_all_blogs	( $data );										}
		if ( $data['use_in_all_pages'] === 1 && $data['use_in_all_blogs'] === 1 ){ neverpo_delete_all_pages	( $data );	neverpo_delete_all_blogs( $data );	}
		
		neverpo_insert_translation	( $data );
	}
}

//INSERT
function neverpo_insert_translation ( $data )  {
								
	global $wpdb;
	
	$wpdb->insert(NEVERPO_DICTIONARY_TABLE_NAME,
		array (
			'original_text'		=> $data['original_text'],
			'translated_text'	=> $data['translated_text'],
			'current_screen_id' => $data['current_screen_id'],
			'current_blog_id'	=> $data['blogid'],
			'path'				=> $data['path'],
			'use_in_all_blogs'	=> $data['use_in_all_blogs'],
			'use_in_all_pages'	=> $data['use_in_all_pages'],
			'local'				=> get_locale()
		)
	);
}

//DELETE
function neverpo_delete_translation ( $data ) {
							
	global $wpdb;
	
	$wpdb->delete(NEVERPO_DICTIONARY_TABLE_NAME,
		array (
			'original_text'		=> $data['original_text'],
			'current_screen_id' => $data['current_screen_id'],
			'current_blog_id'	=> $data['blogid'],
			'local'				=> get_locale()
		)
	);
}

function neverpo_delete_all_pages ( $data ) {
							
	global $wpdb;
	
	$wpdb->delete(NEVERPO_DICTIONARY_TABLE_NAME,
		array (
			'original_text'		=> $data['original_text'],
			'current_blog_id'	=> $data['blogid'],
			'local'				=> get_locale()
		)
	);
}

function neverpo_delete_all_blogs ( $data ) {
							
	global $wpdb;
	
	$wpdb->delete(NEVERPO_DICTIONARY_TABLE_NAME,
		array (
			'original_text'		=> $data['original_text'],
			'current_screen_id' => $data['current_screen_id'],
			'local'				=> get_locale()
		)
	);
}

/** 
* Get the url to access a particular menu page based on the slug it was registered with 
* on the network admin in a multisite install. 
* 
* If the slug hasn't been registered properly no url will be returned 
* 
* @since 3.0.0 
* 
* @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu) 
* @param bool $echo Whether or not to echo the url - default is true 
* @return string the url 
*/ 
function network_menu_page_url( $menu_slug, $echo = true ) {
	global $_parent_pages; 

		if ( isset( $_parent_pages[$menu_slug] ) ) {
			$parent_slug = $_parent_pages[$menu_slug]; 
			if ( $parent_slug && ! isset( $_parent_pages[$parent_slug] ) ) { 
				$url = network_admin_url( add_query_arg( 'page', $menu_slug, $parent_slug ) ); 
			} else { 
				$url = network_admin_url( 'admin.php?page=' . $menu_slug ); 
			} 
		} else { 
		$url = ''; 
		} 

		$url = esc_url( $url ); 

		if ( $echo ) 
		echo $url; 

	return $url; 
}

/** 
* Register menu page for blog console mode
*/
function neverpo_register_blog_page() {
	
	$page = add_submenu_page(
		'options-general.php',
		'NeverPo Setting',
		esc_html__('Translation', 'neverpo'),
		'manage_options',
		NEVERPO_TOP_LEVEL_MENU_SLUG,
		'neverpo_blog_top_level_menu_page'
		);
		
		if ( current_user_can( 'manage_options' ) )  {
			add_action( 'admin_print_scripts-' . $page,	function () { wp_enqueue_script	( 'options-page-js'	); });
			add_action( 'admin_print_styles-'  . $page,	function () { wp_enqueue_style	( 'options-page-css'); });
		}
}

/** 
* Register menu page for network console mode
*/
function neverpo_register_network_menupage() {
	$page = add_submenu_page(
		'settings.php',
		'NeverPo Setting',
		esc_html__('Translation', 'neverpo'),
		'manage_options',
		NEVERPO_TOP_LEVEL_MENU_SLUG,
		'neverpo_network_top_level_menu_page'
		);
	
		if ( current_user_can( 'manage_options' ) )  {
			add_action( 'admin_print_scripts-' . $page,	function () { wp_enqueue_script	( 'options-page-js'	); });
			add_action( 'admin_print_styles-'  . $page,	function () { wp_enqueue_style	( 'options-page-css'); });
		}
}

/** 
* Top level menu page content for network&blog console mode
*/
function neverpo_network_top_level_menu_page()	{ require_once(NEVERPO_ROOT_PATH . '/options.php'); }
function neverpo_blog_top_level_menu_page()		{ require_once(NEVERPO_ROOT_PATH . '/options.php'); }

/** 
* Top level menu page content for adminbar
*/
function neverpo_admin_bar_menu_create( $wp_admin_bar ) {
	
	if ( current_user_can( 'manage_options' ) )  {

		if ( get_current_screen()->is_network == 1 ) {
			$top_level_page_url = network_menu_page_url( NEVERPO_TOP_LEVEL_MENU_SLUG, 0 );
		}
		else {
			$top_level_page_url = menu_page_url( NEVERPO_TOP_LEVEL_MENU_SLUG, 0 );
		}
			
		
		$sliding_button_html_1 ='<span id="fs_adminbar" class="flipswitch">' . 
									'<input type="checkbox" name="flipswitch" class="flipswitch-cb" id="fs_1_translate_mode">' . 
										'<label class="flipswitch-label" for="fs_1_translate_mode">' .
											'<p class="flipswitch-inner"></p>' .
											'<p class="flipswitch-switch"></p>' .
										'</label>' .
								'</span>';
								
		//top level
		$wp_admin_bar->add_menu( array(
			'id'    => 'neverpo_top_level_menu',
			'href'	=> $top_level_page_url,
			'title' => '<span class="ab-icon dashicons-randomize"></span><span class="ab-label">' . esc_html__('Translation', 'neverpo') . '</span>',
			'meta'	=> array (
				//'html'		=> '',
				'class'			=> 'bypass',
				//'rel'			=> 
				//'onclick'		=>  
				//'target'		=> 
				//'title'		=> 
				//'tabindex'	=> 
				)
			)
		);
		
		//1st level - 1
		$wp_admin_bar->add_menu( array(
			'parent'=> 'neverpo_top_level_menu',
			'id'    => 'neverpo_trnsmd_level_menu',
			'href'	=> '#',
			'title' => __( 'Translate mode', 'neverpo' ) . $sliding_button_html_1,
			'meta'	=> array (
				//'html'		=> 
				'class'			=> 'bypass',
				//'rel'			=> 
				//'onclick'		=> 
				//'target'		=> 
				//'title'		=> 
				//'tabindex'	=> 
				)
			)
		);

		//1st level - 2
		$wp_admin_bar->add_menu( array( // второй в списке пункт меню
			'parent' => 'neverpo_top_level_menu',
			'id'     => 'neverpo_envpin_level_menu',
			'href'	 => $top_level_page_url,
			'title'  =>  __( 'Go To -> Dictionary edit page', 'neverpo' ),
			'meta'	 => array (
				//'html'		=> '<div></div>',
				'class'			=> 'bypass',
				//'rel'			=> 
				//'onclick'		=>  
				//'target'		=> 
				//'title'		=> 
				//'tabindex'	=> 
				)
			)
		);
	}
}

function neverpo_add_body_class( $classes ) {
	
	$classes = $classes . ' neverpo-css-scope np_translatable_page';
	return $classes;
}