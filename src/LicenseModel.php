<?php 
namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class LicenseModel
|--------------------------------------------------------------------------
|
| This class represents the license model for the Appsumo PLG Licensing plugin.
| It provides methods for creating, updating, deleting, and querying license records
| in the database.
|
*/
class LicenseModel
{
    // The name of the database table
    public static $table = 'appsumo_plg_licenses_v2';

    // The attributes that are mass assignable
    protected $fillable = [
        'id',
        'license_key',
        'product_id',
        'variation_id',
        'payment_id',
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

    // The attributes that should be cast to native types
    protected $casts = [
        'extra' => 'array',
    ];

    // The attributes of the model
    protected $attributes = [];

    // The query conditions for the model
    protected static $query = [];

    // Callback to be executed when creating a new record
    protected static $creatingCallback;

    // Callback to be executed when updating an existing record
    protected static $updatingCallback;

    /**
     * Constructor to initialize the model with attributes.
     *
     * @param array $attributes The attributes to initialize the model with.
     */
    public function __construct($attributes = [])
    {
        // Filter the attributes to only include fillable and casted attributes
        $this->attributes = $this->filterFillableAndCast($attributes);
    }

    /**
     * Set the creating callback.
     *
     * @param callable $callback The callback to be executed when creating a new record.
     * @return void
     */
    public static function creating($callback)
    {
        self::$creatingCallback = function ($model) {
            if($model->license_status == 'active') {
                // (new EDD($model))->purchase();
            }
        };
    }

    /**
     * Set the updating callback.
     *
     * @param callable $callback The callback to be executed when updating an existing record.
     * @return void
     */
    public static function updating($callback)
    {
        self::$updatingCallback = function ($model) {
            if($model->license_status == 'active') {
                // (new EDD($model))->purchase();
            }

            if($model->license_status == 'inactive') {
                // (new EDD($model))->deactivate();
            }
        };
    }

    /**
     * Add a where condition to the query.
     *
     * @param mixed ...$query The query conditions.
     * @return static
     */
    public static function where(...$query)
    {
        // If the first argument is an array, set it as the query conditions
        if (\is_array($query[0])) {
            self::$query = $query[0];
            return new static;
        }

        // If there are two arguments, assume the condition is column = value
        if (count($query) == 2) {
            self::$query = [[$query[0], '=', $query[1]]];
            return new static;
        }
        
        // If there are three arguments, assume the condition is column operator value
        if (count($query) == 3) {
            self::$query = [[$query[0], $query[1], $query[2]]];
            return new static;
        }

        // Log an error if the query is invalid
        \error_log('Invalid where query');
    }

    /**
     * Execute the query and return the results.
     *
     * @return array The query results.
     */
    public function get()
    {
        global $wpdb;
        // Build the SQL query
        $query = "SELECT * FROM " . $wpdb->prefix . static::$table . " WHERE ";
        $conditions = [];
        $values = [];
    
        // Add the query conditions
        foreach (self::$query as $data) {
            $conditions[] = "$data[0] $data[1] %s";
            $values[] = $data[2]; // Assuming $data[2] contains the value to be matched
        }
    
        // Prepare and execute the query
        $query .= implode(' AND ', $conditions);
        $prepared_query = $wpdb->prepare($query, $values);
    
        return $wpdb->get_results($prepared_query);
    }

    /**
     * Execute the query and return the first result.
     *
     * @return object|null The first query result, or null if no results.
     */
    public function first()
    {
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Create a new record in the database.
     *
     * @param array $attributes The attributes of the new record.
     * @return static The created model.
     */
    public static function create($attributes)
    {
        $model = new static($attributes);
        // Execute the creating callback if set
        if (isset(self::$creatingCallback)) {
            call_user_func(self::$creatingCallback, $model);
        }
        return $model->save();
    }

    /**
     * Update the model with new attributes.
     *
     * @param array $attributes The new attributes.
     * @return int|false The number of rows affected, or false on failure.
     */
    public function update($attributes)
    {
        global $wpdb;
        // Filter the attributes to only include fillable and casted attributes
        $attributes = $this->filterFillableAndCast($attributes);
        // Execute the updating callback if set
        if (isset(self::$updatingCallback)) {
            call_user_func(self::$updatingCallback, $this);
        }
        
        // Build the SQL query
        $set = '';
        foreach ($attributes as $column => $value) {
            $set .= "$column = '$value', ";
        }
        $set = rtrim($set, ', ');
    
        $query = "UPDATE " . $wpdb->prefix . static::$table . " SET $set WHERE ";
        $conditions = [];
        $values = [];
    
        // Add the query conditions
        foreach (self::$query as $data) {
            $conditions[] = "$data[0] $data[1] %s";
            $values[] = $data[2]; // Assuming $data[2] contains the value to be matched
        }
    
        // Prepare and execute the query
        $query .= implode(' AND ', $conditions);
        $prepared_query = $wpdb->prepare($query, $values);
    
        return $wpdb->query($prepared_query);
    }

    /**
     * Delete the model from the database.
     *
     * @return int|false The number of rows affected, or false on failure.
     */
    public function delete()
    {
        global $wpdb;
        // Build the SQL query
        $query = "DELETE FROM ".$wpdb->prefix.static::$table." WHERE ";
        foreach (self::$query as $column => $value) {
            $query .= "$column = '$value' AND ";
        }
        $query = rtrim($query, ' AND ');
        return $wpdb->query($query);
    }

    /**
     * Save the model to the database.
     *
     * @return static The saved model.
     */
    public function save()
    {
        global $wpdb;
        // If the model does not have an ID, insert a new record
        if (empty($this->attributes['id'])) {
            $columns = implode(', ', array_keys($this->attributes));
            $values = implode("', '", array_values($this->attributes));
            $query = "INSERT INTO ".$wpdb->prefix.static::$table." ($columns) VALUES ('$values')";
            $wpdb->query($query);
            $this->attributes['id'] = $wpdb->insert_id;
        } else {
            // Otherwise, update the existing record
            $this->update($this->attributes);
        }
        return $this;
    }

    /**
     * Filter the attributes to only include fillable and casted attributes.
     *
     * @param array $attributes The attributes to filter.
     * @return array The filtered attributes.
     */
    protected function filterFillableAndCast($attributes)
    {
        // Filter the attributes to only include fillable attributes
        $filtered = array_filter(
            $attributes,
            function ($key) {
                return in_array($key, $this->fillable);
            },
            ARRAY_FILTER_USE_KEY
        );

        // Cast the attributes to their native types
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

    /**
     * Create the database table for the model.
     *
     * @return void
     */
    public static function up()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . static::$table;

        // Check if the table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            // SQL query to create the table
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