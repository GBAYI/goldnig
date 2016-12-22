<?php
	/*
	*	Goodlayers Transaction File
	*/

	function gdlr_lms_transaction_option(){
		global $wpdb;
		
		if( !empty($_POST['action']) && !empty($_POST['tid']) ){
			$payment_status = ($_POST['action'] == 'mark-as-paid')? 'paid': 'pending';
			
			$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
				array( 'payment_status' => $payment_status ), 
				array( 'id' => intval($_POST['tid']) ), 
				array( '%s' ), 
				array( '%d' ) 
			);
		}
?>
<div class="wrap">
<h2><?php _e('Transaction List', 'gdlr-lms'); ?></h2>
<form class="gdlr-lms-transaction-form" method="GET" action="">
	<div class="gdlr-lms-transaction-form-row">
		<span class="gdlr-lms-head"><?php _e('Search transaction by :', 'gdlr-lms'); ?></span>
		<div class="gdlr-combobox-wrapper">
		<select name="selector" >
			<option value="name" <?php echo (!empty($_GET['selector']) && $_GET['selector']=='name')? 'selected': ''; ?> ><?php _e('Name', 'gdlr-lms'); ?></option>
			<option value="code" <?php echo (!empty($_GET['selector']) &&$_GET['selector']=='code')? 'selected': ''; ?> ><?php _e('Code', 'gdlr-lms'); ?></option>
		</select>
		</div>
		<div class="clear"></div>
	</div>
	<div class="gdlr-lms-transaction-form-row">
		<span class="gdlr-lms-head"><?php _e('Keywords :', 'gdlr-lms'); ?></span>
		<input type="text" name="keywords" value="<?php echo !empty($_GET['keywords'])? $_GET['keywords']: ''; ?>" />
		<input type="hidden" name="page" value="lms-transaction" />
		<input type="submit" value="<?php _e('Search!', 'gdlr-lms'); ?>" />
		<div class="clear"></div>
	</div>
</form>
<form class="gdlr-lms-transaction-form-list" method="POST" action="">
<table class="gdlr-lms-table">
<tr>
	<th><?php _e('ID', 'gdlr-lms'); ?></th>
	<th><?php _e('Name', 'gdlr-lms'); ?></th>
	<th><?php _e('Course', 'gdlr-lms'); ?></th>
	<th><?php _e('Type', 'gdlr-lms'); ?></th>
	<th><?php _e('Price', 'gdlr-lms'); ?></th>
	<th><?php _e('Status', 'gdlr-lms'); ?></th>
	<th><?php _e('Code', 'gdlr-lms'); ?></th>
	<th><?php _e('Booked/Paid Date', 'gdlr-lms'); ?></th>
	<th><?php _e('Action', 'gdlr-lms'); ?></th>
</tr>
<?php
	$temp_sql  = "SELECT id, course_id, student_id, payment_info, payment_status, payment_date, price ";
	$temp_sql .= "FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= "WHERE  price != 0 ";

	if( !empty($_GET['selector']) && !empty($_GET['keywords']) ){
		if( $_GET['selector'] == 'name' ){
			$user_array = array(0);
			$users = new WP_User_Query(array(
				'meta_query' => array(
					'relation' => 'OR',
					array('key'=> 'first_name', 'value'=> $_GET['keywords'], 'compare' => 'LIKE'),
					array('key'=> 'last_name', 'value'=> $_GET['keywords'], 'compare' => 'LIKE')
				)
			));
			$users_found = $users->get_results();
			foreach( $users_found as $user ){
				if( !in_array($user->ID, $user_array) ) $user_array[] = $user->ID;
			}
			$users = new WP_User_Query(array(
				'search'         => '*'.esc_attr($_GET['keywords']).'*',
				'search_columns' => array('user_login','user_nicename')
			));
			$users_found = $users->get_results();
			foreach( $users_found as $user ){
				if( !in_array($user->ID, $user_array) ) $user_array[] = $user->ID;
			}

			$temp_sql .= 'AND student_id IN (' . implode(",", $user_array) . ') ';

		}else if( $_GET['selector'] == 'code' ){
			$temp_sql .= 'AND payment_info LIKE \'%' . $_GET['keywords'] . '%\' ';
		}

	}
	$temp_sql  .= "ORDER BY id desc";

	$results = $wpdb->get_results($temp_sql);

	// handle pagination
	global $gdlr_lms_option;

	$record_num = count($results);
	$current_page = empty($_GET['paged'])? 1: intval($_GET['paged']);
	$record_per_page = empty($gdlr_lms_option['transaction-record'])? 30: intval($gdlr_lms_option['transaction-record']);
	$max_num_page = ceil($record_num/$record_per_page);

	for($i=($record_per_page*($current_page - 1)); $i<$record_num && $i<($record_per_page * $current_page); $i++){ $result = $results[$i];
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta($result->course_id, 'gdlr-lms-course-settings', true));
		$course_options = empty($course_val)? array(): json_decode($course_val, true);

		$payment_info = unserialize($result->payment_info);
		$payment_info['code'] = empty($payment_info['code'])? '': $payment_info['code'];
		$student_info = get_userdata($result->student_id);

		echo '<tr>';
		echo '<td>' . $result->id . '</td>';
		echo '<td class="evidence-of-payment-name">';
		echo $student_info->first_name . ' ' . $student_info->last_name;
		echo '<div class="evidence-of-payment-name-hover" >';
		foreach($payment_info as $key => $value){
			echo '<div class="evidence-of-payment-info">';
			echo '<span class="head">' . $key . ' :</span>';
			if( $key == 'price' ){
				echo '<span class="tail">' . gdlr_lms_money_format($value) . '</span>';
			}else{
				echo '<span class="tail">' . $value . '</span>';
			}
			echo '</div>';
		}
		echo '</div>'; // evd-of-payment-name-hover
		echo '</td>'; // evd-of-payment-name

		echo '<td>' . $course_options['course-code'] . $result->course_id . '</td>';
		echo '<td>';
		echo ($course_options['online-course']=='enable')? __('Online', 'gdlr-lms'): __('Onsite', 'gdlr-lms');
		echo '</td>';
		echo '<td>' . gdlr_lms_money_format(number_format_i18n($result->price, 2)) . '</td>';
		echo '<td>';
		if( $result->payment_status == 'paid' ){
			echo __('paid', 'gdlr-lms');
		}else if( $result->payment_status == 'pending' ){
			echo __('pending', 'gdlr-lms');
		}else{
			echo $result->payment_status;
		}
		echo '</td>';

		echo '<td>' . $payment_info['code'] . '</td>';
		echo '<td>' . gdlr_lms_date_format($result->payment_date) . '</td>';
		
		echo '<td>';
		if( $result->payment_status == 'paid' ){
			echo '<a class="mark-as-pending" data-id="' . $result->id . '" >' . __('Mark As Pending', 'gdlr-lms') . '</a>';
		}else{
			echo '<a class="mark-as-paid" data-id="' . $result->id . '" >' . __('Mark As Paid', 'gdlr-lms') . '</a>';
		}
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
</form>
<?php
	// print pagination
	if( $max_num_page > 1 ){
		$page_var = $_GET;

		echo '<div class="gdlr-lms-pagination">';
		if($current_page > 1){
			$page_var['paged'] = intval($current_page) - 1;
			echo '<a class="prev page-numbers" href="' . esc_url(add_query_arg($page_var)) . '" >';
			echo __('&lsaquo; Previous', 'gdlr-lms') . '</a>';
		}
		for($i=1; $i<=$max_num_page; $i++){
			$page_var['paged'] = $i;
			if( $i == $current_page ){
				echo '<span class="page-numbers current" href="' . esc_url(add_query_arg($page_var)) . '" >' . $i . '</span>';
			}else{
				echo '<a class="page-numbers" href="' . esc_url(add_query_arg($page_var)) . '" >' . $i . '</a>';
			}
		}
		if($current_page < $max_num_page){
			$page_var['paged'] = intval($current_page) + 1;
			echo '<a class="next page-numbers" href="' . esc_url(add_query_arg($page_var)) . '" >';
			echo __('Next &rsaquo;', 'gdlr-lms') . '</a>';
		}
		echo '</div>';
	}
?>
</div>
<?php
	}
?>