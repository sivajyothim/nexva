<?php

class MyDiff_Table extends MyDiff_Item {

    public $name;
    protected $_database;
    protected $_table;
    protected $_columns;
    protected $_primaryColumns;
    protected $_rows;
    protected $_engine;

    public function __construct($db, $tableName) {
        $this->name = $tableName;
        $this->_database = $db;
        $this->_table = new MyDiff_Db_Table(array('name' => $tableName, 'db' => $db->getDb()));
    }

    /**
     * Get list of columns and their metadata
     */
    public function getColumns() {
        if($this->_columns === null) {
            $columns = array();
            $metadata = $this->_table->info(Zend_Db_Table_Abstract::METADATA);
            foreach($metadata AS $columnName => $columnMetaData) {
                $columns[$columnName] = new MyDiff_Table_Column($columnMetaData);
            }

            $this->_columns = $columns;
            unset($columns, $metadata);
        }

        return $this->_columns;
    }

    /**
     * Get array of primary keys (column names)
     */
    public function getPrimaryColumns() {
        if($this->_primaryColumns === null && !$this->_table->noPrimaryKey)
            $this->_primaryColumns = $this->_table->info(Zend_Db_Table_Abstract::PRIMARY);

        return $this->_primaryColumns;
    }

    /**
     * Get the table's mysql storage engine type
     */
    public function getEngine() {
        if($this->_engine === null) {
            $select = $this->_table->select();
            $select->setIntegrityCheck(false);
            $select->from('information_schema.TABLES', 'ENGINE')
                    ->where('TABLE_SCHEMA = ?', $this->_database->name)
                    ->where('TABLE_NAME = ?', $this->name);


            $row = $this->_table->fetchRow($select);
            $this->_engine = isset($row['ENGINE'])? $row['ENGINE'] : null;
        }

        return $this->_engine;
    }

    public function getChecksum() {
        if($this->getEngine() == 'MyISAM') {
            $stmt = $this->_database->getDb()->query('CHECKSUM TABLE `' . $this->name . '`');
            $result = $stmt->fetch();
            return isset($result['Checksum'])? $result['Checksum'] : null;
        }
        else {
            return null;
        }
    }

    /**
     * Get the table rows (i.e. data)
     */
    public function getRows() {
        if($this->_rows === null) {
            $rows = array();
            $dbRows = $this->_table->fetchAll()->toArray();
            foreach($dbRows AS $dbRow) {
                $row = new MyDiff_Table_Row($this, $dbRow);

                // check uid is unique
                // useful for data comparisons where we might have duplicates not using primary keys
                $i = 0;
                $keys = array_keys($rows);
                while(in_array($row->uid, $keys)) {
                    if($i > 1000)
                        throw new Exception('Found over 1000 duplicate rows');
                    $row->uid = md5($row->uid . $i);
                    $i++;
                }
                unset($keys);

                $rows[$row->uid] = $row;
            }

            $this->_rows = $rows;
            unset($rows, $dbRows);
        }

        return $this->_rows;
    }

    /**
     * Remove data from rows that don't have any differences
     */
    public function pruneRows() {
        foreach($this->getRows() AS $rowId => $row) {
            if(!$row->hasDiffs())
                unset($this->_rows[$rowId]);
        }
    }

    /**
     * Set rows to blank, because diff now required
     */
    public function blankRows(){
        $this->_rows = array();
    }

    /**
     * Get the db table instance
     */
    public function getTable() {
        return $this->_table;
    }

}
