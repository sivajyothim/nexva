<?php

class MyDiff_Comparison {

    public $databases = array();

    public function addDatabase(MyDiff_Database $database) {
        if(count($this->databases) >= 2)
            throw new MyDiff_Exception('Can only accept two databases');

        $this->databases[] = $database;
    }

    /**
     * Perform a schema comparison on provided databases
     */
    public function schema() {
        $this->isReady();

        // Grab list of tables for each database
        $tables = array($this->databases[0]->getTables(), $this->databases[1]->getTables());

        // Look for differences in number of tables
        $newTables = array_diff_key($tables[1], $tables[0]);
        $missingTables = array_diff_key($tables[0], $tables[1]);

        // Assign diffs
        foreach($newTables AS $table)
            $table->addDiff(new MyDiff_Diff_Table_New);
        foreach($missingTables AS $table)
            $table->addDiff(new MyDiff_Diff_Table_Missing);

        unset($newTables, $missingTables);

        // Grab tables that are in both
        $matchingTables = array_intersect_key($tables[0], $tables[1]);

        foreach($matchingTables AS $tableName => $table) {
            // Compare schema
            $tableColumns = array(
                    0 => $table->getColumns(),
                    1 => $tables[1][$tableName]->getColumns(),
            );

            // Look for differences in number of columns
            $newColumns = array_diff_key($tableColumns[0], $tableColumns[1]);
            $missingColumns = array_diff_key($tableColumns[1], $tableColumns[0]);

            // Assign diffs
            foreach($newColumns AS $column)
                $column->addDiff(new MyDiff_Diff_Table_Column_New);
            foreach($missingColumns AS $column)
                $column->addDiff(new MyDiff_Diff_Table_Column_Missing);

            unset($newColumns, $missingColumns);

            // Remove metadata columns we don't want to compare
            foreach($tableColumns AS &$columns) {
                foreach($columns AS &$column)
                    unset($column->metadata['COLUMN_POSITION']);
            }

            // List matching columns
            $matchingColumns = array_intersect_key($tableColumns[0], $tableColumns[1]);

            // Compare each columns metadata
            foreach($matchingColumns AS $columnName => $column) {
                $differences = array_diff($column->metadata, $tableColumns[1][$columnName]->metadata);
                foreach($differences AS $metaKey => $metaValue) {
                    $column->addDiff(new MyDiff_Diff_Table_Column_Property($metaKey, $column->metadata[$metaKey]));
                    $tableColumns[1][$columnName]->addDiff(new MyDiff_Diff_Table_Column_Property($metaKey, $tableColumns[1][$columnName]->metadata[$metaKey]));
                }
            }

            unset($matchingColumns, $tableColumns, $columnNames);
        }
    }

    /**
     * Perform a schema comparison on provided databases
     */
    public function data() {
        $this->isReady();

        // Grab list of tables for each database
        $tables = array($this->databases[0]->getTables(), $this->databases[1]->getTables());

	// Grab tables that are in both
	$matchingTables = array_intersect(array_keys($tables[0]), array_keys($tables[1]));

        // Try to do quick checksum comparison
        foreach($matchingTables AS $i => $tableName) {
            if($tables[0][$tableName]->getEngine() == 'MyISAM' && $tables[1][$tableName]->getEngine() == 'MyISAM') {
                $sum1 = $tables[0][$tableName]->getChecksum();
                $sum2 = $tables[1][$tableName]->getChecksum();

                if($sum1 !== null && $sum2 !== null && $sum1 === $sum2) {
                    unset($matchingTables[$i]);
                    $tables[0][$tableName]->blankRows();
                    $tables[1][$tableName]->blankRows();
                }
            }
        }

        foreach($matchingTables AS $tableName) {
            $rows = array($tables[0][$tableName]->getRows(), $tables[1][$tableName]->getRows());

            // Look for new/missing rows
            $newRows = array_diff_key($rows[1], $rows[0]);
            $missingRows = array_diff_key($rows[0], $rows[1]);

            // Assign diffs
            foreach($newRows AS $row)
                $row->addDiff(new MyDiff_Diff_Table_Row_New);
            foreach($missingRows AS $row)
                $row->addDiff(new MyDiff_Diff_Table_Row_Missing);

            unset($newRows, $missingRows);

            // Find rows that exist in both
            $compareRows = array_intersect_key($rows[0], $rows[1]);

            // Compare rows for differences in data
            foreach($compareRows AS $rowName => $row) {
                // Only compare if both are using primary keys
                if($row->hasPrimary()) {
                    $differences = array_diff($row->data, $rows[1][$rowName]->data);
                    foreach($differences AS $key => $value) {
                        $row->addDiff(new MyDiff_Diff_Table_Row_Value($key, $row->data[$key], $rows[1][$rowName]->data[$key]));
                        $rows[1][$rowName]->addDiff(new MyDiff_Diff_Table_Row_Value($key, $row->data[$key], $rows[1][$rowName]->data[$key]));
                    }
                    unset($differences);
                }
            }

            unset($compareRows, $rows);

            // Prune rows that havent got diffs
            $tables[0][$tableName]->pruneRows();
            $tables[1][$tableName]->pruneRows();
        }

        unset($tables, $tableNames, $matchingTables);
    }

    public function isReady() {
        if(count($this->databases) !== 2)
            throw new MyDiff_Exception('Must provide two databases');

        set_time_limit(600);
        ini_set('memory_limit', '500M');

        return true;
    }

}
