<!-- <div class="sidebar-menu"> -->
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
                        <a class="nav-link" href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/dashboard">
                            <i class="entypo-gauge"></i>
                            <span><?php echo get_phrase('dashboard'); ?></span>
                        </a>
                    </li>

                    <!-- STUDENT -->
                    <li class="nav-item dropdown <?php
                        if ($page_name == 'student_add' ||
                                $page_name == 'student_information' ||
                                $page_name == 'student_marksheet')
                            echo 'opened active has-sub';
                        ?> ">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-group"></i>
                            <span><?php echo get_phrase('student'); ?><i class="entypo-right"></i></span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <!-- STUDENT ADMISSION -->
                            <!-- <li class="dropdown-item <?php// if ($page_name == 'student_add') echo 'active'; ?> ">
                                <a href="<?php// echo base_url(); ?>index.php?<?php //echo $account_type; ?>/student_add">
                                    <span><i class="entypo-dot"></i> <?php// echo get_phrase('admit_student'); ?></span>
                                </a>
                            </li> -->

                            <!-- STUDENT INFORMATION -->
                            <li class="dropdown-item has-submenu <?php if ($page_name == 'student_information') echo 'opened active'; ?> ">
                                <a href="#">
                                    <span><i class="entypo-dot"></i> <?php echo get_phrase('student_information'); ?></span>
                                </a>
                                <ul class="">
                                <?php $this->db->where('school_id',$this->session->userdata('school'));
                                      $classes = $this->db->get('class')->result_array();
                                foreach ($classes as $row):
                                    ?>
                                        <li class="dropdown-content <?php if ($page_name == 'student_information' && $class_id == $row['class_id']) echo 'active'; ?>">
                                            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/student_information/<?php echo $row['class_id']; ?>">
                                                <span><?php echo get_phrase('class'); ?> <?php echo $row['name']; ?></span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                </ul>
                            </li>

                            <!-- STUDENT MARKSHEET -->
                            <li class="dropdown-item has-submenu<?php if ($page_name == 'student_marksheet') echo 'opened active'; ?> ">
                                <a href="#">
                            <!-- <a href="<?php// echo base_url(); ?>index.php?<?php// echo $account_type; ?>/student_marksheet/<?php// echo $row['class_id']; ?>"> -->
                                    <span><i class="entypo-dot"></i> <?php echo get_phrase('student_marksheet'); ?></span>
                                </a>
                                <ul>
                                    <?php //$classes = $this->db->get('class')->result_array();
                                    $myclasses = $this->db->get_where('class',array('teacher_id'=>$this->session->userdata('teacher_id'),'school_id'=>$this->session->userdata('school')))->result_array();

                                    foreach ($myclasses as $row):
                                        ?>
                                        <li class="dropdown-content <?php if ($page_name == 'student_marksheet' && $class_id == $row['class_id']) echo 'active'; ?>">
                                            <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/student_marksheet/<?php echo $row['class_id']; ?>">
                                                <span><?php echo get_phrase('class'); ?> <?php echo $row['name']; ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!-- TEACHER -->
                    <li class="nav-item <?php if ($page_name == 'teacher') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/teacher_list">
                            <i class="entypo-users"></i>
                            <span><?php echo get_phrase('teacher'); ?></span>
                        </a>
                    </li>



                    <!-- SUBJECT -->
                    <li class="nav-item dropdown <?php if ($page_name == 'subject') echo 'opened active'; ?> ">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >
                            <i class="entypo-docs"></i>
                            <span><?php echo get_phrase('subject'); ?><i class="entypo-right"></i></span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php //$classes = $this->db->get('class')->result_array();
                             $myclasses = $this->db->get_where('class',array('teacher_id'=>$this->session->userdata('teacher_id'),'school_id'=>$this->session->userdata('school')))->result_array();

                            foreach ($myclasses as $row):
                                ?>
                                <li class="dropdown-item <?php if ($page_name == 'subject' && $class_id == $row['class_id']) echo 'active'; ?>">
                                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/subject/<?php echo $row['class_id']; ?>">
                                        <span><?php echo get_phrase('class'); ?> <?php echo $row['name']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <!-- CLASS ROUTINE -->
                    <!-- <li class="nav-item <?php/// if ($page_name == 'class_routine') echo 'active'; ?> ">
                        <a href="<?php// echo base_url(); ?>index.php?<?php// echo $account_type; ?>/class_routine">
                            <i class="entypo-target"></i>
                            <span><?php //echo get_phrase('class_routine'); ?></span>
                        </a>
                    </li> -->

                    <li class="nav-item dropdown <?php if ($page_name == 'class_routine') echo 'opened active'; ?> ">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="entypo-docs"></i>
                            <span><?php echo ucwords(get_phrase('Time_Table')); ?><i class="entypo-right"></i></span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php
                           // $myclasses = $this->db->get_where('class',array('teacher_id'=>$this->session->userdata('teacher_id'),'school_id'=>$this->session->userdata('school')))->result_array();
                            foreach ($myclasses as $row):
                                ?>
                                <li class="dropdown-item <?php if ($page_name == 'class_routine' && $class_id == $row['class_id']) echo 'active'; ?>">
                                    <a href="<?php echo base_url(); ?>index.php?teacher/class_routine/<?php echo $row['class_id']; ?>">
                                        <span><?php echo ucwords(get_phrase('class')); ?> <?php echo $row['name']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                     <!-- Syllabus -->
                     <li class="nav-item <?php if ($page_name == 'academic_syllabus') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/academic_syllabus">
                            <i class="entypo-book"></i>
                            <span><?php echo get_phrase('syllabus'); ?></span>
                        </a>
                    </li>
                    
                    <!-- STUDY MATERIAL -->
                    <li class="nav-item <?php if ($page_name == 'study_material') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/study_material">
                            <i class="entypo-book-open"></i>
                            <span><?php echo get_phrase('study_material'); ?></span>
                        </a>
                    </li>

                    <!-- DAILY ATTENDANCE -->
                    <li class="nav-item <?php if ($page_name == 'manage_attendance') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/manage_attendance/<?php echo date("d/m/Y"); ?>">
                            <i class="entypo-chart-area"></i>
                            <span><?php echo get_phrase('daily_attendance'); ?></span>
                        </a>

                    </li>

                    <!-- EXAMS -->
                    <li class="nav-item dropdown <?php
                        if ($page_name == 'exam' ||
                                $page_name == 'grade' ||
                                $page_name == 'marks')
                            echo 'opened active';
                        ?> ">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >
                            <i class="entypo-graduation-cap"></i>
                            <span><?php echo get_phrase('exam'); ?><i class="entypo-right"></i></span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            <li class="<?php if ($page_name == 'marks') echo 'active'; ?> ">
                                <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/marks_manage">
                                    <span><i class="entypo-dot"></i> <?php echo get_phrase('manage_marks'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>


                    <!-- LIBRARY -->
                    <li class="nav-item <?php if ($page_name == 'book') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/book">
                            <i class="entypo-book"></i>
                            <span><?php echo get_phrase('library'); ?></span>
                        </a>
                    </li>

                   
                    <!-- NOTICEBOARD -->
                    <li class="nav-item <?php if ($page_name == 'noticeboard') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/noticeboard">
                            <i class="entypo-doc-text-inv"></i>
                            <span><?php echo get_phrase('noticeboard'); ?></span>
                        </a>
                    </li>

                    <!-- MESSAGE -->
                    <li class="nav-item <?php if ($page_name == 'message') echo 'active'; ?> ">
                        <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/message">
                            <i class="entypo-mail"></i>
                            <span><?php echo get_phrase('message'); ?></span>
                        </a>
                    </li>

                   
                </ul>
      </div>

     
      <div class="col-lg-2  profileLink">
		
        <ul class="list-inline pull-right">
        <!-- Language Selector -->			
           <li class="dropdown language-selector link">
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
          <li style="list-style:none" class="pull-right link">
                            <a class="pull-right" href="<?php echo base_url();?>index.php?login/logout">
                                <i class="entypo-logout "></i>Log Out 
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
