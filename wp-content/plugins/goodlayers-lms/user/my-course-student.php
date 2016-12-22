<?php
	if( !empty($_POST['gdlr-certificate']) && !empty($_POST['student_id']) && !empty($_GET['course_id']) ){
		if($_POST['gdlr-certificate'] == 'yes'){
			$course_val = gdlr_lms_decode_preventslashes(get_post_meta($_GET['course_id'], 'gdlr-lms-course-settings', true));
			$course_options = empty($course_val)? array(): json_decode($course_val, true);
			
			gdlr_lms_add_certificate($_GET['course_id'], $course_options['certificate-template'], -1, -1, $_POST['student_id']);
		}else if($_POST['gdlr-certificate'] == 'no'){
			gdlr_lms_remove_certificate($_GET['course_id'], $_POST['student_id']);
		}
	}else if( !empty($_POST['gdlr-attendance']) && !empty($_POST['student_id']) && !empty($_GET['course_id']) ){
		$user_attendance = get_user_meta($_POST['student_id'], 'gdlr-lms-attendance', true); 
		$user_attendance = empty($user_attendance)? array(): $user_attendance;
		if($_POST['gdlr-attendance'] == 'attended'){
			$user_attendance[$_GET['course_id']] = 'yes';
		}else if($_POST['gdlr-attendance'] == 'missed'){
			$user_attendance[$_GET['course_id']] = 'no';
		}
		update_user_meta($_POST['student_id'], 'gdlr-lms-attendance', $user_attendance);
	}
	


	$course_val = gdlr_lms_decode_preventslashes(get_post_meta($_GET['course_id'], 'gdlr-lms-course-settings', true));
	$course_options = empty($course_val)? array(): json_decode($course_val, true);
	if( !empty($course_options['enable-certificate']) && $course_options['enable-certificate'] == 'enable' ){
		$certificate = true;
	}else{
		$certificate = false;
	}
?>
<h3 class="gdlr-lms-admin-head" ><?php echo get_the_title($_GET['course_id']); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php _e('Student', 'gdlr-lms'); ?></th>
	<?php if($course_options['online-course'] == 'disable'){ ?>
	<th align="center" ><?php _e('Seat', 'gdlr-lms'); ?></th>
	<?php } ?>
	<th align="center" ><?php _e('Status', 'gdlr-lms'); ?></th>
	<th align="center" ><?php _e('Code', 'gdlr-lms'); ?></th>
	<?php if($course_options['online-course'] == 'enable'){ ?>
	<th align="center" ><?php _e('Score', 'gdlr-lms'); ?></th>
	<th align="center" ><?php _e('Badge', 'gdlr-lms'); ?></th>
	<?php }else{ ?>
	<th align="center" ><?php _e('Attention', 'gdlr-lms'); ?></th>
		<?php if( $certificate ){ ?>
			<th align="center" ><?php _e('Certificate', 'gdlr-lms'); ?></th>
		<?php } ?>
	<?php } ?>
</tr>
<?php 
	$temp_sql  = "SELECT id, student_id, payment_info, payment_status FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= "WHERE course_id = " . $_GET['course_id'] . " ";
	$temp_sql .= "ORDER BY payment_status, ID ASC";

	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$user_info = get_user_meta($result->student_id);
		$payment_info = unserialize($result->payment_info);
		
		echo '<tr>';
		echo '<td>' . $user_info['first_name'][0] . ' ' . $user_info['last_name'][0] . ' ';
		echo '<a href="#" title="' . esc_attr(__('Delete Student', 'gdlr-lms')) . '" class="gdlr-lms-delete-student" ';
		echo 'data-title="' . esc_attr(__('Are you sure you want to remove this student from this course', 'gdlr-lms')) . '" ';
		echo 'data-yes="' . esc_attr(__('Confirm', 'gdlr-lms')) . '" data-no="' . esc_attr(__('Cancel', 'gdlr-lms')) . '" ';
		echo 'data-id="' . $result->id . '" data-ajax="' . admin_url('admin-ajax.php') . '" >';
		echo __('(Delete)', 'gdlr-lms') . '</a>';
		echo '</td>';
		
		if($course_options['online-course'] == 'disable'){
			echo '<td>' . $payment_info['amount'] . '</td>';
		}
		echo '<td>';
		if( $result->payment_status == 'paid' ){
			_e('Paid', 'gdlr-lms');
		}else{
			_e('Pending', 'gdlr-lms');
		}
		echo '</td>';
		echo '<td>' . $payment_info['code'] . '</td>';
		if($course_options['online-course'] == 'enable'){
			$temp_sql  = "SELECT quiz_score, quiz_status FROM " . $wpdb->prefix . "gdlrquiz ";
			$temp_sql .= "WHERE course_id = " . $_GET['course_id'] . " ";
			$temp_sql .= "AND student_id = " . $result->student_id . " ";
			$temp_sql .= "AND section_quiz IS NULL";
			$quiz_row = $wpdb->get_row($temp_sql);
			
			echo '<td>';
			if( $quiz_row->quiz_status == 'complete'){
				$quiz_score = empty($quiz_row)? array(): unserialize($quiz_row->quiz_score);
				$quiz_score = empty($quiz_score)? array(): $quiz_score;
				$score_summary = gdlr_lms_score_summary($quiz_score);	
				echo $score_summary['score'] . '/' . $score_summary['from'];
			}else if( $quiz_row->quiz_status == 'pending'){
				_e('Pending', 'gdlr-lms');
			}else if( $quiz_row->quiz_status == 'submitted'){
				_e('Submitted', 'gdlr-lms');
			}
			echo '</td>';
			
			if( $quiz_row->quiz_status == 'complete'){
				$user_meta = get_user_meta($result->student_id); 
				if( !empty($user_meta['gdlr-lms-badge']) ){
					$badges = unserialize($user_meta['gdlr-lms-badge'][0]);
				}
				echo '<td>' . (empty($badges[$_GET['course_id']])? __('No', 'gdlr-lms'): __('Yes', 'gdlr-lms')) . '</td>';
			}else{
				echo '<td>-</td>';
			}
		}else{
			$attendance = get_user_meta($result->student_id, 'gdlr-lms-attendance', true); 
			$attendance = empty($attendance)? array(): $attendance;
			echo '<td>';
			echo '<form action="" method="POST">';
			echo '<input type="hidden" name="student_id" value="' . $result->student_id . '" />';
			echo '<select name="gdlr-attendance">';
			echo '<option value="missed" ' . ((empty($attendance[$_GET['course_id']]) || $attendance[$_GET['course_id']] == 'yes')? '': 'selected') . ' >' . __('Missed', 'gdlr-lms') . '</option>';
			echo '<option value="attended" ' . ((empty($attendance[$_GET['course_id']]) || $attendance[$_GET['course_id']] == 'yes')? 'selected': '') . ' >' . __('Attended', 'gdlr-lms') . '</option>';
			echo '</select>';
			echo '</form>';
			echo '</td>';
			
			if( $certificate ){
				$certificates = get_user_meta($result->student_id, 'gdlr-lms-certificate', true); 
				$certificates = empty($certificates)? array(): $certificates;
				echo '<td>'; 
				echo '<form action="" method="POST">';
				echo '<input type="hidden" name="student_id" value="' . $result->student_id . '" />';
				echo '<select name="gdlr-certificate">';
				echo '<option value="no" ' . (empty($certificates[$_GET['course_id']])? 'selected': '') . ' >' . __('No', 'gdlr-lms') . '</option>';
				echo '<option value="yes" ' . (empty($certificates[$_GET['course_id']])? '': 'selected') . ' >' . __('Yes', 'gdlr-lms') . '</option>';
				echo '</select>';
				echo '</form>';
				echo '</td>';
			}
?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('select[name="gdlr-certificate"], select[name="gdlr-attendance"]').change(function(){
			jQuery(this).closest('form').submit();
		});
	});
</script>
<?php			
		}
		echo '</tr>';
	}	
?>
</table>