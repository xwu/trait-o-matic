<?php

class Job extends Model {

	var $_table = 'jobs';
	
	function Job()
	{
		parent::Model();
	}
	
	function insert() // note that this function takes no arguments
	{
		////////////////////////////////////////////
		$this->db->set('submitted', 'NOW()', FALSE);
		////////////////////////////////////////////
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
	
	////////////////////////////////////////////
	function update_timestamp($key, $where)
	{
		$this->db->set($key, 'NOW()', FALSE);
		$this->db->where($where);
		return $this->db->update($this->_table);
	}
	////////////////////////////////////////////

}

/* End of file job.php */
/* Location: ./system/application/models/job.php */