<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:			Accounts Langfile
*
* Description:  Generic language file for Nails. Admin Accounts
* 
*/

	//	Generic for module
	$lang['accounts_module_name']		= 'Members';
	$lang['accounts_sort_id']			= 'User ID';
	$lang['accounts_sort_first']		= 'First Name, Surname';
	$lang['accounts_sort_last']			= 'Surname, First Name';
	$lang['accounts_sort_email']		= 'Email';
	
	// --------------------------------------------------------------------------
	
	//	Nav
	$lang['accounts_nav_index']			= 'View All Members';
	$lang['accounts_nav_create']		= 'Create New User';
	
	// --------------------------------------------------------------------------
	
	//	Overview
	$lang['accounts_index_title']				= 'View All Members';
	$lang['accounts_index_search_results']		= 'search for "%s" returned %s results';
	$lang['accounts_index_intro']				= 'This section lists all users registered on site. You can browse or search this list using the search facility below.';
	
	//	Listings
	$lang['accounts_index_th_id']				= 'User ID';
	$lang['accounts_index_th_user']				= 'User';
	$lang['accounts_index_th_group']			= 'Group';
	$lang['accounts_index_th_actions']			= 'Actions';
	$lang['accounts_index_no_users']			= 'No users found';
	$lang['accounts_index_verified']			= 'Verified email address';
	$lang['accounts_index_social_connected']	= 'Connected to %s';
	$lang['accounts_index_last_login']			= 'Last login: <span class="nice-time">%s</span> (%s logins)';
	$lang['accounts_index_last_nologins']		= 'Last login: Never Logged In';
	
	// --------------------------------------------------------------------------
	
	//	Create new user
	$lang['accounts_create_title']						= 'Create New User';
	$lang['accounts_create_intro']						= 'Create a new user by completing the following basic information and clicking \'Create User\' below. You will be given the opportunity to edit the user once the basic account has been created.';
	$lang['accounts_create_basic_legend']				= 'Basic Information';	
	$lang['accounts_create_field_group_label']			= 'User\'s Group';
	$lang['accounts_create_field_group_tip']			= 'Specify to which group this user belongs';
	$lang['accounts_create_field_password_tip']			= 'Leave the password field blank to have the system auto-generate a 6 character password.';
	$lang['accounts_create_field_password_placeholder']	= 'The user\'s password, leave blank to auto-generate';
	$lang['accounts_create_field_send_welcome_label']	= 'Send Welcome Email';
	$lang['accounts_create_field_send_welcome_yes']		= '<strong>Yes</strong>, send user welcome email containing their password.';
	$lang['accounts_create_field_send_welcome_no']		= '<strong>No</strong>, do not send welcome email.';
	$lang['accounts_create_field_temp_pw_label']		= 'Update on log in';
	$lang['accounts_create_field_temp_pw_yes']			= '<strong>Yes</strong>, require user to update password on first log in.';
	$lang['accounts_create_field_temp_pw_no']			= '<Strong>No</strong>, do not require user to update password on first log in.';
	$lang['accounts_create_field_first_placeholder']	= 'The user\'s first name';
	$lang['accounts_create_field_last_placeholder']		= 'The user\'s surname';
	$lang['accounts_create_field_email_placeholder']	= 'The user\'s email address';
	$lang['accounts_create_submit']						= 'Create User';
	
	// --------------------------------------------------------------------------
	
	//	Edit user
	$lang['accounts_edit_title']			= 'Edit User (%s)';
	$lang['accounts_edit_unknown_id']		= 'Unknown User ID';
	$lang['accounts_edit_editing_self']		= '<strong>Hello there!</strong> You are currently editing your own account.';
	
	$lang['accounts_edit_actions_legend']	= 'Actions';
	
	
	$lang['accounts_edit_basic_legend']							= 'Basic Information';
	$lang['accounts_edit_basic_field_group_label']				= 'User Group';
	$lang['accounts_edit_basic_field_group_tip']				= 'Specify to which group this user belongs';
	$lang['accounts_edit_basic_field_password_label']			= 'Reset Password';
	$lang['accounts_edit_basic_field_password_placeholder']		= 'Reset the user\'s password by specifying a new one here';
	$lang['accounts_edit_basic_field_password_tip']				= 'The user will NOT be informed of any password changes';
	$lang['accounts_edit_basic_field_temp_pw_label']			= 'Update on next log in';
	$lang['accounts_edit_basic_field_temp_pw_yes']				= '<strong>Yes</strong>, require user to update password on next log in.';
	$lang['accounts_edit_basic_field_temp_pw_no']				= '<strong>No</strong>, do not require user to update password on next log in.';
	$lang['accounts_edit_basic_field_first_placeholder']		= 'The user\'s first name';
	$lang['accounts_edit_basic_field_last_placeholder']			= 'The user\'s surname';
	$lang['accounts_edit_basic_field_email_placeholder']		= 'The user\'s email address';
	$lang['accounts_edit_basic_field_verified_label']			= 'Email verified';
	$lang['accounts_edit_basic_field_username_label']			= 'Username';
	$lang['accounts_edit_basic_field_username_placeholder']		= 'The user\'s username';
	$lang['accounts_edit_basic_field_register_ip_label']		= 'Registration IP';
	$lang['accounts_edit_basic_field_last_ip_label']			= 'Last IP';
	$lang['accounts_edit_basic_field_created_label']			= 'Created';
	$lang['accounts_edit_basic_field_modified_label']			= 'Modified';
	$lang['accounts_edit_basic_field_logincount_label']			= 'Log in counter';
	$lang['accounts_edit_basic_field_last_login_label']			= 'Last Login';
	$lang['accounts_edit_basic_field_not_logged_in']			= 'Never Logged In';
	$lang['accounts_edit_basic_field_referral_label']			= 'Referral Code';
	$lang['accounts_edit_basic_field_referred_by_label']		= 'Referred By';
	$lang['accounts_edit_basic_field_referred_by_placeholder']	= 'The user who referred this user, if any';
	
	$lang['accounts_edit_meta_legend']		= 'Meta Information';
	$lang['accounts_edit_meta_noeditable']	= 'There is no editable meta information for this user.';
	
	$lang['accounts_edit_img_legend']		= 'Profile Image';
	
	$lang['accounts_edit_social_legend']	= 'Social Media';
	$lang['accounts_edit_social_connected']	= 'Connected to %s';
	$lang['accounts_edit_social_none']		= 'This user is not currently connected to any social media network';
		
	//	TODO: Errors
	
	// --------------------------------------------------------------------------
	
	//	Suspending/Unsuspending
	$lang['accounts_suspend_success']		= '<strong>Success!</strong> %s was suspended.';
	$lang['accounts_suspend_error']			= '<strong>Sorry,</strong> there was a problem suspending %s.';
	$lang['accounts_unsuspend_success']		= '<strong>Success!</strong> %s was unsuspended.';
	$lang['accounts_unsuspend_error']		= '<strong>Sorry,</strong> there was a problem unsuspending %s.';
	
	// --------------------------------------------------------------------------
	
	//	Activating/Deactivating
	$lang['accounts_activate_success']		= '<strong>Success!</strong> %s was activated.';
	$lang['accounts_activate_error']		= '<strong>Sorry,</strong> there was a problem activating %s.';
	$lang['accounts_deactivate_success']	= '<strong>Success!</strong> %s was deactivated.';
	$lang['accounts_deactivate_error']		= '<strong>Sorry,</strong> there was a problem deactivating %s.';
	
	// --------------------------------------------------------------------------
	
	//	Deleting
	$lang['accounts_delete_success']		= '<strong>See ya!</strong>User %s was deleted successfully.';
	$lang['accounts_delete_error']			= '<strong>Sorry,</strong> there was a problem deleting %s.';
	
	// --------------------------------------------------------------------------
	
	//	Deleting profile image
	$lang['accounts_delete_img_success']		= '<strong>Success!</strong> Profile image was deleted.';
	$lang['accounts_delete_img_error']			= '<strong>Sorry,</strong> I was unable delete this user\'s profile image. The CDN said: "%s"';
	$lang['accounts_delete_img_error_noid']		= '<strong>Sorry,</strong> I was unable to find a user by that ID.';
	$lang['accounts_delete_img_error_noimg']	= '<strong>Hey!</strong> This user doesn\'t have a profile image to delete.';
	
	