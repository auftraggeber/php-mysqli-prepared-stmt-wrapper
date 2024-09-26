<?php

namespace auftraggeber\php_mysqli_prepared_stmt_wrapper;

class Result
{

    private bool $error;

    private int $affected_rows;

    private array $result_array;

    private $last_insert_id;

    public function __construct(bool $error, int $affected_rows, array $result_array, $last_insert_id) {
        $this->error = $error;
        $this->affected_rows = $affected_rows;
        $this->result_array = $result_array;
        $this->last_insert_id = $last_insert_id;
    }

    public function hadError(): bool {
        return $this->error;
    }

    public function getAffectedRows(): int {
        return $this->affected_rows;
    }

    public function getResultArray(): array {
        return $this->result_array;
    }

    public function getResultArrayWithOnlyColumnIndices(): array {
        $return_array = [];

        foreach ($this->result_array as $key => $row) {
            if (!is_int($key)) continue;
            $return_array[] = $row;
        }

        return $return_array;
    }

    public function getResultArrayWithOnlyColumnNames(): array {
        $return_array = [];

        foreach ($this->result_array as $key => $row) {
            if (is_int($key)) continue;
            $return_array[] = $row;
        }

        return $return_array;
    }

    public function getFirstRow(): ?array {
        if (empty($this->result_array)) return null;
        return $this->result_array[0];
    }

    public function getLastInsertId() {
        return $this->last_insert_id;
    }

}