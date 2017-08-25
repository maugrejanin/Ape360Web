<?php

include_once( dirname(__FILE__) . '/Treat.class.php' );

class Filter{

	static private $binds = [], $map = [
		'in' => 'inList',
		'notin' => 'notInList',
		'>=' => 'biggerOrEqual',
		'<=' => 'smallerOrEqual',
		'=' => 'equal',
 		'<>' => 'different',
 		'between' => 'between',
 		'like_and' => 'likeNinjaAND',
 		'like_or' => 'likeNinjaOR'
	];

	static public function run(array $rules, array $data, $key_filter = null){
		$filters = [];

		if($key_filter)
			$data = isset($data[$key_filter])? $data[$key_filter]: [];

		foreach($rules as $alias => $alias_rules){

			$alias_index = is_array($alias_rules);

			if(!$alias_index)
				$alias_rules = $rules;

			foreach($alias_rules as $field_treat => $rule){
				list($field, $treat) = array_pad(explode('|', $field_treat), 2, false);

				if( (is_callable($rule) || isset(self::$map[$rule])) and isset($data[$field])){
					if(is_callable($rule))
						$filter_func = $rule;
					else{
						$filter_func_str = self::$map[$rule];
						$filter_func = "Filter::$filter_func_str";
					}

					if($treat){
						$values = is_array($data[$field])?
							array_map("Treat::$treat", $data[$field])
						:
							Treat::$treat($data[$field]);
					} else
						$values = $data[$field];

					$filter = call_user_func(
						$filter_func,
						$alias_index? $alias . '.' . $field: $field,
						$values,
						$treat
					);

					if($filter)
						array_push($filters, $filter);
				}
			}

			if(!$alias_index)
				break;

		}

		$bind_filter = self::$binds;
		self::$binds = [];

		$str_filter = $filters? implode(' AND ', array_map(function($filter){
			return '(' . $filter . ')';
		}, $filters)): 1;

		return compact('str_filter', 'bind_filter');
	}

	static public function inList($field, array $values){
		self::$binds = array_merge(self::$binds, $values);
		return $field . " IN (" . implode(', ', array_fill(0, sizeof($values), '?')) . ")";
	}

	static public function notInList($field, array $values){
		self::$binds = array_merge(self::$binds, $values);
		return $field . " NOT IN (" . implode(', ', array_fill(0, sizeof($values), '?')) . ")";
	}

	static public function biggerOrEqual($field, $value){
		array_push(self::$binds, $value);
		return $field . " >= ?";
	}

	static public function smallerOrEqual($field, $value){
		array_push(self::$binds, $value);
		return $field . " <= ?";
	}

	static public function equal($field, $value){
		array_push(self::$binds, $value);
		return $field . " = ?";
	}

	static public function different($field, $value){
		array_push(self::$binds, $value);
		return $field . " <> ?";
	}

	static public function between($field, array $values, $treat){
		$betweens = [];
		$end = array_pop($values);
		$begin = array_pop($values);

		if($end !== ""){
			if($treat == 'datetime')
				$end .= ' 23:59:59';

			array_push($betweens, self::smallerOrEqual($field, $end));
		}

		if($begin !== ""){
			if($treat == 'datetime')
				$begin .= ' 00:00:00';

			array_push($betweens, self::biggerOrEqual($field, $begin));
		}

		if($betweens)
			return sizeof($betweens) > 1? implode(' AND ', $betweens): current($betweens);
		else
			return 1;
	}

	static public function likeNinjaOR($field, $value){
		return self::likeNinja($field, $value, 'OR');
	}

	static public function likeNinjaAND($field, $value){
		return self::likeNinja($field, $value, 'AND');
	}

	static private function likeNinja($field, $value, $str_concat){
		$values = explode(' ', $value);
		self::$binds = array_merge(self::$binds, $values);

		return implode(' ' . trim($str_concat) . ' ', array_fill(0, sizeof($values), $field . ' LIKE CONCAT("%", ?, "%")'));
	}

	/**
	* @description Baseado na requisição de paginação do bootstrap table.
	*/

	static public function pagination($query, $count = null, $bind = [], $bind_count = []){
		extract(require_request('order', 'offset', 'limit'));//$order, $offset, $limit
		
		$sort = isset($_REQUEST['sort'])? $_REQUEST['sort']: null;
		$search = isset($_REQUEST['search'])? $_REQUEST['search']: null;

		$order = mb_strtoupper($order);
		$str_order_by = $sort? " ORDER BY {$sort} {$order} ": "";

		$offset = (integer)$offset;
		$limit = (integer)$limit;

		$pagquery = $query . $str_order_by . " LIMIT {$offset}, {$limit} ";
		$data_table = Model::search($pagquery, $bind);

		if(!$count)
			$count = Model::countQuery($query, $bind_count);

		return [
			'total' => (integer)$count,
			'rows' => $data_table
		];
	}

}