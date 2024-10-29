<?php
/**
 * Plugin Name: Automatic Tag Selector
 * Plugin URI: http://biostall.com
 * Description: Entering tags for posts each time can be tedious. This plugin will look for existing tags within your content and add them automatically.
 * Author: Steve Marks (BIOSTALL)
 * Author URI: http://biostall.com
 * Version: 1.0.0
 *
 * Copyright 2013 BIOSTALL ( email : info@biostall.com )
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

function custom_posts_per_tag($id, $post_type)
{
	$args = array(
        'post_type' => array($post_type),
        'posts_per_page' => 1,
		'tag_id' => $id
	);

	$the_query = new WP_Query( $args );
	wp_reset_query();

	return sizeof($the_query->posts);
}
 
add_filter( 'tiny_mce_before_init', 'ats_plugin_tiny_mce_before_init' );
function ats_plugin_tiny_mce_before_init( $initArray )
{
	global $post;
	
	$tags = get_tags();
	
	$post_type = $post->post_type;
	
	$tag_array = array();

	foreach ($tags as $tag)
	{
		if (custom_posts_per_tag($tag->term_id, $post_type) > 0)
		{
			$tag_array[] = $tag->name;
		}
	}
?>
<script type="text/javascript">
	
	var tag_search_timeout;
	
	var existing_tags = <?php echo json_encode($tag_array); ?>; 
	
	function check_tags()
	{
		if (typeof tinymce.activeEditor != 'undefined')
		{
			var content_to_check = tinymce.activeEditor.getContent();
			content_to_check = content_to_check.toLowerCase();
			
			if ( existing_tags.length )
			{
				for ( var i in existing_tags )
				{
					var tag_to_check = existing_tags[i].toLowerCase();
					
					if ( content_to_check.indexOf(tag_to_check) != -1 )
					{
						// The content does contain this tag
						// Simulate typing it into the box and clicking 'Add'
						jQuery('#new-tag-post_tag').val(existing_tags[i]);
						jQuery('#new-tag-post_tag').next('.tagadd').trigger('click');
					}
				}
			}
		}
	}
	
</script>

<?php
    $initArray['setup'] = <<<JS
[function(ed) {
	
    ed.onKeyDown.add(function(ed, e) 
    {
        if (typeof tag_search_timeout != 'undefined') 
		{
			clearTimeout(tag_search_timeout);
		}
        tag_search_timeout = setTimeout('check_tags()', 2000);
    });

}][0]
JS;
    return $initArray;
}

// Activation / Deactivation / Deletion
register_activation_hook( __FILE__, 'ats_plugin_activation' );
register_deactivation_hook( __FILE__, 'ats_plugin_deactivation' );

function ats_plugin_activation()
{
	// Actions to perform on plugin activation go here
	
	//register uninstaller
    register_uninstall_hook( __FILE__, 'ats_plugin_uninstall' );
}

function ats_plugin_deactivation()
{    
	// Actions to perform on plugin deactivation go here
}

function ats_plugin_uninstall()
{
	 
}