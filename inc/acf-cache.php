<?php

// Acf function & Cache management for hotels & attractions

// Handle hotel cache - renew upon hotel post save
add_action('acf/save_post', 'siam_acf_save_post');
function siam_acf_save_post( $post_id ) {
    $cpt = get_post_type($post_id);
    // Get newly saved values.
   if ($cpt == 'attractions'){
        //$post = get_post($post_id);
        $flds = get_fields($post_id);
        if (!isset($flds['location']) || empty($flds['location'])) {
            if (get_post_status($post_id) != 'draft') // ignore missing data in draft posts
                error_log("siam_acf_save_post MISSING ATTRACTION LOCATION in post $post_id= ". print_r(get_post($post_id), true));
            return; // location not found possible missing category definition of hotel post
        }
        $attraction_parent_category_id = get_cat_ID( 'אטרקציות' ); // 34; // ID of אטרקציות מידע
        $slug_cat =  $flds['location'] . '-אטרקציות';
        $attraction_child_category = get_category_by_slug( $slug_cat ); 
        $attraction_child_category_id = $attraction_child_category->term_id; 
        $retain_old_categories = false; // always reset categories if hotel || attraction

        wp_set_post_categories( $post_id, array(  $attraction_parent_category_id, $attraction_child_category_id), $retain_old_categories); 
        //$reset = true;
        //$res = get_attractions_in_location_cache($attraction_child_category_id, $flds['location'], $reset); // will reset cache
        return;
   }
   if ($cpt != 'hotel-info') return; // irrelevant for our purpose
  
   return reset_location_hotel_transients($post_id);
}
function reset_location_hotel_transients($post_id){
    $fld = get_field('hotel_description', $post_id);
    if (!$fld || empty($fld['location'])){
        if (get_post_status($post_id) != 'draft') // ignore missing data in draft posts
            error_log("siam_acf_save_post MISSING HOTEL INFO LOCATION in post $post_id= ". print_r(get_post($post_id), true));
        return; // location not found possible missing category definition of hotel post
    } 

    $parent_category_id = 2; //get_cat_ID( 'מלונות מידע' ); // ID of מלונות מידע
    $location = $fld['location'];
    $catid = get_cat_ID($location);
    $retain_old_categories = false; // always reset categories if hotel || attraction
    wp_set_post_categories( $post_id, array(  $parent_category_id, $catid), $retain_old_categories); 

    $posts = get_hotels_in_location($catid);
        // error_log("siam_acf_save_post GOT get_hotels_in_location() =". print_r($posts, true));

    $hotels = get_hotels_in_location_cache($catid, $location, true); // reset
        // error_log("siam_acf_save_post GOT get_hotels_in_location_cache =". print_r($hotels, true));
    //$location = str_replace(' ', '_', trim($location));
    set_transient('SIAM_LOC_HOTELS_'. $location, $hotels, 360*24*3600); // 1 year, will update if tramsient exists
    return;
}
function reset_location_attraction_transients($post_id){
    $flds = get_fields($post_id);
    if (!isset($flds['location']) || empty($flds['location'])) {
        if (get_post_status($post_id) != 'draft') // ignore missing data in draft posts
            error_log("siam_acf_save_post MISSING ATTRACTION LOCATION in post $post_id= ". print_r(get_post($post_id), true));
        return; // location not found possible missing category definition of hotel post
    }
    $attraction_parent_category_id = get_cat_ID( 'אטרקציות' ); // 34; // ID of אטרקציות מידע
    $attraction_child_category_id = get_cat_ID( $flds['location'] ); // ID of אטרקציות
    wp_set_post_categories( $post_id, array(  $attraction_parent_category_id, $attraction_child_category_id), $retain_old_categories); 
    $reset = true;
    $res = get_attractions_in_location_cache($attraction_child_category_id, $flds['location'], $reset); // will reset cache
    return;
}
function intercept_status_change($new_status, $old_status, $post) {
   //error_log('GOT intercept_status_change '. print_r($post, true));
   if ($old_status === 'publish' && $new_status !== 'publish')
       return reset_location_hotel_cache($post->ID);
   if ($old_status !== 'publish' && $new_status === 'publish')
       return reset_location_hotel_cache($post->ID);

   return ;
}
add_action('transition_post_status', 'intercept_status_change', 10, 3);
add_action('wp_trash_post', 'reset_location_hotel_cache');

add_action('before_delete_post', 'reset_location_hotel_cache');
function reset_location_hotel_cache($pid){
    $cats = get_the_category();
    if (!empty($cats)){
        $parent_category_id = 2; // ID of מלונות מידע
        foreach ($cats as $category) {
            if ($category->parent == $parent_category_id) {
                $hotels = get_hotels_in_location_cache($category->cat_ID, $category->name, true); // reset
                // error_log('reset_location_hotel_cache hotels '. print_r($hotels, true));
                return;
            }
        }
    }
    return ;
 }
// handle reset transient AFTER change of category using bulk or quick edit or edit
function siam_category_change_handler( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    if (empty($tt_ids)) return;
    $cpt = get_post_type($object_id);
    if ($cpt != 'hotel-info' && $cpt != 'attractions') return; // irrelevant for our purpose
    if ( 'category' === $taxonomy ) {
        // error_log("siam_category_change_handler Categories changed for post ID {$object_id}: Old Term IDs: " . implode(',', $old_tt_ids) . " New Term IDs: " . implode(',', $tt_ids));
        $retain_old_categories = false; // always reset categories if hotel || attraction
        if ($cpt == 'attractions'){
            $flds = get_fields($object_id);
            if (!isset($flds['location']) || empty($flds['location'])) {
                if (get_post_status($post_id) != 'draft') // ignore missing data in draft posts
                    error_log("siam_acf_save_post MISSING ATTRACTION LOCATION in post $object_id= ". print_r(get_post($object_id), true));
                return; // location not found possible missing category definition of hotel post
            }
            $attraction_parent_category_id = get_cat_ID( 'אטרקציות' ); // 34; // ID of אטרקציות מידע
            $slug_cat =  $flds['location'] . '-אטרקציות';
            $attraction_child_category = get_category_by_slug( $slug_cat ); 
            $attraction_child_category_id = $attraction_child_category->term_id; 
            $reset = true;
            $res = get_attractions_in_location_cache($attraction_child_category_id, $flds['location'], $reset); // will reset cache
            return;
        }
        if ($cpt == 'hotel-info') {
            $post_id = $object_id;
            $fld = get_field('hotel_description', $post_id);

            if (!$fld || empty($fld['location'])){
                if (get_post_status($post_id) != 'draft') // ignore missing data in draft posts
                    error_log("siam_acf_save_post MISSING HOTEL INFO LOCATION in post $post_id= ". print_r(get_post($post_id), true));
                return; // location not found possible missing category definition of hotel post
            } 
        
            $parent_category_id = 2; //get_cat_ID( 'מלונות מידע' ); // ID of מלונות מידע
            $location = $fld['location'];
            $catid = get_cat_ID($location);
            $retain_old_categories = false; // always reset categories if hotel || attraction
            //////////////////////////wp_set_post_categories( $post_id, array(  $parent_category_id, $catid), $retain_old_categories); 
        
            $posts = get_hotels_in_location($catid);
                // error_log("siam_acf_save_post GOT get_hotels_in_location() =". print_r($posts, true));
        
            $hotels = get_hotels_in_location_cache($catid, $location, true); // reset
                // error_log("siam_acf_save_post GOT get_hotels_in_location_cache =". print_r($hotels, true));
            //$location = str_replace(' ', '_', trim($location));
            set_transient('SIAM_LOC_HOTELS_'. $location, $hotels, 360*24*3600); // 1 year, will update if tramsient exists
            return;  
        }  
    }
}

add_action( 'set_object_terms', 'siam_category_change_handler', 10, 6 );
