<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kompetisi extends MY_Controller
{
	private $_path = 'frontend/kompetisi/';
	private $_table = '';

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->templates->load([
			'title' => 'Kompetisi',
			'type' => 'frontend',
			'uri_segment' => $this->_path,
			'page' => $this->_path . 'index',
			'script' => $this->_path . 'index_js',
			'modals' => [],
			'header' => $this->_path . 'header',
		]);
	}
}
