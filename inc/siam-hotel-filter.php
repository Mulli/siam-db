<?php
// hotel filter contains 2 parts:
// 1. AJAX handler for filter - called from hotel filter page
// 2. Shortcode Support for Elementor form with checkboxes for filter
add_action( 'wp_ajax_nopriv_hotel_filter', 'hotel_filter' ); 
add_action( 'wp_ajax_hotel_filter', 'hotel_filter' );

function hotel_filter(){
    //error_log('hotel_filter POST='. print_r($_POST, true));
    // filter
    // create filter from form $_POST values for required tests only
    $filter = [];
    foreach ($_POST as $key => $value){
        if ($key == 'action' || $key == 'location') continue;
        if (!$value || $value == "false") continue;
        $filter[$key] = $value;
    }
    
    // Get all hotels in location 
    $location = $_POST['location'];
    $catid = get_cat_ID( $_POST['location'] );
    $hotels = get_hotels_in_location_cache($catid, $location);
    
    // run the filter
    
    $link_list = '';
    foreach ($hotels as $hotel){
        // error_log('hotel_filter hotel='. print_r($hotel, true));
        $flds = get_field ('hotel_description', $hotel['id']);
        //error_log('hotel_filter hotel_description=' . print_r($flds, true));

        if (check_filter($filter, $flds)){
            $link_list .= '<li><a class="siam-hotel-links" href="'.$hotel['link'].'" >'. $hotel['title'] . '</a></li>';
            continue;
        } 
    }
    
    if (empty($link_list))
        $answer = '<p>לא נמצאו מלונות</p>';
    else 
        $answer = '<ul class="link-list-result">' . $link_list . '</ul>';

    wp_send_json($answer);
    wp_die();
}
function check_filter($filter, $flds){
    foreach ($filter as $key => $filter_value){
        if (!$filter_value) continue; // ignored conditions
       // error_log('COMPARE key= ' . $key . ' flds value= '. print_r($flds,true));
        if ($key == 'families')
            if ($flds['recomended'] != 'כן') return false;     // recommened to families
            else continue;
        
        if ($key == 'balkony')
            if ($flds['balkony'] != 'יש')  return false;    // balkony for each room
            else continue;
        if ($key == 'connected')
            if ( $flds['gconnected'] != 'יש')  return false; // connection between rooms
            else continue;
        if ($key == 'familyroom')
            if ( $flds['family'] != 'יש')  return false; // family room
            else continue;
        
        if ($key == 'privatebeach')
            if ($flds['privatebeach'] != 'יש')  return false; // private beach
            else continue;
        if ($key == 'nearcenter')
            if ($flds['hotelincenter'] != 'כן')  return false; //hotel near center
            else continue;
        if ($key == 'youth')
            if ($flds['youthrecommend'] != 'כן')  return false; // recommended for youth
            else continue;
        if ($key == 'honeymoon')
            if ($flds['honeymoon'] != 'כן')  return false; // private beach
            else continue;
        if ($key == 'habad')
            if ($flds['nearhabad'] != 'כן')  return false; // private beach
            else continue;
        if ($key == 'cheapoption'){
            if ($flds['cheapoption'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'specialgroup'){
            if ($flds['specialgroup'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'accessibility'){
            if ($flds['accessibility'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'preferred'){
            if ($flds['preferred'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'privatepool'){
            if ($flds['privatepool'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'fullaccess'){
            if ($flds['fullaccess'] != 'כן')  return false; // private beach
            else continue;
        }
        if ($key == 'jakuzzi'){
            if (isset($flds['jakuzzi']) && $flds['jakuzzi'] != 'כן')  return false; // private beach
            else continue;
        }
         if ($key == 'contractexists'){
            if (isset($flds['contractexists']) && $flds['contractexists'] != 'כן')  return false; // private beach
            else continue;
        }
         if ($key == 'clubaccess'){
            if (isset($flds['clubaccess']) && $flds['clubaccess'] != 'כן')  return false; // private beach
            else continue;
        }
    }
    return true;
    //error_log('check_filter flds=' . print_r($flds, true));
}

// 2. Shortcode Support for Elementor form with checkboxes for filter
add_shortcode('get-hotel-desc', 'get_hotel_desc');
function get_hotel_desc( $atts ) {
	$a = shortcode_atts( array(
		'data' => 'desc2',
	), $atts );
	global $post_id;
	$pid= get_the_ID();
	if (!$pid){
	   error_log("get_hotel_desc GET_FIELD fail NO PID postid=[$post_id] a[data]= ". print_r($a, true)); 
	   return '';
	}
	$fld = get_field('hotel_description');
	if (!$fld){
	   error_log("get_hotel_desc GET_FIELD fail NO PID postid=[$post_id] a[data]= ". print_r($a, true)); 
	   return '';
	}
	// error_log('get_hotel_desc GET_FIELD OK flds='. print_r($fld, true));
	if ($a['data'] == 'desc') return  $fld['short_description'];
	if ($a['data'] == 'desc2') {
	    
    	$rtl_mark = "\xE2\x80\x8F"; // Unicode for RLM (Right-to-Left Mark)
		$rtl_embedding = "\xE2\x80\xAB"; // Unicode for RLE (Right-to-Left Embedding)

        if (!isset($fld['short_description']) || $fld['short_description'] == null)
	        return $rtl_embedding . 'לא נמצא טקסט'. $rtl_mark;
		return   $rtl_embedding . strip_tags($fld['short_description']) . $rtl_mark;
	}
	if ($a['data'] == 'agentinfo') return  $fld['agentinfo']?? 'לא נמצא טקסט';
	
	if ($a['data'] == 'location'){
	    if (empty($fld['location'])) $alink = 'חסרה הגדרת יעד';
	    else{ 
    	    $link = get_category_link(get_cat_ID($fld['location']));
    	    $alink = '<a href="' . $link . '" class="siam-link" >'. $fld['location'] . '</a>';
	    }
	    $locname = ' &raquo; ' . get_the_title();
        return  $alink . $locname; //$fld['location'];  
	} 
	// yes no values
	//error_log('before a='. print_r($a, true));
	if ($a['data'] == 'specialgroup'){
		//	error_log('Found specialgroup value='. print_r($fld, true));
		if ($fld['specialgroup'] == 'כן') 
			return  add_fw_check('אפשרות להרכבים מיוחדים');
		return '';
	}
	if ($a['data'] == 'recomended') return  ans($fld['recomended']);
	if ($a['data'] == 'starrate') return  $fld['starrate'];
	if ($a['data'] == 'balkony') return  ans($fld['balkony']);
	if ($a['data'] == 'gconnected') return  ans($fld['gconnected']);
	if ($a['data'] == 'family') return ans($fld['family']);
	if ($a['data'] == 'privatebeach') return ans($fld['privatebeach']);
	if ($a['data'] == 'hotelincenter') return ans($fld['hotelincenter']);
	if ($a['data'] == 'youthrecommend'){
	    if ($fld['youthrecommend'] == 'כן') 
            return ans($fld['youthrecommend']);   
        return '';
	}
	if ($a['data'] == 'nearhabad') return ans($fld['nearhabad']);
	if ($a['data'] == 'honeymoon') return ans($fld['honeymoon']);
	if ($a['data'] == 'fullaccess') return ans($fld['fullaccess']);

	if ($a['data'] == 'accessibility'){
	    if ($fld['accessibility'] == 'כן')
	        return add_fw_check('נגיש לנכים'); //ans($fld['accessibility']);  
		return '';
	} 
	if ($a['data'] == 'cheapoption'){
		if ($fld['cheapoption'] == 'כן') 
			return  add_fw_check('אופציה זולה');
		return '';
	}
	if ($a['data'] == 'preferred'){
		if ($fld['preferred'] == 'כן') 
			return  add_fw_check('מלון מועדף');
		return '';
	}
	if ($a['data'] == 'privatepool'){
		if ($fld['privatepool'] == 'כן') 
			return  add_fw_check('בריכה פרטית');
		return '';
	}
	if ($a['data'] == 'jakuzzi'){
		if (isset($fld['jakuzzi']) && $fld['jakuzzi'] == 'כן') 
			return  add_fw_check("ג' קוזי בחדר");
		return '';
	}
	if ($a['data'] == 'contractexists'){
		if ($fld['contractexists'] == 'כן') 
			return  add_fw_check('קיים חוזה');
		return '';
	}
	if ($a['data'] == 'clubaccess'){
		if ($fld['clubaccess'] == 'כן') 
			return  add_fw_check(' Club אקסס');
		return '';
	}
	return 'פרמטר: ' . $a['data'] . ' לא מוגדר עדיין';
}

/* Moved to Elementor Code Management...
add_shortcode('get-filter-code', 'get_filter_code');
function get_filter_code(){
?>
	<script>
		/* load select field values into El. form * /
		jQuery(function($) {
			var href = window.location.href;
			var index = href.indexOf('/', 'https://'.length);
			var homeUrl = href.substring(0, index);


			$('#hotel-filter-button').on('click', function(){
				data = {
					'action': 'hotel_filter',
					'location': $('#form-field-location').val(),

					'families': $('#form-field-features-0').is(':checked'),
					'youth': $('#form-field-features-1').is(':checked'),
					'honeymoon': $('#form-field-features-2').is(':checked'),
					'specialgroup': $('#form-field-features-3').is(':checked'),
					'connected': $('#form-field-features-4').is(':checked'),
					'familyroom': $('#form-field-features-5').is(':checked'),
					'balkony': $('#form-field-features-6').is(':checked'),
					'privatebeach': $('#form-field-features-7').is(':checked'),
					'nearcenter': $('#form-field-features-8').is(':checked'),
					'habad': $('#form-field-features-9').is(':checked'),
					'cheapoption': $('#form-field-features-10').is(':checked')
				}

				if (data.location.indexOf('בחר') !== -1){
					alert('חובה לבחור יעד');
					return;
				}
				jQuery.post(homeUrl + '/wp-admin/admin-ajax.php', 
					data, function(response) {
					// alert('response: ' + response);
					if (response){
					  //  jQuery('#display-result').html(response)
					    jQuery('#display-result').fadeOut('fast', function() {
					        jQuery('#display-result').html(response).fadeIn('fast').focus();
					       
					    });

						$('<div class="search-price-results"><p>'+response+'</p></div>')
							.appendTo('#search-results-content');
					} else {
						alert('החיפוש נכשל: ' + data.location); 
					}
				});
			});

			$('#delete-results').on('click', function(){
				$('.search-price-results').remove();
			});

		});
	</script>
<?php
}*/
