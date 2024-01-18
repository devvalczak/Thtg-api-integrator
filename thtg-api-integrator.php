<?php
/*
Plugin Name: Thtg API form integrator
Version: 1.1
Author: devvalczak
*/

// Hook to enqueue scripts and styles for both front-end and admin
add_action('admin_enqueue_scripts', 'custom_form_action_enqueue_scripts');
add_action('wp_enqueue_scripts', 'custom_form_action_enqueue_scripts');

// Enqueue scripts
function custom_form_action_enqueue_scripts() {
    wp_enqueue_script('custom-form-action', plugins_url('/js/custom-form-action.js', __FILE__), array('jquery'), '1.0', true);
    wp_enqueue_style('custom-form-action-styles', plugins_url('/css/main.css', __FILE__));

    // Pass PHP variables to JavaScript
    wp_localize_script('custom-form-action', 'customFormActionParams', array(
        'formTable' => json_encode(get_option('custom_form_action_form_table')),
        'formNames' => json_encode(get_option('custom_form_action_form_names')),
        'apiEndpoint' => get_option('custom_form_action_api_endpoint'),
        'pluginUrl' => esc_js(plugin_dir_url(__FILE__)),
        'integrations' => json_encode(array("client")),
    ));
}

// Add settings link on plugin page
function custom_form_action_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=custom-form-action-settings">Ustawienia</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'custom_form_action_settings_link');

// Register settings
function custom_form_action_register_settings() {
    add_option('custom_form_action_form_table', array());
    add_option('custom_form_action_form_names', array());
    add_option('custom_form_action_api_endpoint', '');
    register_setting('custom_form_action_settings_group', 'custom_form_action_form_table');
    register_setting('custom_form_action_settings_group', 'custom_form_action_form_names');
    register_setting('custom_form_action_settings_group', 'custom_form_action_api_endpoint');
}
add_action('admin_init', 'custom_form_action_register_settings');

// Add settings page
function custom_form_action_settings_page() {
    add_options_page('Thtg API Form Integrator Settings', 'Thtg API Integrator', 'manage_options', 'custom-form-action-settings', 'custom_form_action_settings_page_content');
}
add_action('admin_menu', 'custom_form_action_settings_page');

// Settings page content
function custom_form_action_settings_page_content() {
    $integrations = array('client');
    $formNames = get_option('custom_form_action_form_names', array());
    ?>
    <div class="wrap thtgIntegrator">
        <h2>Thtg API Form Integrator Settings</h2>
        <form method="post" action="options.php" class="divi-integration-plugin">
            <?php settings_fields('custom_form_action_settings_group'); ?>
            <?php do_settings_sections('custom_form_action_settings_group'); ?>
            <h3>Ustawienia</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Endpoint API (public)</th>
                    <td class="apiEndpointWrapper" colspan="6">
                        <input class="apiEndpointInput" type="text" name="custom_form_action_api_endpoint" value="<?php echo esc_attr(get_option('custom_form_action_api_endpoint', '')); ?>" />
                    </td>
                </tr>
            </table>
            <h3>Nazwy pól formularzy dla integracji 'client'</h3>
            <table class="form-table">
                <thead>
                    <tr>
                        <th scope="row">name</th>
                        <th scope="row">surname</th>
                        <th scope="row">email</th>
                        <th scope="row">source</th>
                        <th scope="row">phone</th>
                        <th scope="row">message</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" name="custom_form_action_form_names[name]" value="<?php echo esc_attr($formNames['name']); ?>" />
                        </td>
                        <td>
                            <input type="text" name="custom_form_action_form_names[surname]" value="<?php echo esc_attr($formNames['surname']); ?>" />
                        </td>
                        <td>
                            <input type="text" name="custom_form_action_form_names[email]" value="<?php echo esc_attr($formNames['email']); ?>" />
                        </td>
                        <td>
                            <input type="text" name="custom_form_action_form_names[source]" value="<?php echo esc_attr($formNames['source']); ?>" />
                        </td>
                        <td>
                            <input type="text" name="custom_form_action_form_names[phone]" value="<?php echo esc_attr($formNames['phone']); ?>" />
                        </td>
                        <td>
                            <input type="text" name="custom_form_action_form_names[message]" value="<?php echo esc_attr($formNames['message']); ?>" />
                        </td>
                    </tr>
                <tbody>
            </table>
            <h3>Integracje</h3>
            <table class="form-table" id="form-table">
                <thead>
                    <tr valign="top">
                        <th scope="col">ID formularza</th>
                        <th scope="col">Integracja</th>
                        <th scope="col">ID oddziału (branch)</th>
                        <th scope="col">Callback</th>
                        <th scope="col">JS</th>
                        <th scope="col">Usuń domyślne akcje</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                        $formTable = get_option('custom_form_action_form_table', array());
                        if (!empty($formTable)) foreach ($formTable as $index => $formRow) {
                    ?>
                        <tr valign="top">
                            <td>
                                <input type="text" spellcheck="false" name="custom_form_action_form_table[<?php echo $index; ?>][form_id]" value="<?php echo esc_attr($formRow['form_id']); ?>" />
                            </td>
                            <td>
                                <select name="custom_form_action_form_table[<?php echo $index; ?>][integration]">
                                    <?php
                                        foreach ($integrations as $integration) {
                                            $selected = ($integration === $formRow['integration']) ? 'selected="selected"' : '';
                                            echo "<option value='$integration' $selected>$integration</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="custom_form_action_form_table[<?php echo $index; ?>][branch]" value="<?php echo esc_attr($formRow['branch']); ?>" />
                            </td>
                            <td>
                                <input class="monospace" spellcheck="false" type="text" name="custom_form_action_form_table[<?php echo $index; ?>][callback]" value="<?php echo esc_attr($formRow['callback']); ?>" />
                            </td>
                            <td>
                                <textarea class="scriptTextarea monospace" spellcheck="false" name="custom_form_action_form_table[<?php echo $index; ?>][script]"><?php echo esc_attr($formRow['script']); ?></textarea>
                            </td>
                            <td>
                                <input type="checkbox" name="custom_form_action_form_table[<?php echo $index; ?>][prevent]" <?php echo isset($formRow['prevent']) && $formRow['prevent'] ? 'checked' : ''; ?> />
                            </td>
                            <td>
                                <a href="#" class="remove-form-row">Usuń</a>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                            <tr valign="top">
                                <td colspan="6" id="noRows">Brak aktywnych integracji</td>
                            </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <a href="#" class="add-form-row">Dodaj nową integrację</a>
            <?php submit_button(); ?>
        </form>
        <div class="readme monospace">
            Integracja `client`: <br />
            Wymagane pola: `name`, `surname`, `email`, `source`<br />
            Pola opcjonalne: `phone`, `message`
        </div>
    </div>
    <?php
}
