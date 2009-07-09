<?php

class Genotype extends Model {

	var $_db = 'genotypes';

	function Genotype()
	{
		parent::Model();
	}
	
	function count($id, $where)
	{
		if (is_object($where))
			$where = get_object_vars($where);		
		$this->db->where($where);
		return $this->db->count_all_results($this->_db.".".$id);
	}
	
	function get($id, $where, $limit=NULL, $offset=NULL)
	{
		if (is_object($where))
			$where = get_object_vars($where);		
		$this->db->where($where);
		$query = $this->db->get($this->_db.".".$id, $limit, $offset);
		if (is_object($where))
			return $limit == 1 ? $query->row() : $query->result();
		return $limit == 1 ? $query->row_array() : $query->result_array();
	}
	
	function update($id, $set, $where)
	{
		return $this->db->update($this->_db.".".$id, $set, $where);
	}

}

/* End of file genotype.php */
/* Location: ./system/application/models/genotype.php */