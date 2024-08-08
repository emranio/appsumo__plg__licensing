<?php 
namespace Appsumo_PLG_Licensing;

class LicenseModel
{
    public static $table = 'appsumo_plg_licenses_v2';
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

    protected $attributes = [];
    protected static $query = [];
    protected static $creatingCallback;
    protected static $updatingCallback;

    public function __construct($attributes = [])
    {
        $this->attributes = $this->filterFillableAndCast($attributes);
    }

    public static function creating($callback)
    {
        self::$creatingCallback = function ($model) {
            if($model->license_status == 'active') {
                (new EDD($model))->purchase();
            }
        };
    }

    public static function updating($callback)
    {
        self::$updatingCallback = function ($model) {
            if($model->license_status == 'active') {
                (new EDD($model))->purchase();
            }

            if($model->license_status == 'inactive') {
                (new EDD($model))->deactivate();
            }
        };
    }

    public static function where(...$query)
    {
        
        if (\is_array($query[0])) {
            self::$query = $query[0];
            return new static;
        }

        if (count($query) == 2) {
            self::$query = [[$query[0], '=', $query[1]]];
            return new static;
        }
        
        if (count($query) == 3) {
            self::$query = [[$query[0], $query[1], $query[2]]];
            return new static;
        }

        \error_log('Invalid where query');
    }

    public function get()
    {
        global $wpdb;
        $query = "SELECT * FROM " . $wpdb->prefix . static::$table . " WHERE ";
        $conditions = [];
        $values = [];
    
        foreach (self::$query as $data) {
            $conditions[] = "$data[0] $data[1] %s";
            $values[] = $data[2]; // Assuming $data[2] contains the value to be matched
        }
    
        $query .= implode(' AND ', $conditions);
        $prepared_query = $wpdb->prepare($query, $values);
    
        return $wpdb->get_results($prepared_query);
    }

    public function first()
    {
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public static function create($attributes)
    {
        $model = new static($attributes);
        if (isset(self::$creatingCallback)) {
            call_user_func(self::$creatingCallback, $model);
        }
        return $model->save();
    }

    public function update($attributes)
    {
        global $wpdb;
        $attributes = $this->filterFillableAndCast($attributes);
        if (isset(self::$updatingCallback)) {
            call_user_func(self::$updatingCallback, $this);
        }
        $set = '';
        foreach ($attributes as $column => $value) {
            $set .= "$column = '$value', ";
        }
        $set = rtrim($set, ', ');
        $query = "UPDATE ".$wpdb->prefix.static::$table." SET $set WHERE ";
        foreach (self::$query as $column => $value) {
            $query .= "$column = '$value' AND ";
        }
        $query = rtrim($query, ' AND ');
        return $wpdb->query($query);
    }

    public function delete()
    {
        global $wpdb;
        $query = "DELETE FROM ".$wpdb->prefix.static::$table." WHERE ";
        foreach (self::$query as $column => $value) {
            $query .= "$column = '$value' AND ";
        }
        $query = rtrim($query, ' AND ');
        return $wpdb->query($query);
    }

    public function save()
    {
        global $wpdb;
        if (empty($this->attributes['id'])) {
            $columns = implode(', ', array_keys($this->attributes));
            $values = implode("', '", array_values($this->attributes));
            $query = "INSERT INTO ".$wpdb->prefix.static::$table." ($columns) VALUES ('$values')";
            $wpdb->query($query);
            $this->attributes['id'] = $wpdb->insert_id;
        } else {
            $this->update($this->attributes);
        }
        return $this;
    }

    protected function filterFillableAndCast($attributes)
    {
        $filtered = array_filter(
            $attributes,
            function ($key) {
                return in_array($key, $this->fillable);
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($filtered as $key => $value) {
            if (isset($this->casts[$key])) {
                switch ($this->casts[$key]) {
                    case 'array':
                        $filtered[$key] = json_encode($value);
                        break;
                    // Add more cases as needed for other types
                }
            }
        }

        return $filtered;
    }

    public static function up()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . static::$table;

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