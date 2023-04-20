<?php
/**
 * Plugin Name: Easy Google Analytics Integration
 * Plugin URI: #
 * Description: Easily add Google Analytics tracking code to your WordPress site.
 * Version: 1.0
 * Author: Amine Ouhannou
 * Author URI: #
 */

add_action('admin_menu', 'ega_plugin_menu');
add_action('admin_init', 'ega_plugin_settings_init');
add_action('wp_footer', 'ega_plugin_insert_tracking_code');

function ega_plugin_menu() {
    add_options_page('Google Analytics Settings', 'Easy GA4 Integration', 'manage_options', 'ega_plugin_settings', 'ega_plugin_settings_page');
}

function ega_plugin_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    ?>
    <div class="wrap">
    <h2>Easy Google Analytics Integration Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('ega_plugin_settings_group'); ?>
        <?php do_settings_sections('ega_plugin_settings_group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">GA4 Measurement ID</th>
                <td>
                    <input type="text" name="ega_ga4_measurement_id" value="<?php echo esc_attr(get_option('ega_ga4_measurement_id')); ?>" />
                    <p class="description">Enter your Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX).</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Anonymize IP Addresses</th>
                <td>
                    <input type="checkbox" name="ega_anonymize_ip" value="1" <?php checked(get_option('ega_anonymize_ip', 0), 1); ?> />
                    <p class="description">Enable this option to anonymize visitors' IP addresses in Google Analytics.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">GDPR Compliance</th>
                <td>
                    <input type="checkbox" name="ega_gdpr_compliance" value="1" <?php checked(get_option('ega_gdpr_compliance', 0), 1); ?> />
                    <p class="description">Enable this option to include a JavaScript function for users to opt-out of Google Analytics tracking.</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

    <?php
}

function ega_plugin_settings_init() {
    register_setting('ega_plugin_settings_group', 'ega_ga4_measurement_id');
    register_setting('ega_plugin_settings_group', 'ega_anonymize_ip');
    register_setting('ega_plugin_settings_group', 'ega_gdpr_compliance');
    register_setting('ega_plugin_settings_group', 'ega_use_ga4', array('default' => 1));
}

function ega_plugin_insert_tracking_code() {
    $options = [
        'ga4_measurement_id' => get_option('ega_ga4_measurement_id'),
        'anonymize_ip' => get_option('ega_anonymize_ip'),
        'gdpr_compliance' => get_option('ega_gdpr_compliance'),
        'use_ga4' => get_option('ega_use_ga4'),
    ];

    $ga4_measurement_id = $options['ga4_measurement_id'];
    $anonymize_ip = $options['anonymize_ip'];
    $gdpr_compliance = $options['gdpr_compliance'];
    $use_ga4 = $options['use_ga4'];

    if ($use_ga4 && $ga4_measurement_id) {
        // Add GA4 tracking code
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($ga4_measurement_id) . '"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag("consent", "default", {
              ad_storage: "denied",
              analytics_storage: "denied"
          });
          gtag("set", "allow_google_signals", false);
          gtag("set", "anonymize_ip", ' . ($anonymize_ip ? 'true' : 'false') . ');
          gtag("set", "send_to", "' . esc_attr($ga4_measurement_id) . '");
          gtag("event", "page_view", { send_to: "' . esc_attr($ga4_measurement_id) . '" });
        </script>';

        // Check if GDPR compliance is enabled
        if ($gdpr_compliance) {
            echo '<script>
              function gaOptout() {
                  document.cookie = "ga-disable-' . esc_attr($ga4_measurement_id) . '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/";
                  window["ga-disable-' . esc_attr($ga4_measurement_id) . '"] = true;
              }
              </script>';
        }
    }
}