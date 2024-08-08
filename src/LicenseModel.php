<?php 
namespace Appsumo_PLG_Licensing;

use Dbout\WpOrm\Orm\AbstractModel;

class LicenseModel extends AbstractModel
{
    protected $table = 'appsumo_plg_licenses_v2';

    public static function up()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'appsumo_plg_licenses_v2';

        // Check if the table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                license_key varchar(70) NOT NULL,
                product_id mediumint(9) NOT NULL,
                user_id mediumint(9) NOT NULL,
                tier varchar(50) NOT NULL,
                prev_license_key varchar(70) NOT NULL,
                plan_id varchar(100) NOT NULL,
                license_status varchar(20) NOT NULL,
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                event_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}