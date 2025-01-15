<h1>Booking Requests</h1>

<?php

global $wpdb;
$table_name = $wpdb->prefix . 'booking_table';

$data = $wpdb->get_results("SELECT booking_date FROM $table_name where status=1");
$booked_dates = array();
foreach($data as $row){
    $booked_dates[] = $row->booking_date;
}
echo "<pre>";
print_r($booked_dates);
echo "</pre>";