<?php

namespace auftraggeber\php_mysqli_prepared_stmt_wrapper;

/**
 * Represents the result of a database operation.
 * This class encapsulates the result of a query.
 *
 * @package auftraggeber\php_mysqli_prepared_stmt_wrapper
 * @author Jonas Langner
 * @version 0.1.1
 * @since 2024-09-26
 */
class Result
{

    private bool $had_error;

    private ?string $error_message;

    private int $affected_rows;

    private array $result_array;

    private $last_insert_id;

    public function __construct(bool $error, ?string $error_message, int $affected_rows, array $result_array, $last_insert_id) {
        $this->had_error     = $error;
        $this->error_message = $error_message;
        $this->affected_rows = $affected_rows;
        $this->result_array = $result_array;
        $this->last_insert_id = $last_insert_id;
    }

    public function hadError(): bool {
        return $this->had_error;
    }

    public function getErrorMessage(): ?string {
        return $this->error_message;
    }

    public function getAffectedRows(): int {
        return $this->affected_rows;
    }

    public function getResultArray(): array {
        return $this->result_array;
    }

    public function getResultArrayWithOnlyColumnIndices(): array {
        $return_array = [];

        foreach ($this->result_array as $row_array) {
            $arr = [];
            foreach ($row_array as $key => $value) {
                if (!is_int($key)) continue;
                $arr[$key] = $value;
            }
            $return_array[] = $arr;
        }

        return $return_array;
    }

    public function getResultArrayWithOnlyColumnNames(): array {
        $return_array = [];

        foreach ($this->result_array as $row_array) {
            $arr = [];
            foreach ($row_array as $key => $value) {
                if (!is_string($key)) continue;
                $arr[$key] = $value;
            }
            $return_array[] = $arr;
        }

        return $return_array;
    }

    public function getFirstRow(): ?array {
        if (!$this->hasResult()) return null;
        return $this->result_array[0];
    }

    public function getLastInsertId() {
        return $this->last_insert_id;
    }

    public function hasResult(): bool {
        if ($this->had_error) return false;
        return !empty($this->result_array);
    }

}