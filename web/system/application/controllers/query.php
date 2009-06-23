<?php

class Query extends Controller {

	function Query()
	{
		parent::Controller();	
	}
	
	function index()
	{
		// process submitted genotype
		if ($this->input->post('submit-gene-form'))
		{
			// load the necessary modules
			$this->load->library('upload');
			$this->load->library('form_validation');
			$this->load->model('File', 'file', TRUE);
			$this->load->model('Job', 'job', TRUE);
			
			// error checking
			$error = FALSE;
			if (!$this->upload->do_upload('genotype', 'Genotype'))
			{
				$error = TRUE;
			}
			// save the upload data
			else
			{
				$genotype_u = $this->upload->data();
				$genotype_path = $genotype_u['full_path'];
			}
			
			// more error checking
			$coverage = FALSE;
			if (isset($_FILES['coverage']) && $_FILES['coverage']['name'])
				$coverage = TRUE;
			
			if ($coverage && !$this->upload->do_upload('coverage', 'Coverage'))
			{
				$error = TRUE;
			}
			// save the upload data
			else if ($coverage)
			{
				$coverage_u = $this->upload->data();
				$coverage_path = $coverage_u['full_path'];
			}
			
			// accommodate asynchronous submissions
			if ($this->input->post('asynchronous-submission'))
				$data['asynchronous'] = true;
			else
				$data['asynchronous'] = false;
			
			// now bail if we've encountered errors
			if ($error)
			{
				$data['error'] = $this->upload->error_msg;
				$this->load->view('genes', $data);
				return;
			}
			
			// insert a new job and retrieve its ID
			$job = $this->job->insert();
			$job_hash = hash('sha256', $job);
			
			// make a subdirectory for the job
			//TODO: double slash problem
			$job_subdirectory = $genotype_u['file_path'].'/'.$job_hash;
			if (!mkdir($job_subdirectory))
			{
				//TODO: error out
			}
			
			// rename file(s)
			$genotype_info = pathinfo($genotype_path);
			$genotype_hash = hash_file('sha256', $genotype_path);
			$new_genotype_path = $job_subdirectory.'/'.$genotype_hash.'.'.$genotype_info['extension'];
			if (!rename($genotype_path, $new_genotype_path))
			{
				//TODO: error out
			}
			else
			{
				// record it in our database
				$this->file->insert(array('job' => $job, 'kind' => 'genotype', 'path' => $new_genotype_path));
			}
			
			if ($coverage)
			{
				$coverage_info = pathinfo($coverage_path);
				$coverage_hash = hash_file('sha256', $coverage_path);
				$new_coverage_path = $job_subdirectory.'/'.$coverage_hash.'.'.$coverage_info['extension'];
				if (!rename($coverage_path, $new_coverage_path))
				{
					//TODO: error out
				}
				else
				{
					// record it in our database
					$this->file->insert(array('job' => $job, 'kind' => 'coverage', 'path' => $new_coverage_path));
				}
			}
			
			// now move on to the next stage
			$data['job'] = $job;
			$this->load->view('traits', $data);
		}
		// process submitted phenotype
		else if ($this->input->post('submit-trait-form'))
		{
			// load the necessary modules
			$this->load->library('form_validation');
			$this->load->helper('file');
			$this->load->helper('json');
			$this->load->model('File', 'file', TRUE);
			
			// keep track of the job ID (very important!)
			$job = $this->input->post('job');

			// validate the form
			$this->form_validation->set_rules('date-of-birth', 'Date of birth', 'trim|required|callback__valid_date');
			$this->form_validation->set_rules('sex', 'Sex', 'required');
			$this->form_validation->set_rules('ancestry[]', 'Ancestry', 'required');
			$this->form_validation->set_rules('eye-color', 'Eye color', 'required');
			$this->form_validation->set_rules('handedness', 'Handedness', 'required');
			$this->form_validation->set_rules('height-in-centimeters', 'Height', 'is_natural');
			//TODO: sanity check; allow either imperial or metric
			$this->form_validation->set_rules('weight-in-kilograms', 'Weight', 'is_natural');
			//TODO: sanity check; allow either imperial or metric
			
			// bail if we've encountered errors
			if (!$this->form_validation->run())
			{
				$data['job'] = $job;
				$this->load->view('traits', $data);
				return;
			}			
			
			// construct the file we want to save (not very elegant, but...)
			unset($_POST['job']);
			unset($_POST['submit-trait-form']);
			$traits = json_encode($this->input->xss_clean($_POST));
			
			// save it to a file
			$genotype_file = $this->file->get(array('kind' => 'genotype', 'job' => $job), 1);
			$genotype_path = $genotype_file['path'];
			$phenotype_path = dirname($genotype_path).'/'.hash('sha256', $traits).'.json';
			if (!write_file($phenotype_path, $traits))
			{
				//TODO: error out
			}
			
			// record it in our database
			$this->file->insert(array('job' => $job, 'kind' => 'phenotype', 'path' => $phenotype_path));
			
			// now move on to the next stage
			$data['job'] = $job;
			$this->load->view('signup', $data);
		}
		// processed submitted account info
		else if ($this->input->post('submit-signup-form'))
		{
			// load the necessary modules
			$this->load->library('form_validation');
			$this->load->library('xmlrpc');
			$this->load->helper('file');
			$this->load->helper('url');
			$this->load->model('File', 'file', TRUE);
			$this->load->model('Job', 'job', TRUE);
			$this->load->model('User', 'user', TRUE);
			
			// keep track of the job ID (very important!)
			$job = $this->input->post('job');
			
			// validate the form
			$this->form_validation->set_rules('username', 'Name', 'trim|required|max_length[64]|callback__unique');
			$this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required');
			$this->form_validation->set_rules('verify-password', 'Verify', 'required|matches[password]');
			
			// bail if we've encountered errors
			if (!$this->form_validation->run())
			{
				$data['job'] = $job;
				$this->load->view('signup', $data);
				return;
			}
			
			// insert a new user, and update our job with the user ID
			$user_details = array(
				'username' => $this->input->post('username'),
				'password_hash' => hash('sha256', $this->input->post('password'))
			);
			if ($this->input->post('email'))
				$user_details['email'] = $this->input->post('email');
			$user = $this->user->insert($user_details);
			$this->job->update(array('user' => $user), array('id' => $job));
			
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
			
			// create random request token and save it
			$trackback_url = site_url("query/trackback/{$job}");
			$request_token = hash('sha256', rand());
			$request_token_path = dirname($genotype_path).'/'.$request_token.'.txt';
			if (!write_file($request_token_path, $request_token))
			{
				//TODO: error out
			}
			$this->file->insert(array('job' => $job, 'kind' => 'request token', 'path' => $request_token_path));
			
			// now, actually submit the job
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

			//TODO: make a confirmation page
			$this->load->view('confirm');
		}
		// nothing to process: show the first page
		else
		{
			$this->load->view('genes');
		}
	}
	
	// trackback: note that this function is silent unless successful
	function trackback()
	{
		$this->load->model('File', 'file', TRUE);
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);
		$this->load->helper('file');
		$this->load->helper('url');
		
		$job = $this->uri->segment(3);
		if ($job === FALSE)
			return;
		
		$path = $this->input->post('path');
		$kind = $this->input->post('kind');
		$request_token = $this->input->post('request_token');
		if (!$path || !$kind || !$request_token)
			return;
		
		log_message('debug', "Trackback with request token {$request_token} received, path={$path} kind={$kind}");
		// check the claimed request token against what we know
		$request_token_file = $this->file->get(array('job' => $job, 'kind' => 'request token'), 1);
		if (!$request_token_file)
			return;
		$request_token_path = $request_token_file['path'];
		if (!$request_token == read_file($request_token_path))
			return;
		
		// if the file is a readme, then the job is complete
		// we should record this, and also email the user if necessary
		if ($kind == 'out/readme')
		{
			$this->job->update_timestamp('processed', array('id' => $job));
			
			$job_details = $this->job->get(array('id' => $job), 1);
			$user_details = $this->user->get(array('id' => $job_details['user']), 1);
			if (!is_null($user_details['email']))
			{
				$host = parse_url($this->config->site_url(), PHP_URL_HOST);
				$username = $user_details['username'];
				$results_url = site_url('results');
				
				$from = "\"Trait-o-matic\" <no-reply@{$host}>";
				$to    = $user_details['email'];
				$sub   = 'Trait-o-matic analysis complete';
				$msg   = wordwrap("As requested, you are receiving a notification that your Trait-o-matic submission has been analyzed and is ready for viewing. Log in at {$results_url} with the username '{$username}' to retrieve your results.", 70);
				$hdrs  = "From: {$from}" . "\r\n" .
						 "Reply-To: {$from}" . "\r\n" .
						 'X-Mailer: PHP/' . phpversion();
				mail($to, $sub, $msg, $hdrs);
			}
		}
		$this->file->insert(array('job' => $job, 'kind' => $kind, 'path' => $path));
		$this->output->set_header("<?xml version=\"1.0\" encoding=\"utf-8\"?".
		                          ">\n<response>\n<error>0</error>\n</response>");
	}
	
	// check for the uniqueness of the username
	function _unique($str)
	{
		if (!isset($this->user))
			$this->load->model('User', 'user', TRUE);
		
		$this->form_validation->set_message('_unique', "<strong>%s</strong> &ldquo;{$str}&rdquo; already exists.");
		
		$c = $this->user->count(array('username' => $str));
		if ($c)
			return FALSE;
		return TRUE;
	}
	
	// check for the validity of a date
	function _valid_date($str) 
	{
		$this->form_validation->set_message('_valid_date', "<strong>%s</strong> must be a valid date.");
		
		$timestamp = strtotime($str);
		if ($timestamp && $timestamp != -1)
			return date('Y-m-d', $timestamp);
		return FALSE; 
	}
	
}

/* End of file query.php */
/* Location: ./system/application/controllers/query.php */