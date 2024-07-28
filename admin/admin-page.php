<?php
function webtoapp_admin_page() {
    // I start the HTML output for the admin page
?>
    <div class="wrap">
        <h1>Web to APK Generator</h1>
        <!-- I added a description for the plugin functionality -->
        <p>This plugin allows you to generate an APK from your WordPress website using GitHub Actions. Please enter the URL of your website below and click "Generate APK" to start the process.</p>
        <!-- I created a form for inputting the website URL -->
        <form id="webtoapp-form">
            <!-- I included a hidden field for the security nonce -->
            <input type="hidden" name="security" value="<?php echo wp_create_nonce('webtoapp_generate_apk_nonce'); ?>">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Website URL</th>
                    <!-- I added an input field for the website URL -->
                    <td><input type="url" id="webtoapp_url" name="webtoapp_url" value="" class="regular-text" required /></td>
                </tr>
            </table>
            <p class="submit">
                <!-- I added a submit button to trigger the APK generation -->
                <input type="submit" class="button-primary" value="Generate APK" />
            </p>
        </form>
        <!-- I added a div to display the result of the APK generation request -->
        <div id="webtoapp-result"></div>
        <?php if ($download_link = webtoapp_get_download_link()): ?>
            <!-- I added a download link for the latest APK if available -->
            <a href="<?php echo esc_url($download_link); ?>" class="button">Download Latest APK</a>
        <?php endif; ?>
    </div>
<?php
    // I close the HTML output for the admin page
}
?>
