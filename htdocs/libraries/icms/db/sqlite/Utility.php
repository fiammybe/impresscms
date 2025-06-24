<?php
/**
 * ImpressCMS SQLite Database Utility
 *
 * This file defines the SQLite utility class for ImpressCMS,
 * implementing the icms_db_IUtility interface and working with the
 * icms_db_sqlite_Connection class.
 *
 * @package     icms_db_sqlite
 * @subpackage  libraries
 * @author      Your Name
 * @copyright   ImpressCMS Project
 * @license     GNU GPL v2 or later
 */

namespace icms\db\sqlite;

if (!interface_exists('icms_db_IUtility')) {
    // Adjust the path as necessary for your project structure
    require_once ICMS_ROOT_PATH . '/libraries/icms/db/icms_db_IUtility.php';
}

/**
 * SQLite utility class for ImpressCMS
 */
class Utility implements \icms_db_IUtility
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Utility constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Split a SQL file into individual queries.
     *
     * @param array $ret Output array of statements
     * @param string $sql The SQL file contents
     */
    public static function splitSqlFile(&$ret, $sql)
    {
        // This is a simple, naive implementation. SQLite doesn't use DELIMITER.
        $statements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed !== '') {
                $ret[] = $trimmed;
            }
        }
    }

    /**
     * Check if a table exists in the SQLite database.
     *
     * @param string $table Table name (without prefix)
     * @return bool
     */
    public function tableExists($table)
    {
        $table = $this->connection->getPrefix() . $table;
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=:table";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':table' => $table]);
        return ($stmt->fetchColumn() !== false);
    }

    /**
     * Get fields of a table.
     *
     * @param string $table Table name (without prefix)
     * @return array List of field names
     */
    public function getTableFields($table)
    {
        $table = $this->connection->getPrefix() . $table;
        $sql = "PRAGMA table_info($table)";
        $stmt = $this->connection->query($sql);
        $fields = [];
        if ($stmt) {
            while ($col = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $fields[] = $col['name'];
            }
        }
        return $fields;
    }

    /**
     * Optimize table. SQLite VACUUMs the whole database, not a single table.
     *
     * @param string $table
     * @return bool
     */
    public function optimizeTable($table)
    {
        // SQLite only supports full database vacuum.
        try {
            $this->connection->exec('VACUUM');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Repair table. Not applicable in SQLite, but we can run integrity_check.
     *
     * @param string $table
     * @return bool
     */
    public function repairTable($table)
    {
        $result = $this->connection->query('PRAGMA integrity_check');
        $check = $result ? $result->fetchColumn() : null;
        return ($check === 'ok');
    }

    /**
    
