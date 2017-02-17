<?php

class Model{

	protected $name, $primary;
	protected $columns;
	
	public function __construct($name, $primary = null){
		$this->name = $name;
		$this->primary = $primary;
	}

	public function find($primary, $columns = '*'){
		if(!is_array($columns))
			$columns = (array)$columns;

		$str_columns = implode(', ', $columns);

		extract($this->getPrimaryWhere($primary));//$primary_where, $primary_bind

		$query = "SELECT $str_columns FROM $this->name WHERE $primary_where";

	    return self::search($query, $primary_bind, false);
	}

	public function delete($primary){
		$primary_composed = is_array($this->primary);

		extract($this->getPrimaryWhere($primary));//$primary_where, $primary_bind

		$query = "DELETE FROM $this->name WHERE $primary_where";

	    $command = self::exec($query, $primary_bind);

	    return $command->rowCount();
	}

	public function select(array $columns = ['*']){
		$str_columns = implode(', ', $columns);

		$query = "SELECT {$str_columns} FROM $this->name";

	    return self::search($query);
	}

	public function insert(array $data, array $columns = []){
		global $mypdo;

		$columns = $columns? $columns: array_keys($data);

		$query = "INSERT INTO $this->name (".implode(', ', $columns).") VALUES (".substr(str_repeat('?, ', count($columns)), 0, -2).")";

		$binds = array_map(function($value) use($data) {
			return isset($data[$value])? Treat::sanitize($data[$value]) : NULL;
		}, $columns);

		try{
			if(self::exec($query, $binds) !== false){
		    	$last_id = $mypdo->lastInsertId();
		    	return $last_id? $last_id : true;//no caso de o id retornado ser zero(acontace quando a tebela não tem um autoincrement), retorne true para não confundir 0 com false, aparentando um erro na execução do script, o que ñ é verdade.
		    }else
		    	return false;
	    }catch(PDOException $pdoe){
			Ex_Validate::throwTreatPDOException($pdoe);
		}
	}

	public function update(array $data, $primary, array $columns = []){
		$columns = $columns? $columns: array_keys($data);
		$data = array_sub($data, $columns);

		if(!$data)
			return 0;

		extract($this->getPrimaryWhere($primary));//$primary_where, $primary_bind

		$query = "UPDATE $this->name SET " . implode(', ', array_map(function($key) {
			return "$key = ?";
		}, array_keys($data)) ) . " WHERE $primary_where";

		$data = array_merge($data, $primary_bind);

		$binds = array_values(array_map(function($value) {
			return Treat::sanitize($value);
		}, $data));

		try{
			$command = self::exec($query, $binds);
		}catch(PDOException $pdoe){
			Ex_Validate::throwTreatPDOException($pdoe);
		}

		return $command->rowCount();//retorna FALSE em falha e 'count_rows_affected' em caso de sucesso.
	}

	public function multInsert(array $data, array $columns = [], $all_id = false){
        global $mypdo;

        $columns = $columns? $columns: array_keys(current($data));

        try{
            $mypdo->beginTransaction();
            $insert_values = array();

            foreach ($data as $record) {
                $question_marks[] = '('  . implode(', ', array_fill(0, sizeof($record), '?') ) . ')';
                $insert_values = array_merge($insert_values, 
                	array_values( array_sub_order($record, $columns) )
                );
            }

            $query = 
                "INSERT INTO $this->name (" . implode(",", $columns ) . ") VALUES " . implode(',', $question_marks);
        
            $statement = self::exec($query, $insert_values);
            $mypdo->commit();
        }catch(Exception $e){
            $mypdo->rollback();
            throw $e;
        }

        $last_id = $mypdo->lastInsertId();

        if($last_id){
        	return $all_id? range($last_id, $last_id + sizeof($data) - 1): $last_id;
        }else{
        	$row_count = $statement->rowCount();//!!! talvez esse rowCount não faça sentido porque talvez ou todos os dados são inseridos ou nenhum, logo ou é lançada uma exception ou $row_count = count($data).
        	return $row_count? $row_count : true;//no caso de o id retornado ser zero(acontace quando a tebela não tem um autoincrement) ou de $row_count ser 0(ocorre quando nenhum registro é inserido), retorne true para não confundir 0 com false, aparentando um erro na execução do script, o que ñ é verdade.
        }
    }

    public function insertUpdate(array $data, array $columns = []){
    	$columns = $columns? $columns: array_keys($data);

        $string_cols = implode(', ', $columns);
        $string_binds = implode(', ', array_fill(0, count($data), '?'));
        $string_update = implode(', ', array_map(function($value){
                return "{$value} = VALUES({$value})";
            }, $columns)
        );

        $binds = array_map(function($value) use($data) {
			return isset($data[$value])? Treat::sanitize($data[$value]) : NULL;
		}, $columns);

        self::exec(
            "INSERT INTO $this->name
                  ($string_cols)
            VALUES
                  ($string_binds)
            ON DUPLICATE KEY UPDATE
                  $string_update", array_values($binds)
        );
    }

    public function multiInsertUpdate(array $data, array $columns = []){
        global $mypdo;

        $columns = $columns? array_keys(current($data)): $columns;

        try{
            $mypdo->beginTransaction();
            $insert_values = array();

            foreach ($data as $record) {
                $question_marks[] = '('  . implode(', ', array_fill(0, sizeof($record), '?') ) . ')';
                $insert_values = array_merge($insert_values, array_values($record));
            }

            $string_update = implode(', ', array_map(function($column){
                    return "{$column} = VALUES({$column})";
                }, $columns)
            );

            $string_columns = implode(',', $columns);
            $string_marks = implode(',', $question_marks);

            $query = 
                "INSERT INTO $this->name ({$string_columns}) VALUES {$string_marks} 
                ON DUPLICATE KEY UPDATE {$string_update}";

            self::exec($query, $insert_values);
            $mypdo->commit();
        }catch(Exception $e){
            $mypdo->rollback();
            throw $e;
        }

        return true;
    }

    static public function getStrUpdateFields(array $fields, $data){
    	$result_fields = [];
    	$used_fields = array_keys($data);
    	$bind = [];

    	if (array_count_dim($fields) == 1) {
    		$fields = array_sub_order($fields, $used_fields);

    		return implode(', ', 
	    		array_map(function($value){
	    			return $value . ' = ?';
	    		}, $fields)
	    	);
    	} else {
    		$fields = array_map(function($values) use($used_fields){
    			return array_intersect($values, $used_fields);
    		}, $fields);

	    	foreach ($fields as $alias => $alias_fields)
	    		array_push($result_fields, array_map(function($value) use($alias, &$bind, $data){
	    			array_push($bind, $data[$value]);
	    			return (is_numeric($alias)? '': $alias . '.') . $value . ' = ?';
	    		}, $alias_fields));
			
			$result_fields = call_user_func_array('array_merge', $result_fields);
			$str_fields = implode(', ', $result_fields);

	    	return compact('bind', 'str_fields');
    	}
    }

    //Destinado a UPDATE, INSERT AND DELETE
	static public function exec($query, $binds = []){
		global $mypdo, $user_log;

		$stm = $mypdo->prepare($query);
		$stm->execute((array)$binds);

		return $stm;
	}
	
	//Destinado a SELECT
	static public function search($query, $binds = [], $fetch_all = true){
		global $mypdo;

	    $stm = $mypdo->prepare($query);
		$stm->execute((array)$binds);
		
		return $fetch_all? $stm->fetchAll(PDO::FETCH_ASSOC) : $stm->fetch(PDO::FETCH_ASSOC);
	}

	private function getPrimaryWhere($primary){
		$primary_composed = is_array($this->primary);

		$primary_bind = $primary_composed? array_values(array_sub_order($primary, $this->primary)): [$primary];

		$primary_where = $primary_composed? implode(' AND ', array_map(function($column){
			return $column . ' = ?';
		}, $this->primary)): "$this->primary = ?";

		return compact('primary_bind', 'primary_where');
	}

	protected function setRequiredColumns(array &$columns, $required){
		if(!$columns)
			return;

		$required = (array)$required;
		$required_not_seted = array_diff($required, $columns);

		if($required_not_seted){
			foreach($required_not_seted as $required)
				$columns[] = $required;
		}
	}

}