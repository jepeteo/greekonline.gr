<?php
/* Start Theodore Functions */

add_filter('post_row_actions','my_action_row', 10, 2);
function my_action_row($actions, $post){
    if ($post->post_type =="wpecbd"){
		// The default $actions passed has the Edit, Quick-edit and Trash links.
		$trash = $actions['trash'];

	}
    return $actions;
}

// Add new column in admin custom posts page
add_filter('manage_edit-wpecbd_columns', 'my_extra_wpecbd_columns');
function my_extra_wpecbd_columns($columns) {
    $columns['new_display'] =__('Εμφάνιση','myplugindomain');
    return $columns;
}

add_action( 'manage_wpecbd_posts_custom_column', 'my_wpecbd_column_content', 10, 2 );
function my_wpecbd_column_content( $column_name, $post_id ) {
    if ( 'new_display' != $column_name )
        return;
    //Get number of slices from post meta
    $new_display = get_post_meta($post_id, 'new_display', true);
//    echo intval($new_display);

	switch($new_display)
	{
			case "1"; echo "<div class='ndisp-new'><span></span></div>";break;
			case "0"; echo "<div class='ndisp-old'><span></span></div>";break;
			default; echo "<div class='ndisp-empty'><span></span></div>"; break;
/* [DEBUG] // TODO delete
      case "1"; echo "<div class='ndisp-new'><span></span>" . $new_display . "</div>";break;
			case "0"; echo "<div class='ndisp-old'><span></span>" . $new_display . "</div>";break;
			default; echo "<div class='ndisp-empty'><span></span>" . $new_display . "</div>"; break;
*/
	}
}

add_filter( 'manage_edit-wpecbd_sortable_columns', 'my_sortable_wpecbd_column' );
function my_sortable_wpecbd_column( $columns ) {
    $columns['new_display'] = 'ndisplay';
     //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
     return $columns;
}
add_action( 'pre_get_posts', 'my_ndisplay_orderby' );
function my_ndisplay_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
 
    if( 'ndisplay' == $orderby ) {
        $query->set('meta_key','new_display');
        $query->set('orderby','meta_value_num');
    }
}

//Disable feeds
function disable_feed() {
 wp_die( __( 'This site does not have a feed, visit the <a href="'. esc_url( home_url( '/' ) ) .'">home page</a>!' ) );
}
add_action('do_feed', 'disable_feed', 1);
add_action('do_feed_rdf', 'disable_feed', 1);
add_action('do_feed_rss', 'disable_feed', 1);
add_action('do_feed_rss2', 'disable_feed', 1);
add_action('do_feed_atom', 'disable_feed', 1);
add_action('do_feed_rss2_comments', 'disable_feed', 1);
add_action('do_feed_atom_comments', 'disable_feed', 1);

remove_action('wp_head', 'feed_links', 2 );
add_filter('post_comments_feed_link',function () { return null;});

// set feature image from ACF, only if feature image is not set
function acf_set_featured_image( $value, $post_id, $field  ){
    if($value != ''){
		update_post_meta(false, '_thumbnail_id', $value);
    }
    return $value;
}
// acf/update_value/name={$field_name} - filter for a specific field based on it's name
add_filter('acf/update_value/name=side_image', 'acf_set_featured_image', 10, 3);

// Add PNG to media select dropdown
function modify_post_mime_types( $post_mime_types ) {
     $post_mime_types['image/png'] = array( __( 'PNGs' ), __( 'Manage PNGs' ), _n_noop( 'PNG <span class="count">(%s)</span>', 'PNGs <span class="count">(%s)</span>' ) );
     // then we return the $post_mime_types variable
    return $post_mime_types;
}
 
// Add Filter Hook // set / unset image sizes
add_filter( 'post_mime_types', 'modify_post_mime_types' );
if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'tablet-full', 720, 200, true ); //(cropped)
	add_image_size( 'tablet-post', 520, 149, false ); //(scaled)
}
if ( function_exists( 'remove_image_size' ) ) { 
    remove_image_size( '1536x1536' );
    remove_image_size( '2048x2048' );
    remove_image_size( 'medium' );
    remove_image_size( 'medium_large' );
}

// Max image dimensions
function teo_big_image_size_threshold( $threshold, $imagesize, $file, $attachment_id ) {
    return 2048;
}
add_filter( 'big_image_size_threshold', 'teo_big_image_size_threshold', 10, 4 );

function my_login_logo_one() { 
?> 
<style type="text/css"> 
body.login div#login h1 a   { background-image: url(https://www.greekonline.gr/wp-content/uploads/2021/01/go-logo-white.png); width: 95%; background-size: 100%; max-width:300px; } 
body.login                  { background: #aaccee; }
body.login form             { box-shadow: 0 0 10px 1px #666 !important; }
</style>
 <?php 
} 

add_action( 'login_enqueue_scripts', 'my_login_logo_one' );

// Add google maps API key. Required for backend.
function my_acf_init() {
    acf_update_setting('google_api_key', 'enter-api-key');
}
add_action('acf/init', 'my_acf_init');

/* Add custom css to admin */
function admin_css() { ?>
    <link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/admin.css" type="text/css" media="all" />
<?php }
add_action('admin_head', 'admin_css');

/* Unhook WPECBD custom post, if new display is selected */
function unhook_old_catal() { 
//    $postid = $_GET['post'];
// Get access to the current WordPress object instance
global $wp;
// Get the base URL
$current_url = home_url(add_query_arg(array(),$wp->request));
// Add WP's redirect URL string
$current_url = $current_url . $_SERVER['REDIRECT_URL'];
// Retrieve the current post's ID based on its URL
$id = url_to_postid($current_url);

    $postid = $id;
    if (get_field ("new_display", $postid)) { 
        $value = get_field( "fix_map", $postid );
            if( $value == "1") { 

                wp_deregister_script('jquery.geocomplete');
              //// [DEBUG] ////
//              wp_deregister_script('google-maps');
//              remove_meta_box('wpecbd', 'wpecbd_images','normal');
/*
                wp_deregister_script('fancybox');
                wp_deregister_script('jquery.columnizer');
                wp_deregister_script('jquery.select2');
                wp_deregister_script('wpecbd-frontend');
                wp_deregister_script('jquery.maskedinput');
                wp_deregister_script('jquery-ui-core-bd');
                wp_deregister_script('jquery-ui-datepicker-bd');
                wp_deregister_script('plupload');
                wp_deregister_script('plupload-html5');
                wp_deregister_script('plupload-flash');
*/                
//                global $wp_business_directory;
//                remove_action('admin-init', array($wp_business_directory,'admin_init_functions'));
            }
    }
}
add_action ('init', 'unhook_old_catal');

/* Search results sorted by date descending */
function searchfilter($query) {
    if ($query->is_search && !is_admin() ) {
        $query->set( 'post_type',array('wpecbd'));
        $query->set( 'orderby', 'date' );
        $query->set( 'order', 'DESC' );
    } 
return $query;
}
add_filter('pre_get_posts','searchfilter');
?>
