<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kewarganegaraan extends CI_Controller {

    function  __construct()
    {
		parent::__construct();

        //Load flexigrid helper and library
        $this->load->helper('flexigrid_helper');
        $this->load->library('flexigrid');
        $this->load->helper('form');
        $this->load->model('m_kewarganegaraan');
    }

    function index()    {
		$session['hasil'] = $this->session->userdata('logged_in');
		$role = $session['hasil']->role;
		if($this->session->userdata('logged_in') AND $role == 'Pengelola Data')
		{
			$this->lists();
		}else
			redirect('c_login', 'refresh'); 
        	
    }

    function lists() {
        // $colModel['id_kewarganegaraan'] = array('NO',30,TRUE,'center',0);
		$colModel['No'] = array('NO',30,TRUE,'center',0);		
		$colModel['deskripsi'] = array('Deskripsi',150,TRUE,'left',2);
        $colModel['aksi'] = array('AKSI',60,FALSE,'center',0);
		
		//Populate flexigrid buttons..
        $buttons[] = array('Select All','check','btn');
		$buttons[] = array('separator');
        $buttons[] = array('DeSelect All','uncheck','btn');
        $buttons[] = array('separator');
		$buttons[] = array('Add','add','btn');
        $buttons[] = array('separator');
        $buttons[] = array('Delete Selected Items','delete','btn');
        $buttons[] = array('separator');
       		
        $gridParams = array(
            'height' => 300,
            'rp' => 10,
            'rpOptions' => '[10,20,30,40]',
            'pagestat' => 'Displaying: {from} to {to} of {total} items.',
            'blockOpacity' => 0.5,
            'title' => '',
            'showTableToggleBtn' => false
	);

        $grid_js = build_grid_js('flex1',site_url('pustaka/c_kewarganegaraan/load_data'),$colModel,'id_kewarganegaraan','asc',$gridParams,$buttons);

		$data['js_grid'] = $grid_js;

        $data['page_title'] = 'DATA KEWARGANEGARAAN';		
		$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
        $data['content'] = $this->load->view('kewarganegaraan/v_list', $data, TRUE);
        $this->load->view('utama', $data);
    }

    function load_data() {	
		$this->load->library('flexigrid');
        $valid_fields = array('id_kewarganegaraan','deskripsi');

		$this->flexigrid->validate_post('id_kewarganegaraan','ASC',$valid_fields);
		$records = $this->m_kewarganegaraan->get_kewarganegaraan_flexigrid();
	
		$this->output->set_header($this->config->item('json_header'));
	
		$record_items = array();
		$counter=0;
		foreach ($records['records']->result() as $row)
		{
			$counter++;
			$record_items[] = array(
				$row->id_kewarganegaraan,
				$counter,
				// $row->id_kewarganegaraan,
				$row->deskripsi,
				'<button type="submit" class="btn btn-default btn-xs" title="Edit" onclick="edit_kewarganegaraan(\''.$row->id_kewarganegaraan.'\')"/><i class="fa fa-pencil"></i></button>'
			);  
		}
		//Print please
		$this->output->set_output($this->flexigrid->json_build($records['record_count'],$record_items));
    }
    
    function add(){		
		$session['hasil'] = $this->session->userdata('logged_in');
		$role = $session['hasil']->role;
		if($this->session->userdata('logged_in') AND $role == 'Pengelola Data')
		{			
			$data['page_title'] = 'Tambah Kewarganegaraan';
			$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
			$data['content'] = $this->load->view('kewarganegaraan/v_tambah', $data, TRUE);
		
			$this->load->view('utama', $data);
		}else
			redirect('c_login', 'refresh'); 
        
    }
	
	function simpan_kewarganegaraan() {
		$deskripsi = $this->input->post('deskripsi', TRUE);
		
		$this->form_validation->set_rules('deskripsi', 'Nama Kewarganegaraan', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$result['hasil'] = $this->m_kewarganegaraan->cekFIleExist($deskripsi);				
			
			if ($result['hasil'] == NULL) {				
				$data = array(
					'deskripsi' => $deskripsi
				);

			$this->m_kewarganegaraan->insertKewarganegaraan($data);	
			redirect('pustaka/c_kewarganegaraan','refresh');
			}			
			else $this->add();
			/* Handle ketika nomer kewarganegaraan telah digunakan */
        }
		else $this->add();
    }

    function edit($id){
		$session['hasil'] = $this->session->userdata('logged_in');
		$role = $session['hasil']->role;
		if($this->session->userdata('logged_in') AND $role == 'Pengelola Data')
		{
			$data['hasil'] = $this->m_kewarganegaraan->getKewarganegaraanByIdKewarganegaraan($id);
			$data['page_title'] = 'Edit Data Kewarganegaraan';
			$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
			$data['content'] = $this->load->view('kewarganegaraan/v_ubah', $data, TRUE);
			
			$this->load->view('utama', $data);
		}else
			redirect('c_login', 'refresh'); 
    }
	
	function update_kewarganegaraan() {	
	
		$id_kewarganegaraan = $this->input->post('id_kewarganegaraan', TRUE);
		$deskripsi = $this->input->post('deskripsi', TRUE);
		
		$this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
					'deskripsi' => $deskripsi
				);
	
		  	$result = $this->m_kewarganegaraan->updateKewarganegaraan(array('id_kewarganegaraan' => $id_kewarganegaraan), $data);
			
		  	redirect('pustaka/c_kewarganegaraan','refresh');
		}
		else $this->edit($id_kewarganegaraan);
    }
	
	function delete()    {
        $post = explode(",", $this->input->post('items'));
        array_pop($post); $sucess=0;
        foreach($post as $id){
            $this->m_kewarganegaraan->deleteKewarganegaraan($id);
            $sucess++;
        }
		
        redirect('admin/c_kewarganegaraan', 'refresh');
    }
	

}
?>