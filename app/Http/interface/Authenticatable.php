<?php

trait Authenticatable
{
	public function __construct(public mysqli $db, public ?bool $force = false)
	{
		parent::__construct($db);
		
		if (!$this->check()) {
			$all_users = $this->all()->result();
			$this->all = $this->original = $this::addAttributesToModel($all_users);
			
			if (!empty($this->all)) {
				$this->first = $this->all[0];
				$this->last = end($this->all);
			}
			
		} else {
			$this->original = $this->where(['email' => ($_SESSION['picfuse_user'])->email, 'is_active' => true, 'is_deleted' => false])->result()->fetch_object();
			
			if (!empty($this->original)) {
				foreach ($this->original as $column => $value)
					$this->$column = $value;
			} else {
				if (!$this->force)
					redirect('logout');
			}
		}
		return $this;
	}
	
	public function check():bool
	{
		return !empty($_SESSION['picfuse_user']);
	}
}
