<?php
/*  Plugin Name: Booking Form
    Author: Me
    Version: 1.0
    Description: Form to book travel tickets
*/

class KstmForm {
    public function __construct(){
        register_activation_hook(__FILE__,array($this,'activate'));
        register_deactivation_hook(__FILE__,array($this,'deactivate'));

        add_shortcode('book_form',array($this,'book_form_func'));
        add_action('admin_post_book_action', array($this, 'book_action_func'));
        add_action('admin_post_nopriv_book_action', array($this, 'book_action_func'));

        add_action('wp_enqueue_scripts',array($this,'form_script'));

        //admin menu page
        add_action('admin_menu', array($this, 'admin_menu_func'));
    }

    public function activate(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_table';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (id int(3) NOT NULL auto_increment, name varchar(50) NOT NULL,
        booking_from varchar(30) NOT NULL, booking_to varchar(30) NOT NULL, email varchar(50) NOT NULL,
        booking_date varchar(10) NOT NULL, status TINYINT(1) default 0, PRIMARY KEY (id));$charset_collate";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivate(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_table';

        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    public function book_form_func(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_table';

        $data = $wpdb->get_results("SELECT booking_date FROM $table_name where status=1");
        $booked_dates = array();
        foreach($data as $row){
            $booked_dates[] = $row->booking_date;
        }
        ob_start();
        ?>
        <script>
            var unavailableDates =<?php echo json_encode($booked_dates);?>
        </script>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" placeholder="Your Name" required/>

            <label for="booking_from">Booking From</label>
            <select name="booking_from" id="booking_from">
                <option value="">Select City</option>
                <option value="toronto">Toronto</option>
                <option value="nanaimo">Nanaimo</option>
            </select>

            <label for="booking_to">Booking To</label>
            <select name="booking_to" id="booking_to">
                <option value="">Select City</option>
                <option value="toronto">Toronto</option>
                <option value="nanaimo">Nanaimo</option>
            </select>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Your Email" required/>

            <label for="date">Date:</label>
            <input type="text" name="date" id="date" placeholder="Date of travel" required/>

            
            <input type="hidden" name="action" value="book_action" />

            <input type="submit" name="submit" value="Book Now"/>
        </form>
        <?php

        if($_GET['booked'] == 1){
            ?>
            <div class="book-sucess">Thank You for booking. Please check your email for confirmation</div>
            <?php
        }
        return ob_get_clean();
    }

    public function book_action_func(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'booking_table';

        $name = sanitize_text_field($_POST['name']);
        $booking_from = sanitize_text_field($_POST['booking_from']);
        $booking_to = sanitize_text_field($_POST['booking_to']);
        $email = sanitize_email($_POST['email']);
        $booking_date = sanitize_text_field($_POST['date']);


        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'booking_from' => $booking_from,
                'booking_to' => $booking_to,
                'email' => $email,
                'booking_date' => $booking_date,
                'status' => 0
            )
        );
        
        wp_redirect(add_query_arg('booked','1',home_url('/booking')));
    }

    public function form_script(){
        wp_enqueue_script('form-script', plugin_dir_url(__FILE__).'js/location.js',array(),'1.0',false);
        wp_enqueue_script('date-pick','https://code.jquery.com/ui/1.14.1/jquery-ui.js', array('jquery'),'1.14.1',true);
        wp_enqueue_style('date-picker-style','https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css',false,'1.14.1','all');
    }

    //menu page admin
    public function admin_menu_func(){
        // add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = ”, string $icon_url = ”, int|float $position = null ): string
        add_menu_page('Booking Entries', 'Booking Requests','manage_options','booking_requests',array($this,'booking_request_page'));
    }
    public function booking_request_page(){
        require "booking-page.php";
    }
}

$kf = new KstmForm();