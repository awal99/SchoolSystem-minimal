 <?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*	
 *	@author 	: Zinnia Tech
 *	date		: 13th june, 2018
 */

class Admin extends CI_Controller
{
    
    
	function __construct()
	{
		parent::__construct();
		$this->load->database();
        $this->load->library('session');
        $this->load->library('user_agent');
		
       /*cache control*/
       if(!$this->agent->is_mobile()){
        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");       
       }
       
    }
    
    /***default functin, redirects to login page if no admin logged in yet***/
    public function index()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($this->session->userdata('admin_login') == 1)
            redirect(base_url(), 'refresh');
    }
    
    /***ADMIN DASHBOARD***/
    function dashboard()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('admin_dashboard');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');  
        $this->load->view('backend/index', $page_data);
    }

    /***INVENTORY ***/
    function inventory($param1='', $param2='')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');

        if($param1=='create')
        {
            $this->db->like('name',$this->input->post('name'));
            $resp = $this->db->get('inventory');

            if($resp->num_rows() <= 0)
            {
              $data['name']             =        $this->input->post('name');
              $data['quantity']         =        $this->input->post('quantity');
              $data['last_stocked']     =        $this->input->post('date');
              $data['unit']             =        $this->input->post('unit');
              $data['current_quantity'] =        $this->input->post('quantity');
              $data['school_id']        =        $this->session->userdata('school');
              $this->db->insert('inventory',$data);

              $this->session->set_flashdata('flash_message' , 'Item Added Successfully!');
              redirect(base_url() . 'index.php?admin/inventory', 'refresh');

            }
            $this->session->set_flashdata('flash_message' , 'The Item Already Exists!');
            redirect(base_url() . 'index.php?admin/inventory', 'refresh');
        }

        if($param1=='edit')
        {

            $data['name']             =        $this->input->post('name');
            $data['quantity']         =        $this->input->post('quantity');
            $data['last_stocked']     =        $this->input->post('date');
            $data['unit']             =        $this->input->post('unit');

            $this->db->where('id', $param2);
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->update('inventory', $data);
        
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/inventory/', 'refresh');
        }
            
        if($param1=='update')
        {
            $current_quantity=0;

            if($this->input->post('action')=='add')
            { 
                $cq = 0;
                $this->db->select('current_quantity');
                $this->db->from('inventory');
                $this->db->where('id' , $param2);
                $this->db->where('school_id', $this->session->userdata('school'));
                $resp  =  $this->db->get()->row()->current_quantity;
               // foreach($resp as $row){
                 //   $cq  =  $row['current_quantity'];
                //}
               
                $current_quantity = $resp + $this->input->post('quantity');
            }

            if($this->input->post('action')=='reduce')
            {
                $cq = 0;
                $this->db->select('current_quantity');
                $this->db->from('inventory');
                $this->db->where('id' , $param2);
                $this->db->where('school_id', $this->session->userdata('school'));
                $resp  =  $this->db->get()->row()->current_quantity;
                //foreach($resp as $row){
                  //  $cq  =  $row['current_quantity'];
                //}
               
                $current_quantity = $resp - $this->input->post('quantity');
            }

           // $data2['current_quantity']   =   $current_quantity;

            $this->db->where('id', $param2);
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->set('current_quantity' , $current_quantity);
            $this->db->set('quantity' , $this->input->post('quantity'));
            $this->db->set('last_stocked', date('d/m/Y'));
            $this->db->update('inventory', $data);
        
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/inventory/', 'refresh');
        }

		$page_data['page_name']  = 'inventory';
        $page_data['page_title'] = get_phrase('school_inventory');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
    }
    
    
    /****MANAGE STUDENTS CLASSWISE*****/
	function student_add()
	{
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
			
		$page_data['page_name']  = 'student_add';
        $page_data['page_title'] = get_phrase('add_student');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
	}
	
	function student_bulk_add($param1 = '')
	{
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
			
		if ($param1 == 'import_excel')
		{
			move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/student_import.xlsx');
			// Importing excel sheet for bulk student uploads

			include 'simplexlsx.class.php';
			
			$xlsx = new SimpleXLSX('uploads/student_import.xlsx');
			
			list($num_cols, $num_rows) = $xlsx->dimension();
			$f = 0;
			foreach( $xlsx->rows() as $r ) 
			{
				// Ignore the inital name row of excel file
				if ($f == 0)
				{
					$f++;
					continue;
				}
				for( $i=0; $i < $num_cols; $i++ )
				{
					if ($i == 0)	    $data['name']			=	$r[$i];
					else if ($i == 1)	$data['birthday']		=	$r[$i];
					else if ($i == 2)	$data['sex']		    =	$r[$i];
					else if ($i == 3)	$data['address']		=	$r[$i];
					else if ($i == 4)	$data['phone']			=	$r[$i];
                    else if ($i == 5){
                                        if($r[$i] != ''){
                                            $data_login['email']			=	$r[$i];
                                        }else{
                                            
                                            $data_login['email']  = substr(md5( rand()),0,7)."@email.com";  
                                        }
                    }
					else if ($i == 6){
                                        if($r[$i] != ''){
                                            $data_login['password']	=	$r[$i];
                                        }else{
                                            $data_login['password'] = substr(sha1('Student123') , 0,10);
                                        }
                    }	
					else if ($i == 7)	{$data['roll']			=	$r[$i]; $data2['roll']          =   $r[$i];}
                }

				$data['class_id']	=	$this->input->post('class_id');
                $data['school_id']  =   $this->session->userdata('school');
                $data['student_id'] =   $this->crud_model->get_student_id($this->session->userdata('school'),$this->input->post('class_id'));
                
                //user login credentials to the credentials table in the database.
                $data_login['school_id']    =   $data['school_id'];
                $data_login['user_id']      =   $data['student_id'];
                $data_login['account']  = 3;
                //$data_login['email']        =   $data['email'];
                //$data_login['password']     =   $data['password'];
                //print_r($data);

                $get_email = true; //true for a new email generation
                while($get_email == true){
                   $email  = substr(md5( rand()),0,7)."@email.com";
                   $resp  = $this->db->get_where('credentials',array('email'=>$email));
                   if($resp->num_rows() <= 0){
                       $get_email = false;
                       $data_login['email'] = $email;
                   }
                }
                
                
                   
                $data2['enroll_code']   =   substr(md5(rand(0, 1000000)), 0, 7);
                $data2['roll']          =   $data['roll'];
                $data2['student_id']    =   $data['student_id'];
                $data2['class_id']      =   $this->input->post('class_id');
                $data2['date_added']    =   strtotime(date("Y-m-d H:i:s"));
                $data2['year']          =   $this->session->userdata('running_year');

                $this->db->trans_start();
                $this->db->insert('student' , $data);
                $this->db->insert('credentials',$data_login);
                $this->db->insert('enroll' , $data2);
                $this->db->trans_complete();
			}
			redirect(base_url() . 'index.php?admin/student_information/' . $this->input->post('class_id'), 'refresh');
		}
		$page_data['page_name']  = 'student_bulk_add';
        $page_data['page_title'] = get_phrase('add_bulk_student');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
	}
	
	function student_information($class_id = '')
	{
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
			
		$page_data['page_name']  	= 'student_information';
		$page_data['page_title'] 	= get_phrase('student_information'). " - ".get_phrase('class')." : ".
											$this->crud_model->get_class_name($class_id);
        $page_data['class_id'] 	= $class_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
	}
    
    function modal_student_marksheet($student_id = '')
    {
      
         if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');

        $student_id = urldecode($student_id);
        $class_id     = $this->db->get_where('enroll' , array(
            'school_id' =>  $this->session->userdata('school'),
            'student_id' => $student_id,
            'year'  => $this->session->userdata('running_year') 
           /*'year' => $this->db->get_where('settings' , array('type' => 'running_year'))->row()->description*/
        ))->row()->class_id;
        $student_name = $this->db->get_where('student' , array('school_id' =>  $this->session->userdata('school'),'student_id' => $student_id))->row()->name;
        $class_name   = $this->db->get_where('class' , array('school_id' =>  $this->session->userdata('school'),'class_id' => $class_id))->row()->name;
        $page_data['page_name']  =   'modal_student_marksheet';
        $page_data['page_title'] =   get_phrase('marksheet_for') . ' ' . $student_name . ' (' . get_phrase('class') . ' ' . $class_name . ')';
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $page_data['student_id'] =   $student_id;
        $page_data['class_id']   =   $class_id;
        $this->load->view('backend/index',$page_data);
    }

    function student_marksheet_print_view($student_id , $exam_id) {
        $student_id = urldecode($student_id);
        if ($this->session->userdata('admin_login') != 1)
            redirect('login', 'refresh');
        $class_id     = $this->db->get_where('enroll' , array(
            'student_id' => $student_id , 'year' => $this->session->userdata('running_year')
        ))->row()->class_id;
        $class_name   = $this->db->get_where('class' , array('class_id' => $class_id))->row()->name;

        $page_data['student_id'] =   $student_id;
        $page_data['class_id']   =   $class_id;
        $page_data['exam_id']    =   $exam_id;
        $this->load->view('backend/admin/student_marksheet_print_view', $page_data);
    }
	
	function student_marksheet($class_id = '')
	{
		if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
			
		$page_data['page_name']  = 'student_marksheet';
		$page_data['page_title'] 	= get_phrase('student_marksheet'). " - ".get_phrase('class')." : ".
                                            $this->crud_model->get_class_name($class_id);
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$page_data['class_id'] 	= $class_id;
		$this->load->view('backend/index', $page_data);
	}
	
    function student($param1 = '', $param2 = '', $param3 = '')
    {
       // $stdid  = $param3;
        $student_id =    urldecode($param3);
        $stdid = explode('/',$student_id);
        $pic_id = $stdid[1].'_'.$stdid[2];


        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
           // $this->db->where('email', $this->input->post('email'));
             //$resp = $this->db->get('credentials');
           
          //  if($resp->num_rows() <= 0){
            /*one default password created for all students */
                
                $password = substr(sha1('Student123') , 0,10);

            /*get new id for new student*/   
            $student_id = $this->crud_model->get_student_id($this->session->userdata('school'),$this->input->post('class_id'));
           
            $data['student_id'] = $student_id;
            $data['school_id']  = $this->session->userdata('school');
            $data['name']       = $this->input->post('name');
            $data['birthday']   = $this->input->post('birthday');
            $data['sex']        = $this->input->post('sex');
            $data['address']    = $this->input->post('address');
            $data['phone']      = $this->input->post('phone');
            if($this->input->post('dormitory_id') != ''){
            $data['dormitory_id']  = $this->input->post('dormitory_id');
            }
           
            $data_login['email']      = $this->input->post('email');

            if($this->input->post('password') > ''){
                $data_login['password']   = substr(sha1($this->input->post('password')),0,10);
              
            }else{
                $data_login['password']   = $password;
            }
           
            $data['class_id']   = $this->input->post('class_id');
            if ($this->input->post('section_id') != '') {
                $data['section_id'] = $this->input->post('section_id');
            }
            if ($this->input->post('parent_id') != '') {
                $data['parent_id']  = $this->input->post('parent_id');
            }
            $data['roll']       = $this->input->post('roll');
            

            $data2['student_id']     = $student_id;
            $data2['enroll_code']    = substr(md5(rand(0, 1000000)), 0, 7);
            $data2['class_id']       = $this->input->post('class_id');
            if ($this->input->post('section_id') != '') {
                $data2['section_id'] = $this->input->post('section_id');
            }
            
            $data2['roll']           = $this->input->post('roll');
            $data2['school_id']  = $this->session->userdata('school');
            $data2['date_added']     = strtotime(date("Y-m-d H:i:s"));
            $data2['year']           = $this->session->userdata('running_year');

            $data_login['user_id']  =   $student_id;
            $data_login['school_id']    =   $data2['school_id'];
            $data_login['account']  = 3;

            $this->db->trans_start();
            $this->db->insert('student', $data);
            $this->db->insert('enroll', $data2);
            $this->db->insert('credentials', $data_login);
            $this->db->trans_complete();
           // $student_id = $this->db->insert_id();

           //$student_id = $this->crud_model->get_student_id($this->session->userdata('school'),$this->input->post('class_id'));


            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/student_image') === false){
                mkdir('uploads/'.$school_name.'/student_image');
            }

            if($pic_id == null){
                $stdid = explode('/',$student_id);
                $pic_id = $stdid[1].'_'.$stdid[2].'_'.$stdid[3];
            }

            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/student_image/'.$pic_id. '.jpg');
            /**MANAGE ERROR WHEN ADDING STUDENT */
            if($this->db->trans_status()===true){
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            $this->email_model->account_opening_email('student', $data['email']); //SEND EMAIL ACCOUNT OPENING EMAIL
            redirect(base_url() . 'index.php?admin/student_add/' . $data['class_id'], 'refresh');
            }else{
                $this->session->set_flashdata('flash_message' , get_phrase('Error_adding_student')); 
                redirect(base_url() . 'index.php?admin/student_add/' . $data['class_id'], 'refresh');
            }
           //}else{
          //  $this->session->set_flashdata('flash_message' , 'The Email Already Exists!');
          //  redirect(base_url() . 'index.php?admin/student_add/' . $data['class_id'], 'refresh');
          // }
        }
        if ($param2 == 'do_update') {
            $data['name']        = $this->input->post('name');
            $data['birthday']    = $this->input->post('birthday');
            $data['sex']         = $this->input->post('sex');
            $data['address']     = $this->input->post('address');
            $data['phone']       = $this->input->post('phone');
            $data_login['email']       = $this->input->post('email');
            $data['class_id']    = $this->input->post('class_id');
            $data['section_id']  = $this->input->post('section_id')==null?0:$this->input->post('section_id');
            $data['parent_id']   = $this->input->post('parent_id');
            $data['roll']        = $this->input->post('roll');
            $data['dormitory_id']  = $this->input->post('dormitory_id');
           


            $data2['section_id']    =   $data['section_id'];
            $data2['roll']          =   $this->input->post('roll');
            $running_year = $this->session->userdata('running_year');

            $this->db->trans_start();
            $this->db->where('student_id', $student_id);
            $this->db->update('student', $data);

            $this->db->where('student_id' ,  $student_id);
            $this->db->where('year' , $running_year);
            $this->db->update('enroll' , array(
                'section_id' => $data2['section_id'] , 'roll' => $data2['roll']
            ));
            $this->db->where('user_id',$student_id);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->where('account',3); //without this line update may affect a non student row with the same user_id
            $this->db->update('credentials',$data_login);

            $this->db->trans_complete();

            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/student_image') === false){
                mkdir('uploads/'.$school_name.'/student_image');
            }
           

           $stdid = explode('/',urldecode($param3));
           $pic_id = $stdid[1].'_'.$stdid[2].'_'.$stdid[3];

            if(!move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/student_image/' .$pic_id. '.jpg'))
            {
                $this->crud_model->clear_cache();
                $this->session->set_flashdata('flash_message' , get_phrase('data_updated_without_picture'));
                redirect(base_url() . 'index.php?admin/student_information/' . $param1, 'refresh');
            }
            //move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/student_image/' . $param3 . '.jpg');
            $this->crud_model->clear_cache();
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated_successfully'));
            redirect(base_url() . 'index.php?admin/student_information/' . $param1, 'refresh');
        } 
		
        if ($param2 == 'delete') {
            $this->db->trans_start();
            $this->db->where('student_id' , $student_id);
            $this->db->delete('student');
            $this->db->where('user_id',$student_id);
            $this->db->delete('credentials');
            $this->db->trans_complete();
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/student_information/' . $param1, 'refresh');
        }
    }

    // STUDENT PROMOTION
    function student_promotion($param1 = '' , $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect('login', 'refresh');

        if($param1 == 'promote') {
            $running_year  =   $this->input->post('running_year');  
            $from_class_id =   $this->input->post('promotion_from_class_id'); 
            $students_of_promotion_class =   $this->db->get_where('enroll' , array(
                'class_id' => $from_class_id , 'year' => $running_year
            ))->result_array();
            foreach($students_of_promotion_class as $row) {
                $enroll_data['enroll_code']     =   substr(md5(rand(0, 1000000)), 0, 7);
                $enroll_data['student_id']      =   $row['student_id'];
                $enroll_data['class_id']        =   $this->input->post('promotion_status_'.$row['student_id']);
                $enroll_data['year']            =   $this->input->post('promotion_year');
                $enroll_data['date_added']      =   strtotime(date("Y-m-d H:i:s"));
                $enroll_data['school_id']       =   $this->session->userdata('school');
                //$enroll_data['roll']            =   $this->input->post('promotion_year');
                $this->db->insert('enroll' , $enroll_data);
            } 
            $this->session->set_flashdata('flash_message' , get_phrase('new_enrollment_successfull'));
            redirect(base_url() . 'index.php?admin/student_promotion' , 'refresh');
        }

        $page_data['page_title']    = get_phrase('student_promotion');
        $page_data['page_name']  = 'student_promotion';
        $page_data['settings']   = $this->db->get_where('s_settings',array('id'=>$this->session->userdata('school')));
		$page_data['class_id'] 	= $class_id;
        $this->load->view('backend/index', $page_data);
    }

    function get_students_to_promote($class_id_from , $class_id_to , $running_year , $promotion_year)
    {
        $page_data['class_id_from']     =   $class_id_from;
        $page_data['class_id_to']       =   $class_id_to;
        $page_data['running_year']      =   $running_year;
        $page_data['promotion_year']    =   $promotion_year;
        $this->load->view('backend/admin/student_promotion_selector' , $page_data);
    }


     // ACADEMIC SYLLABUS
    function academic_syllabus($class_id = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        // detect the first class
        if ($class_id == '')
            $class_id           =   $this->db->get_where('class',array('school_id'=>$this->session->userdata('school')))->first_row()->class_id;

        $page_data['page_name']  = 'academic_syllabus';
        $page_data['page_title'] = get_phrase('academic_syllabus');
        $page_data['class_id']   = $class_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function upload_academic_syllabus()
    {
        $data['academic_syllabus_code'] =   substr(md5(rand(0, 1000000)), 0, 7);
        $data['title']                  =   $this->input->post('title');
        $data['description']            =   $this->input->post('description');
        $data['class_id']               =   $this->input->post('class_id');
        $data['uploader_type']          =   $this->session->userdata('login_type');
        $data['uploader_id']            =   $this->session->userdata('login_user_id');
        $data['year']                   =   $this->session->userdata('running_year');
        $data['timestamp']              =   strtotime(date("Y-m-d H:i:s"));
        $data['school_id']              =   $this->session->userdata('school');
        $data['subject_id']             =   $this->input->post('subject_id');
        //uploading file using codeigniter upload library
        $files = $_FILES['file_name'];
        $this->load->library('upload');

        //get school name//
        $this->db->where('id',$this->session->userdata('school'));
        $school_name  = $this->db->get('s_settings')->row()->system_name;

        $config['upload_path']   =  'uploads/'.$school_name.'/syllabus/';
        $config['allowed_types'] =  '*';
        $_FILES['file_name']['name']     = $files['name'];
        $_FILES['file_name']['type']     = $files['type'];
        $_FILES['file_name']['tmp_name'] = $files['tmp_name'];
        $_FILES['file_name']['size']     = $files['size'];
        $this->upload->initialize($config);
        $this->upload->do_upload('file_name');

        $data['file_name'] = $_FILES['file_name']['name'];

        $this->db->insert('academic_syllabus', $data);
        $this->session->set_flashdata('flash_message' , get_phrase('syllabus_uploaded'));
        redirect(base_url() . 'index.php?admin/academic_syllabus/' . $data['class_id'] , 'refresh');

    }

    function download_academic_syllabus($academic_syllabus_code)
    {
        $file_name = $this->db->get_where('academic_syllabus', array(
            'academic_syllabus_code' => $academic_syllabus_code
        ))->row()->file_name;
        $this->load->helper('download');
        $data = file_get_contents("uploads/syllabus/" . $file_name);
        $name = $file_name;

        force_download($name, $data);
    }

    // TABULATION SHEET
    function tabulation_sheet($class_id = '' , $exam_id = '') {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($this->input->post('operation') == 'selection') {
            $page_data['exam_id']    = $this->input->post('exam_id');
            $page_data['class_id']   = $this->input->post('class_id');
            
            if ($page_data['exam_id'] > 0 && $page_data['class_id'] > 0) {
                redirect(base_url() . 'index.php?admin/tabulation_sheet/' . $page_data['class_id'] . '/' . $page_data['exam_id'] , 'refresh');
            } else {
                $this->session->set_flashdata('mark_message', 'Choose class and exam');
                redirect(base_url() . 'index.php?admin/tabulation_sheet/', 'refresh');
            }
        }
        $page_data['exam_id']    = $exam_id;
        $page_data['class_id']   = $class_id;
        
        $page_data['page_info'] = 'Exam marks';
        
        $page_data['page_name']  = 'tabulation_sheet';
        $page_data['page_title'] = get_phrase('tabulation_sheet');
        $this->load->view('backend/index', $page_data);
    
    }

    function tabulation_sheet_print_view($class_id , $exam_id) {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        $page_data['class_id'] = $class_id;
        $page_data['exam_id']  = $exam_id;
        $this->load->view('backend/admin/tabulation_sheet_print_view' , $page_data);
    }

     /****MANAGE PARENTS CLASSWISE*****/
    function parent($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $this->db->where('email', $this->input->post('email'));
            $resp = $this->db->get('credentials');
          
           
           if($resp->num_rows() <= 0){
           /*one default password created for all parents */
            $password = substr(sha1('Parent123') , 0,10);

            $data['name']        			= $this->input->post('name');
            //$data['email']       			= $this->input->post('email');
            if($this->input->post('email') != ''){
                $data_login['email']      = $this->input->post('email');
            }else{
                
                $data_login['email']  = substr(md5( rand()),0,7)."@email.com";  
            }
            //$data['password']    			= $this->input->post('password');
            if($this->input->post('password') > ''){
                $data_login['password']   = substr(sha1($this->input->post('password')),0,10);
            }else{
                $data_login['password']   = $password;  
            }
            $data['phone']       			= $this->input->post('phone');
            $data['address']     			= $this->input->post('address');
            $data['profession']  			= $this->input->post('profession');
            $data['school_id']              =  $this->session->userdata('school');

            $data_login['school_id']        = $data['school_id'];
            $data_login['account']  = 4;

            $this->db->trans_start();
            $this->db->insert('parent', $data);
            $data_login['user_id'] = $this->db->insert_id();
            $this->db->insert('credentials',$data_login);
            $this->db->trans_complete();

            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            $this->email_model->account_opening_email('parent', $data['email']); //SEND EMAIL ACCOUNT OPENING EMAIL
            redirect(base_url() . 'index.php?admin/parent/', 'refresh');
           }else{
            $this->session->set_flashdata('flash_message' , 'Error, the email you entered already exists');
            redirect(base_url() . 'index.php?admin/parent/', 'refresh');
           }
        }
        if ($param1 == 'edit') {
            $data['name']                   = $this->input->post('name');
            $data_login['email']                  = $this->input->post('email');
            $data['phone']                  = $this->input->post('phone');
            $data['address']                = $this->input->post('address');
            $data['profession']             = $this->input->post('profession');
            $data['school_id']              =  $this->session->userdata('school');

            $this->db->trans_start();
            $this->db->where('parent_id' , $param2);
            $this->db->where('school_id' , $this->session->userdata('school'));
            $this->db->update('parent' , $data);
            $this->db->where('user_id',$param2);
            $this->db->where('account',4); //without this line update may affect a non parent row with the same user_id
            $this->db->update('credentials',$data_login);
            $this->db->trans_complete();

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/parent/', 'refresh');
        }
        if ($param1 == 'delete') {
            $this->db->trans_start();
            $this->db->where('parent_id' , $param2);
            $this->db->delete('parent');
            $this->db->where('user_id',$param2);
            $this->db->delete('credentials');
            $this->db->trans_complete();

            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/parent/', 'refresh');
        }
        $page_data['page_title'] 	= get_phrase('all_parents');
        $page_data['page_name']  = 'parent';
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
	
    
    /****MANAGE TEACHERS*****/
    function teacher($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $this->db->where('email', $this->input->post('email'));
            $resp = $this->db->get('credentials');
          
           
           if($resp->num_rows() <= 0){
            $data['name']        = $this->input->post('name');
            $data['birthday']    = $this->input->post('birthday');
            $data['sex']         = $this->input->post('sex');
            $data['address']     = $this->input->post('address');
            $data['phone']       = $this->input->post('phone');
            $data_login['email']       = $this->input->post('email');
            $data_login['password']    = substr(sha1($this->input->post('password')),0,10);
            $data['school_id']  =   $this->session->userdata('school');

            $data_login['school_id']    =   $this->session->userdata('school');
            $data_login['account']  = 2;
             
            $this->db->trans_start();
            $this->db->insert('teacher', $data);
            $teacher_id = $this->db->insert_id();
            $data_login['user_id'] = $teacher_id;
            $this->db->insert('credentials',$data_login);
            $this->db->trans_complete();

            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/teacher_image') === false){
                mkdir('uploads/'.$school_name.'/teacher_image');
            }

            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/teacher_image/' . $teacher_id . '.jpg');
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            $this->email_model->account_opening_email('teacher', $data['email']); //SEND EMAIL ACCOUNT OPENING EMAIL
            redirect(base_url() . 'index.php?admin/teacher/', 'refresh');
           }else{
            $this->session->set_flashdata('flash_message' ,'Error, the email you entered already exists' );
            redirect(base_url() . 'index.php?admin/teacher/', 'refresh');
           }
        }
        if ($param1 == 'do_update') {
            $data['name']        = $this->input->post('name');
            $data['birthday']    = $this->input->post('birthday');
            $data['sex']         = $this->input->post('sex');
            $data['address']     = $this->input->post('address');
            $data['phone']       = $this->input->post('phone');
            $data_login['email']       = $this->input->post('email');
            
            $this->db->trans_start();
            $this->db->where('teacher_id', $param2);
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->update('teacher', $data);
            $this->db->where('user_id',$param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->where('account',2); //if this where is not added it may update a non teacher row with the same user_id
            $this->db->update('credentials',$data_login);
            $this->db->trans_complete();

            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/teacher_image') === false){
                mkdir('uploads/'.$school_name.'/teacher_image');
            }


            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/teacher_image/' . $param2 . '.jpg');
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/teacher/', 'refresh');
        } else if ($param1 == 'personal_profile') {
            $page_data['personal_profile']   = true;
            $page_data['current_teacher_id'] = $param2;
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('teacher', array(
                'teacher_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->trans_start();
            $this->db->where('teacher_id' , $param2);
            $this->db->delete('teacher');
            $this->db->where('user_id',$param2);
            $this->db->delete('credentials');
            $this->db->trans_complete();
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/teacher/', 'refresh');
        }
        $page_data['teachers']   = $this->db->get_where('teacher',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'teacher';
        $page_data['page_title'] = get_phrase('manage_teacher');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /****MANAGE SUBJECTS*****/
    function subject($param1 = '', $param2 = '' , $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']       = $this->input->post('name');
            $data['class_id']   = $this->input->post('class_id');
            $data['teacher_id'] = $this->input->post('teacher_id');
            $data['school_id']  =   $this->session->userdata('school');
            //$data['year']       =  $this->session->userdata('running_year');

            $this->db->insert('subject', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/subject/'.$data['class_id'], 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']       = $this->input->post('name');
            $data['class_id']   = $this->input->post('class_id');
            $data['teacher_id'] = $this->input->post('teacher_id');
            
            $this->db->where('subject_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('subject', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/subject/'.$data['class_id'], 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('subject', array(
                'subject_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('subject_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('subject');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/subject/'.$param3, 'refresh');
        }
		 $page_data['class_id']   = $param1;
        $page_data['subjects']   = $this->db->get_where('subject' , array('class_id' => $param1))->result_array();
        $page_data['page_name']  = 'subject';
        $page_data['page_title'] = get_phrase('manage_subject');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /****MANAGE CLASSES*****/
    function classes($param1 = '', $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']         = $this->input->post('name');
            $data['name_numeric'] = $this->input->post('name_numeric');
            $data['teacher_id']   = $this->input->post('teacher_id');
            $data['school_id']    = $this->session->userdata('school');

            $this->db->insert('class', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/classes/', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']         = $this->input->post('name');
            $data['name_numeric'] = $this->input->post('name_numeric');
            $data['teacher_id']   = $this->input->post('teacher_id');
            
            $this->db->where('class_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('class', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/classes/', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('class', array(
                'class_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('class_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('class');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/classes/', 'refresh');
        }
        $page_data['classes']    = $this->db->get_where('class',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'class';
        $page_data['page_title'] = get_phrase('manage_class');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }


    function get_section($class_id) {
        $page_data['class_id'] = $class_id; 
        $this->load->view('backend/admin/manage_attendance_section_holder' , $page_data);
  }

    /****MANAGE SECTIONS*****/
    function section($class_id = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        // detect the first class
        if ($class_id == '')
            $class_id     =   $this->db->get_where('class',array('school_id'=>$this->session->userdata('school')))->first_row()->class_id;

        $page_data['page_name']  = 'section';
        $page_data['page_title'] = get_phrase('manage_sections');
        $page_data['class_id']   = $class_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);    
    }

    function sections($param1 = '' , $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']       =   $this->input->post('name');
            $data['nick_name']  =   $this->input->post('nick_name');
            $data['class_id']   =   $this->input->post('class_id');
            $data['teacher_id'] =   $this->input->post('teacher_id');
            $data['school_id']    = $this->session->userdata('school');

            $this->db->insert('section' , $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/section/' . $data['class_id'] , 'refresh');
        }

        if ($param1 == 'edit') {
            $data['name']       =   $this->input->post('name');
            $data['nick_name']  =   $this->input->post('nick_name');
            $data['class_id']   =   $this->input->post('class_id');
            $data['teacher_id'] =   $this->input->post('teacher_id');
            $this->db->where('section_id' , $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('section' , $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/section/' . $data['class_id'] , 'refresh');
        }

        if ($param1 == 'delete') {
            $this->db->where('section_id' , $param2);
            $this->db->delete('section');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/section' , 'refresh');
        }
    }

    function get_class_section($class_id)
    {
        $sections = $this->db->get_where('section' , array(
            'school_id' => $this->session->userdata('school'),
            'class_id' => $class_id
        ))->result_array();
        foreach ($sections as $row) {
            echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
        }
    }

    function get_class_subject($class_id)
    {
        $subjects = $this->db->get_where('subject' , array(
            'school_id' => $this->session->userdata('school'),
            'class_id' => $class_id
        ))->result_array();
        foreach ($subjects as $row) {
            echo '<option value="' . $row['subject_id'] . '">' . $row['name'] . '</option>';
        }
    }

    function section_subject_edit($class_id , $class_routine_id)
    {
        $page_data['class_id']          =   $class_id;
        $page_data['class_routine_id']  =   $class_routine_id;
        $this->load->view('backend/admin/class_routine_section_subject_edit' , $page_data);
    }

    /****MANAGE EXAMS*****/
    function exam($param1 = '', $param2 = '' , $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']    = $this->input->post('name');
            $data['date']    = $this->input->post('date');
            $data['comment'] = $this->input->post('comment');
            $data['school_id']    = $this->session->userdata('school');
            $data['year']       =   $this->session->userdata('running_year');

            $this->db->insert('exam', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/exam/', 'refresh');
        }
        if ($param1 == 'edit' && $param2 == 'do_update') {
            $data['name']    = $this->input->post('name');
            $data['date']    = $this->input->post('date');
            $data['comment'] = $this->input->post('comment');
            
            $this->db->where('exam_id', $param3);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('exam', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/exam/', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('exam', array(
                'school_id'=> $this->session->userdata('school'),
                'exam_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('exam_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('exam');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/exam/', 'refresh');
        }
        $page_data['exams']      = $this->db->get_where('exam',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'exam';
        $page_data['page_title'] = get_phrase('manage_exam');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    /****** SEND EXAM MARKS VIA SMS ********/
    function exam_marks_sms($param1 = '' , $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');

        if ($param1 == 'send_sms') {

            $exam_id    =   $this->input->post('exam_id');
            $class_id   =   $this->input->post('class_id');
            $receiver   =   $this->input->post('receiver');

            // get all the students of the selected class
            $students = $this->db->get_where('student' , array(
                'school_id' => $this->session->userdata('school'),
                'class_id' => $class_id
            ))->result_array();
            // get the marks of the student for selected exam
            foreach ($students as $row) {
                if ($receiver == 'student')
                    $receiver_phone = $row['phone'];
                if ($receiver == 'parent' && $row['parent_id'] != '') 
                    $receiver_phone = $this->db->get_where('parent' , array('parent_id' => $row['parent_id'],'school_id' => $this->session->userdata('school')))->row()->phone;
                

                $this->db->where('exam_id' , $exam_id);
                $this->db->where('student_id' , $row['student_id']);
                $this->db->where('school_id',$this->session->userdata('school'));
                $marks = $this->db->get('mark')->result_array();
                $message = '';
                foreach ($marks as $row2) {
                    $subject       = $this->db->get_where('subject' , array('subject_id' => $row2['subject_id']))->row()->name;
                    $mark_obtained = $row2['mark_obtained'];  
                    $message      .= $row2['student_id'] . $subject . ' : ' . $mark_obtained . ' , ';
                    
                }
                // send sms
                $this->sms_model->send_sms( $message , $receiver_phone );
            }
            $this->session->set_flashdata('flash_message' , get_phrase('message_sent'));
            redirect(base_url() . 'index.php?admin/exam_marks_sms' , 'refresh');
        }
                
        $page_data['page_name']  = 'exam_marks_sms';
        $page_data['page_title'] = get_phrase('send_marks_by_sms');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    /****MANAGE EXAM MARKS*****/
    function marks($exam_id = '', $class_id = '', $subject_id = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($this->input->post('operation') == 'selection') {
            $page_data['exam_id']    = $this->input->post('exam_id');
            $page_data['class_id']   = $this->input->post('class_id');
            $page_data['subject_id'] = $this->input->post('subject_id');
            
            if ($page_data['exam_id'] > 0 && $page_data['class_id'] > 0 && $page_data['subject_id'] > 0) {
                redirect(base_url() . 'index.php?admin/marks/' . $page_data['exam_id'] . '/' . $page_data['class_id'] . '/' . $page_data['subject_id'], 'refresh');
            } else {
                $this->session->set_flashdata('mark_message', 'Choose exam, class and subject');
                redirect(base_url() . 'index.php?admin/marks/', 'refresh');
            }
        }
        if ($this->input->post('operation') == 'update') {
            $data['mark_obtained'] = $this->input->post('mark_obtained');
            $data['comment']       = $this->input->post('comment');
            
            $this->db->where('mark_id', $this->input->post('mark_id'));
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('mark', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/marks/' . $this->input->post('exam_id') . '/' . $this->input->post('class_id') . '/' . $this->input->post('subject_id'), 'refresh');
        }
        $page_data['exam_id']    = $exam_id;
        $page_data['class_id']   = $class_id;
        $page_data['subject_id'] = $subject_id;
        
        $page_data['page_info'] = 'Exam marks';
        
        $page_data['page_name']  = 'marks';
        $page_data['page_title'] = get_phrase('manage_exam_marks');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function marks_manage()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        $page_data['page_name']  =   'marks_manage';
        $page_data['running_year'] = $this->session->userdata('running_year');
        $page_data['page_title'] = get_phrase('manage_exam_marks');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    function marks_selector()
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');

        $data['exam_id']    = $this->input->post('exam_id');
        $data['class_id']   = $this->input->post('class_id');
       
        if($this->input->post('section_id') != ''){
            $data['section_id'] = $this->input->post('section_id');
        }else{
            $data['section_id'] = 0;
        }
       
        $data['subject_id'] = $this->input->post('subject_id');
        $data['year']       = $this->session->userdata('running_year');
        $data['school_id']  =  $this->session->userdata('school');
        $query = $this->db->get_where('mark' , array(
            'school_id'=>$data['school_id'],
                    'exam_id' => $data['exam_id'],
                        'class_id' => $data['class_id'],
                            'section_id' => $data['section_id'],
                                'subject_id' => $data['subject_id'],
                                    'year' => $data['year']
                ));
        if($query->num_rows() < 1) {
            $students = $this->db->get_where('enroll' , array(
                'class_id' => $data['class_id'] , 'section_id' => $data['section_id'] , 'year' => $data['year']
            ))->result_array();
            foreach($students as $row) {
                $data['student_id'] = $row['student_id'];
                $this->db->insert('mark' , $data);
            }
        }
        redirect(base_url() . 'index.php?admin/marks_manage_view/' . $data['exam_id'] . '/' . $data['class_id'] . '/' . $data['section_id'] . '/' . $data['subject_id'] , 'refresh');
        
    }

    function marks_manage_view($exam_id = '' , $class_id = '' , $section_id = '' , $subject_id = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        $page_data['exam_id']    =   $exam_id;
        $page_data['class_id']   =   $class_id;
        $page_data['subject_id'] =   $subject_id;
        $page_data['section_id'] =   $section_id;
        $page_data['page_name']  =   'marks_manage_view';
        $page_data['page_title'] = get_phrase('manage_exam_marks');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function marks_update($exam_id = '' , $class_id = '' , $section_id = '' , $subject_id = '')
    {
        $running_year = $this->session->userdata('running_year');
        $marks_of_students = $this->db->get_where('mark' , array(
            'school_id'=>$this->session->userdata('school'),
            'exam_id' => $exam_id,
                'class_id' => $class_id,
                    'section_id' => $section_id,
                        'year' => $running_year,
                            'subject_id' => $subject_id,
                            'year' => $this->session->userdata('running_year')
        ))->result_array();
        foreach($marks_of_students as $row) {
            $obtained_marks = $this->input->post('marks_obtained_'.$row['mark_id']);
            $position = $this->input->post('position_'.$row['mark_id'])==null?0:$this->input->post('position_'.$row['mark_id']);
            $class_score = $this->input->post('class_score_'.$row['mark_id']);
            $exam_score = $this->input->post('exam_score_'.$row['mark_id']);
            $total_score = $class_score + $exam_score;
            $this->db->where('mark_id' , $row['mark_id']);
            $this->db->update('mark' , array('mark_obtained' => $obtained_marks , 'position' => $position , 'class_score' => $class_score , 'exam_score' => $exam_score, 'total_score' => $total_score));
        }
        $this->session->set_flashdata('flash_message' , get_phrase('marks_updated'));
        redirect(base_url().'index.php?admin/marks_manage_view/'.$exam_id.'/'.$class_id.'/'.$section_id.'/'.$subject_id , 'refresh');
    }

    function marks_get_subject($class_id)
    {
        $page_data['class_id'] = $class_id;
        $this->load->view('backend/admin/marks_get_subject' , $page_data);
    }

    
    /****MANAGE GRADES*****/
    function grade($param1 = '', $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']        = $this->input->post('name');
            $data['grade_point'] = $this->input->post('grade_point');
            $data['mark_from']   = $this->input->post('mark_from');
            $data['mark_upto']   = $this->input->post('mark_upto');
            $data['comment']     = $this->input->post('comment');
            $data['school_id']    = $this->session->userdata('school');

            $this->db->insert('grade', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/grade/', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']        = $this->input->post('name');
            $data['grade_point'] = $this->input->post('grade_point');
            $data['mark_from']   = $this->input->post('mark_from');
            $data['mark_upto']   = $this->input->post('mark_upto');
            $data['comment']     = $this->input->post('comment');
            
            $this->db->where('grade_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('grade', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/grade/', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('grade', array(
                'grade_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('grade_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('grade');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/grade/', 'refresh');
        }
        $page_data['grades']     = $this->db->get_where('grade',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'grade';
        $page_data['page_title'] = get_phrase('manage_grade');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    
    
      /****** MANAGE STUDENT REPORT ********/
    function report1($param1 = '' , $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['student_id']   =   $this->input->post('student_id');
            $data['exam_id']  =   $this->input->post('exam_id');
            $data['st_attendance']   =   $this->input->post('st_attendance');
            $data['conduct']   =   $this->input->post('conduct');
            $data['interest'] =   $this->input->post('interest');
            $data['form_master_remarks'] =   $this->input->post('form_master_remarks');
            $data['head_master_remarks'] =   $this->input->post('head_master_remarks');
            $this->db->insert('terminal_report' , $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/student_marksheet/' . $data['class_id'] , 'refresh');
        }

        if ($param1 == 'edit') {
            $data['student_id']   =   $this->input->post('student_id');
            $data['exam_id']  =   $this->input->post('exam_id');
            $data['conduct']   =   $this->input->post('conduct');
            $data['interest'] =   $this->input->post('interest');
            $data['form_master_remarks'] =   $this->input->post('form_master_remarks');
            $data['head_master_remarks'] =   $this->input->post('head_master_remarks');
            $this->db->where('section_id' , $param2);
            $this->db->update('terminal_report' , $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/student_marksheet/' . $data['class_id'] , 'refresh');
        }

        if ($param1 == 'delete') {
            $this->db->where('terminal_report_id' , $param2);
            $this->db->delete('terminal_report');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/student_marksheet' , 'refresh');
        }
    }
    
          ///////ATTENDANCE REPORT /////
          function attendance_report() {
            $page_data['month']        = date('m');
            $page_data['page_name']    = 'attendance_report';
            $page_data['page_title']   = get_phrase('attendance_report');
            $this->db->where('id',$this->session->userdata('school'));
           $page_data['settings']   = $this->db->get('s_settings');
            $this->load->view('backend/index',$page_data);
        }
        function attendance_report_view($class_id = '' , $section_id = '', $month = '') {
            if($this->session->userdata('admin_login')!=1)
               redirect(base_url() , 'refresh');
           $class_name = $this->db->get_where('class' , array(
               'class_id' => $class_id
           ))->row()->name;
           $page_data['class_id'] = $class_id;
           $page_data['month']    = $month;
           $page_data['page_name'] = 'attendance_report_view';
           if($section_id != ''){
           $section_name = $this->db->get_where('section' , array(
               'section_id' => $section_id
           ))->row()->name;
           $page_data['section_id'] = $section_id;
           }
           $page_data['page_title'] = get_phrase('attendance_report_of_class') . ' ' . $class_name . ' : ' . get_phrase('section') . ' ' . $section_name;
           $this->db->where('id',$this->session->userdata('school'));
           $page_data['settings']   = $this->db->get('s_settings');
           $this->load->view('backend/index', $page_data);
        }
        function attendance_report_print_view($class_id ='' , $section_id = '' , $month = '') {
             if ($this->session->userdata('admin_login') != 1)
               redirect(base_url(), 'refresh');
           $page_data['class_id'] = $class_id;
           $page_data['section_id']  = $section_id;
           $page_data['month'] = $month;
           $this->load->view('backend/admin/attendance_report_print_view' , $page_data);
       }
        
       function attendance_report_selector()
       {
           $data['class_id']   = $this->input->post('class_id');
           $data['year']       = $this->input->post('year');
           $data['month']  = $this->input->post('month');
           $data['section_id'] = $this->input->post('section_id');
          
           if(!empty($data['section_id'])){
           redirect(base_url().'index.php?admin/attendance_report_view/'.$data['class_id'].'/'.$data['section_id'].'/'.$data['month'],'refresh');
           }else{
            redirect(base_url().'index.php?admin/attendance_report_view/'.$data['class_id'].'/0/'.$data['month'],'refresh');
    
           }
       }
    
    /**********MANAGING CLASS ROUTINE******************/
    function class_routine($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {

            $data['class_id']       = $this->input->post('class_id');
            if($this->input->post('section_id') != '') {
                $data['section_id'] = $this->input->post('section_id');
            }
            $data['subject_id']     = $this->input->post('subject_id');
            $data['time_start']     = $this->input->post('time_start') + (12 * ($this->input->post('starting_ampm') - 1));
            $data['time_end']       = $this->input->post('time_end') + (12 * ($this->input->post('ending_ampm') - 1));
            $data['time_start_min'] = $this->input->post('time_start_min');
            $data['time_end_min']   = $this->input->post('time_end_min');
            $data['day']            = $this->input->post('day');
            $data['school_id']    = $this->session->userdata('school');
            $data['year']           = $this->db->get_where('s_settings' , array('id' => $this->session->userdata('school')))->row()->running_year;
            $this->db->insert('class_routine', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/class_routine/'.$data['class_id'], 'refresh');

        }
        if ($param1 == 'do_update') {
            $data['class_id']   = $this->input->post('class_id');
            if($this->input->post('section_id') != '') {
                $data['section_id'] = $this->input->post('section_id');
            }
            $data['subject_id'] = $this->input->post('subject_id');
            $data['time_start'] = $this->input->post('time_start') + (12 * ($this->input->post('starting_ampm') - 1));
            $data['time_end']   = $this->input->post('time_end') + (12 * ($this->input->post('ending_ampm') - 1));
            $data['time_start_min'] = $this->input->post('time_start_min');
            $data['time_end_min']   = $this->input->post('time_end_min');
            $data['day']        = $this->input->post('day');
            $data['school_id']    = $this->session->userdata('school');
            $data['year']           = $this->db->get_where('s_settings' , array('id' => $this->session->userdata('school')))->row()->running_year;

            
            $this->db->where('class_routine_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('class_routine', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/class_routine/'.$data['class_id'], 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('class_routine', array(
                'class_routine_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('class_routine_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('class_routine');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/class_routine/'.$param3, 'refresh');
        }
        $page_data['class_id']  =   $param1; //this only applies if the link in navbar is just clicked
        $page_data['page_name']  = 'class_routine';
        $page_data['page_title'] = get_phrase('manage_class_routine');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function get_class_section_subject($class_id)
    {
        $page_data['class_id'] = $class_id;
        $this->load->view('backend/admin/class_routine_section_subject_selector' , $page_data);
    }


    function class_routine_print_view($class_id , $section_id)
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect('login', 'refresh');
        $page_data['class_id']   =   $class_id;
        $page_data['section_id'] =   $section_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/admin/class_routine_print_view' , $page_data);
    }
	
   
    function manage_attendance($date='',$month='',$year='',$class_id='' , $section_id = '' , $session = '')
    {
        if($this->session->userdata('admin_login')!=1)
            redirect(base_url() , 'refresh');

        $active_sms_service = $this->db->get_where('s_settings' , array('id' =>  $this->session->userdata('school')))->row()->active_sms_service;
        $running_year = $this->db->get_where('s_settings' , array('id' => $this->session->userdata('school')))->row()->running_year;

        
        if($_POST)
        {
            // Loop all the students of $class_id
            $this->db->where('class_id' , $class_id);
            if($section_id != 0) {
                $this->db->where('section_id' , $section_id);
            }
            //$session = base64_decode( urldecode( $session ) );
            $this->db->where('year' , $session);
            $students = $this->db->get_where('enroll',array('school_id'=>$this->session->userdata('school')))->result_array();
            foreach ($students as $row)
            {
                $attendance_status  =   $this->input->post('status_' . $row['student_id']);

                $this->db->where('student_id' , $row['student_id']);
                $this->db->where('date' , $year.'-'.$month.'-'.$date);
                $this->db->where('year' , $session);
                $this->db->where('class_id' , $row['class_id']);
                if($row['section_id'] != '' && $row['section_id'] != 0) {
                    $this->db->where('section_id' , $row['section_id']);
                }
                //$this->db->where('session' , $session);

                $this->db->update('attendance' , array('status' => $attendance_status));

                if ($attendance_status == 2) {

                    if ($active_sms_service != '' || $active_sms_service != 'disabled') {
                        $student_name   = $this->db->get_where('student' , array('student_id' => $row['student_id']))->row()->name;
                        $parent_id      = $this->db->get_where('student' , array('student_id' => $row['student_id']))->row()->parent_id;
                        $receiver_phone = $this->db->get_where('parent' , array('parent_id' => $parent_id))->row()->phone;
                        $message        = 'Your child' . ' ' . $student_name . 'is absent today.';
                        $this->sms_model->send_sms($message,$receiver_phone);
                    }
                }

            }

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/manage_attendance/'.$date.'/'.$month.'/'.$year.'/'.$class_id.'/'.$section_id.'/'.$session , 'refresh');
        }
        $page_data['date']       =  $date;
        $page_data['month']      =  $month;
        $page_data['year']       =  $year;
        $page_data['class_id']   =  $class_id;
        $page_data['section_id'] =  $section_id;
        $page_data['session']    =  $session;
        
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings'); 
        $page_data['page_name']  =  'manage_attendance';
        $page_data['page_title'] =  get_phrase('manage_daily_attendance');
        $page_data['running_year'] = $this->session->userdata('running_year');
        $this->load->view('backend/index', $page_data);
    }

    function manage_attendance_view($class_id = '' , $section_id = '' , $timestamp = '')
    {
        if($this->session->userdata('admin_login')!=1)
            redirect(base_url() , 'refresh');
        $class_name = $this->db->get_where('class' , array(
            'class_id' => $class_id
        ))->row()->name;
        $page_data['class_id'] = $class_id;
        $page_data['timestamp'] = $timestamp;
        $page_data['page_name'] = 'manage_attendance_view';
        $section_name = $this->db->get_where('section' , array(
            'section_id' => $section_id
        ))->row()->name;
        $page_data['section_id'] = $section_id;
        $page_data['page_title'] = get_phrase('manage_attendance_of_class') . ' ' . $class_name . ' : ' . get_phrase('section') . ' ' . $section_name;
        $this->load->view('backend/index', $page_data);
    }


 	
    function attendance_selector()
    {
        //$session = $this->input->post('session');
        //$encoded_session = urlencode( base64_encode( $session ) );
       $sec_id = 0;
      if($this->input->post('section_id') !=''){
        $sec_id = $this->input->post('section_id');
      }

        redirect(base_url() . 'index.php?admin/manage_attendance/'.$this->input->post('date').'/'.
                    $this->input->post('month').'/'.
                        $this->input->post('year').'/'.
                            $this->input->post('class_id').'/'.
                                          $sec_id.'/'.
                                    $this->input->post('session') , 'refresh');
    }
    /******MANAGE BILLING / INVOICES WITH STATUS*****/
    function invoice($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($param1 == 'create') {
            $data['student_id']         = $this->input->post('student_id');
            $data['title']              = $this->input->post('title');
            $data['description']        = $this->input->post('description');
            $data['amount']             = $this->input->post('amount');
            $data['amount_paid']        = $this->input->post('amount_paid');
            $data['due']                = $data['amount'] - $data['amount_paid'];
            $data['status']             = $this->input->post('status');
            $data['creation_timestamp'] = strtotime($this->input->post('date'));
            $data['school_id']          = $this->session->userdata('school');
            $data['year']               = $this->session->userdata('running_year');

            

            
            $data2['student_id']        =   $this->input->post('student_id');
            $data2['title']             =   $this->input->post('title');
            $data2['description']       =   $this->input->post('description');
            $data2['payment_type']      =  'income';
            $data2['method']            =   $this->input->post('method');
            $data2['amount']            =   $this->input->post('amount_paid');
            $data2['timestamp']         =   strtotime($this->input->post('date'));
            $data2['school_id']         =   $this->session->userdata('school');
            $data2['year']               = $this->session->userdata('running_year');

            $this->db->trans_start();
            $this->db->insert('invoice', $data);
            $invoice_id = $this->db->insert_id();
            $data2['invoice_id']        =   $invoice_id;
            $this->db->insert('payment' , $data2);
            $this->db->trans_complete();

            if($this->db->trans_status() == false){
            $this->session->set_flashdata('flash_message' , get_phrase('error_adding_data'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
            }else{
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
            }
        }

        if ($param1 == 'create_mass_invoice') {
            foreach ($this->input->post('student_id') as $id) {

                $data['student_id']         = $id;
                $data['title']              = $this->input->post('title');
                $data['description']        = $this->input->post('description');
                $data['amount']             = $this->input->post('amount');
                $data['amount_paid']        = $this->input->post('amount_paid');
                $data['due']                = $data['amount'] - $data['amount_paid'];
                $data['status']             = $this->input->post('status');
                $data['creation_timestamp'] = strtotime($this->input->post('date'));
                $data['year']               = $this->session->userdata('running_year');
                $data['school_id']          = $this->session->userdata('school');
                
               
                $data2['student_id']        =   $id;
                $data2['title']             =   $this->input->post('title');
                $data2['description']       =   $this->input->post('description');
                $data2['payment_type']      =  'income';
                $data2['method']            =   $this->input->post('method');
                $data2['amount']            =   $this->input->post('amount_paid');
                $data2['timestamp']         =   strtotime($this->input->post('date'));
                $data2['year']               =   $this->session->userdata('running_year');
                $data2['school_id']          = $this->session->userdata('school');

                $this->db->trans_start();
                $this->db->insert('invoice', $data);
                $invoice_id = $this->db->insert_id();
                $data2['invoice_id']        =   $invoice_id;
                $this->db->insert('payment' , $data2);
                $this->db->trans_complete();
            }
            
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
        }


        if ($param1 == 'do_update') {
            $data['student_id']         = $this->input->post('student_id');
            $data['title']              = $this->input->post('title');
            $data['description']        = $this->input->post('description');
            $data['amount']             = $this->input->post('amount');
            $data['status']             = $this->input->post('status');
            $data['creation_timestamp'] = strtotime($this->input->post('date'));
            
            $this->db->where('invoice_id', $param2);
            $this->db->where('school_id' , $this->session->userdata('school'));
            $this->db->update('invoice', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('invoice', array(
                'school_id' => $this->session->userdata('school'),
                'invoice_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'take_payment') {
            $data['invoice_id']   =   $this->input->post('invoice_id');
            $data['student_id']   =   $this->input->post('student_id');
            $data['title']        =   $this->input->post('title');
            $data['description']  =   $this->input->post('description');
            $data['payment_type'] =   'income';
            $data['method']       =   $this->input->post('method');
            $data['amount']       =   $this->input->post('amount');
            $data['timestamp']    =   strtotime($this->input->post('timestamp'));
            $data['school_id']         =   $this->session->userdata('school');
            $data['year']               = $this->session->userdata('running_year');
            $this->db->insert('payment' , $data);

            $amount = $this->db->get_where('invoice',array('invoice_id'=>$param2))->row()->amount;
            $amount_paid = $this->db->get_where('invoice',array('invoice_id'=>$param2))->row()->amount_paid;

            $data2['amount_paid']   =   $this->input->post('amount');
            $this->db->where('invoice_id' , $param2);
            $this->db->where('school_id' , $this->session->userdata('school'));
            $this->db->set('amount_paid', 'amount_paid + ' . $data2['amount_paid'], FALSE);
            $this->db->set('due', 'due - ' . $data2['amount_paid'], FALSE);
            if($amount==$amount_paid+$data2['amount_paid']){
                $this->db->set('status','paid');
            }
            $this->db->update('invoice');
               
            $this->session->set_flashdata('flash_message' , get_phrase('payment_successfull'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
        }

        if ($param1 == 'delete') {
            $this->db->where('invoice_id', $param2);
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->delete('invoice');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/invoice', 'refresh');
        }
        $page_data['page_name']  = 'invoice';
        $page_data['page_title'] = get_phrase('manage_invoice/payment');
        $this->db->order_by('creation_timestamp', 'desc');
        $page_data['invoices'] = $this->db->get_where('invoice',array('school_id'=>$this->session->userdata('school')))->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings'); 
        $this->load->view('backend/index', $page_data);
    }

    function get_class_students_mass($class_id)
    {
        $students = $this->db->get_where('enroll' , array(
            'class_id' => $class_id , 'year' => $this->session->userdata('running_year')
        ))->result_array();
        echo '<div class="form-group">
                <label class="col-sm-3 control-label">' . get_phrase('students') . '</label>
                <div class="col-sm-9">';
        foreach ($students as $row) {
             $name = $this->db->get_where('student' , array('student_id' => $row['student_id']))->row()->name;
            echo '<div class="checkbox">
                    <label><input type="checkbox" class="check" name="student_id[]" value="' . $row['student_id'] . '">' . $name .'</label>
                </div>';
        }
        echo '<br><button type="button" class="btn btn-default" onClick="select()">'.get_phrase('select_all').'</button>';
        echo '<button style="margin-left: 5px;" type="button" class="btn btn-default" onClick="unselect()"> '.get_phrase('select_none').' </button>';
        echo '</div></div>';
    }

   
    /**********MANAGE LIBRARY / BOOKS********************/
    function book($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']        = $this->input->post('name');
            $data['description'] = $this->input->post('description');
            $data['price']       = $this->input->post('price');
            $data['author']      = $this->input->post('author');
            $data['class_id']    = $this->input->post('class_id');
            $data['status']      = $this->input->post('status');
            $data['school_id']   = $this->session->userdata('school');
            $this->db->insert('book', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/book', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']        = $this->input->post('name');
            $data['description'] = $this->input->post('description');
            $data['price']       = $this->input->post('price');
            $data['author']      = $this->input->post('author');
            $data['class_id']    = $this->input->post('class_id');
            $data['status']      = $this->input->post('status');
            
            $this->db->where('book_id', $param2);
            $this->db->update('book', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/book', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('book', array(
                'school_id' =>  $this->session->userdata('school'),
                'book_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('book_id', $param2);
            $this->db->delete('book');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/book', 'refresh');
        }
        $page_data['books']      = $this->db->get_where('book',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'book';
        $page_data['page_title'] = get_phrase('manage_library_books');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
        
    }
   
    /**********MANAGE DORMITORY / HOSTELS / ROOMS ********************/
    function dormitory($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {
            $data['name']           = $this->input->post('name');
            $data['number_of_room'] = $this->input->post('number_of_room');
            $data['description']    = $this->input->post('description');
            $data['school_id']      = $this->session->userdata('school');

            $this->db->insert('dormitory', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/dormitory', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']           = $this->input->post('name');
            $data['number_of_room'] = $this->input->post('number_of_room');
            $data['description']    = $this->input->post('description');
            
            $this->db->where('dormitory_id', $param2);
            $this->db->update('dormitory', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/dormitory', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('dormitory', array(
                'school_id' => $this->session->userdata('school'),
                'dormitory_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('dormitory_id', $param2);
            $this->db->delete('dormitory');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/dormitory', 'refresh');
        }
        $page_data['dormitories'] = $this->db->get_where('dormitory',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']   = 'dormitory';
        $page_data['page_title']  = get_phrase('manage_dormitory');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
        
    }
    
    /***MANAGE EVENT / NOTICEBOARD, WILL BE SEEN BY ALL ACCOUNTS DASHBOARD**/
    function noticeboard($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($param1 == 'create') {
            $data['notice_title']     = $this->input->post('notice_title');
            $data['notice']           = $this->input->post('notice');
            $data['create_timestamp'] = strtotime($this->input->post('create_timestamp'));
            $data['school_id']        = $this->session->userdata('school');
            $data['status']           = 1;

            $this->db->insert('noticeboard', $data);

            $check_sms_send = $this->input->post('check_sms');

            if ($check_sms_send == 1) {
                // sms sending configurations

                $parents  = $this->db->get_where('parents', array('school_id'=>$this->session->userdata('school')))->result_array();
                $students = $this->db->get_where('student', array('school_id'=>$this->session->userdata('school')))->result_array();
                $teachers = $this->db->get_where('teacher', array('school_id'=>$this->session->userdata('school')))->result_array();
                $date     = $this->input->post('create_timestamp');
                $message  = $data['notice_title'] . ' ';
                $message .= get_phrase('on') . ' ' . $date;
                foreach($parents as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
                foreach($students as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
                foreach($teachers as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
            }

            $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
            redirect(base_url() . 'index.php?admin/noticeboard/', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['notice_title']     = $this->input->post('notice_title');
            $data['notice']           = $this->input->post('notice');
            $data['create_timestamp'] = strtotime($this->input->post('create_timestamp'));
            $this->db->where('notice_id', $param2);
            $this->db->update('noticeboard', $data);

            $check_sms_send = $this->input->post('check_sms');

            if ($check_sms_send == 1) {
                // sms sending configurations

                $parents  = $this->db->get_where('parents', array('school_id'=>$this->session->userdata('school')))->result_array();
                $students = $this->db->get_where('student', array('school_id'=>$this->session->userdata('school')))->result_array();
                $teachers = $this->db->get_where('teacher', array('school_id'=>$this->session->userdata('school')))->result_array();
                $date     = $this->input->post('create_timestamp');
                $message  = $data['notice_title'] . ' ';
                $message .= get_phrase('on') . ' ' . $date;
                foreach($parents as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
                foreach($students as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
                foreach($teachers as $row) {
                    $reciever_phone = $row['phone'];
                    $this->sms_model->send_sms($message , $reciever_phone);
                }
            }

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/noticeboard/', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('noticeboard', array(
                'notice_id' => $param2
            ))->result_array();
        }

        if($param1 == 'mark_as_archive'){
            $data['status'] =   0;
            $this->db->where('notice_id',$param2);
            $this->db->update('noticeboard',$data);

            $this->session->set_flashdata('flash_message' , get_phrase('notice_archived'));
            redirect(base_url() . 'index.php?admin/noticeboard/', 'refresh');
        }

        if($param1 == 'remove_from_archived'){
            $data['status'] =   1;
            $this->db->where('notice_id',$param2);
            $this->db->update('noticeboard',$data);

            $this->session->set_flashdata('flash_message' , get_phrase('notice_removed_from_archive'));
            redirect(base_url() . 'index.php?admin/noticeboard/', 'refresh');
        }

        if ($param1 == 'delete') {
            $this->db->where('notice_id', $param2);
            $this->db->delete('noticeboard');
            $this->session->set_flashdata('flash_message' , get_phrase('data_deleted'));
            redirect(base_url() . 'index.php?admin/noticeboard/', 'refresh');
        }
        $page_data['page_name']  = 'noticeboard';
        $page_data['page_title'] = get_phrase('manage_noticeboard');
        $page_data['notices']    = $this->db->get_where('noticeboard', array('school_id'=>$this->session->userdata('school')))->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /* private messaging */

    function message($param1 = 'message_home', $param2 = '', $param3 = '') {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(base_url() . 'index.php?admin/message/message_read/' . $message_thread_code, 'refresh');
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2);  //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(base_url() . 'index.php?admin/message/message_read/' . $param2, 'refresh');
        }

        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2;  // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }

        $page_data['message_inner_page_name']   = $param1;
        $page_data['page_name']                 = 'message';
        $page_data['page_title']                = get_phrase('private_messaging');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /*****SITE/SYSTEM SETTINGS*********/
    function system_settings($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url() . 'index.php?login', 'refresh');
        
        if ($param1 == 'do_update') {
		
            $data['system_name']            =           $this->input->post('system_name');
            $data['system_title']           =           $this->input->post('system_title');
            $data['address']                =           $this->input->post('address');
            $data['phone']                  =           $this->input->post('phone');
            $data['paypal_email']           =           $this->input->post('paypal_email');
            $data['currency']               =           $this->input->post('currency');
            $data['system_email']           =           $this->input->post('system_email');
            $data['language']               =           $this->input->post('language');
            $data['text_align']             =           $this->input->post('text_align');

            $this->db->trans_start();
            $this->db->where('id',$this->session->userdata('school'));
            $this->db->update('s_settings', $data);
            $this->db->trans_complete();
			
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated')); 
            redirect(base_url() . 'index.php?admin/system_settings/', 'refresh');
        }
        if ($param1 == 'upload_logo') {
            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name) === false){
                mkdir('uploads/'.$school_name);
            }

            if(move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/logo.png')==true)
            {
                $this->session->set_flashdata('flash_message', get_phrase('settings_updated'));
            }
            $this->session->set_flashdata('flash_message', "error uploading pic");
            redirect(base_url() . 'index.php?admin/system_settings/', 'refresh');
        }
        if ($param1 == 'change_skin') {
            $data['skin_colour'] = $param2;
            $this->db->where('id' ,$this->session->userdata('school') );
            $this->db->update('s_settings' , $data);
            $this->session->set_flashdata('flash_message' , get_phrase('theme_selected')); 
            redirect(base_url() . 'index.php?admin/system_settings/', 'refresh'); 
        }
        $page_data['page_name']  = 'system_settings';
        $page_data['page_title'] = get_phrase('system_settings');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function get_session_changer()
    {
        $this->load->view('backend/admin/change_session');
    }

    function change_session()
    {
        $data['running_year'] = $this->input->post('running_year');
        $this->db->where('id' , $this->session->userdata('school'));
        $this->db->update('s_settings' , $data);
        $this->session->set_userdata('running_year',$data['running_year']);
        $this->session->set_flashdata('flash_message' , get_phrase('session_changed')); 
        redirect(base_url() . 'index.php?admin/dashboard/', 'refresh'); 
    }

    /*****SMS SETTINGS*********/
    function sms_settings($param1 = '' , $param2 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url() . 'index.php?login', 'refresh');
        if ($param1 == 'clickatell') {

            $data['description'] = $this->input->post('clickatell_user');
            $this->db->where('type' , 'clickatell_user');
            $this->db->update('settings' , $data);

            $data['description'] = $this->input->post('clickatell_password');
            $this->db->where('type' , 'clickatell_password');
            $this->db->update('settings' , $data);

            $data['description'] = $this->input->post('clickatell_api_id');
            $this->db->where('type' , 'clickatell_api_id');
            $this->db->update('settings' , $data);

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/sms_settings/', 'refresh');
        }

        if ($param1 == 'twilio') {

            $data['description'] = $this->input->post('twilio_account_sid');
            $this->db->where('type' , 'twilio_account_sid');
            $this->db->update('settings' , $data);

            $data['description'] = $this->input->post('twilio_auth_token');
            $this->db->where('type' , 'twilio_auth_token');
            $this->db->update('settings' , $data);

            $data['description'] = $this->input->post('twilio_sender_phone_number');
            $this->db->where('type' , 'twilio_sender_phone_number');
            $this->db->update('settings' , $data);

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/sms_settings/', 'refresh');
        }

        if ($param1 == 'active_service') {

            $data['description'] = $this->input->post('active_sms_service');
            $this->db->where('type' , 'active_sms_service');
            $this->db->update('settings' , $data);

            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?admin/sms_settings/', 'refresh');
        }

        $page_data['page_name']  = 'sms_settings';
        $page_data['page_title'] = get_phrase('sms_settings');
        $page_data['settings']   = $this->db->get('settings')->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /*****LANGUAGE SETTINGS*********/
    function manage_language($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
			redirect(base_url() . 'index.php?login', 'refresh');
		
		if ($param1 == 'edit_phrase') {
			$page_data['edit_profile'] 	= $param2;	
		}
		if ($param1 == 'update_phrase') {
			$language	=	$param2;
			$total_phrase	=	$this->input->post('total_phrase');
			for($i = 1 ; $i < $total_phrase ; $i++)
			{
				//$data[$language]	=	$this->input->post('phrase').$i;
				$this->db->where('phrase_id' , $i);
				$this->db->update('language' , array($language => $this->input->post('phrase'.$i)));
			}
			redirect(base_url() . 'index.php?admin/manage_language/edit_phrase/'.$language, 'refresh');
		}
		if ($param1 == 'do_update') {
			$language        = $this->input->post('language');
			$data[$language] = $this->input->post('phrase');
			$this->db->where('phrase_id', $param2);
			$this->db->update('language', $data);
			$this->session->set_flashdata('flash_message', get_phrase('settings_updated'));
			redirect(base_url() . 'index.php?admin/manage_language/', 'refresh');
		}
		if ($param1 == 'add_phrase') {
			$data['phrase'] = $this->input->post('phrase');
			$this->db->insert('language', $data);
			$this->session->set_flashdata('flash_message', get_phrase('settings_updated'));
			redirect(base_url() . 'index.php?admin/manage_language/', 'refresh');
		}
		if ($param1 == 'add_language') {
			$language = $this->input->post('language');
			$this->load->dbforge();
			$fields = array(
				$language => array(
					'type' => 'LONGTEXT'
				)
			);
			$this->dbforge->add_column('language', $fields);
			
			$this->session->set_flashdata('flash_message', get_phrase('settings_updated'));
			redirect(base_url() . 'index.php?admin/manage_language/', 'refresh');
		}
		if ($param1 == 'delete_language') {
			$language = $param2;
			$this->load->dbforge();
			$this->dbforge->drop_column('language', $language);
			$this->session->set_flashdata('flash_message', get_phrase('settings_updated'));
			
			redirect(base_url() . 'index.php?admin/manage_language/', 'refresh');
		}
		$page_data['page_name']        = 'manage_language';
		$page_data['page_title']       = get_phrase('manage_language');
        //$page_data['language_phrases'] = $this->db->get('language')->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);	
    }
    
    /*****BACKUP / RESTORE / DELETE DATA PAGE**********/
    function backup_restore($operation = '', $type = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($operation == 'create') {
            $this->crud_model->create_backup($type);
        }
        if ($operation == 'restore') {
            $this->crud_model->restore_backup();
            $this->session->set_flashdata('backup_message', 'Backup Restored');
            redirect(base_url() . 'index.php?admin/backup_restore/', 'refresh');
        }
        if ($operation == 'delete') {
            $this->crud_model->truncate($type);
            $this->session->set_flashdata('backup_message', 'Data removed');
            redirect(base_url() . 'index.php?admin/backup_restore/', 'refresh');
        }
        
        $page_data['page_info']  = 'Create backup / restore from backup';
        $page_data['page_name']  = 'backup_restore';
        $page_data['page_title'] = get_phrase('manage_backup_restore');
        $this->load->view('backend/index', $page_data);
    }
    
    /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1)
            redirect(base_url() . 'index.php?login', 'refresh');
        if ($param1 == 'update_profile_info') {
            $data['name']  = $this->input->post('name');
            $data_login['email'] = $this->input->post('email');
            
            $this->db->trans_start();
            $this->db->where('user_id', $this->session->userdata('admin_id'));
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->where('account',1);
            $this->db->update('credentials', $data_login);
            $this->db->where('admin_id', $this->session->userdata('admin_id'));
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->update('admin',$data);
            $this->db->trans_complete();

            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/admin_image') === false){
                mkdir('uploads/'.$school_name.'/admin_image');
            }


            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/admin_image/' . $this->session->userdata('admin_id') . '.jpg');
            $this->session->set_flashdata('flash_message', get_phrase('account_updated'));
            redirect(base_url() . 'index.php?admin/manage_profile/', 'refresh');
        }
        if ($param1 == 'change_password') {
            $data['password']             = $this->input->post('password');
            $data['new_password']         = $this->input->post('new_password');
            $data['confirm_new_password'] = $this->input->post('confirm_new_password');
            


            $current_password = $this->db->get_where('credentials', array(
                'user_id' => $this->session->userdata('admin_id'),
                'school_id' => $this->session->userdata('school'),
                'account'   => 1
            ))->row()->password;
            if ($current_password == substr(sha1($data['password']),0,10) && $data['new_password'] == $data['confirm_new_password']) {
                $this->db->where('user_id', $this->session->userdata('admin_id'));
                $this->db->where('school_id', $this->session->userdata('school'));
                $this->db->update('credentials', array(
                    'password' => substr(sha1($data['new_password']),0,10)
                ));
                $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
            } else {
                $this->session->set_flashdata('flash_message', get_phrase('password_mismatch'));
            }
            redirect(base_url() . 'index.php?admin/manage_profile/', 'refresh');
        }
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = get_phrase('manage_profile');
        $page_data['name']  = $this->db->get_where('admin', array(
            'admin_id' => $this->session->userdata('admin_id')
        ))->row()->name;
        $page_data['email']  = $this->db->get_where('credentials', array(
            'user_id' => $this->session->userdata('admin_id'),
            'account' => 1
        ))->row()->email;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }

    function check_email($email)
    {
        $email_decode = urldecode($email);
        $resp = $this->db->get_where('credentials',array('email'=>$email_decode));
            if($resp->num_rows() > 0){
                $response['status'] = 'invalid';
                
            }else{
              
                if($email_decode == ''){
                    $email_decode  = substr(md5( rand()),0,7)."@email.com";
                    $response['data']   =   $email_decode;
                    $response['status'] = 'invalid';
                }else{
                $response['status'] = 'valid'; 
                }
            }

        echo json_encode($response);
    }

    function check_email_update($email,$id,$account)
    {
        $email_decode = urldecode($email);

        $id_decode = urldecode($id);


        $resp = $this->db->get_where('credentials',array('email'=>$email_decode,'user_id'=>$id_decode,'account'=>$account));
            if($resp->num_rows() > 0){
                $response['status'] = 'valid';//the email is correct and for the current user
                
            }else{
                $resp = $this->db->get_where('credentials',array('email'=>$email_decode));
                if($resp->num_rows() > 0){
                    $response['status'] = 'invalid';//the email is correct but exists
                }else{
                $response['status'] = 'valid'; //the email is valid for use
                }
            }

        echo json_encode($response);
    }
    
}
