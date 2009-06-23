<?php

class Samples extends Controller {

	function Samples()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->database(); // we use some active record commands
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);
		
		// find the 20 most recent samples
		$data['samples'] = array();
		$this->db->select('user');
		$this->db->distinct();
		$this->db->group_by('submitted', 'desc');
		$sample_users = $this->job->get(array('public' => 1), 20);
		foreach ($sample_users as $u)
		{
			$user = $this->user->get(array('id' => $u['user']), 1);
			$data['samples'][] = $user['username'];
		}
		
		$this->load->view('samples', $data);
	}
	
	// note that in ./system/application/config/routes.php,
	// samples/:any is remapped to results/samples/$1
	
}

/* End of file samples.php */
/* Location: ./system/application/controllers/samples.php */