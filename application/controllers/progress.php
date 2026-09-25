<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Progress extends CI_Controller {
	//public $progressFile;
	
	function __construct()
	{
		parent::__construct();
		//$this->progressFile = realpath(APPPATH .'../emboss/data.txt');
	}
	
    function index()
	{
		//$this->load->driver('cache');
		//$this->cache->memcached->save('foo', 'bar', 10);
		$this->load->library('memcached_library');
		
		// Lets try to get the key
		//$results = $this->memcached_library->get('test');
		//$this->memcached_library->add('progress', 0);
		//$this->memcached_library->replace('progress', 1);
		$data['x'] = $this->memcached_library->get('progress');
		//if ($this->cache->memcached->is_supported()) {
			//$data['x'] = $this->cache->memcached->get('foo');
		//}
//$data['x'] = $m->get('progress');
		//$progressFile = realpath(APPPATH .'../emboss/data.txt');
		$this->load->view('progress', $data);
	}
	
	function post()
	{
		$this->load->library('memcached_library');
		//$this->load->driver('cache');
		//$this->load->driver('cache');
		//$this->load->library('session');
		//session_start();
		//$this->load->helper('file');
		$this->memcached_library->add('progress', 0);
		for ($i = 0; $i <= 100; $i++) {
			$this->memcached_library->replace('progress', $i);
			$this->memcached_library->flush();
			//write_file($this->progressFile, $i);
			sleep(1);
			//$_SESSION['progress'] = $i;
			//$this->cache->apc->save('progress', $i);
			//usleep(100000);
			//sleep(1);
			//$this->session->set_userdata('progress', $i);
			//session_write_close();
			//$this->cache->save($this->core->getSessionID() .'progress', $i);
			//sleep(0.3);
			//$this->session->keep_flashdata('progress');
		}
		//$this->session->unset_userdata('progress');
		//write_file($this->progressFile, '');
		$this->memcached_library->delete('progress');
	}
	
	function get()
	{
		$this->load->library('memcached_library');
		$time = time();
		while((time() - $time) < 30) {
			// query memcache, database, etc. for new data
			$data = $this->memcached_library->getversion();
		 
			// if we have new data return it
			if($data !== '') {
				echo json_encode($data);
				break;
			}
		 
			usleep(25000);
		}
	}
	
	function getProgress()
	{
		$this->load->library('memcached_library');
		//$this->load->driver('cache');
		//session_start();
		//return file_get_contents($this->progressFile);
		//return $_SESSION['progress'];
		//$this->load->library('session');
		//$this->cache->save($this->core->getSessionID() .'progressx', 'franz');
		//return $this->cache->memcached->get('foo');
		return $this->memcached_library->get('progress');
		//return $this->session->userdata('progress');
	}
}