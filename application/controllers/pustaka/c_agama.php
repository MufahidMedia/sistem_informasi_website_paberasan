<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_agama extends CI_Controller {

    function  __construct()
    {
		parent::__construct();

        //Load flexigrid helper and library
        $this->load->helper('flexigrid_helper');
        $this->load->library('flexigrid');
        $this->load->helper('form');
        $this->load->model('m_agama');
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
        // $colModel['id_agama'] = array('NO',30,TRUE,'center',0);
		$colModel['No'] = array('NO',30,TRUE,'center',0);		
		$colModel['deskripsi'] = array('Deskripsi',220,TRUE,'left',2);
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

        $grid_js = build_grid_js('flex1',site_url('pustaka/c_agama/load_data'),$colModel,'id_agama','asc',$gridParams,$buttons);

		$data['js_grid'] = $grid_js;

        $data['page_title'] = 'DATA AGAMA';		
		$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
        $data['content'] = $this->load->view('agama/v_list', $data, TRUE);
        $this->load->view('utama', $data);
    }

    function load_data() {	
		$this->load->library('flexigrid');
        $valid_fields = array('id_agama','deskripsi');

		$this->flexigrid->validate_post('id_agama','ASC',$valid_fields);
		$records = $this->m_agama->get_agama_flexigrid();
	
		$this->output->set_header($this->config->item('json_header'));
	
		$record_items = array();
		$counter=0;
		foreach ($records['records']->result() as $row)
		{
			$counter++;
			$record_items[] = array(
				$row->id_agama,
				$counter,
				// $row->id_agama,
				$row->deskripsi,
				'<button type="submit" class="btn btn-default btn-xs" title="Edit" onclick="edit_agama(\''.$row->id_agama.'\')"/><i class="fa fa-pencil"></i></button>'
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
			$data['page_title'] = 'Tambah Agama';
			$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
			$data['content'] = $this->load->view('agama/v_tambah', $data, TRUE);
		
			$this->load->view('utama', $data);
		}else
			redirect('c_login', 'refresh'); 
        
    }
	
	function simpan_agama() {
		$deskripsi = $this->input->post('deskripsi', TRUE);
		
		$this->form_validation->set_rules('deskripsi', 'Nama Agama', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$result['hasil'] = $this->m_agama->cekFIleExist($deskripsi);				
			
			if ($result['hasil'] == NULL) {				
				$data = array(
					'deskripsi' => $deskripsi
				);

			$this->m_agama->insertAgama($data);	
			redirect('pustaka/c_agama','refresh');
			}			
			else $this->add();
			/* Handle ketika nomer agama telah digunakan */
        }
		else $this->add();
    }

    function edit($id){
		$session['hasil'] = $this->session->userdata('logged_in');
		$role = $session['hasil']->role;
		if($this->session->userdata('logged_in') AND $role == 'Pengelola Data')
		{
			$data['hasil'] = $this->m_agama->getAgamaByIdAgama($id);
			$data['page_title'] = 'Edit Data Agama';
			$data['menu'] = $this->load->view('menu/v_pengelolaData', $data, TRUE);
			$data['content'] = $this->load->view('agama/v_ubah', $data, TRUE);
       
			$this->load->view('utama', $data);
		}else
			redirect('c_login', 'refresh'); 
    }
	
	function update_agama() {	
	
		$id_agama = $this->input->post('id_agama', TRUE);
		$deskripsi = $this->input->post('deskripsi', TRUE);
		
		$this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required');

		if ($this->form_validation->run() == TRUE)
		{
			$data = array(
					'deskripsi' => $deskripsi
				);
	
		  	$result = $this->m_agama->updateAgama(array('id_agama' => $id_agama), $data);
			
		  	redirect('pustaka/c_agama','refresh');
		}
		else $this->edit($id_agama);
    }
	
	function delete()    {
        $post = explode(",", $this->input->post('items'));
        array_pop($post); $sucess=0;
        foreach($post as $id){
            $this->m_agama->deleteAgama($id);
            $sucess++;
        }
		
        redirect('admin/c_agama', 'refresh');
    }
	

}
?>