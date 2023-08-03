<?php
/*
Plugin Name: User List Export Plugin
Description: A WordPress plugin to display and export a list of users with their name, email, and role.
Version: 1.0
Author: Oskars Tuns
*/

function user_list_export_admin_menu() {
    add_menu_page(
        'User List Export',
        'User List Export',
        'manage_options',
        'user-list-export',
        'user_list_export_page'
    );
}
add_action('admin_menu', 'user_list_export_admin_menu');

function user_list_export_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Retrieve the user list sorted by roles
    $users = get_users(array(
        'orderby' => 'meta_value',
        'meta_key' => 'wp_capabilities',
        'order' => 'ASC',
    ));

    // Output the user list table
    echo '<div class="wrap">';
    echo '<h1>User List Export</h1>';
    echo '<table class="wp-list-table widefat striped">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>';
    echo '<tbody>';
    foreach ($users as $user) {
        $name = $user->display_name;
        $email = $user->user_email;
        $roles = $user->roles;
        foreach ($roles as $role) {
            echo "<tr><td>$name</td><td>$email</td><td>$role</td></tr>";
        }
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
    echo '<input type="hidden" name="action" value="user_list_export_csv">';
    echo '<input type="submit" class="button" value="Export to CSV">';
    echo '</form>';
}


function user_list_export_to_csv() {
    // Retrieve the user list sorted by roles
    $users = get_users(array(
        'orderby' => 'meta_value',
        'meta_key' => 'wp_capabilities',
        'order' => 'ASC',
    ));

    // Prepare the CSV data
    $csv_data = "Name,Email,Role\n";
    foreach ($users as $user) {
        $name = $user->display_name;
        $email = $user->user_email;
        $roles = $user->roles;
        foreach ($roles as $role) {
            $csv_data .= "$name,$email,$role\n";
        }
    }

    // Set the headers to force download the CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="user_list.csv"');

    // Output the CSV data
    echo $csv_data;
    exit;
}
add_action('admin_post_user_list_export_csv', 'user_list_export_to_csv');

function register_user_list_export_csv_action() {
    add_action('admin_post_user_list_export_csv', 'user_list_export_to_csv');
}
add_action('admin_init', 'register_user_list_export_csv_action');
