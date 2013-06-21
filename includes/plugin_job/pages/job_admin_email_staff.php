<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */
if(!$job_safe)die('failed');
if(!module_job::can_i('edit','Jobs'))die('no perms');
$job_id = (int)$_REQUEST['job_id'];
$staff_id = (int)$_REQUEST['staff_id'];
$staff = module_user::get_user($staff_id);
$job = module_job::get_job($job_id);
$job_tasks = module_job::get_tasks($job_id);
?>

<?php print_heading(_l('Email Job: %s',$job['name'])); ?>

<?php

module_template::init_template('job_staff_email','Dear {STAFF_NAME},<br>
<br>
Please find below your {TASK_COUNT} assigned tasks for job: {JOB_NAME}.<br><br>
You can view this job by <a href="{JOB_URL}">clicking here</a>.<br><br>
{JOB_TASKS}<br><br>
Thank you,<br><br>
{FROM_NAME}
','Assigned Job Tasks: {JOB_NAME}',array(
                                       'STAFF_NAME' => 'Customers Name',
                                       'JOB_NAME' => 'Job Name',
                                       'TASK_COUNT' => 'Number of assigned tasks',
                                       'JOB_URL' => 'Link to job for customer',
                                       'JOB_TASKS' => 'Output of job tasks for this staff member',
                                       ));



// template for sending emails.
// are we sending the paid one? or the dueone.
$template_name = isset($_REQUEST['template_name']) ? $_REQUEST['template_name'] : 'job_staff_email';
$template = module_template::get_template_by_key($template_name);
$job['job_name'] = $job['name'];
$job['staff_name'] = $staff['name'];
$job['job_url'] = module_job::link_open($job_id);

$job['job_tasks'] = '<ul>';
$job['task_count'] = 0;
foreach($job_tasks as $job_task){
    if($job_task['user_id']!=$staff_id)continue;
    if(module_config::c('job_staff_email_skip_complete',0)&&$job_task['fully_completed'])continue;
    $job['job_tasks'] .= '<li><strong>'.$job_task['description'].'</strong>';
    if($job_task['fully_completed']) $job['job_tasks'] .= ' <span style="color: #99cc00; font-weight:bold;">('._l('complete').')</span>';
    $job['job_tasks'] .= ' <br/>';
    if($job_task['long_description'])$job['job_tasks'] .= _l('Notes:').' <em>'.$job_task['long_description'].'</em><br/>';
    if($job_task['date_due']&&$job_task['date_due']!='0000-00-00')$job['job_tasks'] .= _l('Date Due:').' '.print_date($job_task['date_due']).'<br/>';
    if($job_task['hours'])$job['job_tasks'] .= _l('Assigned Hours:').' '.$job_task['hours'].'<br/>';
    if($job_task['completed'])$job['job_tasks'] .= _l('Completed Hours:').' '.$job_task['completed'].'<br/>';
    $job['job_tasks'] .= '</li>';
    $job['task_count']++;
}
$job['job_tasks'] .= '</ul>';

// find available "to" recipients.
// customer contacts.
$to = array();
$to[] = array(
    'name' => $staff['name'],
    'email' => $staff['email'],
);

$template->assign_values($job);

module_email::print_compose(
    array(
        'find_other_templates' => 'job_staff_email', // find others based on this name, eg: job_staff_email*
        'current_template' => $template_name,
        'job_id'=>$job['job_id'],
        'debug_message' => 'Sending job to staff',
        'to'=>$to,
        'bcc'=>module_config::c('admin_email_address',''),
        'content' => $template->render('html'),
        'subject' => $template->replace_description(),
        'success_url'=>module_job::link_open($job_id),
        //'success_callback'=>'module_job::staff_email_sent('.$job_id.',"'.$template_name.'");',
        /*'success_callback'=>'module_job::staff_email_sent',
        'success_callback_args'=>array(
            'job_id' => $job_id,
            'template_name' => $template_name,
        ),*/
        'cancel_url'=>module_job::link_open($job_id),
    )
);
?>