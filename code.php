// BM Studio Code - barmosseri.com
function allow_password_reset_access() {
    $request_uri = esc_url_raw($_SERVER['REQUEST_URI']);
    if (isset($_GET['action']) && ($_GET['action'] === 'rp' || $_GET['action'] === 'resetpass') ||
        strpos($request_uri, '/wp-login.php?action=rp') !== false) {
        return;
    }
}
add_action('init', 'allow_password_reset_access');

function redirect_non_logged_in_or_pending_users() {
    $request_uri = esc_url_raw($_SERVER['REQUEST_URI']);
    $allowed_pages = ['/wp-login.php', '/register/', '/wp-login.php?action=lostpassword'];

    $is_allowed_page = false;
    foreach ($allowed_pages as $page) {
        if (strpos($request_uri, $page) !== false) {
            $is_allowed_page = true;
            break;
        }
    }

    if (!is_user_logged_in() && !$is_allowed_page) {
        wp_safe_redirect(wp_login_url());
        exit;
    }

    $current_user = wp_get_current_user();
    if (is_user_logged_in() && in_array('pending', $current_user->roles) && !$is_allowed_page) {
        wp_safe_redirect(wp_login_url());
        exit;
    }
}
add_action('init', 'redirect_non_logged_in_or_pending_users');

function custom_login_message($message) {
    if (isset($_GET['registration']) && sanitize_text_field($_GET['registration']) === 'pending') {
        $message = "<div id='login_info' class='notice notice-info'><p>Thank you for registering. Your account is pending approval by the site manager. You will be notified via email once your registration has been approved.</p></div>";
    } elseif (empty($message)) {
        $message = "<div id='login_error' class='notice notice-error'><p>Only registered and logged-in users are allowed to view this site. Please log in now.</p></div>";
    }
    return $message;
}
add_filter('login_message', 'custom_login_message');

function notify_user_registration_pending($user_id) {
    $is_manual = get_user_meta($user_id, 'is_manual', true);
    if ($is_manual) {
        return;
    }
    $user_info = get_userdata($user_id);
    $to = sanitize_email($user_info->user_email);
    $subject = 'Registration Pending Approval';
    $message = 'Thank you for registering. Your account is pending approval by the site manager.';
    
    wp_mail($to, sanitize_text_field($subject), sanitize_textarea_field($message));
}
add_action('user_register', 'notify_user_registration_pending');

function redirect_after_registration($user_id) {
    wp_safe_redirect(esc_url_raw(wp_login_url() . '?registration=pending'));
    exit;
}
add_action('user_register', 'redirect_after_registration');

function set_pending_role_on_registration($user_id) {
    $user = new WP_User($user_id);
    if (!in_array('administrator', $user->roles)) {
        $user->set_role('pending');
    }
}
add_action('user_register', 'set_pending_role_on_registration');

function redirect_pending_users_on_login($user, $username) {
    $user = get_user_by('login', $username);
    if ($user && in_array('pending', (array) $user->roles)) {
        wp_safe_redirect(wp_login_url() . '?registration=pending-for-approval');
        exit;
    }
}
add_action('wp_authenticate', 'redirect_pending_users_on_login', 10, 2);

function custom_pending_approval_message($message) {
    if (isset($_GET['registration']) && sanitize_text_field($_GET['registration']) === 'pending-for-approval') {
        $message = "<div id='login_error' class='notice notice-error'><p>Your account is pending approval by the site manager. You will be notified via email once your registration has been approved.</p></div>";
    }
    return $message;
}
add_filter('login_message', 'custom_pending_approval_message');

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

function notify_admins_on_user_registration($user_id) {
    $administrators = get_users(array('role' => 'administrator'));
    foreach ($administrators as $admin) {
        $to = $admin->user_email;
        $subject = 'New User Registration';
        $message = 'A new user has registered on your website.';
        wp_mail($to, sanitize_text_field($subject), sanitize_textarea_field($message));
    }
}
add_action('user_register', 'notify_admins_on_user_registration');
