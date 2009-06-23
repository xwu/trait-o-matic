<?php

class File extends Model {

	var $_table = 'files';

	function File()
	{
		parent::Model();
	}
	
	function insert($set)
	{
		//////////////////
		// sanity check //
		//////////////////
		if (is_object($set))
			$check = get_object_vars($set);
		else
			$check = $set;	
		// each job can have only one of each kind of file
		if (!array_key_exists('kind', $check) || !array_key_exists('job', $check))
			return FALSE;
		$this->db->where('kind', $check['kind']);
		$this->db->where('job', $check['job']);
		if ($this->db->count_all_results($this->_table))
			return FALSE;
		//////////////////
		$this->db->set($set);
		if ($this->db->insert($this->_table))
			return $this->db->insert_id();
		return FALSE;
	}
	
	function count($where)
	{
		if (is_object($where))
			$where = get_object_vars($where);		
		$this->db->where($where);
		return $this->db->count_all_results($this->_table);
	}
	
	function get($where, $limit=NULL, $offset=NULL)
	{
		if (is_object($where))
			$where = get_object_vars($where);		
		$this->db->where($where);
		$query = $this->db->get($this->_table, $limit, $offset);
		if (is_object($where))
			return $limit == 1 ? $query->row() : $query->result();
		return $limit == 1 ? $query->row_array() : $query->result_array();
	}
	
	function update($set, $where)
	{
		return $this->db->update($this->_table, $set, $where);
	}

}

/* End of file file.php */
/* Location: ./system/application/models/file.php */