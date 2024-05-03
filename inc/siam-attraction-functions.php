<?php

// add_shortcode('siam_attraction', 'siam_attraction_shortcode');
add_shortcode('attractions-in-location', 'attractions_in_location');

function attractions_in_location( $atts ) {
	$a = shortcode_atts( array(
		'location' => '',
	), $atts );
	if (empty($a['location']))
        return 'Missing location';

	/*    //return get_hotels_in_location_cache(0, $a['location']);
    $catparent = get_cat_ID('אטרקציות');
    $cat_children = get_term_children($catparent, 'category');
    $catid = 0;
    for ($i = 0; $i < count($cat_children); $i++) {
        $cat = get_category($cat_children[$i]);
        if ($cat->name == $a['location']) {
            $catid = $cat->term_id;
            break;
        }
    }*/
    $catid = get_attraction_cat_by_location($a['location']);

    if (!$catid || $catid == 0)
        return 'לא נמצאו אטרקציות ביעד: ' . $a['location'];

    // $c = get_category($catid);
    $attractions = get_attractions_in_location_cache($catid, $a['location']);

    // create list
    $link_list = '';
    foreach ($attractions as $attraction){
        //$flds = get_field ('hotel_description', $attraction['id']);
        //error_log('hotel_filter hotel_description=' . print_r($flds, true));
        $link_list .= '<li><a href="'.$attraction['link'].'" >'. $attraction['title'] . '</a></li>';
    }
    if (empty($link_list))
        return 'לא נמצאו אטרקציות';
    return $link_list;
}

function get_attractions_in_location_cache($catid, $location = '', $reset=false){
    /*if (!$reset){
        $data = get_transient('SIAM_LOC_ATTRACTIONS_'. $location);
        if (false !== $data){
            // error_log("get_attractions_in_location_cache using cache $location  data=". print_r($data, true));
            return $data;  
        }
    }*/
    //$catid = get_cat_ID( $location );
    $attractions = get_attractions_in_location($catid);
    $res = [];
    $link = '';
    foreach ($attractions as $attraction){
        $link = preg_replace("/https:\/\/[^\/]+/", '', get_permalink($attraction));
        // error_log("get_attractions_in_location_cache link=$link= post attraction=". print_r($attraction, true));
        //$res[] = array('id'=>$attraction->ID, 'title' => $attraction->post_title, 'link' => $link);
        $flds = get_fields($attraction->ID);
        $star =  ($flds['recommended'] == 'כן') ? ' &#9733;' : ''; // only recommended attractions (for now

        $res[] = array('id'=>$attraction->ID, 'title' =>  $attraction->post_title . $star , 'link' => $link);
    }
    set_transient('SIAM_LOC_ATTRACTIONS_'. $location, $res, 360*24*3600); // 1 year, will update if tramsient exists
    return $res;
}

function get_attractions_in_location($catid){
    ////// get posts from transient CACHE: SIAM_HOTEL_INFO_<ID> // ID=cat_id
    ////// generated/updated WHENVER post type of that location is save OR if not found using acf/save hook
    
    //error_log('hotel_filter catid='. $catid);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'title',
        'order'            => 'ASC',
        'post_type'        => 'attractions',
        'category'         => $catid,
        'status'        => 'publish',
    );
    
    $posts = get_posts($args);
    // $nhotels = count($posts);
    return $posts;
}
add_shortcode('get-attraction-desc', 'get_attraction_field_value');
function get_attraction_field_value( $atts ) {
	$a = shortcode_atts( array(
		'data' => 'desc',
	), $atts );
	$post_id = get_the_ID();
	if (!$post_id){
	   error_log("get_attraction_field_value FAIL NO PID postid=[$post_id] a[data]= ". print_r($a, true)); 
	   error_log(print_r(debug_backtrace(), true));
	   return '';
	}
	
	$fld = get_fields(); // 'group_64db82cf89a27'
	if (!$fld){
	   error_log("get_attraction_field_value GET_FIELD fail NO PID postid=[$post_id] a[data]= ". print_r($a, true)); 
	   return '';
	}
	//error_log('get_attraction_desc GET_FIELD OK flds='. print_r($fld, true));
    $field_names = array('esuf', 'extrim', 'allfamily', 'accessibility', 'selfarrival', 'wetline', 'lunchincluded', 'includeallcosts', 'recommended');
    if (in_array($a['data'], $field_names)) 
        return  ans($fld[$a['data']]);
	if ($a['data'] == 'desc') return  $fld['short_description'];
    
    if (isset($fld[$a['data']])){ // unlisted but exists with or without value
        //error_log('get_attraction_field_value unlisted but exists ' . $a['data'] . ' flds='. print_r($fld[$a['data']], true));
        //error_log(print_r(debug_backtrace(), true));

        return  $fld[$a['data']];
    }
	return 'ERROR: parameter: ' . $a['data'] . ' not in the code yet';
}