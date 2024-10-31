<?php

if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'neverpo' ) );
}
	
function neverpo_outpoot_dictionary_table () {
	
	$dictionary = neverpo_select_all_records();
	$dict_count	= count( $dictionary );
	$dict_dummy = array(array("entry_id"=>"","current_screen_id"=>"","current_blog_id"=>"","path"=>"","use_in_all_blogs"=>"","use_in_all_pages"=>"","original_text"=>"","translated_text"=>"","local"=>""));
	
	if ( $dict_count > 0 ){ $content = $dictionary; } else { $content = $dict_dummy; };

		echo '<table id="neverpo-dic-table"  class="bypass display nowrap" cellspacing="0" width="100%">';
		echo '<caption>' . __('Your NeverPo dictionary content', 'neverpo') . '</caption>';
		
			//table header
			echo '<thead><tr>';
				foreach ( $content[0] as $key => $value ){
					
					switch ( $key ) {
						
						case 'entry_id'				: echo '<th id="hentry">'	. __('ID', 'neverpo')				. '</th>'; break;
						case 'current_screen_id'	: echo '<th id="hscreen">'	. __('Screen', 'neverpo')			. '</th>'; break;
						case 'current_blog_id'		: echo '<th id="hscreen">'	. __('Blog', 'neverpo')				. '</th>'; break;
						case 'path'					: 								/* skip column */								   break;
						case 'use_in_all_blogs'		: 								/* skip column */								   break;
						case 'use_in_all_pages'		: 								/* skip column */								   break;
						case 'original_text'		: echo '<th id="hot">'		. __('Original Text', 'neverpo')	. '</th>'; break;
						case 'translated_text'		: echo '<th id="htt">'		. __('Translation', 'neverpo')		. '</th>'; break;
						case 'local'				: echo '<th id="hlocal">'	. __('Local', 'neverpo')			. '</th>'; break;
						default						: echo '<th>'				. __('Unknown column', 'neverpo')	. '</th>';
					}
				}
			echo '</tr></thead>';
			
			//table footer
			echo '<tfoot><tr>';
				foreach ( $content[0] as $key => $value ){
					
					switch ( $key ) {
						
						case 'entry_id'				: echo '<th id="fentry">'	. __('ID', 'neverpo')				. '</th>'; break;
						case 'current_screen_id'	: echo '<th id="fscreen">'	. __('Screen', 'neverpo')			. '</th>'; break;
						case 'current_blog_id'		: echo '<th id="hscreen">'	. __('Blog', 'neverpo')				. '</th>'; break;
						case 'path'					: 								/* skip column */								   break;
						case 'use_in_all_blogs'		: 								/* skip column */								   break;
						case 'use_in_all_pages'		: 								/* skip column */								   break;
						case 'original_text'		: echo '<th id="fot">' 		. __('Original Text', 'neverpo')	. '</th>'; break;
						case 'translated_text'		: echo '<th id="ftt">'		. __('Translation', 'neverpo')		. '</th>'; break;
						default						: echo '<th id="flocal">'	. __('Unknown column', 'neverpo')	. '</th>';
					}
				}
			echo '</tr></tfoot>';
			
			//table body
			echo '<tbody>';
				if ( $dict_count > 0 ){
					foreach ( $content as $value ){
					
					$this_current_blog_id	= $value->current_blog_id;
					$location 				= get_home_url( $value->current_blog_id, $value->path);
					$blogs	  				= $value->use_in_all_blogs;
					$pages	  				= $value->use_in_all_pages;
					
						echo '<tr>';
							foreach ( $value  as $key => $value ){
								
								if ( is_multisite() ){
									
									if( $key == 'current_blog_id' ) {
										if ( $blogs == 0					)		{ echo '<td>' . get_blog_option( $value, 'blogname' ) 	. '</td>';	}
										if ( $blogs == 1					)		{ echo '<td>' . __('All Blogs', 'neverpo') 				. '</td>';	}
									}
									else if ( $key == 'path' 				) 		{ 							/* skip column */						}
									else if ( $key == 'current_screen_id'	) 		{
										if ( $pages == 0 )							{ echo '<td><a href="' . $location . '">' . $value 	. '</a></td>';	}
										if ( $pages == 1 )							{ echo '<td>' . __('All pages', 'neverpo') 				. '</td>';	}
									}
									else if ( $key == 'use_in_all_pages'	) 		{ 							/* skip column */						}
									else if ( $key == 'use_in_all_blogs'	) 		{ 							/* skip column */						}
									else 											{ echo '<td>' . $value	. 								  '</td>';	}
								}
								else{
									
									if( $key == 'current_blog_id' ) {
										if ( $value == 1					)		{ echo '<td>' . get_option( 'blogname' ) 				. '</td>';	}
										else 										{ echo '<td>' . __('Translated in MS mode', 'neverpo') 	. '</td>';	}
									}
									else if ( $key == 'path' 				) 		{ 							/* skip column */						}
									else if ( $key == 'current_screen_id'	) 		{
										if ( $this_current_blog_id == 1 )			{ echo '<td><a href="' . $location . '">' . $value 	. '</a></td>';	}
										else										{ echo '<td>' . $value 									. '</td>';	}
									}
									else if ( $key == 'use_in_all_pages'	) 		{ 							/* skip column */						}
									else if ( $key == 'use_in_all_blogs'	) 		{ 							/* skip column */						}
									else 											{ echo '<td>' . $value	. 								  '</td>';	}
								}
							}
						echo '</tr>';
					}
				}
			echo '</tbody>';
		echo '</table>';
		
		?>
		<script type="text/javascript">
			(function($){
				$('#neverpo-dic-table').data({'local' : {
					'emptyTable'			: '<?php echo __('Your dictionary is empty', 'neverpo'); ?>',
					'row_'					: '<?php echo __('Selected %d row(s)', 'neverpo'); ?>',
					'row0'					: '<?php echo __('Click on row to select It', 'neverpo'); ?>',
					'row1'					: '<?php echo __('Selected 1 row', 'neverpo'); ?>',
					'previous'				: '<?php echo __('Previous', 'neverpo'); ?>',
					'next'					: '<?php echo __('Next', 'neverpo'); ?>',
					'search'				: '<?php echo __('Search', 'neverpo'); ?>',
					'info'					: '<?php echo __('Info', 'neverpo'); ?>',
					'infoEmpty'				: '<?php echo __('infoEmpty', 'neverpo'); ?>',
					'lengthMenu'			: '<?php echo __('lengthMenu', 'neverpo'); ?>',
					'lengthMenuAmount'		: '<?php echo __('All', 'neverpo'); ?>',
					'buttons'				:
						{
						'ColumnsButton'		:
							{
								'text'		: '<?php echo __('Columns', 'neverpo') . '<span title class="tip">?</span>';?>',
								'title'		: '<?php echo __('Columns visibility. NOTE: all export operations process only currently visible columns.', 'neverpo'); ?>'
							},
						'CopyButton'		:
							{
								'text'		: '<?php echo __('Copy', 'neverpo') . '<span title class="tip">?</span>'; ?>',
								'title'		: '<?php echo __('Copy hole table content into clipboard', 'neverpo'); ?>',
								'success'	: '<?php echo __('Copy to clipboard', 'neverpo'); ?>',
								'copyKeys'	: '<?php echo esc_html__('Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy the table data<br>to your system clipboard.<br><br>To cancel, click this message or press escape', 'neverpo'); ?>', 
								'info_1'	: '<?php echo __('Copied one row to clipboard', 'neverpo'); ?>',
								'info__'	: '<?php echo __('Copied %d rows to clipboard', 'neverpo'); ?>',
							},
						'ExportCSVButton'	:
							{
								'text'		: '<?php echo __('Export CSV', 'neverpo') . '<span title class="tip">?</span>'; ?>',
								'title'		: '<?php echo __('Export as CSV file. You may select nessesary rows to import only it. NOTE: Export uses UTF-8 encoding, so if you are gonna open it via Excel - use IMPORT, not regular "Open file" menu option', 'neverpo'); ?>',
							},
						'JSONPairButton'	:
							{
								'text'		: '<?php echo __('JSON Pair', 'neverpo') . '<span title class="tip">?</span>'; ?>',
								'title'		: '<?php echo __('Export as JSON with pair "Original text" -> "Translated text"', 'neverpo'); ?>',
							},
						}

				}});
			})(jQuery);
		</script>
		<?php
}
	
	echo '<div class="wrap">';
		echo '<h2 class="bypass">' . __( 'Dictionary settings page', 'neverpo' ) . '</h2>';
		neverpo_outpoot_dictionary_table();
	echo '</div>';