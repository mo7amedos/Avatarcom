<?php

return [
    'login' => [
        'username' => 'Email/Username',
        'email' => 'Email',
        'password' => 'Password',
        'title' => 'User Login',
        'remember' => 'Remember me?',
        'login' => 'Sign in',
        'placeholder' => [
            'username' => 'Enter your username or email address',
            'email' => 'Enter your email address',
            'password' => 'Enter your password',
        ],
        'success' => 'Login successfully!',
        'fail' => 'Wrong username or password.',
        'not_active' => 'Your account has not been activated yet!',
        'banned' => 'This account is banned.',
        'logout_success' => 'Logout successfully!',
        'dont_have_account' => 'You don\'t have account on this system, please contact administrator for more information!',
    ],
    'forgot_password' => [
        'title' => 'Forgot Password',
        'message' => '<p>Have you forgotten your password?</p><p>Please enter your email account. System will send a email with active link to reset your password.</p>',
        'submit' => 'Submit',
    ],
    'reset' => [
        'new_password' => 'New password',
        'password_confirmation' => 'Confirm new password',
        'email' => 'Email',
        'title' => 'Reset your password',
        'update' => 'Update',
        'wrong_token' => 'This link is invalid or expired. Please try using reset form again.',
        'user_not_found' => 'This username is not exist.',
        'success' => 'Reset password successfully!',
        'fail' => 'Token is invalid, the reset password link has been expired!',
        'reset' => [
            'title' => 'Email reset password',
        ],
        'send' => [
            'success' => 'A email was sent to your email account. Please check and complete this action.',
            'fail' => 'Can not send email in this time. Please try again later.',
        ],
        'new-password' => 'New password',
        'placeholder' => [
            'new_password' => 'Enter your new password',
            'new_password_confirmation' => 'Confirm your new password',
        ],
    ],
    'email' => [
        'reminder' => [
            'title' => 'Email reset password',
        ],
    ],
    'password_confirmation' => 'Password confirm',
    'failed' => 'Failed',
    'throttle' => 'Throttle',
    'not_member' => 'Not a member yet?',
    'register_now' => 'Register now',
    'lost_your_password' => 'Lost your password?',
    'login_title' => 'Admin',
    'login_via_social' => 'Login with social networks',
    'back_to_login' => 'Back to login page',
    'sign_in_below' => 'Sign In Below',
    'languages' => 'Languages',
    'reset_password' => 'Reset Password',
    'deactivated_message' => 'Your account has been deactivated. Please contact the administrator.',
    'settings' => [
        'email' => [
            'title' => 'ACL',
            'description' => 'ACL email configuration',
            'templates' => [
                'password_reminder' => [
                    'title' => 'Reset password',
                    'description' => 'Send email to user when requesting reset password',
                    'subject' => 'Reset Password',
                    'reset_link' => 'Reset password link',
                    'email_title' => 'Reset Password Instruction',
                    'email_message' => 'You are receiving this email because we received a password reset request for your account.',
                    'button_text' => 'Reset password',
                    'trouble_text' => 'If you\'re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <a href=":reset_link">:reset_link</a> and paste it into your browser. If you didn\'t request a password reset, please ignore this message or contact us if you have any questions.',
                ],
            ],
        ],
    ],
];
