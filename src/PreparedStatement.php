<?php

namespace auftraggeber\php_mysqli_prepared_stmt_wrapper;

use mysqli;
use mysqli_result;
use mysqli_stmt;

class PreparedStatement
{

    private static ?mysqli $connection = null;

    public static function setConnection(mysqli $connection): void {
        self::$connection = $connection;
    }

    private static function getConnection(): ?mysqli {
        return self::$connection;
    }

    private mysqli_stmt $stmt;

    public function __construct(string $query) {
        $connection = self::getConnection();
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

        $result = new Result($this->stmt->error, $this->stmt->affected_rows, $result_array, $this->stmt->insert_id);
        $this->stmt->reset();
        return $result;
    }

}