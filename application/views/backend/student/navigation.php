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
                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/dashboard">
                        <i class="entypo-gauge"></i>
                        <span><?php echo get_phrase('dashboard'); ?></span>
                    </a>
                </li>



                <!-- TEACHER -->
                <li class="nav-item <?php if ($page_name == 'teacher') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/teacher_list">
                        <i class="entypo-users"></i>
                        <span><?php echo get_phrase('teacher'); ?></span>
                    </a>
                </li>



                <!-- SUBJECT -->
                <li class="nav-item <?php if ($page_name == 'subject') echo ' active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/subject">
                        <i class="entypo-docs"></i>
                        <span><?php echo get_phrase('subject'); ?></span>
                    </a>
                </li>

                <!-- CLASS ROUTINE -->
                <li class="nav-item <?php if ($page_name == 'class_routine') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/class_routine">
                        <i class="entypo-target"></i>
                        <span><?php echo get_phrase('class_routine'); ?></span>
                    </a>
                </li>
                
                <!-- STUDY MATERIAL -->
                <li class="nav-item <?php if ($page_name == 'study_material') echo 'active'; ?> ">
                    <a href="<?php echo base_url(); ?>index.php?<?php echo $account_type; ?>/study_material">
                        <i class="entypo-book-open"></i>
                        <span><?php echo get_phrase('study_material'); ?></span>
                    </a>
                </li>

                <!-- EXAMS -->
                <li class="nav-item dropdown <?php
                if ($page_name == 'exam' ||
                        $page_name == 'grade' ||
                        $page_name == 'marks')
                    echo 'opened active';
                ?> ">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="entypo-graduation-cap"></i>
                        <span><?php echo get_phrase('exam'); ?><i class="entypo-right"></i></span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                        <li class="dropdown-content <?php if ($page_name == 'marks_manage') echo 'active'; ?> ">
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
			</li>
        </ul>
        
	</div>

</nav>