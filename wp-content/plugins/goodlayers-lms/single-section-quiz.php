<?php 
	global $wpdb, $current_user, $post;
	global $gdlr_course_settings, $lms_page;
	
	$quiz_page = (empty($_GET['section-quiz']))? 1: intval($_GET['section-quiz']);
	
	// initialte the value
	$quiz_id = $gdlr_course_settings[$lms_page-1]['section-quiz'];

	$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($quiz_id, 'gdlr-lms-content-settings', true));
	$quiz_options = empty($quiz_val)? array(): json_decode($quiz_val, true);	
	
	// save section
	if( $current_user->ID != $post->post_author ){
	
		// get the old value 
		$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'gdlrquiz ';
		$sql .= 'WHERE quiz_id=' . $quiz_id . ' AND student_id=' . $current_user->ID . ' AND course_id=' . get_the_ID() . ' AND section_quiz=' . $lms_page;
		$current_row = $wpdb->get_row($sql);	
		$quiz_answer = empty($current_row)? array(): unserialize($current_row->quiz_answer);
		
		// save quiz answer action
		if( isset($_POST['action']) && $_POST['action'] == 'save_quiz_answer' ){
		
			$question_set = intval($_POST['quiz_page']) - 1;
			if( isset($_POST['timeleft']) ){
				$quiz_answer[$question_set]['timeleft'] = $_POST['timeleft'];
			}
    
			for($i=0; $i<sizeof($_POST); $i++){
				if( isset($_POST['question' . $i]) ){
					if( is_array($_POST['question' . $i]) ){
						$quiz_answer[$question_set][$i] = $_POST['question' . $i];
					}else{
						$quiz_answer[$question_set][$i] = stripslashes($_POST['question' . $i]);
					}
				}
			}
			
			$quiz_score = array(); $quiz_status = 'pending';
			if( $_GET['section-quiz'] == 'finish' ){
				$quiz_score = gdlr_lms_calculating_score($quiz_options, $quiz_answer, $quiz_score);
				$quiz_status = (sizeof($quiz_score) == sizeof($quiz_options))? 'complete': 'submitted';
			}		
			
			if($current_row){
				$wpdb->update( $wpdb->prefix . 'gdlrquiz', 
					array('quiz_answer'=>serialize($quiz_answer), 'quiz_score'=>serialize($quiz_score), 'quiz_status'=>$quiz_status), 
					array('quiz_id'=>$quiz_id, 'student_id'=>$current_user->ID, 'course_id'=>get_the_ID(), 'section_quiz'=>$_POST['course_section']), 
					array('%s', '%s', '%s'), 
					array('%d', '%d')
				);			
			}else{
				$wpdb->insert( $wpdb->prefix . 'gdlrquiz', 
					array('quiz_id'=>$quiz_id, 'student_id'=>$current_user->ID, 'course_id'=>get_the_ID(), 'section_quiz'=>$_POST['course_section'],
						'quiz_answer'=>serialize($quiz_answer), 'quiz_score'=>serialize($quiz_score), 'quiz_status'=>$quiz_status), 
					array('%d', '%d', '%d', '%d', '%s', '%s', '%s') 
				);		
			}
		}
	}

	if( isset($_GET['section-quiz']) && $_GET['section-quiz'] == 'finish' ){
		exit();
	}
	
	
	get_header();
?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
	
	if( $lms_page < sizeof($gdlr_course_settings) ){
		$back_to_course = add_query_arg(array('course_type'=>'content', 'course_page'=> ($lms_page + 1), 'lecture'=> 1, 'section-quiz'=>''));
	}else{
		if( !empty($gdlr_course_options['quiz']) ){
			$back_to_course = add_query_arg(array('course_type'=>'quiz', 'course_page'=>1), get_permalink());
		}else{
			$back_to_course = get_permalink();
		}
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
		<?php 
			echo '<form class="gdlr-lms-course-single gdlr-lms-quiz-type" method="post" action="" >';
			echo '<input type="hidden" name="action" value="save_quiz_answer" />';
			echo '<input type="hidden" name="course_section" value="' . $lms_page . '" />';
			echo '<input type="hidden" name="quiz_page" value="' . $quiz_page . '" />';
			
			echo '<div class="gdlr-lms-course-info-wrapper">';
			echo '<div class="gdlr-lms-course-info-title">' . __('Quiz Process', 'gdlr-lms') . '</div>';
			echo '<div class="gdlr-lms-quiz-timer">';
			echo '<i class="fa fa-clock-o icon-time"></i>';
			if( $quiz_options[$quiz_page-1]['section-timer'] == 'enable' ){
				$full_time = intval($quiz_options[$quiz_page-1]['time-period'])*60;
				if( !isset($quiz_answer[$quiz_page-1]['timeleft']) ){
					$timeleft = $full_time;
				}else{
					$timeleft = $quiz_answer[$quiz_page-1]['timeleft'];
				}
				
				echo '<span class="timer" ></span>';
				echo '<input type="hidden" name="timeleft" ';
				echo 'data-full="' . $full_time . '" ';
				echo 'value="' . $timeleft . '" />';	
				
				// if not last page
				if( $quiz_page < sizeof($quiz_options) ){
					gdlr_lms_quiz_timeout_form($quiz_page+1);
				}else{
					gdlr_lms_quiz_timeout_form();
				}
			}else{
				echo '<span class="timer" >-</span>';
			}
			echo '</div>'; // quiz-timer
			echo '<div class="gdlr-lms-course-info">';
			for( $i=1; $i<=sizeof($quiz_options); $i++ ){
				$part_class  = ($i == sizeof($quiz_options))? 'gdlr-last ': '';
				if($i < $quiz_page){ $part_class .= 'gdlr-pass '; }
				else if($i == $quiz_page){ $part_class .= 'gdlr-current '; }
				else{ $part_class .= 'gdlr-next '; }
				
				echo '<div class="gdlr-lms-course-part ' . $part_class . '">';
				echo '<div class="gdlr-lms-course-part-icon">';
				echo '<div class="gdlr-lms-course-part-bullet"></div>';
				echo '<div class="gdlr-lms-course-part-line"></div>';
				echo '</div>'; // part-icon
				
				echo '<div class="gdlr-lms-course-part-content">';
				echo '<span class="part">' . __('Part', 'gdlr-lms') . ' ' . $i . '</span>';
				echo '<span class="title">' . $quiz_options[$i-1]['section-name'] . '</span>';
				echo '</div>'; // part-content
				echo '</div>'; // course-part
			}
			echo '</div>'; // course-info
			gdlr_lms_finish_section_quiz($back_to_course);
			echo '</div>'; // course-info-wrapper
			
			echo '<div class="gdlr-lms-course-content">';
			echo '<h3>' . __('Section Quiz', 'gdlr-lms') . ' : ' . $gdlr_course_settings[$lms_page-1]['section-name'] . '</h3>';
			echo '<h3>' . __('Part', 'gdlr-lms') . ' ' . $quiz_page . ' ' . $quiz_options[$quiz_page-1]['section-name'] . '</h3>';
			echo '<div class="gdlr-lms-quiz-content-wrapper">';

			$count = 0;
			$quiz_options[$quiz_page-1]['question'] = json_decode($quiz_options[$quiz_page-1]['question'], true);
			foreach($quiz_options[$quiz_page-1]['question'] as $question){ $count++;

				echo '<div class="gdlr-lms-quiz-question-wrapper">';
				echo '<div class="gdlr-lms-quiz-question">' . $count . '. ' . $question['question'] . '</div>';
				echo '<div class="gdlr-lms-quiz-answer">';
				switch($quiz_options[$quiz_page-1]['question-type']){
					case 'single':
						$choice_count = 0;
						foreach($question['quiz-choice'] as $quiz_choice){ $choice_count++;
							echo '<div class="gdlr-lms-quiz-choice">';
							echo '<input type="radio" value="' . $choice_count . '" name="question' . ($count-1) . '" ';
							if( !empty($quiz_answer[$quiz_page-1][$count-1]) && 
								$choice_count == $quiz_answer[$quiz_page-1][$count-1] ){
								echo 'checked ';
							}
							echo '/>' . $quiz_choice;
							echo '</div>';
						}
						break;
					case 'multiple':
						$choice_count = 0;
						foreach($question['quiz-choice'] as $quiz_choice){ $choice_count++;
							echo '<div class="gdlr-lms-quiz-choice">';
							echo '<input type="checkbox" value="' . $choice_count . '" name="question' . ($count-1) . '[]" ';
							if( !empty($quiz_answer[$quiz_page-1][$count-1]) && 
								in_array($choice_count, $quiz_answer[$quiz_page-1][$count-1]) ){
								echo 'checked ';
							}
							echo '/>' . $quiz_choice;
							echo '</div>';
						}
						break;
					case 'large':
					case 'small':
						echo '<textarea name="question' . ($count-1) . '">';
						if( !empty($quiz_answer[$quiz_page-1][$count-1]) ){
							echo $quiz_answer[$quiz_page-1][$count-1];
						}
						echo '</textarea>';
						break;
				}
				echo '<div class="gdlr-lms-question-score" >';
				echo '<span class="gdlr-head">'; 
				echo empty($question['score'])? 1: $question['score'];
				echo '</span>';
				echo '<span class="gdlr-tail">' . __('Point(s)', 'gdlr-lms') . '</span>'; 
				echo '</div>'; // question-score
				
				echo '</div>'; // quiz-answer
				echo '</div>'; // question-wrapper
			}
			echo '</div>'; // quiz-content-wrapper

			echo '<div class="gdlr-lms-course-pagination">';
			if( $quiz_page > 1 ){
				echo '<a href="' . esc_url(add_query_arg(array('section-quiz'=> $quiz_page-1))) . '" class="gdlr-lms-button blue submit-quiz-form">';
				echo __('Previous Part', 'gdlr-lms');
				echo '</a>';
			}
			if( $quiz_page < sizeof($quiz_options) ){
				echo '<a href="' . esc_url(add_query_arg(array('section-quiz'=> $quiz_page+1))) . '" class="gdlr-lms-button blue submit-quiz-form">';
				echo __('Next Part', 'gdlr-lms');
				echo '</a>';
			}
			if( $quiz_page == sizeof($quiz_options) ){
				gdlr_lms_finish_section_quiz($back_to_course);
			}
			echo '</div>'; // pagination
			echo '</div>'; // course-content
			
			echo '<div class="clear"></div>';
			echo '</form>'; // course-single		
		?>
		</div><!-- gdlr-lms-container -->
	</div><!-- gdlr-lms-content -->
</div>
</div>
<?php 
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
}

get_footer(); ?>