<?php

/**
 * Plugin Name: Siam DB
 * Description: Enable build Hotel, Attractions, Tranfers and other DB for siam project.
 * Version: 0.1
 * Author: Mulli Bahr
 */
if ( ! defined( 'ABSPATH' ) ) {
	die('no direct access'); // Exit if accessed directly.
}


include_once('inc/lib.php');
include_once('inc/acf-cache.php');
include_once('inc/acf-location-choices.php');
include_once('inc/siam-attraction-functions.php');
include_once('inc/siam-attraction-filter.php');
include_once('inc/siam-hotel-filter.php');
include_once('inc/siam-hotel-location-list.php');