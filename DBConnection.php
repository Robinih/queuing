<?php
// Check if directory 'db' exists, if not, create it
if (!is_dir(__DIR__ . './db'))
    mkdir(__DIR__ . './db');

// Define constant 'db_file' with path to SQLite database file
if (!defined('db_file')) define('db_file', __DIR__ . './db/cashier_queuing_db.db');

// Define constant 'tZone' with default timezone set to Asia/Manila
if (!defined('tZone')) define('tZone', "Asia/Manila");

// Define constant 'dZone' with timezone from PHP configuration
if (!defined('dZone')) define('dZone', ini_get('date.timezone'));

// Custom function 'my_udf_md5' for MD5 hashing
function my_udf_md5($string) {
    return md5($string);
}
/**********************************************************************************************************/
/**********************************************************************************************************/

// Class DBConnection extends SQLite3
class DBConnection extends SQLite3 {
    protected $db;

    function __construct() {
        // Open SQLite database connection
        $this->open(db_file);

        // Register custom MD5 function 'my_udf_md5' with SQLite
        $this->createFunction('md5', 'my_udf_md5');

        // Enable foreign key constraints
        $this->exec("PRAGMA foreign_keys = ON;");

        // Create 'user_list' table if not exists
        $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Create 'cashier_list' table if not exists
        $this->exec("CREATE TABLE IF NOT EXISTS `cashier_list` (
            `cashier_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL,
            `log_status` INTEGER NOT NULL DEFAULT 0,
            `status` INTEGER NOT NULL DEFAULT 1
        )");

        // Create 'queue_list' table if not exists
        $this->exec("CREATE TABLE IF NOT EXISTS `queue_list` (
            `queue_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `queue` TEXT NOT NULL,
            `customer_name` Text NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert default admin user if not exists
        $this->exec("INSERT or IGNORE INTO `user_list` VALUES (1, 'Administrator', 'admin', md5('admin123'), 1, CURRENT_TIMESTAMP)");
    }

    function __destruct() {
        // Close database connection when object is destroyed
        $this->close();
    }
}

// Create an instance of DBConnection class
$conn = new DBConnection();
?>
