<?php
/*
Plugin Name: Konnichiwa! Cancel Subscription
Description: Adds a cancel subscription option using the shortcode [ad_konnichiwa_cancel].
Author: admiralchip
Version: 0.1
License: GPLv2 or later
*/
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
	function find_subs(){
		if( is_user_logged_in() ) {	
			if(!empty($_GET['cancel'])) {
				if($_POST['cancel_subs']) {
				$subs_id = trim($_POST['subs_id']);				
				global $wpdb;
				$user_ID = get_current_user_id();
				$subscription_table = $wpdb->prefix . "konnichiwa_subscriptions";
				$del_query = $wpdb->prepare( "DELETE FROM " . $subscription_table . " WHERE user_id=%d AND id=%d", $user_ID, $subs_id );				
				$wpdb->query($del_query);
				echo "<p>" . _e('Subscription cancelled!') . "</p>";
				} elseif($_POST['cancel_subs_no']) {
					wp_redirect( home_url() );
				}				
				else {
				$subs_id = filter_input(INPUT_GET, 'cancel');
				global $wpdb;
				$user_ID = get_current_user_id();
				$subscription_table = $wpdb->prefix . "konnichiwa_subscriptions";
				$subs_row = $wpdb->get_var( "SELECT COUNT(*) FROM " . $subscription_table . " WHERE id=" . $subs_id . " AND user_id=" . $user_ID );
				if($subs_row == 1) {
				?>
				<p><?php _e('Are you sure you want to cancel your subscription?'); ?></p>
				<form method="POST" action="">
				<input type="hidden" name="subs_id" value="<?php echo $subs_id; ?>" />
				<input type="submit" name="cancel_subs" value="<?php _e('Yes');?>" />
				<input type="submit" name="cancel_subs_no" value="<?php _e('No');?>" />
				</form>
				<?php			
				} else {
					echo "<p>". _e('This subscription has already been cancelled.') . "</p>";					
				}
				}		
			} else {
		//Select active subscriptions.
		global $wpdb;
		$subscription_table = $wpdb->prefix . "konnichiwa_subscriptions";
		$user_ID = get_current_user_id(); //select current user id
		$subscriptions = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $subscription_table . " WHERE 
			expires >= CURDATE() AND user_id=%d AND status=%d", $user_ID, 1)); 
		if($subscriptions) {
			echo "<p>" . _e('You have the following active plans:') . "</p>";
			echo "<table cellspacing='0' cellpadding='0'>";
		foreach($subscriptions as $subscription) { 
			$subs_id = $subscription->id;
			$plan_id = $subscription->plan_id;
			global $wpdb;
			$plan_table = $wpdb->prefix . "konnichiwa_plans";
			$plans = $wpdb->get_results("SELECT name FROM " . $plan_table . " WHERE id=" . $plan_id);
			if($plans) { 			
				foreach($plans as $plan) {
				$plan_name = $plan->name;
				echo "<tr><td>" . $plan_name . "</td><td><a href='?cancel=" . $subs_id . "'>Cancel</a></td></tr>";				
				}			
			} else {
				echo "<p>" . _e('No such plan.') . "</p>";
			}
		}
		echo "</table>";
		} else {
			echo "<p>" . _e('You have no active subscriptions.') . "</p>";
		}
		}
		} else {
			wp_redirect( home_url('/wp-login.php') );
		}
	}
	
	add_shortcode('ad_konnichiwa_cancel', 'find_subs');
