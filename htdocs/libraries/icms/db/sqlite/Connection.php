<?php
/**
 * ImpressCMS SQLite Database Connection
 *
 * This file defines the SQLite connection class for ImpressCMS,
 * extending PDO and implementing the icms_db_IConnection interface.
 *
 * @package     icms_db_sqlite
 * @subpackage  libraries
 * @author      Your Name
 * @copyright   ImpressCMS Project
 * @license     GNU GPL v2 or later
 */

namespace icms\db\sqlite;

use PDO;
use PDOException;

if (!interface_exists('icms_db_IConnection')) {
    // Adjust the path as necessary for your project structure
    require_once ICMS_ROOT_PATH . '/libraries/icms/db/icms_db_IConnection.php';
}

/**
 * SQLite connection class for ImpressCMS
 */
class Connection extends PDO implements \icms_db_IConnection
{
    /**
     * @var string The table prefix for this connection.
     */
    protected $prefix = '';

    /**
     * @var \icms_core_Logger|null Optional logger.
     */
    protected $logger = null;

    /**
     * Connection constructor.
     *
     * @param string $database_file Full path to SQLite database file.
     * @param string $prefix        Table prefix.
     * @param array  $options       Optional PDO options.
     *
     * @throws PDOException If the connection fails.
     */
    public function __construct($database_file, $prefix = '', $options = [])
    {
        $dsn = "sqlite:$database_file";
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($defaultOptions, $options);

        parent::__construct($dsn, null, null, $options);

        $this->prefix = $prefix;
    }

    /**
     * Set the table prefix to use for queries.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the table prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the logger instance.
     *
     * @param \icms_core_Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger instance.
     *
     * @return \icms_core_Logger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Connect to the database (PDO does this on __construct, so just return true).
     *
     * @param bool $select_db Ignored for SQLite.
     * @return bool
     */
    public function connect($select_db = true)
    {
        // Connection is established in constructor, just return true.
        return true;
    }

    /**
     * Run a query and return the PDOStatement.
     *
     * @param string $sql
     * @return \PDOStatement|false
     */
    public function query($sql)
    {
        if ($this->logger) {
            $this->logger->addQuery($sql);
        }
        return parent::query($sql);
    }

    /**
     * Prepare and execute a statement with parameters.
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement|false
     */
    public function execute($sql, array $params = [])
    {
        if ($this->logger) {
            $this->logger->addQuery($sql);
        }
        $stmt = $this->prepare($sql);
        if ($stmt && $stmt->execute($params)) {
            return $stmt;
        }
        return false;
    }

    /**
     * Escape a string for use in an SQL statement.
     *
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        return $this->quote($string);
    }

    /**
     * Get the last inserted ID.
     *
     * @param string|null $name
     * @return string
     */
    public function getInsertId($name = null)
    {
        return $this->lastInsertId($name);
    }
}
