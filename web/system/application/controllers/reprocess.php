<?php

class Reprocess extends Controller {

	function Reprocess()
	{
		parent::Controller();	
	}
	
	function index()
	{
		// load the necessary modules
		$this->load->library('xmlrpc');
		$this->load->helper('file');
		$this->load->helper('url');
		$this->load->model('File', 'file', TRUE);
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);
		
		// keep track of the job ID (very important!)
		$job = $this->uri->rsegment(3);
		if (!$job)
		{
			return;
		}
		
		// authenticate
		$user_details = $this->_authenticate();
		if (!$user_details)
			return;
				
		// now make sure the user is the correct one (i.e. the owner of the job)
		$user = $this->user->get($user_details, 1);
		if (!$this->job->count(array('user' => $user['id'], 'id' => $job)))
		{
			$data['error'] = 'Only users who have submitted a query may have it reprocessed.';
			$data['redirect'] = $this->uri->uri_string();
			$this->load->view('login', $data);
			return;
		}
		
		// find the files we need to submit
		$genotype_file = $this->file->get(array('kind' => 'genotype', 'job' => $job), 1);
		$genotype_path = $genotype_file ? $genotype_file['path'] : '';
		$coverage_file = $this->file->get(array('kind' => 'coverage', 'job' => $job), 1);
		$coverage_path = $coverage_file ? $coverage_file['path'] : '';
		if ($genotype_path == '')
		{
			//TODO: find some other way to error out
			return;
		}
		$trackback_url = site_url("query/trackback/{$job}");
		$request_token_file = $this->file->get(array('kind' => 'request token', 'job' => $job), 1);
		$request_token_path = $request_token_file ? $request_token_file['path'] : '';
		if ($request_token_path == '')
		{
			//TODO: find some other way to error out
			return;
		}
		$request_token = read_file($request_token_path);
		
		// actually submit the job
		//TODO: move server address into a config file
		$this->xmlrpc->server('http://localhost/', 8080);
		$this->xmlrpc->method('submit_local');
		$request = array($genotype_path, $coverage_path, $trackback_url, $request_token);
		$this->xmlrpc->request($request);
		if (!$this->xmlrpc->send_request())
		{
			// echo $this->xmlrpc->display_error();
			//TODO: error out, with some sort of interface
		}
		
		$data['heading'] = 'Reprocessing Query';
		$this->load->view('confirm', $data);
	}
	
	// identical to Results::_authenticate()
	//TODO: eventually, this should be refactored into a library or something
	function _authenticate()
	{
		if ($this->input->post('username') !== FALSE)
		{
			// populate array with user details
			$user_details = array(
				'username' => trim($this->input->post('username')),
				'password_hash' => hash('sha256', $this->input->post('password'))
			);
			
			// error checking
			if (!$user_details['username'])
			{
				$data['error'] = '<strong>Name</strong> is required.';
				$data['redirect'] = $this->uri->uri_string();
				$this->load->view('login', $data);
				return FALSE;
			}
			if (!$this->user->count($user_details))
			{
				$data['error'] = 'Incorrect name or password.';
				$data['redirect'] = $this->uri->uri_string();
				$this->load->view('login', $data);
				return FALSE;
			}
			
			// set session data
			$session_user_details = array(
				'username' => $user_details['username']
			);
			$this->session->set_userdata($session_user_details);
		}
		// look at session data if no input is supplied
		else if ($this->session->userdata('username') !== FALSE)
		{

			// populate array with user details and do a sanity check
			$user_details = array(
				'username' => $this->session->userdata('username')
			);
			if (!$this->user->count($user_details))
			{
				$data['error'] = 'Incorrect name or password.';
				$data['redirect'] = $this->uri->uri_string();
				$this->load->view('login', $data);
				return FALSE;
			}
		}
		else
		{
			$data['redirect'] = $this->uri->uri_string();
			$this->load->view('login', $data);
			return FALSE;
		}
		return $user_details;
	}

}

/* End of file reprocess.php */
/* Location: ./system/application/controllers/reprocess.php */