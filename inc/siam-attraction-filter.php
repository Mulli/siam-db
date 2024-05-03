<?php

add_action( 'wp_ajax_nopriv_attraction_filter', 'attraction_filter' ); 
add_action( 'wp_ajax_attraction_filter', 'attraction_filter' );

function attraction_filter(){
    //error_log('attraction_filter POST='. print_r($_POST, true));
    // filter
    // create filter from form $_POST values for required tests only
    $filter = [];
    foreach ($_POST as $key => $value){
        if ($key == 'action' || $key == 'location') continue;
        if (!$value || $value == "false") continue;
        $filter[$key] = $value;
    }
    
    // Get all attractions in location according to $arg
    $location = $_POST['location'];
    $location = str_replace('\\', '', $location);
    //$catid = get_cat_ID( $_POST['location'] );
    $catid = get_attraction_cat_by_location($location);
    if (empty($location) || $catid < 0){
        wp_send_json('בעיה מערכת בזיהוי יעד- פנה לתמיכה');
        wp_die();
    }

    $attractions = get_attractions_in_location_cache($catid, $location);
    
    // run the filter
    
    $link_list = '';
    // for documentation only ... $attraction_group_key = 'group_64db82cf89a27';
    foreach ($attractions as $attraction){
        // error_log('attraction_filter attraction='. print_r($attraction, true));
        $flds = get_fields($attraction['id']); // get_field ('attraction_description', $attraction['id']);
        //error_log('attraction_filter attraction_description=' . print_r($flds, true));
        
        if (check_attraction_filter($filter, $flds)){
            $link_list .= '<li><a href="'.$attraction['link'].'" >'. $attraction['title'] . '</a></li>';
            continue;
        } 
    }
    
    if (empty($link_list))
        $answer = '<p>לא נמצאו אטרקציות</p>';
    else $answer = '<ul class="link-list-result">' . $link_list . '</ul>';
    wp_send_json($answer);
    wp_die();
}
function check_attraction_filter($filter, $flds){
    foreach ($filter as $key => $filter_value){
        if (!$filter_value) 
            continue; // ignored conditions
       // error_log('COMPARE key= ' . $key . ' flds value= '. print_r($flds,true));
        if ($flds[$key] != 'כן') 
            return false;     // recommened to families
    }
    return true;
    //error_log('check_filter flds=' . print_r($flds, true));
}