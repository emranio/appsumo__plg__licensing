<?php 
namespace Appsumo_PLG_Licensing;

use Dbout\WpOrm\Orm\AbstractModel;

class LicenseModel extends AbstractModel
{
    protected $table = 'appsumo_plg_licenses_v2';
    protected $fillable = [
        'license_key',
        'product_id',
        'user_id',
        'tier',
        'prev_license_key',
        'plan_id',
        'license_status',
        'created_at',
        'updated_at',
        'extra',
        'event_timestamp',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    // in boot, before create and before save, check if status field is changed, do some stuff
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if($model->license_status == 'active') {
                (new EDD($model))->purchase();
            }
        });
        static::updating(function ($model) {
            if($model->license_status == 'active') {
                (new EDD($model))->purchase();
            }

            if($model->license_status == 'inactive') {
                (new EDD($model))->deactivate();
            }
        });
    }

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
                product_id mediumint(9) NULL,
                variation_id mediumint(9) NULL,
                user_id mediumint(9) NULL,
                tier varchar(50) NOT NULL,
                prev_license_key varchar(70) NULL,
                plan_id varchar(100) NULL,
                license_status varchar(20) NOT NULL,
                extra json DEFAULT NULL,
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                event_timestamp datetime DEFAULT '0000-00-00 00:00:00' NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}