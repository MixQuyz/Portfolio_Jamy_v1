<?php

namespace MetForm\Core\Forms;

use MetForm\Core\Entries\Map_El;

defined('ABSPATH') || exit;

class Api extends \MetForm\Base\Api
{

    public function config()
    {
        $this->prefix = 'forms';
        $this->param = "/(?P<id>\w+)";
    }

    public function post_update()
    {
        $form_id = $this->request['id'];

        $form_setting = $this->request->get_params();


        /**
         * Hubspot form settings save
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Crm\Hubspot\Hubspot')) {


            if (isset($form_setting['mf_hubspot_form_guid'])) {
                $fields = [];

                foreach ($form_setting as $key => $value) {

                    if (strpos($key, 'mf_hubspot_form_field_name_') !== false) {
                        array_push($fields, [$key => $value]);
                    }
                }

                update_option('mf_hubspot_form_guid_' . $form_id, $form_setting['mf_hubspot_form_guid']);
                update_option('mf_hubspot_form_portalId_' . $form_id, $form_setting['mf_hubspot_form_portalId']);
                update_option('mf_hubspot_form_data_' . $form_id, $fields);
            }


        }

        /**
         * Mailster form settings
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Crm\Hubspot\Hubspot')) {


            if (isset($form_setting['mf_mailster_list_id'])) {
                $fields = [];


                foreach ($form_setting as $key => $value) {

                    if (strpos($key, 'mailster_field_') !== false) {
                        array_push($fields, [$key => $value]);
                    }
                }

                update_option('mf_mailster_form_data_' . $form_id, $fields);
            }


        }


        /**
         * Auth / Registration settings save
         */


        if (class_exists('\MetForm_Pro\Core\Integrations\Email\Mailster\Mailster')) {

            if (isset($form_setting['mf_auth_reg_user_name'])) {

                $data = [

                    'mf_auth_reg_user_name' => $form_setting['mf_auth_reg_user_name'],
                    'mf_auth_reg_user_email' => $form_setting['mf_auth_reg_user_email'],
                    'mf_auth_reg_role' => $form_setting['mf_auth_reg_role']

                ];

                update_option('mf_auth_reg_settings_' . $form_id, $data);
            }


        }

        /**
         * Auth / Login settings save
         */
        if (class_exists('\MetForm_Pro\Core\Integrations\Auth\Login\Login')) {


            if (isset($form_setting['mf_auth_login_user_name'])) {
                $data = [

                    'mf_auth_login_user_name' => $form_setting['mf_auth_login_user_name'],
                    'mf_auth_login_user_password' => $form_setting['mf_auth_login_user_password']

                ];

                update_option('mf_auth_login_settings_' . $form_id, $data);
            }


        }

        /**
         * Post submission
         */
        if (class_exists('MetForm_Pro\Core\Integrations\Post\Form_To_Post\Post')) {


            if (isset($form_setting['mf_post_submission_post_type'])) {
                $data = [

                    'mf_post_submission_post_type' => $form_setting['mf_post_submission_post_type'],
                    'mf_post_submission_title' => isset($form_setting['mf_post_submission_title']) ? $form_setting['mf_post_submission_title'] : '',
                    'mf_post_submission_content' => isset($form_setting['mf_post_submission_content']) ? $form_setting['mf_post_submission_content'] : '' ,
                    'mf_post_submission_author' => isset($form_setting['mf_post_submission_author']) ? $form_setting['mf_post_submission_author'] : '',
                    'mf_post_submission_featured_image' => isset($form_setting['mf_post_submission_featured_image']) ? $form_setting['mf_post_submission_featured_image'] : '',

                ];

                /**
                 * If Custom field settings available
                 */
                if (isset($form_setting['mf_post_submission_custom_fields_name'])) {
                    $custom_fields_settings = [];
                    $mf_custom_fields = isset($form_setting['mf_post_submission_custom_fields_name']) ? $form_setting['mf_post_submission_custom_fields_name'] : '';
                    $mf_field_names =  isset($form_setting['mf_post_submission_mf_field_name']) ? $form_setting['mf_post_submission_mf_field_name'] : '';
                    $total_field_count = count($mf_custom_fields);
                    for ($index = 0; $index <= $total_field_count; $index++) {
                        if (!empty($mf_custom_fields[$index])) {
                            $custom_fields_settings[$mf_custom_fields[$index]] = $mf_field_names[$index];
                        }
                    }

                    update_option('mf_post_submission_custom_fields_' . $form_id, $custom_fields_settings );
                }

                update_option('mf_post_submission_' . $form_id, $data);
            }


        }


        return Action::instance()->store($form_id, $form_setting);
    }

    public function get_get()
    {
        $form_id = $this->request['id'];

        return Action::instance()->get_all_data($form_id);
    }

    public function get_builder()
    {
        $form_id = $this->request['id'];
        return Builder::instance()->get_editor($form_id);
    }

    public function get_form_content()
    {
        $form_id = $this->request['id'];
        return Builder::instance()->get_form_content($form_id);
    }

    public function get_builder_form_id()
    {
        $title = $this->request['title'];
        $template_id = $this->request['id'];
        return Builder::instance()->create_form($title, $template_id);
    }

    public function get_templates()
    {
        $form_id = $this->request['id'];
        $args = array(
            'post_type' => Base::instance()->form->get_name(),
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        $forms = get_posts($args);

        foreach ($forms as $form) {
            echo '<option value="' . $form->ID . '" ' . selected($form_id, $form->ID, false) . '>' . $form->post_title . '</option>';
        }

        exit();
    }

    public function post_views()
    {
        $form_id = $this->request['id'];
        return Action::instance()->count_views($form_id);
    }

    /**
     * Store hubspot forms
     */

    public function get_hubspot_forms()
    {

        $form_id = $this->request['id'];

        $key = 'hubsopt_forms_' . $form_id . '_';

        $data = \MetForm\Core\Forms\Action::instance()->get_all_data($form_id);

        $token = $data['mf_hubsopt_token'];

        $forms = json_decode(file_get_contents('https://api.hubapi.com/forms/v2/forms?hapikey=' . $token));

        $save_forms = [];
        foreach ($forms as $form) {
            array_push($save_forms, [
                'portalId' => $form->portalId,
                'guid' => $form->guid,
                'name' => $form->name,
            ]);
        }

        update_option('mf_hubspot_saved_forms', $save_forms);

        update_option($key, $forms);
        return get_option($key);
    }

    public function get_get_hubspot_forms()
    {
        $key = $form_id = $this->request['id'];

        $key = 'hubsopt_forms_' . $form_id . '_';

        return get_option($key);
    }

    public function post_hubspot_form_fields()
    {
        $form_id = $this->request['id'];

        $form_guid = $this->request['guid'];

        $data = \MetForm\Core\Forms\Action::instance()->get_all_data($form_id);

        $token = $data['mf_hubsopt_token'];

        $fields = json_decode(file_get_contents('https://api.hubapi.com/forms/v2/fields/' . $form_guid . '?hapikey=' . $token));
        return $fields;

    }

    public function get_get_fields_data()
    {

        $form_id = $this->request['id'];
        $input_widgets = \Metform\Widgets\Manifest::instance()->get_input_widgets();

        $widget_input_data = get_post_meta($form_id, '_elementor_data', true);
        $widget_input_data = json_decode($widget_input_data);

        return Map_El::data($widget_input_data, $input_widgets)->get_el();
    }

    public function get_get_mailster_forms()
    {
        return (new \MetForm_Pro\Core\Integrations\Email\Mailster\Api())->get_forms();
    }

    public function get_get_mailster_form()
    {
        $form_id = $this->request['id'];
        return (new \MetForm_Pro\Core\Integrations\Email\Mailster\Api())->get_form($form_id);
    }

    public function get_get_mailster_form_data()
    {
        $form_id = $this->request['id'];
        return get_option('mf_mailster_form_data_' . $form_id);
    }

    public function get_get_helpscout_access_token(){
      
        $app_id = \MetForm\Utils\Util::get_form_settings('mf_helpscout_app_id');
        $app_secret = \MetForm\Utils\Util::get_form_settings('mf_helpscout_app_secret');

        $url = 'https://api.helpscout.net/v2/oauth2/token?grant_type=client_credentials&client_id='.$app_id.'&client_secret=' . $app_secret;
        $response = wp_remote_post($url,[
            'method' => 'POST',
        ]);

        if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$return['status'] = 0;
			$return['msg'] = "Something went wrong: " . esc_html($error_message);
		} else {
            $access_token = (json_decode($response['body'],true))['access_token'];
            $return['status'] = 200;
            $return['msg'] = 'Helpscout access token';
            $return['access_token'] = $access_token;
            update_option('mf_helpscout_access_token',$access_token);
        }
        
        $url = 'https://api.helpscout.net/v2/mailboxes';

    $mailboxes = [];

    $token = $access_token;

    $response = wp_remote_get($url, [
      'method' => 'GET',
      'headers' => [

        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json; charset=utf-8'
      ],
    ]);

    $data = json_decode( wp_remote_retrieve_body( $response ) , true);

    foreach ($data['_embedded']['mailboxes'] as $mailbox) {

        array_push( $mailboxes, [
            'id' => $mailbox['id'],
            'name' => $mailbox['name']
        ]);
        
    }

    update_option('mf_helpscout_mailboxes',$mailboxes);

		return $return;
    }

    public function get_get_helpscout_mailboxes(){
        return get_option('mf_helpscout_mailboxes');
    }

    public function get_get_helpscout_mailbox(){
        return  \MetForm\Utils\Util::get_form_settings('mf_helpscout_mailbox');
    }

}
