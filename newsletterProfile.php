<?php
/**
 * Template name: Newsletter Profile
 */
get_header();
$user = \NewsletterProfile::instance()->get_user_by_wp_user_id(get_current_user_id());
echo NewsletterProfile::instance()->get_profile_url($user);
get_footer();