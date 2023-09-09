<?php
/*
Plugin Name:  Zoyana KYC Forms
Plugin URI:   https://www.zoyana.co.za
Description:  A plugin for Know Your Customer forms.
Version:      1.0
Author:       Zoyana Team
Author URI:   https://www.zoyana.co.za
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  zoyana
Domain Path:  /languages
*/

add_filter( 'login_redirect', 'login_redirect', 10, 3 );
add_filter('the_generator', 'wpb_remove_version');

add_action( 'wp_login_failed', 'login_failed' ); // hook failed login
add_action('wp_ajax_register_user_front_end', 'register_user_front_end', 0);
add_action('wp_ajax_nopriv_register_user_front_end', 'register_user_front_end');

add_shortcode( 'zoyana_kyc_register', 'registration_form' );
add_shortcode( 'zoyana_kyc_login', 'login_form' );
add_shortcode( 'zoyana_kyc_profile', 'user_profile_form' );

function user_profile_form( $input ) {
	if ( !is_user_logged_in()) {
		ob_start();
		
		?>
		<div class="col">
			<h2>User Profile</h2>
			<p class="register-message" style="display:none"></p>

			<div class="register clearfix">
				<p>Hello There and Welcome to Zoyana</p>
			</div>

		</div>
		<?php
		$form = ob_get_clean();
		return $form;
	}
}

function registration_form( $input ) {
	if ( !is_user_logged_in()) {
		ob_start();
		
		?>
		<div class="col">
			<h2>Registration Form</h2>
			<p class="register-message" style="display:none"></p>

			<div class="register clearfix">
				<form class="register-form" action="#" method="POST" name="register-form"  id="registration_form">
					<div class="first form-col"><input type="text" name="new_user_name" id="new-username" class="form-control input-lg" placeholder="UserName" required autofocus /></div>
					<div class="form-col"><input type="email" name="new_user_email" id="new-useremail" class="form-control input-lg" placeholder="Email" required autofocus /> </div>
					<div class="clear"></div>
					<div class="first form-col"><input type="password" id="new-password" name="new_user_password" class="form-control input-lg" placeholder="Password" required /></div>
					<div class="form-col"><input type="password" name="re_pwd" id="new-userpassword" class="form-control input-lg" placeholder="Re-enter Password" required /></div>
					<input type="submit" id="register-button" class="btn btn-primary btn-lg btn-block" value="Register">
				</form>
			</div>

		</div>
		<?php
		$form = ob_get_clean();
		return $form;
	}
}


function login_form( $input ) {
	if ( !is_user_logged_in()) {
		ob_start();
		$args = array();
		$defaults = array('echo' => true,
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Default redirect is back to the current page
			'form_id' => 'loginformfrontend',
			'label_username' => __('Username'),
			'label_password' => __('Password'),
			'label_remember' => __('Remember Me'),
			'label_log_in' => __('SIGN IN'),
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			'value_remember' => true, // Set this to true to default the "Remember me" checkbox to checked
		);
		$args = wp_parse_args($args, apply_filters('login_form_defaults', $defaults));

		if ( isset( $_GET['login'] ) && !empty( $_GET['login'] ) && $_GET['login'] == 'failed' ) {
			$error =  '<strong>ERROR</strong>: The Username Or Password may be incorrect';
		}

		$form = '<div class="col"> <h2>Login</h2>'.$error.'
			<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url(home_url('/user-profile/'))	 . '" method="post" class="form col-md-12">
				' . apply_filters('login_form_top', '', $args) . '
				<input type="text" name="log" id="' . esc_attr($args['id_username']) . '" class="form-control form-user-name" value="' . esc_attr($args['value_username']) . '" size="20" placeholder="Username" />
				<div class="clear"></div>
				<input placeholder="Password" type="password" name="pwd" id="' . esc_attr($args['id_password']) . '" class="form-control form-password" value="" />
				<div class="clear"></div>
				<input type="submit" name="wp-submit" id="' . esc_attr($args['id_submit']) . '" class="submit-btn" value="' . esc_attr($args['label_log_in']) . '" />
				<div class="forgot-pass-wrap clearfix">
					<div class="checkbox pull-left ">
						<label>'.
							( $args['remember'] ? '<input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever" tabindex="90"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' />Remember me'  : '' ) . 
							'
						</label>
					</div>
					<div class="pull-right forgot">
						<a href="javascript:void(0);" class="forgot-password" id="forgot-password">Forgot Password</a>
						<a href="/portal/register" class="register" id="register">Register</a>
					</div>
				</div>
				<input type="hidden" name="redirect_to" value="' . esc_url($args['redirect']) . '" />' . apply_filters('login_form_middle', '', $args) . '
				' . apply_filters('login_form_bottom', '', $args) . '
			</form></div>';

		if ( $args['echo'] )
			echo $form;
		$login_form = ob_get_clean();
		return $login_form;
	}
}

function login_redirect( $redirect, $request, $user ) {
	global $user;

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'administrator', $user->roles ) ) {
			return home_url() .'/wp-admin/index.php';
		} else {
			return home_url().'/my-account/';
		}
	} else {
		return $redirect;
	}
}

function login_failed( $user ) {

    $referrer = $_SERVER['HTTP_REFERER'];	

    if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && $user!=null ) {

    	if ( !strstr($referrer, '?login=failed' )) {
    		wp_redirect( $referrer . '?login=failed');
		} else {
			wp_redirect( $referrer );
		}

		exit;
	}
}

function register_user_front_end() {
	$new_user_name = stripcslashes($_POST['new_user_name']);
	$new_user_email = stripcslashes($_POST['new_user_email']);
	$new_user_password = $_POST['new_user_password'];
	$user_nice_name = strtolower($_POST['new_user_email']);
	$user_data = array(
		'user_login' => $new_user_name,
		'user_email' => $new_user_email,
		'user_pass' => $new_user_password,
		'user_nicename' => $user_nice_name,
		'display_name' => $new_user_first_name,
		'role' => 'author'
		);
	$user_id = wp_insert_user($user_data);
	if (!is_wp_error($user_id)) {
		echo 'Created an account for you.';
	} else {
		if (isset($user_id->errors['empty_user_login'])) {
			$notice_key = 'User Name and Email are mandatory';
			echo $notice_key;
		} elseif (isset($user_id->errors['existing_user_login'])) {
			echo'User name already exixts.';
		} else {
			echo'Error Occured please fill up the sign up form carefully.';
		}
	}
	die;
}

function wpb_remove_version() {
  return '';
}
