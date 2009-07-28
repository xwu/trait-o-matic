<?php

class Logout extends Controller {

	function Logout()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->helper('url');
		
		$this->session->unset_userdata('username');
		
		//TODO: this is unreliable; find some way to use a GET string for redirect
		if ($_SERVER['HTTP_REFERER'])
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		else
			redirect("/");
	}

}

/* End of file logout.php */
/* Location: ./system/application/controllers/logout.php */