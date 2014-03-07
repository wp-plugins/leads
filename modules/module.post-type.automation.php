<?php


/* Build Rule CPT */

add_action('init', 'automation_cpt_register', 11);
function automation_cpt_register() {
	//echo $slug;exit;
    $labels = array(
        'name' => __('Automation', 'leads'),
        'singular_name' => __( 'Automation Rule', 'leads' ),
        'add_new' => __( 'Add New Automation Rule', 'leads' ),
        'add_new_item' => __( 'Create New Automation Rule' , 'leads' ),
        'edit_item' => __( 'Edit Automation Rule' , 'leads' ),
        'new_item' => __( 'New Automation Rules' , 'leads' ),
        'view_item' => __( 'View Automation Rules' , 'leads' ),
        'search_items' => __( 'Search Automation Rules' , 'leads' ),
        'not_found' =>  __( 'Nothing found' , 'leads' ),
        'not_found_in_trash' => __( 'Nothing found in Trash' , 'leads' ),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'query_var' => true,
        'menu_icon' => WPL_URL . '/images/automation.png',
       	'show_in_menu'  => 'edit.php?post_type=wp-lead',
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title' , 'custom-fields' )
    );

    register_post_type( 'automation' , $args );


}

/*----------------------------------------------------------------------------------------------------------------------------*/
/*********************** PREPARE COLLUMNS FOR LISTS****************************************************************************/
/*----------------------------------------------------------------------------------------------------------------------------*/


if (is_admin())
{
	// Change the columns for the edit CPT screen
	add_filter( "manage_automation_posts_columns", "automation_change_columns" );
	function automation_change_columns( $cols ) {
		$cols = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => __( 'Automation' , 'leads' ),
			"ma-automation-status" => __( 'Automation Status' , 'leads' )
		);

		$cols = apply_filters('automation_change_columns',$cols);

		return $cols;
	}


	add_action( "manage_posts_custom_column", "automation_custom_columns", 10, 2 );
	function automation_custom_columns( $column, $post_id )
	{
		global $post;

		if ($post->post_type !='automation'){
			return $column;
		}

		switch ( $column ) {
			case "title":
				$automation_name = get_the_title( $post_id );

				$automation_name = apply_filters('automation_name',$automation_name);

				echo $automation_name;
			  break;

			case "ma-automation-status":
				$status = get_post_meta($post_id,'automation_active',true);
				echo $status;
			  break;

		}

		do_action('automation_custom_columns',$column, $post_id);

	}


	// Make these columns sortable
	//add_filter( "manage_edit-automation_sortable_columns", "automation_sortable_columns" );
	function automation_sortable_columns($columns) {

		$columns = apply_filters('',$columns);

		return $columns;
	}



}

