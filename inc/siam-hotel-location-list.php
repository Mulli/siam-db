<?php


// generate hotels list for shortcode called by elementor template
add_shortcode('hotels-in-location', 'hotels_in_location');
function hotels_in_location( $atts ) {
	$a = shortcode_atts( array(
		'location' => '',
	), $atts );
	global $post_id;
	if (!empty($a['location'])){
	    //return get_hotels_in_location_cache(0, $a['location']);
	    $catid = get_cat_ID( $a['location'] );

	    $hotels = get_hotels_in_location_cache($catid, $a['location']);
    
        // create list
        $link_list = '';
        foreach ($hotels as $hotel){
            $flds = get_field ('hotel_description', $hotel['id']);
            //error_log('hotel_filter hotel_description=' . print_r($flds, true));
            $link_list .= '<li><a class="siam-hotel-links" href="'.$hotel['link'].'" >'. $hotel['title'] . ' </a></li>'; // ' (&#9734;'. $flds['starrate'] .')'
        }
        if (empty($link_list))
            return 'לא נמצאו מלונות';
        return $link_list;
	}
	return 'Missing location';
}
function get_hotels_in_location($catid){
    ////// get posts from transient CACHE: SIAM_HOTEL_INFO_<ID> // ID=cat_id
    ////// generated/updated WHENVER post type of that location is save OR if not found using acf/save hook
    
    //error_log('hotel_filter catid='. $catid);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'title',
        'order'            => 'ASC',
        'post_type'        => 'hotel-info',
        'category'         => $catid,
        'status'        => 'publish',
    );
    
    $posts = get_posts($args);
    // $nhotels = count($posts);
    return $posts;
}
function get_hotels_in_location_cache($catid, $location = '', $reset=false){
    if (!$reset){
        $data = get_transient('SIAM_LOC_HOTELS_'. $location);
        if (false !== $data){
            // error_log("get_hotels_in_location_cache using cache $location  data=". print_r($data, true));
            return $data;  
        }
    }
    //$catid = get_cat_ID( $location );
    $hotels = get_hotels_in_location($catid);
    $res = [];
    $link = '';
    foreach ($hotels as $hotel){
        $link = preg_replace("/https:\/\/[^\/]+/", '', get_permalink($hotel));
        $flds = get_field('hotel_description', $hotel->ID);
        // error_log("get_hotels_in_location_cache link=$link= post hotel=". print_r($flds, true));
        $res[] = array('id'=>$hotel->ID, 'title' => ' (<bdo dir="ltr">'. $flds['starrate'] . '&#9734;</bdo>) ' . $hotel->post_title, 'link' => $link);
    }
    set_transient('SIAM_LOC_HOTELS_'. $location, $res, 360*24*3600); // 1 year, will update if tramsient exists
    return $res;
}