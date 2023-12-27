<?php
// Based on: https://liin.it/wordpress-limit-login-attempts-without-plugin/

class LimitLoginAttempts
{

    /**
     * The number of attempts allowed
     *
     * @since    1.0.0
     * @access   protected
     * @var      int    $attempts.
     */
    protected $attempts;


    /**
     * The number of seconds blocked
     *
     * @since    1.0.0
     * @access   protected
     * @var      int    $seconds_locked.
     */
    protected $seconds_locked;


    public function __construct()
    {
        $this->attempts = 6;
        $this->seconds_locked = 1200;
        add_action('wp_login_failed', [ $this, 'login_failed' ], 10, 3);
        add_filter('wp_authenticate_user', [ $this, 'authentication' ], 30, 2);
    }


    /**
     * Handle authentication based on the login attempts
     *
     * Triggered by the 'authenticate' filter hook. The Hook is used to perform additional validation/authentication any time a user logs in to WordPress.
     *
     * @since    1.0.0
     */
    public function authentication($user, $password)
    {
        $transient = get_transient('mcd_limit_login_attempt');
        if ($transient && $transient > $this->attempts) {
            $transient_expiration = get_option('_transient_timeout_mcd_limit_login_attempt');
            $waiting_seconds = abs($transient_expiration - time());
            return new WP_Error('limit_login_attempt', sprintf(__('You are blocked for %1$s seconds'), $waiting_seconds));
        }
        return $user;
    }


    /**
     * Handle login failure
     *
     * Triggered by the 'wp_login_failed' action hook. Fires after a user login has failed.
     *
     * @since    1.0.0
     */
    public function login_failed($username)
    {
        $transient = get_transient('mcd_limit_login_attempt');
        if ($transient) {
            $attempts = $transient + 1;
            set_transient('mcd_limit_login_attempt', $attempts);
        } else {
            set_transient('mcd_limit_login_attempt', 1, $this->seconds_locked);
        }
    }
}
