<?php

class Results extends Controller {

	function Results()
	{
		parent::Controller();	
	}
	
	function index()
	{
		// load necessary modules
		$this->load->model('User', 'user', TRUE);
		
		// authenticate
		$user_details = $this->_authenticate();
		if (!$user_details)
			return;
		
		// show data
		$this->_load_results_view($this->user->get($user_details, 1));		
	}
	
	// in ./system/application/config/routes.php,
	// chmod/:any/:any is remapped to this function	
	function chmod()
	{
		// load necessary modules
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);

		// keep track of what permissions we're setting
		$job_public_mode_symbol = $this->uri->rsegment(3);
		if (!$job_public_mode_symbol)
		{
			return;
		}
		else
		{
			// again, the given numbers are just for show
			// in order to express the kinds of things that
			// users, curators, and the public may do at
			// each permission level
			//
			// group = curators
			// w = curate
			// x = reprocess, etc.
			$public_modes = array(
				'700' => -1,
				'760' => 0,
				'764' => 1
			);
			
			if (!array_key_exists($job_public_mode_symbol, $public_modes))
				return;
			$job_public_mode = $public_modes[$job_public_mode_symbol];
		}
		
		// keep track of the job ID
		$job = $this->uri->rsegment(4);
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
			$data['error'] = 'Only users who have submitted a query may change its settings.';
			$data['redirect'] = $this->uri->uri_string();
			$this->load->view('login', $data);
			return;
		}
		
		// now actually do some work!
		$this->job->update(array('public' => $job_public_mode), array('id' => $job));
		$this->load->view('confirm_chmod');
	}
	
	// in ./system/application/config/routes.php,
	// download/:any/:any is remapped to this function	
	function download()
	{
		// load necessary modules
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);

		// keep track of what file is being requested
		$what = $this->uri->rsegment(3);
		if (!$what)
		{
			return;
		}
		
		// keep track of the job ID
		$job = $this->uri->rsegment(4);
		if (!$job)
		{
			return;
		}
		
		// public data
		if ($this->job->count(array('id' => $job, 'public' => 1)))
		{
			// force download data
			$this->_force_download_source_file($what, $job);
			return;
		}
		
		// otherwise, authenticate
		$user_details = $this->_authenticate();
		if (!$user_details)
			return;
		
		// now make sure the user is the correct one (i.e. the owner of the job)
		$user = $this->user->get($user_details, 1);
		if (!$this->job->count(array('user' => $user['id'], 'id' => $job)))
		{
			$data['error'] = 'Only users who have submitted a query may download these data.';
			$data['redirect'] = $this->uri->uri_string();
			$this->load->view('login', $data);
			return;
		}
		
		// force download data
		$this->_force_download_source_file($what, $job);
	}
	
	// in ./system/application/config/routes.php,
	// samples/:any is remapped to this function
	function samples()
	{
		// load necessary modules
		$this->load->model('Job', 'job', TRUE);
		$this->load->model('User', 'user', TRUE);
		
		$username = $this->uri->rsegment(3);
		if ($username === FALSE)
			return;
		
		$user = $this->user->get(array('username' => $username), 1);
		// we check to make sure that at least one released job exists;
		// the function _load_results_view does not do this check
		if (!$user || !$this->job->count(array('user' => $user['id'], 'public' => 1)))
			return;
		
		// make sure to show only publicly released results
		$this->_load_results_view($user, TRUE);	
	}
	
	// this is our authentication function
	// it displays the login page, sets the proper redirect
	// and returns FALSE when authentication fails; otherwise
	// it returns an associative array of user details
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
	
	// note that invoking this function incorrectly may permit bypassing
	// password restrictions
	function _force_download_source_file($what, $job)
	{
		// load necessary modules
		$this->load->model('File', 'file', TRUE);
		$this->load->model('Job', 'job', TRUE);
		$this->load->helper('file');
		$this->load->helper('json');
		$this->load->helper('language');
		
		// grab the appropriate file
		//TODO: kind of a hack for "ns"
		$kind = ($what == "ns") ? "genotype" : $what;
		$data_file = $this->file->get(array('kind' => $kind, 'job' => $job), 1);
		if (!$data_file)
			return;
		if ($what == "ns")
			$data_file_path = dirname($data_file['path']) . "/ns.gff";
		else
			$data_file_path = $data_file['path'];
		
		// set unique file name based on hash, preserving the extension
		// (note the use of $data_file_path and $data_file['path'], the
		// latter of which allows the retrieved "ns" file to use the
		// "genotype" file's extension)
		$filename = hash_file('sha256', $data_file_path) . '.' . pathinfo($data_file['path'], PATHINFO_EXTENSION);
		
		// force download
		header("Content-type: text/plain");
		header("Content-disposition: attachment; filename=\"{$filename}\"");
		readfile($data_file_path);
	}
	
	// note that invoking this function incorrectly may permit bypassing
	// password restrictions
	function _load_results_view($user, $public_only=FALSE)
	{
		// load necessary modules
		$this->load->model('File', 'file', TRUE);
		$this->load->model('Job', 'job', TRUE);
		$this->load->helper('file');
		$this->load->helper('json');
		$this->load->helper('language');
		$this->load->helper('url');
		// load strings for phenotypes
		$this->lang->load('phenotype');
		
		// load the user name into our output data
		$data['username'] = $user['username'];
		// ...and remember whether it's being accessed as a public view or sample
		$data['public'] = $public_only;
		
		// retrieve most recent job
		if ($public_only)
			$jobs = $this->job->get(array('user' => $user['id'], 'public' => 1));
		else
			$jobs = $this->job->get(array('user' => $user['id']));
		$most_recent_job = end($jobs);
		
		// update retrieval timestamp on the most recent job
		$debug_most_recent_job_id = $most_recent_job['id'];
		log_message('debug', "Updating timestamp on {$debug_most_recent_job_id}");
		$this->job->update_timestamp('retrieved', array('id' => $most_recent_job['id']));
		
		// load the job ID and privacy setting into our output data
		$data['job_id'] = $most_recent_job['id'];
		$data['job_public_mode'] = $most_recent_job['public'];
		
		// read user-submitted phenotypes and append to data
		$phenotype_file = $this->file->get(array('kind' => 'phenotype', 'job' => $most_recent_job['id']), 1);
		$phenotype_path = $phenotype_file['path'];
		$data['phenotypes'] = get_object_vars(json_decode(read_file($phenotype_path)));
		//TODO: error out if no file is found
		
		// read results
		$data['phenotypes']['omim'] = $this->_load_output_data('omim', $most_recent_job['id']);
		$data['phenotypes']['snpedia'] = $this->_load_output_data('snpedia', $most_recent_job['id']);
		$data['phenotypes']['hgmd'] = $this->_load_output_data('hgmd', $most_recent_job['id']);
		$data['phenotypes']['morbid'] = $this->_load_output_data('morbid', $most_recent_job['id']);
				
		//TODO: set session variable, if necessary
		$this->load->view('results', $data);
	}
	
	// note that invoking this function incorrectly may permit bypassing
	// password restrictions
	function _load_output_data($kind, $job)
	{
		$this->load->model('File', 'file', TRUE);
		$this->load->helper('file');
		$this->load->helper('json');
				
		$file = $this->file->get(array('kind' => "out/{$kind}", 'job' => $job), 1);
		$data = array();
		if ($file)
		{
			$path = $file['path'];
			foreach (preg_split('/[\r\n]+/', read_file($path), -1, PREG_SPLIT_NO_EMPTY) as $line)
			{
				$data[] = get_object_vars(json_decode($line));
				// default sort; first obtain list of columns by which to sort
				foreach ($data as $key => $row) {
					// to have chromosomes sort correctly, we convert X, Y, M (or MT) to numbers
					$chromosome[$key]  = str_replace('chr', '', $row['chromosome']);
					switch ($chromosome[$key])
					{
					case 'X':
						$chromosome[$key] = '23';
						break;
					case 'Y':
						$chromosome[$key] = '24';
						break;
					case 'M':
					case 'MT':
						$chromosome[$key] = '25';
						break;
					}
					// other things to sort by; we include amino acid position despite having genome
					// coordinates to break ties in case of alternative splicings
					$coordinates[$key] = $row['coordinates'];
					$gene[$key] = array_key_exists('gene', $row) ? $row['gene'] : "";
					$amino_acid_position[$key] = array_key_exists('amino_acid_change', $row) ?
					                               preg_replace('/\\D/', '', $row['amino_acid_change']) : "";
					$phenotype[$key] = $row['phenotype'];
				}
				@array_multisort($chromosome, SORT_NUMERIC, $coordinates, SORT_NUMERIC,
				                 $gene, $amino_acid_position, SORT_NUMERIC, $phenotype, $data);
			}
		}
		return $data;
	}
}

/* End of file results.php */
/* Location: ./system/application/controllers/results.php */