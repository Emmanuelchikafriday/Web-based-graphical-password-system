<?php

namespace Base;

use Exception;
use mysqli;
use mysqli_result;
use stdClass;

/**
 * @property $all All rows
 * @property $first first row
 * @property $last Last row
 */
abstract class Model
{
	protected mixed $original;
	protected string $table, $primary_key = 'id';
	
	private static ?string $statement;
	private static string $current_query = '';
	
	public function __construct(public mysqli $db)
	{
	}
	
	/**
	 * @param string|null $class
	 * @return string
	 */
	private static function table(?string $class = NULL):string
	{
		$table_name = '';
		$plural = ['s' => 'ses', 'y' => 'ies'];
		$keys_plural = array_keys($plural);
		
		$class_name = $class;
		$class_split = preg_split("/\\\+/", $class_name);
		$class_name_chars = str_split(end($class_split));
		$class_name_char_count = count($class_name_chars);
		
		foreach ($class_name_chars as $key => $class_name_char) {
			$char = $class_name_char;
			
			if ($key && preg_match("/[A-Z]/", $class_name_char))
				$char = '_' . $class_name_char;
			
			if ($key === ($class_name_char_count - 1))
				if (in_array($class_name_char, $keys_plural))
					$char = $plural[$class_name_char];
				else
					$char .= 's';
			$table_name .= $char;
		}
		return strtolower($table_name);
	}
	
	/**
	 * @return string
	 */
	public function getTable():string
	{
		if (empty($this->table))
			$this->table = self::table(get_called_class());
		return strtolower($this->table);
	}
	
	/**
	 * @return $this
	 */
	public function all():static
	{
		self::setStatement("SELECT * FROM {$this->getTable()}");
		return $this;
	}
	
	/**
	 * @param array $query
	 * @return Model|mysqli_result|bool
	 */
	public function where(array $query):Model|mysqli_result|bool
	{
		$count = 0;
		$query_complete = '';
		$statement = ['SELECT'];
		$num_queries = count($query);
		$operators = ['=', '!=', '<', '>', '<>'];
		
		foreach ($query as $key => $q) {
			$value = trim($q);
			$entire_key = preg_split('/\s+/', trim($key));
			$query_partial = empty($entire_key[1]) ?
				($key . ' = ' . "'" . $value . "'") :
				(!in_array($entire_key[1], $operators) ? $entire_key[0] . ' = ' . "'" . $value . "'" : $entire_key[0] . ' ' . $entire_key[1] . ' ' . "'" . $value . "'");
			
			$query_complete .= $query_partial . (($count < ($num_queries - 1)) ? ' AND ' : NULL);
			$count++;
		}
		
		if (!empty(self::$statement) && preg_split('/\s+/', self::$statement)[0] === 'UPDATE') {
			self::$statement .= $query_complete;
			return $this->result();
		}
		
		self::setCurrentQuery(self::$current_query . (!empty(self::$current_query) ? ' AND ' . $query_complete : $query_complete));
		self::$statement = "SELECT * FROM {$this->getTable()} WHERE " . self::$current_query;
		return !in_array(preg_split('/\s+/', self::$statement)[0], $statement) ? $this->result() : $this;
	}
	
	/**
	 * @param array $column_value_pairs
	 * @return mysqli_result|bool
	 */
	public function create(array $column_value_pairs):mysqli_result|bool
	{
		if (!empty($this->foreign_column) && !empty($this->foreign_value)) {
			$foreign_column = $this->foreign_column;
			$update_value = $this->foreign_value;
			$column_value_pairs = array_merge([$foreign_column => $update_value], $column_value_pairs);
		}
		
		$values = [];
		$columns = array_keys($column_value_pairs);
		
		foreach ($column_value_pairs as $value)
			$values[] = !empty($value) ? ("'" . $value . "'") : 'NULL';
		
		self::setStatement("INSERT INTO {$this->getTable()} (" . implode(', ', $columns) . ") VALUES(" . implode(', ', $values) . ")");
		return $this->result();
	}
	
	/**
	 * @param array|null $query
	 * @return bool|mysqli_result
	 */
	public function delete(?array $query = NULL):mysqli_result|bool
	{
		$primary_key = $this->primary_key;
		self::setStatement("DELETE FROM {$this->getTable()}");
		
		if (!empty($query)) {
			$column_value_pairs = '';
			$columns = array_keys($query);
		}
		
		if (!empty($this->foreign_column) && !empty($this->foreign_value)) {
			if (empty($query)) {
				self::setStatement(self::getStatement() . " WHERE $this->foreign_column = '$this->foreign_value'");
				if (empty($this->all))
					self::setStatement(self::getStatement() . " AND $primary_key = '{$this->first->$primary_key}'");
			} else {
				foreach ($query as $column => $value)
					if (empty($this->all))
						if ($column !== $this->foreign_column && $column !== $primary_key)
							$column_value_pairs .= $column . ' = ' . ("'" . (!empty($value) ? $value : 'NULL') . "'") . (($column !== end($columns)) ? ' AND ' : NULL);
						else {
							if ($column === $this->foreign_column)
								$column_value_pairs .= $this->foreign_column . ' = ' . $this->foreign_value . (($column !== end($columns)) ? ' AND ' : NULL);
							else
								$column_value_pairs .= $primary_key . ' = ' . $this->$primary_key . (($column !== end($columns)) ? ' AND ' : NULL);
						}
					else if ($column !== $this->foreign_column)
						$column_value_pairs .= $column . ' = ' . ("'" . (!empty($value) ? $value : 'NULL') . "'") . (($column !== end($columns)) ? ' AND ' : NULL);
					else
						$column_value_pairs .= $this->foreign_column . ' = ' . $this->foreign_value . (($column !== end($columns)) ? ' AND ' : NULL);
				self::setStatement(self::getStatement() . " WHERE $column_value_pairs");
			}
		} else {
			if (empty($query)) {
				if (!empty($this->$primary_key))
					self::setStatement(self::getStatement() . " WHERE $primary_key = '{$this->$primary_key}'");
			} else {
				foreach ($query as $column => $value)
					$column_value_pairs .= $column . ' = ' . ("'" . (!empty($value) ? $value : 'NULL') . "'") . (($column !== end($columns)) ? ' AND ' : NULL);
				self::setStatement(self::getStatement() . " WHERE $column_value_pairs");
			}
		}
		return $this->result();
	}
	
	/**
	 * @param array $query
	 * @param $update_column
	 * @param $update_value
	 * @return Model|bool|Exception|mysqli_result
	 */
	public function update(array $query, $update_column = NULL, $update_value = NULL):mysqli_result|Exception|bool|Model
	{
		$column_value_pairs = '';
		$columns = array_keys($query);
		
		foreach ($query as $column => $value)
			$column_value_pairs .= $column . ' = ' . ("'" . (!empty($value) ? $value : 'NULL') . "'") . (($column !== end($columns)) ? ', ' : NULL);
		
		if (empty($update_column) && empty($update_value))
			if (!empty($this->foreign_column) && !empty($this->foreign_value)) {
				$update_column = $this->foreign_column;
				$update_value = $this->foreign_value;
			} else {
				$primary_key = $this->primary_key;
				if (!empty($this->$primary_key)) {
					$update_column = $primary_key;
					$update_value = $this->$primary_key;
				}
			}
		
		if (!empty($update_value)) {
			self::setStatement("UPDATE {$this->getTable()} SET $column_value_pairs WHERE ");
			return $this->where([$update_column => $update_value]);
		}
		return new Exception('Cannot update unknown row. Please check that row exists.');
	}
	
	/**
	 * @param $modifiers
	 * @return mysqli_result|bool
	 */
	public function result($modifiers = NULL):mysqli_result|bool
	{
		$query = self::getStatement() . (!empty($modifiers) ? ' ' . $modifiers : NULL);
		self::resetQuery();
		return $this->db->query($query);
	}
	
	/**
	 * @return string
	 */
	public static function getCurrentQuery():string
	{
		return self::$current_query;
	}
	
	/**
	 * @return string|null
	 */
	public static function getStatement():?string
	{
		return self::$statement;
	}
	
	/**
	 * @param $model
	 * @param $column
	 * @param $value
	 * @param null $modifiers
	 * @return mixed
	 */
	public function relateMany($model, $column, $value, $modifiers = NULL):mixed
	{
		$primary_key = $this->primary_key;
		
		$model->foreign_column = $column;
		$model->foreign_value = $this->$primary_key;
		
		$query = $model->where([$column => $value])->result($modifiers);
		$model->all = $model->original = self::addAttributesToModel($query);
		
		if (!empty($model->all)) {
			$model->first = $model->all[0];
			$model->last = end($model->all);
		}
		return $model;
	}
	
	/**
	 * @param $model
	 * @param $column
	 * @param $value
	 * @return mixed
	 */
	public function relateOne($model, $column, $value):mixed
	{
		$primary_key = $this->primary_key;
		
		$model->foreign_column = $column;
		$model->foreign_value = $this->$primary_key;
		$model->original = $model->where([$column => $value])->result()->fetch_object();
		
		if (!empty($model->original))
			foreach ($model->original as $column => $value)
				$model->$column = $value;
		return $model;
	}
	
	/**
	 * @return mixed
	 */
	public function getAll():mixed
	{
		return $this->all;
	}
	
	/**
	 * @return mixed
	 */
	public function getFirst():mixed
	{
		return $this->first;
	}
	
	/**
	 * @return mixed
	 */
	public function getLast():mixed
	{
		return $this->last;
	}
	
	/**
	 * @return mixed
	 */
	public function getOriginal():mixed
	{
		return $this->original;
	}
	
	/**
	 * @param string $table
	 */
	public function setTable(string $table):void
	{
		$this->table = $table;
	}
	
	/**
	 * @param $column_value_pair
	 * @return array
	 */
	public static function addAttributesToModel($column_value_pair):array
	{
		$rows = [];
		foreach ($column_value_pair as $key => $row) {
			$rows[$key] = new stdClass();
			foreach ($row as $column => $value)
				$rows[$key]->$column = $value;
		}
		return $rows;
	}
	
	/**
	 * @param string $current_query
	 */
	public static function setCurrentQuery(string $current_query):void
	{
		self::$current_query = $current_query;
	}
	
	/**
	 * @param string|null $statement
	 */
	public static function setStatement(?string $statement):void
	{
		self::$statement = $statement;
	}
	
	/**
	 * @return void
	 */
	private static function resetQuery():void
	{
		self::setStatement(NULL);
		self::setCurrentQuery('');
	}
}
