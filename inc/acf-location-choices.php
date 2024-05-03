<?php

// Note - currently we use hotel locations for attaractions as well
// load ACF select values for location field
function acf_load_location_field_choices( $field ) {
    // debud   error_log('acf_load_location_field_choices field'. print_r($field, true));

   // Reset choices
   $field['choices'] = [];
   
   // Get subcatgories (child) of hotel info category
   $cat_parent_id = 2; // get_cat_id('מלונות-מידע');
   // error_log('acf_load_color_field_choices parent cat:'. $cat_parent_id);

   $args = array('child_of' => $cat_parent_id, 'hide_empty' => false);
   
   $categories = get_categories($args);
   // debud error_log('acf_load_color_field_choices categories:'. print_r($categories, true));
   foreach($categories as $category) {
       if (intval($category->parent) != intval($cat_parent_id)) 
           continue;
       $choice = $category->name;
       //error_log('acf_load_color_field_choices FOUND CAT :'. $choice);
       $field['choices'][ $choice ] = $choice;
   }
   //error_log('acf_load_color_field_choices field choices='. print_r($field, true));
   // Return the field
   return $field;
}
add_filter( "acf/load_field/key=field_661fb5b19c43c", 'acf_load_location_field_choices', 10, 1 ); // key of location field in hotel-info

/*
function acf_load_hotel_updates_field_choices( $field ) {
   $cat = get_field('location');
   $args = array('child_of' => $cat, 'hide_empty' => false);
   $categories = get_categories($args);

   return 'inDEV';
}

//add_filter('acf/load_field/name=hotel_updates', 'acf_load_hotel_updates_field_choices');
*/