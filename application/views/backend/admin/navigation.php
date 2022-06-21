<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

     <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
 
   
    <div class="collapse navbar-collapse navbar-ex1-collapse" id="navbarToggler">
     <div class="col-lg-1">
           
     </div>

    <div class="col-lg-9">	
        <ul id="main-menu" class="navbar-nav" style="list-style:none">
            <!-- add class "multiple-expanded" to allow multiple submenus to open -->
            <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->


            <!-- DASHBOARD -->
            <li class="nav-item <?php if ($page_name == 'dashboard') echo 'active'; ?> ">
                <a class="nav-link" href="<?php echo base_url(); ?>index.php?admin/dashboard">
                    <i class="entypo-gauge"></i>
                    <span><?php echo ucwords(get_phrase('dashboard')); ?></span>
                </a>
            </li>

            <!-- STUDENT -->
            <li class="nav-item dropdown <?php
            if ($page_name == 'student_add' ||
                    $page_name == 'student_bulk_add' ||
                    $page_name == 'student_information' ||
                    $page_name == 'student_marksheet')
                echo 'opened active has-sub';
            ?> ">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-group"></i> 
                    <span> <?php echo ucwords(get_phrase('student')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <!-- STUDENT ADMISSION -->
                    <li class="dropdown-item <?php if ($page_name == 'student_add') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/student_add">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('admit_student')); ?></span>
                        </a>
                    </li>

                    <!-- STUDENT BULK ADMISSION -->
                    <li class="dropdown-item <?php if ($page_name == 'student_bulk_add') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/student_bulk_add">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('admit_bulk_student')); ?></span>
                        </a>
                    </li>

                    <!-- STUDENT INFORMATION -->
                    <li class="dropdown-item has-submenu<?php if ($page_name == 'student_information') echo 'opened active'; ?> ">
                        <a href="#">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('student_information')); ?></span>
                        </a>
                        <ul>
                            <?php
                            $this->db->where('school_id',$this->session->userdata('school'));
                            $classes = $this->db->get('class')->result_array();
                            foreach ($classes as $row):
                                ?>
                                <li class="dropdown-content <?php if ($page_name == 'student_information' && $class_id == $row['class_id']) echo 'active'; ?>">
                                    <a href="<?php echo base_url(); ?>index.php?admin/student_information/<?php echo $row['class_id']; ?>">
                                        <span><?php echo ucwords(get_phrase('class')); ?> <?php echo $row['name']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- STUDENT MARKSHEET -->
                    <li class="dropdown-item has-submenu <?php if ($page_name == 'student_marksheet') echo 'opened active'; ?> ">
                        <a href="#">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('student_marksheet')); ?></span>
                        </a>
                        <ul>
                            <?php
                           // $classes = $this->db->get('class')->result_array();
                            foreach ($classes as $row):
                                ?>
                                <li class="dropdown-content <?php if ($page_name == 'student_marksheet' && $class_id == $row['class_id']) echo 'active'; ?>">
                                    <a href="<?php echo base_url(); ?>index.php?admin/student_marksheet/<?php echo $row['class_id']; ?>">
                                        <span><?php echo ucwords(get_phrase('class')); ?> <?php echo $row['name']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <!-- STUDENT PROMOTION -->
                <li class="<?php if ($page_name == 'student_promotion') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/student_promotion">
                            <span><i class="entypo-dot"></i> <?php echo get_phrase('student_promotion'); ?></span>
                        </a>
                    </li>

                </ul>
            </li>

            <!-- TEACHER -->
            <li class="nav-item <?php if ($page_name == 'teacher') echo 'active'; ?> ">
                <a href="<?php echo base_url(); ?>index.php?admin/teacher">
                    <i class="entypo-users"></i>
                    <span><?php echo ucwords(get_phrase('teacher')); ?></span>
                </a>
            </li>

            <!-- PARENTS -->
            <li class="nav-item <?php if ($page_name == 'parent') echo 'active'; ?> ">
                <a href="<?php echo base_url(); ?>index.php?admin/parent">
                    <i class="entypo-user"></i>
                    <span><?php echo ucwords(get_phrase('parents')); ?></span>
                </a>
            </li>

            <!-- CLASS -->
            <li class="nav-item dropdown <?php
            if ($page_name == 'class' ||
                    $page_name == 'section')
                echo 'opened active';
            ?> ">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-flow-tree"></i>
                    <span><?php echo ucwords(get_phrase('class')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li class="dropdown-item <?php if ($page_name == 'class') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/classes">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('manage_classes')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'section') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/section">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('manage_sections')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'academic_syllabus') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?admin/academic_syllabus">
                        <span><i class="entypo-dot"></i> <?php echo get_phrase('academic_syllabus'); ?></span>
                    </a>
                </li>
                </ul>
            </li>

            <!-- SUBJECT -->
            <li class="nav-item dropdown <?php if ($page_name == 'subject') echo 'opened active'; ?> ">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-docs"></i>
                    <span><?php echo ucwords(get_phrase('subject')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php
                   // $classes = $this->db->get('class')->result_array();
                    foreach ($classes as $row):
                        ?>
                        <li class="dropdown-item <?php if ($page_name == 'subject' && $class_id == $row['class_id']) echo 'active'; ?>">
                            <a href="<?php echo base_url(); ?>index.php?admin/subject/<?php echo $row['class_id']; ?>">
                                <span><?php echo ucwords(get_phrase('class')); ?> <?php echo $row['name']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- CLASS ROUTINE -->
 
            <li class="nav-item dropdown <?php if ($page_name == 'class_routine') echo 'opened active'; ?> ">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-docs"></i>
                    <span><?php echo ucwords(get_phrase('Time_Table')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php
                    foreach ($classes as $row):
                        ?>
                        <li class="dropdown-item <?php if ($page_name == 'class_routine' && $class_id == $row['class_id']) echo 'active'; ?>">
                            <a href="<?php echo base_url(); ?>index.php?admin/class_routine/<?php echo $row['class_id']; ?>">
                                <span><?php echo ucwords(get_phrase('class')); ?> <?php echo $row['name']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- DAILY ATTENDANCE -->
            <li class="nav-item dropdown <?php if ($page_name == 'manage_attendance') echo 'active'; ?> ">

             <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-chart-area"></i>
                    <span><?php echo ucwords(get_phrase('daily_attendance')); ?><i class="entypo-right"></i></span>
                </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li class="dropdown-item <?php if ($page_name == 'manage_attendance') echo 'active'; ?>">
                    <a href=" <?php echo base_url(); ?>index.php?admin/manage_attendance/<?php echo date("d/m/Y"); ?>">
                        <i class="entypo-chart-area"></i>
                        <span><?php echo ucwords(get_phrase('manage_attendance')); ?></span>
                    </a> 
                 </li> 

                 <li class="dropdown-item <?php if ($page_name == 'attendance_report') echo 'active'; ?>">
                    <a href=" <?php echo base_url(); ?>index.php?admin/attendance_report/">
                        <i class="entypo-chart-area"></i>
                        <span><?php echo ucwords(get_phrase('attendance_report')); ?></span>
                    </a> 
                 </li>       
            </ul>
                
            </li>

            <!-- EXAMS -->
            <li class="nav-item dropdown <?php
            if ($page_name == 'exam' ||
                    $page_name == 'grade' ||
                    $page_name == 'marks' ||
                        $page_name == 'exam_marks_sms')
                            echo 'opened active';
            ?> ">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-graduation-cap"></i>
                    <span><?php echo ucwords(get_phrase('exam')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li class="dropdown-item <?php if ($page_name == 'exam') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/exam">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('exam_list')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'grade') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/grade">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('exam_grades')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'marks') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/marks_manage">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('manage_marks')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'exam_marks_sms') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/exam_marks_sms">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('send_marks_by_sms')); ?></span>
                        </a>
                    </li>
                </ul>
            </li>
         

            <!-- DORMITORY -->
            <li class="nav-item <?php if ($page_name == 'dormitory') echo 'active'; ?> ">
                <a href="<?php echo base_url(); ?>index.php?admin/dormitory">
                    <i class="entypo-home"></i>
                    <span><?php echo ucwords(get_phrase('dormitory')); ?></span>
                </a>
            </li>

            <!-- NOTICEBOARD -->
            <li class="nav-item <?php if ($page_name == 'noticeboard') echo 'active'; ?> ">
                <a href="<?php echo base_url(); ?>index.php?admin/noticeboard">
                    <i class="entypo-doc-text-inv"></i>
                    <span><?php echo ucwords(get_phrase('noticeboard')); ?></span>
                </a>
            </li>

            <!-- MESSAGE -->
            <li class="nav-item <?php if ($page_name == 'message') echo 'active'; ?> ">
                <a href="<?php echo base_url(); ?>index.php?admin/message">
                    <i class="entypo-mail"></i>
                    <span><?php echo ucwords(get_phrase('message')); ?></span>
                </a>
            </li>

            <!-- SETTINGS -->
            <li class="nav-item dropdown <?php
            if ($page_name == 'system_settings' ||
                    $page_name == 'manage_language' ||
                        $page_name == 'sms_settings')
                            echo 'opened active';
            ?> ">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="entypo-lifebuoy"></i>
                    <span><?php echo ucwords(get_phrase('settings')); ?><i class="entypo-right"></i></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li class="dropdown-item <?php if ($page_name == 'system_settings') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/system_settings">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('general_settings')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'data_settings') echo 'active'; ?> ">
                        <!--<a href="<?php// echo base_url(); ?>index.php?admin/data_settings">-->
                        <a href="#" onclick="showAjaxModal('<?php echo base_url();?>index.php?modal/popup/modal_data/');">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('data_settings')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'sms_settings') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/sms_settings">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('sms_settings')); ?></span>
                        </a>
                    </li>
                    <li class="dropdown-item <?php if ($page_name == 'manage_language') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?admin/manage_language">
                            <span><i class="entypo-dot"></i> <?php echo ucwords(get_phrase('language_settings')); ?></span>
                        </a>
                    </li>
                </ul>
            </li>

     

        </ul>

         </div>

     
<div class="col-lg-2 clearfix profileLink">
  
  <ul class="list-inline pull-right">
  <!-- Language Selector -->			
     <li class="dropdown language-selector">

              <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">
                      <i class="entypo-user"></i> <?php echo $this->session->userdata('login_type');?>
              </a>

          <?php if ($account_type != 'parent'):?>
          <ul class="dropdown-menu <?php if ($text_align == 'right-to-left') echo 'pull-right'; else echo 'pull-left';?>">
              <li>
                  <a href="<?php echo base_url();?>index.php?<?php echo $account_type;?>/manage_profile">
                      <i class="entypo-info"></i>
                      <span><?php echo get_phrase('edit_profile');?></span>
                  </a>
              </li>
              <li>
                  <a href="<?php echo base_url();?>index.php?<?php echo $account_type;?>/manage_profile">
                      <i class="entypo-key"></i>
                      <span><?php echo get_phrase('change_password');?></span>
                  </a>
              </li>
          </ul>
          <?php endif;?>
          <!-- logout button -->
          <li style="list-style:none" class="pull-right">
                      <a class="pull-right" href="<?php echo base_url();?>index.php?login/logout">
                          Log Out <i class="entypo-logout "></i>
                      </a>
          </li>
          <?php if ($account_type == 'parent'):?>
          <ul class="dropdown-menu <?php if ($text_align == 'right-to-left') echo 'pull-right'; else echo 'pull-left';?>">
              <li>
                  <a href="<?php echo base_url();?>index.php?parents/manage_profile">
                      <i class="entypo-info"></i>
                      <span><?php echo get_phrase('edit_profile');?></span>
                  </a>
              </li>
              <li>
                  <a href="<?php echo base_url();?>index.php?parents/manage_profile">
                      <i class="entypo-key"></i>
                      <span><?php echo get_phrase('change_password');?></span>
                  </a>
              </li>
          </ul>
          <?php endif;?>

          <div id="session_static">			
	           
               <h4>
                   <a href="#" style="color: #acabab;"
                       <?php if($this->session->userdata('login_type') == 'admin'):?> 
                       onclick="get_session_changer()"
                   <?php endif;?>>
                       <?php echo get_phrase('running_session');?> : <?php echo $this->session->userdata('running_year');?>
                   </a>
               </h4>
      
              </div>
      </li>
  </ul>
    </div>
    </div>
</nav>