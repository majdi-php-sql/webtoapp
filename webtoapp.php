<?php
/*
Plugin Name: Web to APK Generator
Description: A plugin to generate an APK from a WordPress website using GitHub Actions.
Version: 1.1
Author: Majdi M. S. Awad
*/

// I included the admin page
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// I enqueue admin scripts and styles
function webtoapp_enqueue_admin_scripts($hook) {
    if ($hook != 'toplevel_page_webtoapp') {
        return;
    }
    wp_enqueue_style('webtoapp-admin-style', plugin_dir_url(__FILE__) . 'admin/admin-style.css');
    wp_enqueue_script('webtoapp-admin-script', plugin_dir_url(__FILE__) . 'admin/js/admin-script.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'webtoapp_enqueue_admin_scripts');

// I added the admin menu item
function webtoapp_admin_menu() {
    add_menu_page(
        'Web to APK Generator',     // I set the page title
        'Web to APK',               // I set the menu title
        'manage_options',           // I defined the capability required to access the page
        'webtoapp',                 // I defined the menu slug
        'webtoapp_admin_page',      // I linked the function to display the page content
        'dashicons-smartphone',     // I chose a dashicons icon
        6                           // I set the position in the menu
    );
}
add_action('admin_menu', 'webtoapp_admin_menu');

// I handle the APK generation request
function webtoapp_generate_apk() {
    check_ajax_referer('webtoapp_generate_apk_nonce', 'security'); // I verify the AJAX request using a nonce

    if (!current_user_can('manage_options')) { // I check if the user has the necessary permissions
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to perform this action.']);
        wp_die();
    }

    if (!isset($_POST['webtoapp_url']) || empty($_POST['webtoapp_url'])) { // I ensure the URL is provided
        echo json_encode(['status' => 'error', 'message' => 'URL is required']);
        wp_die();
    }

    $site_url = esc_url_raw($_POST['webtoapp_url']); // I sanitize the URL input
    $github_token = 'YOUR_TOKEN'; // I used my GitHub token (should be replaced with your token)

    $api_url = 'https://api.github.com/repos/YOUR_USER/repositories/dispatches'; // I set the GitHub API URL
    $body = json_encode([
        'event_type' => 'build_apk',
        'client_payload' => [
            'site_url' => $site_url
        ]
    ]); // I prepare the request body with the event type and site URL

    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $github_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ],
        'body' => $body
    ]); // I send the POST request to GitHub API

    if (is_wp_error($response)) { // I check for errors in the response
        $error_message = $response->get_error_message();
        error_log("Failed to trigger GitHub Actions build: $error_message");
        echo json_encode(['status' => 'error', 'message' => 'Failed to trigger the GitHub Actions build. Please check the plugin error log for details.']);
        wp_die();
    }

    $response_code = wp_remote_retrieve_response_code($response); // I retrieve the response code
    if ($response_code != 204) { // I check if the response code is not 204 (success)
        $response_body = wp_remote_retrieve_body($response);
        error_log("Failed to trigger GitHub Actions build: $response_body");
        echo json_encode(['status' => 'error', 'message' => 'Failed to trigger the GitHub Actions build. Please check the plugin error log for details.']);
        wp_die();
    }

    echo json_encode(['status' => 'success', 'message' => 'GitHub Actions build triggered successfully. The APK will be available for download once the build is complete.']); // I send a success response
    wp_die();
}
add_action('wp_ajax_webtoapp_generate_apk', 'webtoapp_generate_apk');

// I fetch the latest release information from GitHub
function webtoapp_get_latest_release() {
    $github_token = 'YOUR_TOKEN'; // Replace with your GitHub token

    $api_url = 'https://api.github.com/repos/YOUR_USER/repositories/releases/latest'; // I set the GitHub API URL for the latest release
    $response = wp_remote_get($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $github_token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ]
    ]); // I send the GET request to GitHub API

    if (is_wp_error($response)) { // I check for errors in the response
        return null;
    }

    $response_code = wp_remote_retrieve_response_code($response); // I retrieve the response code
    if ($response_code != 200) { // I check if the response code is not 200 (success)
        return null;
    }

    $response_body = wp_remote_retrieve_body($response); // I retrieve the response body
    $release_data = json_decode($response_body, true); // I decode the JSON response
    return $release_data;
}

// I get the download link for the latest APK release
function webtoapp_get_download_link() {
    $release_data = webtoapp_get_latest_release(); // I fetch the latest release data
    if (!$release_data || !isset($release_data['assets'][0]['browser_download_url'])) { // I check if the release data is valid
        return null;
    }
    return $release_data['assets'][0]['browser_download_url']; // I return the download link
}
?>
