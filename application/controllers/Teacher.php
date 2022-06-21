<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*	
 *	@author : Zinnia Tech
 *	date	: 13 June, 2018
 *
 */

class Teacher extends CI_Controller
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
    
    /***default functin, redirects to login page if no teacher logged in yet***/
    public function index()
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url() . 'index.php?login', 'refresh');
        if ($this->session->userdata('teacher_login') == 1)
            redirect(base_url() . 'index.php?teacher/dashboard', 'refresh');
    }
    
    /***TEACHER DASHBOARD***/
    function dashboard()
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        $page_data['page_name']  = 'dashboard';
        $page_data['page_title'] = get_phrase('teacher_dashboard');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    
    /*ENTRY OF A NEW STUDENT*/
    
    
    /****MANAGE STUDENTS CLASSWISE*****/
    function student_add()
	{
		if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
			
		$page_data['page_name']  = 'student_add';
        $page_data['page_title'] = get_phrase('add_student');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
	}
	
	function student_information($class_id = '')
	{
		if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
			
		$page_data['page_name']  	= 'student_information';
		$page_data['page_title'] 	= get_phrase('student_information'). " - ".get_phrase('class')." : ".
											$this->crud_model->get_class_name($class_id);
        $page_data['class_id'] 	= $class_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
	}
	
	function student_marksheet($class_id = '')
	{
		if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
			
		$page_data['page_name']  = 'student_marksheet';
		$page_data['page_title'] 	= get_phrase('student_marksheet'). " - ".get_phrase('class')." : ".
											$this->crud_model->get_class_name($class_id);
        $page_data['class_id'] 	= $class_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
		$this->load->view('backend/index', $page_data);
    }

    function student_marksheet_print_view($student_id , $exam_id) {
        $student_id = urldecode($student_id);
        if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
        $class_id     = $this->db->get_where('enroll' , array(
            'student_id' => $student_id , 'year' => $this->session->userdata('running_year')
        ))->row()->class_id;
        $class_name   = $this->db->get_where('class' , array('class_id' => $class_id))->row()->name;

        $page_data['student_id'] =   $student_id;
        $page_data['class_id']   =   $class_id;
        $page_data['exam_id']    =   $exam_id;
        $this->load->view('backend/teacher/student_marksheet_print_view', $page_data);
    }
    
    function modal_student_marksheet($student_id = '')
    {
      
         if ($this->session->userdata('teacher_login') != 1)
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
	
    function student($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
            if ($param1 == 'create') {
                $this->db->where('email', $this->input->post('email'));
                 $resp = $this->db->get('student');
                
                if($resp->num_rows() <= 0){
                /*one default password created for all students */
                    
                    $password = substr(md5('Student123') , 0,8);

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
                if($this->input->post('email') != ''){
                    $data['email']      = $this->input->post('email');
                }else{
                    
                    $data['email']  = substr(md5( rand()),0,7)."@email.com";  
                }
                $data['password']   = $password;
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
    
                $this->db->trans_start();
                $this->db->insert('student', $data);
                $this->db->insert('enroll', $data2);
                $this->db->trans_complete();
                $this->db->where('id',$this->session->userdata('school'));
                $school_name  = $this->db->get('s_settings')->row()->system_name;

                if(is_dir('uploads/'.$school_name.'/student_image') === false){
                    mkdir('uploads/'.$school_name.'/student_image');
                }


                if($this->db->trans_status()===true){
                    $this->session->set_flashdata('flash_message' , get_phrase('data_added_successfully'));
                    $this->email_model->account_opening_email('student', $data['email']); //SEND EMAIL ACCOUNT OPENING EMAIL
                    redirect(base_url() . 'index.php?teacher/student_add/' . $data['class_id'], 'refresh');
                    }else{
                        $this->session->set_flashdata('flash_message' , get_phrase('Error_adding_student')); 
                        redirect(base_url() . 'index.php?teacher/student_add/' . $data['class_id'], 'refresh');
                    }
               }else{
                $this->session->set_flashdata('flash_message' , 'The Email Already Exists!');
                redirect(base_url() . 'index.php?teacher/student_add/' . $data['class_id'], 'refresh');
               }
            }
        if ($param2 == 'do_update') {
            $data['name']        = $this->input->post('name');
            $data['birthday']    = $this->input->post('birthday');
            $data['sex']         = $this->input->post('sex');
            $data['address']     = $this->input->post('address');
            $data['phone']       = $this->input->post('phone');
            $data['email']       = $this->input->post('email');
            $data['class_id']    = $this->input->post('class_id');
            $data['section_id']  = $this->input->post('section_id');
            $data['parent_id']   = $this->input->post('parent_id');
            $data['roll']        = $this->input->post('roll');
            $data['dormitory_id']  = $this->input->post('dormitory_id');
            $this->db->where('student_id', $student_id);
            $this->db->update('student', $data);


            $data2['section_id']    =   $this->input->post('section_id');
            $data2['roll']          =   $this->input->post('roll');
            $running_year = $this->session->userdata('school');
            $this->db->where('student_id' , $param3);
            $this->db->where('year' , $running_year);
            $this->db->update('enroll' , array(
                'section_id' => $data2['section_id'] , 'roll' => $data2['roll']
            ));

            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/student_image') === false){
                mkdir('uploads/'.$school_name.'/student_image');
            }


            if(!move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/student_image/' .$pic_id. '.jpg'))
            {
                $this->crud_model->clear_cache();
                $this->session->set_flashdata('flash_message' , get_phrase('data_updated_without_picture'));
                redirect(base_url() . 'index.php?teacher/student_information/' . $param1, 'refresh');
            }
            //move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/student_image/' . $param3 . '.jpg');
            $this->crud_model->clear_cache();
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated_successfully'));
            redirect(base_url() . 'index.php?teacher/student_information/' . $param1, 'refresh');
        } 
		
        if ($param2 == 'delete') {
            $this->db->where('student_id', $param3);
            $this->db->delete('student');
            redirect(base_url() . 'index.php?teacher/student_information/' . $param1, 'refresh');
        }
    }

    function get_class_section($class_id)
    {
        $sections = $this->db->get_where('section' , array(
            'class_id' => $class_id
        ))->result_array();
        foreach ($sections as $row) {
            echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
        }
    }
    
    /****MANAGE TEACHERS*****/
    function teacher_list($param1 = '', $param2 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($param1 == 'personal_profile') {
            $page_data['personal_profile']   = true;
            $page_data['current_teacher_id'] = $param2;
        }
        $page_data['teachers']   = $this->db->get_where('teacher',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'teacher';
        $page_data['page_title'] = get_phrase('teacher_list');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    
    
    /****MANAGE SUBJECTS*****/
    function subject($param1 = '', $param2 = '' , $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        if ($param1 == 'create') {

            $data['name']       = $this->input->post('name');
            $data['class_id']   = $this->input->post('class_id');
            $data['teacher_id'] = $this->input->post('teacher_id');
            $data['school_id']  =   $this->session->userdata('school');

            $this->db->insert('subject', $data);
            redirect(base_url() . 'index.php?teacher/subject/'.$data['class_id'], 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['name']       = $this->input->post('name');
            $data['class_id']   = $this->input->post('class_id');
            $data['teacher_id'] = $this->input->post('teacher_id');
            
            $this->db->where('subject_id', $param2);
            $this->db->update('subject', $data);
            redirect(base_url() . 'index.php?teacher/subject/'.$data['class_id'], 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('subject', array(
                'school_id' =>  $this->session->userdata('school'),
                'subject_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('subject_id', $param2);
            $this->db->delete('subject');
            redirect(base_url() . 'index.php?teacher/subject/'.$param3, 'refresh');
        }
		 $page_data['class_id']   = $param1;
        $page_data['subjects']   = $this->db->get_where('subject' , array('school_id'=>$this->session->userdata('school'),'class_id' => $param1))->result_array();
        $page_data['page_name']  = 'subject';
        $page_data['page_title'] = get_phrase('manage_subject');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    
    
    /****MANAGE EXAM MARKS*****/

    function marks($exam_id = '', $class_id = '', $subject_id = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($this->input->post('operation') == 'selection') {
            $page_data['exam_id']    = $this->input->post('exam_id');
            $page_data['class_id']   = $this->input->post('class_id');
            $page_data['subject_id'] = $this->input->post('subject_id');
            
            if ($page_data['exam_id'] > 0 && $page_data['class_id'] > 0 && $page_data['subject_id'] > 0) {
                redirect(base_url() . 'index.php?teacher/marks/' . $page_data['exam_id'] . '/' . $page_data['class_id'] . '/' . $page_data['subject_id'], 'refresh');
            } else {
                $this->session->set_flashdata('mark_message', 'Choose exam, class and subject');
                redirect(base_url() . 'index.php?teacher/marks/', 'refresh');
            }
        }
        if ($this->input->post('operation') == 'update') {
            $data['mark_obtained'] = $this->input->post('mark_obtained');
            $data['comment']       = $this->input->post('comment');
            
            $this->db->where('mark_id', $this->input->post('mark_id'));
            $this->db->where('school_id',$this->session->userdata('school'));
            $this->db->update('mark', $data);
            $this->session->set_flashdata('flash_message' , get_phrase('data_updated'));
            redirect(base_url() . 'index.php?teacher/marks/' . $this->input->post('exam_id') . '/' . $this->input->post('class_id') . '/' . $this->input->post('subject_id'), 'refresh');
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
        if ($this->session->userdata('teacher_login') != 1)
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
        if ($this->session->userdata('teacher_login') != 1)
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
        redirect(base_url() . 'index.php?teacher/marks_manage_view/' . $data['exam_id'] . '/' . $data['class_id'] . '/' . $data['section_id'] . '/' . $data['subject_id'] , 'refresh');
        
    }

    function marks_manage_view($exam_id = '' , $class_id = '' , $section_id = '' , $subject_id = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
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
        redirect(base_url().'index.php?teacher/marks_manage_view/'.$exam_id.'/'.$class_id.'/'.$section_id.'/'.$subject_id , 'refresh');
    }

    function marks_get_subject($class_id)
    {
        $page_data['class_id'] = $class_id;
        $this->load->view('backend/teacher/marks_get_subject' , $page_data);
    }


    // function marks($exam_id = '', $class_id = '', $subject_id = '')
    // {
    //     if ($this->session->userdata('teacher_login') != 1)
    //         redirect(base_url(), 'refresh');
        
    //     if ($this->input->post('operation') == 'selection') {
    //         $page_data['exam_id']    = $this->input->post('exam_id');
    //         $page_data['class_id']   = $this->input->post('class_id');
    //         $page_data['subject_id'] = $this->input->post('subject_id');
            
    //         if ($page_data['exam_id'] > 0 && $page_data['class_id'] > 0 && $page_data['subject_id'] > 0) {
    //             redirect(base_url() . 'index.php?teacher/marks/' . $page_data['exam_id'] . '/' . $page_data['class_id'] . '/' . $page_data['subject_id'], 'refresh');
    //         } else {
    //             $this->session->set_flashdata('mark_message', 'Choose exam, class and subject');
    //             redirect(base_url() . 'index.php?teacher/marks/', 'refresh');
    //         }
    //     }
    //     if ($this->input->post('operation') == 'update') {
    //         $data['mark_obtained'] = $this->input->post('mark_obtained');
    //         $data['attendance']    = $this->input->post('attendance');
    //         $data['comment']       = $this->input->post('comment');
            
    //         $this->db->where('mark_id', $this->input->post('mark_id'));
    //         $this->db->update('mark', $data);
            
    //         redirect(base_url() . 'index.php?teacher/marks/' . $this->input->post('exam_id') . '/' . $this->input->post('class_id') . '/' . $this->input->post('subject_id'), 'refresh');
    //     }
    //     $page_data['exam_id']    = $exam_id;
    //     $page_data['class_id']   = $class_id;
    //     $page_data['subject_id'] = $subject_id;
        
    //     $page_data['page_info'] = 'Exam marks';
        
    //     $page_data['page_name']  = 'marks';
    //     $page_data['page_title'] = get_phrase('manage_exam_marks');
    //     $this->db->where('id',$this->session->userdata('school'));
    //     $page_data['settings']   = $this->db->get('s_settings');
    //     $this->load->view('backend/index', $page_data);
    // }
    
    /*****BACKUP / RESTORE / DELETE DATA PAGE**********/
    function backup_restore($operation = '', $type = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($operation == 'create') {
            $this->crud_model->create_backup($type);
        }
        if ($operation == 'restore') {
            $this->crud_model->restore_backup();
            $this->session->set_flashdata('backup_message', 'Backup Restored');
            redirect(base_url() . 'index.php?teacher/backup_restore/', 'refresh');
        }
        if ($operation == 'delete') {
            $this->crud_model->truncate($type);
            $this->session->set_flashdata('backup_message', 'Data removed');
            redirect(base_url() . 'index.php?teacher/backup_restore/', 'refresh');
        }
        
        $page_data['page_info']  = 'Create backup / restore from backup';
        $page_data['page_name']  = 'backup_restore';
        $page_data['page_title'] = get_phrase('manage_backup_restore');
        $this->load->view('backend/index', $page_data);
    }
    
    /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url() . 'index.php?login', 'refresh');
        if ($param1 == 'update_profile_info') {
          //  $data['name']        = $this->input->post('name');
           // $data['email']       = $this->input->post('email');
            
            $data['name']  = $this->input->post('name');
            $data_login['email'] = $this->input->post('email');
            
            $this->db->trans_start();
            $this->db->where('user_id', $this->session->userdata('teacher_id'));
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->where('account',2);
            $this->db->update('credentials', $data_login);
            $this->db->where('teacher_id', $this->session->userdata('teacher_id'));
            $this->db->where('school_id', $this->session->userdata('school'));
            $this->db->update('teacher',$data);
            $this->db->trans_complete();


          //  $this->db->where('teacher_id', $this->session->userdata('teacher_id'));
           // $this->db->update('teacher', $data);


            $this->db->where('id',$this->session->userdata('school'));
            $school_name  = $this->db->get('s_settings')->row()->system_name;

            if(is_dir('uploads/'.$school_name.'/teacher_image') === false){
                mkdir('uploads/'.$school_name.'/teacher_image');
            }

            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/'.$school_name.'/teacher_image/' . $this->session->userdata('teacher_id') . '.jpg');
            $this->session->set_flashdata('flash_message', get_phrase('account_updated'));
            redirect(base_url() . 'index.php?teacher/manage_profile/', 'refresh');
        }
        if ($param1 == 'change_password') {
            $data['password']             = $this->input->post('password');
            $data['new_password']         = $this->input->post('new_password');
            $data['confirm_new_password'] = $this->input->post('confirm_new_password');
            
            $current_password = $this->db->get_where('credentials', array(
                'user_id' => $this->session->userdata('teacher_id'),
                'account'    => 2,
                'school_id'  => $this->session->userdata('school')

            ))->row()->password;
            if ($current_password == substr(sha1($data['password']),0,10) && $data['new_password'] == $data['confirm_new_password']) {
                $this->db->where('user_id', $this->session->userdata('teacher_id'));
                $this->db->where('school_id', $this->session->userdata('school'));
                $this->db->where('account',2);
               
                $this->db->update('credentials', array(
                    'password' => substr(sha1($data['new_password']),0,10)
                ));
                $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
            } else {
                $this->session->set_flashdata('flash_message', get_phrase('password_mismatch'));
            }
            redirect(base_url() . 'index.php?teacher/manage_profile/', 'refresh');
        }
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = get_phrase('manage_profile');
        $page_data['name']  = $this->db->get_where('teacher', array(
            'teacher_id' => $this->session->userdata('teacher_id')
        ))->row()->name;
        $page_data['email']  = $this->db->get_where('credentials', array(
            'user_id' => $this->session->userdata('teacher_id'),
            'account' => 2
        ))->row()->email;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    /**********MANAGING CLASS ROUTINE******************/
    function class_routine($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
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
        redirect(base_url() . 'index.php?teacher/class_routine/'.$data['class_id'], 'refresh');

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
        redirect(base_url() . 'index.php?teacher/class_routine/'.$data['class_id'], 'refresh');
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
        redirect(base_url() . 'index.php?teacher/class_routine/'.$param3, 'refresh');
    }
    $page_data['class_id']  =   $param1; //this only applies if the link in navbar is just clicked
    $page_data['page_name']  = 'class_routine';
    $page_data['page_title'] = get_phrase('manage_class_routine');
    $this->db->where('id',$this->session->userdata('school'));
    $page_data['settings']   = $this->db->get('s_settings');
    $this->load->view('backend/index', $page_data);
    }

    function section_subject_edit($class_id , $class_routine_id)
    {
        $page_data['class_id']          =   $class_id;
        $page_data['class_routine_id']  =   $class_routine_id;
        $this->load->view('backend/teacher/class_routine_section_subject_edit' , $page_data);
    }

    function class_routine_print_view($class_id , $section_id)
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
        $page_data['class_id']   =   $class_id;
        $page_data['section_id'] =   $section_id;
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/teacher/class_routine_print_view' , $page_data);
    }
	
	
    /****** DAILY ATTENDANCE *****************/
    

    function manage_attendance($date='',$month='',$year='',$class_id='' , $section_id = '' , $session = '')
    {
        if($this->session->userdata('teacher_login')!=1)
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
            redirect(base_url() . 'index.php?teacher/manage_attendance/'.$date.'/'.$month.'/'.$year.'/'.$class_id.'/'.$section_id.'/'.$session , 'refresh');
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
        if($this->session->userdata('teacher_login')!=1)
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

        redirect(base_url() . 'index.php?teacher/manage_attendance/'.$this->input->post('date').'/'.
                    $this->input->post('month').'/'.
                        $this->input->post('year').'/'.
                            $this->input->post('class_id').'/'.
                                          $sec_id.'/'.
                                    $this->input->post('session') , 'refresh');
    }
   
    
    /**********MANAGE LIBRARY / BOOKS********************/
    function book($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
        
        $page_data['books']      = $this->db->get_where('book',array('school_id'=>$this->session->userdata('school')))->result_array();
        $page_data['page_name']  = 'book';
        $page_data['page_title'] = get_phrase('manage_library_books');
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
        
    }
   
    
    /***MANAGE EVENT / NOTICEBOARD, WILL BE SEEN BY ALL ACCOUNTS DASHBOARD**/
    function noticeboard($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect(base_url(), 'refresh');
        
        if ($param1 == 'create') {
            $data['notice_title']     = $this->input->post('notice_title');
            $data['notice']           = $this->input->post('notice');
            $data['create_timestamp'] = strtotime($this->input->post('create_timestamp'));
            $this->db->insert('noticeboard', $data);
            redirect(base_url() . 'index.php?teacher/noticeboard/', 'refresh');
        }
        if ($param1 == 'do_update') {
            $data['notice_title']     = $this->input->post('notice_title');
            $data['notice']           = $this->input->post('notice');
            $data['create_timestamp'] = strtotime($this->input->post('create_timestamp'));
            $this->db->where('notice_id', $param2);
            $this->db->update('noticeboard', $data);
            $this->session->set_flashdata('flash_message', get_phrase('notice_updated'));
            redirect(base_url() . 'index.php?teacher/noticeboard/', 'refresh');
        } else if ($param1 == 'edit') {
            $page_data['edit_data'] = $this->db->get_where('noticeboard', array(
                'school_id' => $this->session->userdata('school'),
                'notice_id' => $param2
            ))->result_array();
        }
        if ($param1 == 'delete') {
            $this->db->where('notice_id', $param2);
            $this->db->delete('noticeboard');
            redirect(base_url() . 'index.php?teacher/noticeboard/', 'refresh');
        }
        $page_data['page_name']  = 'noticeboard';
        $page_data['page_title'] = get_phrase('manage_noticeboard');
        $page_data['notices']    = $this->db->get_where('noticeboard',array('school_id'=>$this->session->userdata('school')))->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }
    
    
    /**********MANAGE DOCUMENT / home work FOR A SPECIFIC CLASS or ALL*******************/
    function document($do = '', $document_id = '')
    {
        if ($this->session->userdata('teacher_login') != 1)
            redirect('login', 'refresh');
        if ($do == 'upload') {
            move_uploaded_file($_FILES["userfile"]["tmp_name"], "uploads/document/" . $_FILES["userfile"]["name"]);
            $data['document_name'] = $this->input->post('document_name');
            $data['file_name']     = $_FILES["userfile"]["name"];
            $data['file_size']     = $_FILES["userfile"]["size"];
            $this->db->insert('document', $data);
            redirect(base_url() . 'teacher/manage_document', 'refresh');
        }
        if ($do == 'delete') {
            $this->db->where('document_id', $document_id);
            $this->db->delete('document');
            redirect(base_url() . 'teacher/manage_document', 'refresh');
        }
        $page_data['page_name']  = 'manage_document';
        $page_data['page_title'] = get_phrase('manage_documents');
        $page_data['documents']  = $this->db->get('document')->result_array();
        $this->db->where('id',$this->session->userdata('school'));
        $page_data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $page_data);
    }


       // ACADEMIC SYLLABUS
       function academic_syllabus($class_id = '')
       {
           if ($this->session->userdata('teacher_login') != 1)
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
  
       function download_academic_syllabus($academic_syllabus_code)
       {

        $this->db->where('id',$this->session->userdata('school'));
        $school_name  = $this->db->get('s_settings')->row()->system_name;

        if(is_dir('uploads/'.$school_name.'/syllabus') === false){
            mkdir('uploads/'.$school_name.'/syllabus');
        }

           $file_name = $this->db->get_where('academic_syllabus', array(
               'academic_syllabus_code' => $academic_syllabus_code
           ))->row()->file_name;
           $this->load->helper('download');
           $data = file_get_contents("uploads/".$school_name."/syllabus/" . $file_name);
           $name = $file_name;
   
           force_download($name, $data);
       }
   
    
    /*********MANAGE STUDY MATERIAL************/
    function study_material($task = "", $document_id = "")
    {
        if ($this->session->userdata('teacher_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }
                
        if ($task == "create")
        {
            $this->crud_model->save_study_material_info();
            $this->session->set_flashdata('flash_message' , get_phrase('study_material_info_saved_successfuly'));
            redirect(base_url() . 'index.php?teacher/study_material' , 'refresh');
        }
        
        if ($task == "update")
        {
            $this->crud_model->update_study_material_info($document_id);
            $this->session->set_flashdata('flash_message' , get_phrase('study_material_info_updated_successfuly'));
            redirect(base_url() . 'index.php?teacher/study_material' , 'refresh');
        }
        
        if ($task == "delete")
        {
            $this->crud_model->delete_study_material_info($document_id);
            redirect(base_url() . 'index.php?teacher/study_material');
        }
        
        $data['study_material_info']    = $this->crud_model->select_study_material_info();
        $data['page_name']              = 'study_material';
        $data['page_title']             = get_phrase('study_material');
        $this->db->where('id',$this->session->userdata('school'));
        $data['settings']   = $this->db->get('s_settings');
        $this->load->view('backend/index', $data);
    }
    
    /* private messaging */

    function message($param1 = 'message_home', $param2 = '', $param3 = '') {
        if ($this->session->userdata('teacher_login') != 1)
        {
            $this->session->set_userdata('last_page' , current_url());
            redirect(base_url(), 'refresh');
        }

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(base_url() . 'index.php?teacher/message/message_read/' . $message_thread_code, 'refresh');
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2);  //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(base_url() . 'index.php?teacher/message/message_read/' . $param2, 'refresh');
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