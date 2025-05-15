<?php

namespace auftraggeber\php_mysqli_prepared_stmt_wrapper;

use mysqli;
use mysqli_result;
use mysqli_stmt;

/**
 * This class is a wrapper for mysqli prepared statements.
 * It allows you to prepare, bind parameters, and execute statements easily.
 * It also handles a default connection for you.
 * 
 * @package auftraggeber\php_mysqli_prepared_stmt_wrapper
 * @author Jonas Langner
 * @version 0.1.1
 * @since 2024-09-26
 */
class PreparedStatement
{

    private static ?mysqli $connection = null;

    /**
     * Set the default connection for all prepared statements which are created without a connection.
     * @param mysqli $connection The connection to set as default.
     * @return void
     * @deprecated Use {@link setDefaultConnection()} instead.
     */
    public static function setConnection(mysqli $connection): void {
        self::$connection = $connection;
    }

    /**
     * Set the default connection for all prepared statements which are created without a connection.
     * @param mysqli $connection The connection to set as default.
     * @return void
     */
    public static function setDefaultConnection(mysqli $connection): void {
        self::$connection = $connection;
    }

    private static function getConnection(): ?mysqli {
        return self::$connection;
    }

    private mysqli_stmt $stmt;

    /**
     * PreparedStatement constructor.
     * @param string $query The SQL query to prepare.
     * @param mysqli|null $with_connection The connection to use. If null, the default connection will be used.
     * @throws \Exception If the connection is null and no default connection is set, or if the statement could not be prepared.
     */
    public function __construct(string $query, ?mysqli $with_connection = null) {
        $connection ??= self::getConnection();
        if ($connection === null) {
            throw new \Exception("No connection set");
        }
        $stmt = $connection->prepare($query);
        if (!($stmt instanceof mysqli_stmt)) {
            throw new \Exception("Failed to prepare statement: " . $connection->error);
        }

        $this->stmt = $stmt;
    }

    public function __destruct()
    {
        $this->stmt->close();
    }

    private function bindParams(... $params): void {
        if (empty($params)) return;

        $types = "";
        $values = [];
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= "i";
            } elseif (is_float($param)) {
                $types .= "d";
            } elseif (is_string($param)) {
                $types .= "s";
            } else {
                $types .= "b";
            }
            $values[] = $param;
        }
        $this->stmt->bind_param($types, ...$values);
    }

    /**
     * Execute the prepared statement with the given parameters.
     * @param ...$params mixed The parameters to bind to the statement.
     * @return Result The result of the statement execution.
     */
    public function execute(... $params): Result {
        $this->bindParams(...$params);
        $this->stmt->execute();

        $result = $this->stmt->get_result();

        $result_array = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_array()) {
                $array = [];
                foreach ($row as $key => $value) {
                    $array[$key] = $value;
                }
                $result_array[] = $array;
            }
        }

        $error = $this->stmt->error;
        $had_error = !empty($error);
        $result = new Result(
            $had_error,
            $had_error
                ? $error
                : null,
            $this->stmt->affected_rows,
            $result_array,
            $this->stmt->insert_id
        );
        $this->stmt->reset();
        return $result;
    }

}