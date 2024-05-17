// BM Studio Code
function redirect_non_logged_in_users() {
    $request_uri = esc_url_raw($_SERVER['REQUEST_URI']);
    if (!is_user_logged_in() && !is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php' && strpos($request_uri, '/X/') === false) {
        wp_safe_redirect(wp_login_url());
        exit;
    }
}
add_action('init', 'redirect_non_logged_in_users');

function custom_login_message($message) {
    if (isset($_GET['registration']) && sanitize_text_field($_GET['registration']) === 'pending') {
        $message = "<div id='login_info' class='notice notice-info'><p>Thank you for registering. Your account is pending approval by the site administrators. You will be notified via email once your registration has been approved.</p></div>";
    } elseif (empty($message)) {
        $message = "<div id='login_error' class='notice notice-error'><p>Only registered and logged-in users are allowed to view this site. Please log in now.</p></div>";
    }
    return $message;
}
add_filter('login_message', 'custom_login_message');

function notify_user_registration_pending($user_id) {
    $user_info = get_userdata($user_id);
    $to = sanitize_email($user_info->user_email);
    $subject = 'Registration Pending Approval';
    $message = 'Thank you for registering. Your account is pending approval by the site administrators.';
    
    wp_mail($to, sanitize_text_eld($subject), sanitize_textarea_field($message));
}
add_action('user_register', 'notify_user_registration_pending');

function redirect_after_registration($user_id) {
    wp_safe_redirect(esc_url_raw(wp_login_url() . '?registration=pending'));
    exit;
}
add_action('user_register', 'redirect_after_registration');

function set_pending_role_on_regisfitration($user_id) {
    $user = new WP_User($user_id);
    if (!in_array('administrator', $user->roles)) {
        $user->set_role('pending');
    }
}
add_action('user_register', 'set_pending_role_on_registration');

function notify_user_when_approved($user_id, $new_role, $old_roles) {
    if (in_array('pending', $old_roles) && $new_role !== 'pending') {
        $user_info = get_userdata($user_id);
        $to = sanitize_email($user_info->user_email);
        $subject = 'Your Account Has Been Approved';
        $message = "Congratulations! Your account has been approved. You can now log in and enjoy full access to the site at: " . esc_url(wp_login_url());
        
        wp_mail($to, sanitize_text_field($subject), sanitize_textarea_field($message));
    }
}
add_action('set_user_role', 'notify_user_when_approved', 10, 3);
