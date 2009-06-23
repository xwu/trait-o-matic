<?php

class Reprocess extends Controller {

	function Reprocess()
	{
		parent::Controller();	
	}
	
	function index()
	{
	}
	
	function job()
	{
		// load the necessary modules
		$this->load->library('xmlrpc');
		$this->load->helper('file');
		$this->load->helper('url');
		$this->load->model('File', 'file', TRUE);
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);
		
		// keep track of the job ID (very important!)
		$job = $this->uri->segment(3);
		if (!$job)
		{
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
	}

}

/* End of file reprocess.php */
/* Location: ./system/application/controllers/reprocess.php */