<?php
// some library functions
function get_attraction_cat_by_location($location){
    $catparent = get_cat_ID('אטרקציות');
    $cat_children = get_term_children($catparent, 'category');
    for ($i = 0; $i < count($cat_children); $i++) {
        $cat = get_category($cat_children[$i]);
        if ($cat->name == $location) {
            return $cat->term_id;
        }
    }
    return -1;
}

function ans($str){
	return($str == 'כן' || $str == 'יש' ? add_fw_check($str) : add_fw_times($str));
}
function add_fw_times($str){
	$str=''; // do not show 'no' in asnwer
	$icon = '&#xD7; ' ;
	return '<span class="no-color">' . $icon . '</span><span>' .  $str . '</span>';
}
function add_fw_check($str){
	$str=''; // do not show 'no' in asnwer
	$icon = '&#x2713; ' ; 
	return '<span class="yes-color">' . $icon . '</span><span>' .  $str . '</span>';
}
// Remove jQuery Migrate
function dequeue_jquery_migrate( $scripts ) {
    if ( ! empty( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
}
add_action( 'wp_default_scripts', 'dequeue_jquery_migrate' );

function delete_transients_with_prefix( $prefix ) {
	foreach ( get_transient_keys_with_prefix( $prefix ) as $key ) {
		delete_transient( $key );
	}
}
function get_transient_keys_with_prefix( $prefix ) {
	global $wpdb;

	$prefix = $wpdb->esc_like( '_transient_' . $prefix );
	$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
	$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );

	if ( is_wp_error( $keys ) ) {
		return [];
	}

	return array_map( function( $key ) {
		// Remove '_transient_' from the option name.
		return substr( $key['option_name'], strlen( '_transient_' ) );
	}, $keys );
}
function del_trans_hotel(){
    return delete_transients_with_prefix( 'SIAM_LOC_HOTELS' );
}
add_shortcode('del-trans-hotel', 'del_trans_hotel');
