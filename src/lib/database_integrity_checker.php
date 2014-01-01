<?php
class DatabaseChecker
{
	var $structure;
	var $tables;
	var $db;
	
	function __construct()
	{
		global $wpdb;
		$database_structure = $GLOBALS['data_structure'];
		$this->db = &$wpdb;
		$this->structure = $database_structure;
	}
	
	function perform_check()
	{
            
            foreach ($this->structure as $table_name=>$structure)
            {
                if (!$this->table_exists($table_name))
                    $this->create_table($table_name);
                else
                {   
                    $this->modify_table_to_structure($table_name);
                }
            }

	}
	
	function add_prefix(&$table_name)
	{
		$table_name = $this->db->prefix.$table_name;
	}
	
	function create_table($table_name)
	{
		
		
		$table = $this->structure[$table_name];
                
		$this->add_prefix($table_name);		
		if (!$table)
		{
			throw new UndefinedTableException();
		}

                if (!$this->isValidPrimaryKey($table['primary_key'], $table))
                {
                    $table['primary_key'] = array();
                }


                if (!$this->isValidAutoIncrement($table['auto_increment'],$table))
                {
                    $table['auto_increment'] = "";
                }
		
		$tableColumnDefinitions = array();

                $aSingleColumnPrimaryKeyExists = (isset($table['primary_key']) && is_string($table['primary_key']))?true:false;
                
		foreach ($table['columns'] as $columnname=>$columndefinition)
		{
			$columnDefinition = "`$columnname` $columndefinition";
                        if ($aSingleColumnPrimaryKeyExists)
                        {
                            if ($columnname == $table['auto_increment'])
                                $columnDefinition = "$columnDefinition AUTO_INCREMENT";
                        }
			array_push($tableColumnDefinitions,$columnDefinition);
		}
		
		$tableColumnDefinitionsOfQuery = implode(" ," ,$tableColumnDefinitions);
		
		if (count($table['primary_key']))
		{
                    $primary_key = $table['primary_key'];
                    if (is_array($primary_key))
                    {
                            $primary_key_identifier = implode("`,`",$primary_key);
                            $primary_key_identifier = "`$primary_key_identifier`";
                    }
                    else if (is_string($primary_key))
                    {
                            $primary_key_identifier = " `$primary_key` ";
                    }

                    $primary_key_clause = ", PRIMARY KEY ($primary_key_identifier)";
		}
		
		if (count($table['unique']) > 0 )
		{
                    $uniques = $table['unique'];
                    $uniqueClauses = array();
                    foreach ($uniques as $unique_name=>$unique_columns)
                    {
                        
                        if (is_array($unique_columns))
                            $uniqueColumns = implode("`,`",$unique_columns);
                        else if (is_string($unique_columns))
                            $uniqueColumns = $unique_columns;
                        $uniqueClause = "UNIQUE KEY `$unique_name` (`$uniqueColumns`)";
                        array_push($uniqueClauses,$uniqueClause);
                    }
                    $unique_definitions = implode(",", $uniqueClauses);
                    $unique_definitions = ", $unique_definitions";
		}
		
		$completeCreateTableQuery = "CREATE TABLE $table_name ( $tableColumnDefinitionsOfQuery $primary_key_clause $unique_definitions);";
		$this->db->query($completeCreateTableQuery);
	}

        function isValidAutoIncrement($auto_increment,$table)
        {
            //if the auto increment is one of the columns in a primary key then it is valid.
            if (is_string($table['primary_key']))
            {
                if ($auto_increment == $table['primary_key'])
                    return true;
            }
            else if (is_array($table['primary_key']))
            {
                if (in_array($auto_increment,$table['primary_key']))
                     return true;
            }
            return false;
        }

        function isValidPrimaryKey($primary_key,$table)
        {
            //if primary key only has columns from the defined table structure
            //the primary key is valid

            if (empty($primary_key))
                return false;
            
            if (is_string($primary_key))
                $pkey = array($primary_key);
            else
                $pkey = $primary_key;

            $table_columns = array_keys($table['columns']);
            $intersectionOfPrimaryKeyAndTableColumns = array_intersect($table_columns,$pkey);

            //if there are no common columns
            if (count($intersectionOfPrimaryKeyAndTableColumns) == 0)
            {
                //If teh primary key specification has columns that are not in the columns sepcification then throw an exception.
                throw new Exception("Invalid primary key specification for table. Undefined columns in primary key specification.");                
            }
            
            if (count($intersectionOfPrimaryKeyAndTableColumns) != count($pkey))
            {
                throw new Exception("Invalid primary key specification for table. Undefined columns in primary key specification.");                
            }
            
            return true;

        }

        /*
         * modify_table_to_structure
         *
         * This function takes the name of a column in the $database_strucutre
         * array to modify. It will modify the existing table to conform to the
         * spec in the $database_structure array.
         *
         *
         * Preconditions:
         * 1. The specified table $table_name exists.
         * 2. The table definition for the same was specified in the
         * $database_structure global array
         *
         * Postconditions:
         * 1. The table has been modified as per the specification for this
         * table, the unique columns have been added
         * 2. Only the unique keys as specified in the database table spec
         * in the $database_structure array exist. No other unique keys exist.
         * 3. The primary key is as specified in $database_structure
         * specification, any other primary key is dropped.
         */
	
	function modify_table_to_structure($table_name)
	{
            $table = $this->structure[$table_name];

            if (!$table)
                throw new UndefinedTableException();

            if (!$this->isValidPrimaryKey($table['primary_key'], $table))
            {
                $table['primary_key'] = array();
            }


            if (!$this->isValidAutoIncrement($table['auto_increment'],$table))
            {
                $table['auto_increment'] = "";
            }

            //list of expected table columns
            $columns = array_keys($table['columns']);
            //get list of columns in the table
            $this->add_prefix($table_name);

            $getTableColumnsQuery = "SHOW COLUMNS FROM $table_name;";
            $columnsExisting = $this->db->get_col($getTableColumnsQuery,0);
            //get the full definitions
	    $extraInTableDefinitionExisting = $this->db->get_col($getTableColumnsQuery,5);
	    $whetherHasAutoIncrementColumn = in_array("auto_increment",$extraInTableDefinitionExisting);

            $non_existing_columns = array_diff($columns,$columnsExisting);

            $primary_key = (array) $table['primary_key'];
            
            if (count($non_existing_columns) > 0 )
            {
                $primary_key_columns_to_be_added = array_intersect($non_existing_columns,$primary_key);

                $non_existing_columns = array_diff($non_existing_columns,$primary_key_columns_to_be_added);

                foreach ($non_existing_columns as $column)
                {
                    $columnDefinition = $table['columns'][$column];

                    $addColumnQuery = "ALTER TABLE $table_name ADD `$column` $columnDefinition ";            
                    $columnAddQuery = "$addColumnQuery ;";
                    $this->db->query($columnAddQuery);
                }
            }
	    
            $existing_columns_from_required_structure = array_intersect($columns, $columnsExisting);

            if (count($existing_columns_from_required_structure) > 0 )
            {
                //remove teh auto increment element

                $existing_columns_of_primary_key = array_intersect($existing_columns_from_required_structure,$primary_key);
                $existing_columns_from_required_structure = array_diff($existing_columns_from_required_structure,$primary_key);

                foreach ($existing_columns_from_required_structure as $column)
                {
                    $column_def = $table['columns'][$column];
                    $alterColumnQuery = "ALTER TABLE `$table_name` CHANGE `$column` `$column` $column_def";

                    $this->db->query($alterColumnQuery);
                }
            }
            
            //adding primary key
            if (isset($table['primary_key']) && false == $whetherHasAutoIncrementColumn)
            {
	    
                $primary_key = $table['primary_key'];
                
                if (is_string($primary_key))
                {
                        $primary_key_identifier = $primary_key;
                }
                else if (is_array($primary_key))
                {
                        $primary_key_identifier = implode("`,`",$primary_key);
                }


                $array_of_column_defs = array();
                //get the table definitions for non existing columns

                if (count($existing_columns_of_primary_key))
                foreach ($existing_columns_of_primary_key as $existing_column)
                {
                    $modification_clause = "CHANGE `$existing_column` `$existing_column` ".$table['columns'][$existing_column];

                    if ($existing_column == $table['auto_increment'])
                    {
                        $modification_clause .= " AUTO_INCREMENT ";
                    }
                    $array_of_column_defs[] = $modification_clause;
                }

                if (isset($primary_key_columns_to_be_added) && 0 != count($primary_key_columns_to_be_added))
                foreach ($primary_key_columns_to_be_added as $new_column)
                {
                    $column_def_clause = "ADD `$new_column` ".$table['columns'][$new_column];

                    if ($new_column == $table['auto_increment'])
                    {
                        $column_def_clause .= " AUTO_INCREMENT";
                    }
                    $array_of_column_defs[] = $column_def_clause;
                }

                
                $primarykeyDropQuery = "ALTER TABLE `$table_name` DROP PRIMARY KEY;";
                $this->db->query($primarykeyDropQuery);
                $primaryKeyColumnDefinitions = implode(",",$array_of_column_defs);

                if (!empty($primaryKeyColumnDefinitions))
                    $primaryKeyColumnDefinitions = "$primaryKeyColumnDefinitions , ";
                $primarykeyAdditionQuery  = "ALTER TABLE `$table_name` $primaryKeyColumnDefinitions ADD PRIMARY KEY (`$primary_key_identifier`);";
                $this->db->query($primarykeyAdditionQuery);
            }

            $unique_indexes = (array) $table['unique'];
            $unique_indexes = array_keys($unique_indexes);
            $existingIndexes = $this->db->get_results("SHOW INDEX FROM `$table_name`;");

            $existingUniques = array();
            
            foreach ($existingIndexes as $index)
            {
                
                if ($index->Non_unique == 1)
                     continue;

		if ($index->Key_name == "PRIMARY")
		   continue;
                
                array_push($existingUniques,$index->Key_name);
            }
            $existingUniques = array_unique($existingUniques);
            $uniquesToDrop = $existingUniques;
            foreach ($uniquesToDrop as $index)
            {
                $dropIndexQuery = "DROP INDEX `$index` ON `$table_name`;";
                $this->db->query($dropIndexQuery);
            }

            //unique key
            if (isset($table['unique']))
            {
                foreach ($table['unique'] as $unique_key_name=>$unique_key)
                {
                    $whetherSingleColumnUnique=false;
                    if (is_string($unique_key) || 1 == count($unique_key))
                        $whetherSingleColumnUnique=true;
                    if (is_array($unique_key))
                    {
                        $unique_identifier = implode("`,`",$unique_key);
                    }
                    else if (is_string($unique_key))
                    {
                        $unique_identifier = "`$unique_key`";
                        
                    }
                    else
                        continue;

		    //HACK ALERT!!
		    if ($unique_key_name == "meta_key_is_unique")
		    {
		        $setMetaKeyColumnToMd5OfIdQuery=sprintf("UPDATE %swpr_queue SET meta_key=MD5(id) WHERE meta_key='';",$this->db->prefix);
			$this->db->query($setMetaKeyColumnToMd5OfIdQuery);  
		    }
		    

                    $addUniqueKeyQuery = "ALTER TABLE `$table_name` ADD UNIQUE KEY `$unique_key_name` (`$unique_identifier`);";
                    $this->db->query($addUniqueKeyQuery);
                }
            }

            //drop other indexes.


	}
	
	function table_exists($table_name)
	{
		if (count($this->tables)==0)
		{
			$getTablesQuery = "SHOW TABLES;";
			$tables = $this->db->get_col($getTablesQuery);			
			$this->tables = $tables;
		}
                $this->add_prefix($table_name);
		
		return (in_array($table_name,$this->tables))?true:false;
	}	
}

class UndefinedTableException extends Exception
{
}
