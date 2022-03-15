<?php

namespace MetForm\Core\Entries;

use MetForm_Pro\Utils\Helper;

defined('ABSPATH') || exit;

class Action
{
    use \MetForm\Traits\Singleton;

    private $key_form_id;
    private $key_form_data;
    //private $key_form_settings;
    private $key_browser_data;
    private $key_form_total_entries;
    private $key_form_file;
    private $key_payment_status;
    private $post_type;

    private $fields;
    private $entry_id;
    private $form_id;
    private $form_data;
    private $form_settings;
    private $title;
    private $entry_count;
    private $email_name;
    private $file_upload_info;

    private $inserted_form_data;

    private $response;

    public function __construct()
    {
        $this->response = (object) [
            'status' => 0,
            'error' => [
                esc_html__('Some thing went wrong.', 'metform'),
            ],
            'data' => [
                'message' => '',
            ],
        ];

        //$this->key_form_settings = 'metform_form__form_setting';
        $this->key_form_total_entries = 'metform_form__form_total_entries';
        $this->key_browser_data = 'metform_form__entry_browser_data';
        $this->key_form_id = 'metform_entries__form_id';
        $this->key_form_data = 'metform_entries__form_data';
        $this->key_form_file = 'metform_entries__file_upload';
        $this->key_payment_status = 'metform_entries__payment_status';
        $this->post_type = Base::instance()->cpt->get_name();
    }

    public function submit($form_id, $form_data, $file_data, $page_id = '')
    {

        $this->fields = $this->get_fields($form_id);
        if(count(File_Data_Validation::validate($this->fields, $file_data)) > 0){
            return false; // backend validation
        }
        $this->form_id = $form_id;
        $this->title = get_the_title($this->form_id);
        //$this->form_settings = $this->get_form_settings($form_id);
        $this->form_settings = \MetForm\Core\Forms\Action::instance()->get_all_data($form_id);


        $this->response->data['redirect_to'] = (!isset($this->form_settings['redirect_to'])) ? '' : $this->form_settings['redirect_to'];
        $this->response->data['hide_form'] = (!isset($this->form_settings['hide_form_after_submission']) ? '' : $this->form_settings['hide_form_after_submission']);
        $this->response->data['form_data'] = $form_data;
        //$this->response->data['form_setting'] = $this->form_settings;
        //$this->response->data['map_data'] = $this->fields;

        $email_name = $this->get_input_name_by_widget_type('mf-email');
        $this->email_name = (isset($email_name[0]) ? $email_name[0] : null);

        // nonce check
        if (!$this->mf_is_woo_exists()) {
            if (!isset($form_data['form_nonce']) || !wp_verify_nonce($form_data['form_nonce'], 'form_nonce')) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Unauthorized submission.', 'metform');
                return $this->response;
            }
        }


        // validate form with max length, min length, length type and expression
        $validate = $this->validate_form_data($form_data);
        if ($validate == false) {
            $this->response->status = 0;
            return $this->response;
        }

        // google recaptcha condition and action
        if ((isset($form_data['g-recaptcha-response']) || isset($form_data['g-recaptcha-response-v3'])) && (isset($this->fields['mf-recaptcha'])) && (isset($this->form_settings['mf_recaptcha_site_key'])) && $this->form_settings['mf_recaptcha_site_key'] != '') {
            if (isset($form_data['g-recaptcha-response']) && ($form_data['g-recaptcha-response'] == "")) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Please solve the recaptcha.', 'metform');
                return $this->response;
            }

            if ((isset($this->form_settings['mf_recaptcha_version']) && ($this->form_settings['mf_recaptcha_version'] == 'recaptcha-v3')) && (!isset($form_data['g-recaptcha-response-v3']) || ($form_data['g-recaptcha-response-v3'] == ""))) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Google captcha token not found.', 'metform');
                return $this->response;
            }

            if ((isset($this->form_settings['mf_recaptcha_version']) && ($this->form_settings['mf_recaptcha_version'] == 'recaptcha-v2')) && isset($form_data['g-recaptcha-response'])) {
                $response = \MetForm\Core\Integrations\Google_Recaptcha::instance()->verify_captcha_v2($form_data, $this->form_settings);
            }

            if ((isset($this->form_settings['mf_recaptcha_version']) && ($this->form_settings['mf_recaptcha_version'] == 'recaptcha-v3')) && isset($form_data['g-recaptcha-response-v3'])) {
                $response = \MetForm\Core\Integrations\Google_Recaptcha::instance()->verify_captcha_v3($form_data, $this->form_settings);
            }

            //$this->response->data['responseKeys'] = $response['responseKeys'];
            $this->response->status = $response['status'];
            if ($response['status'] == 0) {
                $this->response->error[] = (isset($response['error']) ? $response['error'] : '');
                return $this->response;
            }
        }

        // Captcha solve conditiona and action
        if (isset($form_data['mf-captcha-challenge'])) {
            if (($form_data['mf-captcha-challenge']) == "") {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Please solve the recaptcha.', 'metform');
                return $this->response;
            }

            session_start();

            $time = $_SERVER['REQUEST_TIME'];

            $timeout_duration = 1800;

            if (
                isset($_SESSION['LAST_ACTIVITY']) && ($time - $_SESSION['LAST_ACTIVITY']) > $timeout_duration
            ) {
                session_unset();
                session_destroy();
                session_start();
            }

            $_SESSION['LAST_ACTIVITY'] = $time;

            if (!isset($_SESSION['mf_captcha_text'])) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Time out of this captcha. Please reload or choose another one.', 'metform');
                return $this->response;
            }

            if (isset($form_data['mf-captcha-challenge']) && isset($_SESSION['mf_captcha_text']) && ($form_data['mf-captcha-challenge'] == $_SESSION['mf_captcha_text'])) {
                $this->response->status = 1;
                //$this->response->data['captcha'] = esc_html__('Captcha is verified.', 'metform');
            } else {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Enter correct captcha.', 'metform');
                return $this->response;
            }
        }

        // user login check and action
        $required_loggin = isset($this->form_settings['require_login']) ? ((int) ($this->form_settings['require_login'])) : 0;

        if (($required_loggin == 1) && (!is_user_logged_in())) {
            $this->response->status = 0;
            $this->response->error[] = esc_html__('You must be logged in to submit form.', 'metform');
            return $this->response;
        }

        // Total entry limit check and prevent
        if (isset($this->form_settings['limit_total_entries_status'])) {
            $entry_limit = ((int) ($this->form_settings['limit_total_entries_status']));

            if (($entry_limit == 1) && ($this->get_entry_count() >= $this->form_settings['limit_total_entries'])) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('Form submission limit execed.', 'metform');

                return $this->response;
            }
        }

        // signature input upload as image from base64
        if (class_exists('\MetForm_Pro\Base\Package') && isset($form_data['mf-signature'])) {

            $signature_input = $this->get_input_name_by_widget_type('mf-signature');
            $this->response->data['signature_name'] = $signature_input;
            $this->response->data['signature_input_data'] = $form_data['mf-signature'];

            if ($signature_input != null) {
                $inputs = (is_array($signature_input) ? $signature_input : []);
                foreach ($inputs as $input) {
                    $b64string = isset($form_data[$input]) ? $form_data[$input] : '';
                    $status = $this->covert_base64_to_png($input, $b64string);
                    $form_data[$input] = (isset($status['url']) ? $status['url'] : '');
                }
            }
            //$this->response->data['signature_input'] = $signature_input;
        }

         // file upload check and action
         $file_input_names = $this->get_input_name_by_widget_type('mf-file-upload');

         if ((!empty($file_data)) && ($file_input_names != null)) {
             $this->upload_file($file_data, $file_input_names);
         }


        // Hubspot CRM integration

        if (class_exists('\MetForm_Pro\Core\Integrations\Crm\Hubspot\Hubspot')) {

            $hubspot = new \MetForm_Pro\Core\Integrations\Crm\Hubspot\Hubspot();

            if (isset($this->form_settings['mf_hubspot']) && $this->form_settings['mf_hubspot'] == '1') {

                $hubspot->create_contact($form_data, ['email_name' => $this->email_name]);
            }

            if (isset($this->form_settings['mf_hubspot_forms']) && $this->form_settings['mf_hubspot_forms'] == '1') {

                $hubspot->submit_data($form_id, $form_data, $this->form_settings);
            }
        }

        /**
         * ============================
         *      Zoho Integrations
         * ============================
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Crm\Zoho\Zoho')) {

            // Check if settings exists or enabled

            if (isset($this->form_settings['mf_zoho']) && $this->form_settings['mf_zoho'] == '1') {

                $zoho = new \MetForm_Pro\Core\Integrations\Crm\Zoho\Zoho();
                $zoho->create_contact($form_data, ['email_name' => $this->email_name]);
            }
        }

        /**
         * ==============================
         *      Helpscout Integration
         * ==============================
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Crm\Helpscout\Helpscout')) {

            // Check if settings exists and enabled

            if (isset($this->form_settings['mf_helpscout']) && $this->form_settings['mf_helpscout'] == '1') {

                $file_input_names = $this->get_input_name_by_widget_type('mf-file-upload');

                // error_log( serialize( $this->file_upload_info ));

                (new \Metform_Pro\Core\Integrations\Crm\Helpscout\Helpscout)
                    ->create_ticket(
                        $form_data,
                        $this->form_settings,
                        $file_input_names,
                        $file_data,
                        $this->file_upload_info
                    );
            }
        }

        /**
         * ================================
         *      Auth / Registrations
         * ================================
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Auth\Register\Register')) {

            if (isset($this->form_settings['mf_registration']) && $this->form_settings['mf_registration'] == '1') {

                $register = new \MetForm_Pro\Core\Integrations\Auth\Register\Register();
                $register->action($form_id, $form_data);
            }
        }

        /**
         * ==============================
         *          Mailster action
         * ==============================
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Email\Mailster\Mailster')) {

            if (isset($this->form_settings['mf_mailster']) && $this->form_settings['mf_mailster'] == '1') {
                $mailster = new \MetForm_Pro\Core\Integrations\Email\Mailster\Mailster();
                $mailster->action($form_id, $form_data, $this->form_settings);
            }
        }

        /**
         * ========================
         *      Auth / Login
         * ========================
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Auth\Login\Login')) {

            if (isset($this->form_settings['mf_login']) && $this->form_settings['mf_login'] == '1') {

                $login = new \MetForm_Pro\Core\Integrations\Auth\Login\Login();
                $login->action($form_id, $form_data);
            }
        }


        // do action for pro integration

	    do_action("metform_pro_form_data_for_pro_integrations", $this->form_settings, $form_data, $this->email_name);


        // mailchimp email store action
        if (class_exists('\MetForm\Core\Integrations\Mail_Chimp')) {
            if (isset($this->form_settings['mf_mail_chimp']) && $this->form_settings['mf_mail_chimp'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $mail_chimp = new \MetForm\Core\Integrations\Mail_Chimp();

                if (array_key_exists("mf-listing-optin", $this->fields) && isset($form_data['mf-listing-optin'])) {
                    $response = $mail_chimp->call_api($form_data, ['auth' => $this->form_settings, 'email_name' => $this->email_name]);
                } elseif (!array_key_exists('mf-listing-optin', $this->fields)) {
                    $response = $mail_chimp->call_api($form_data, ['auth' => $this->form_settings, 'email_name' => $this->email_name]);
                }

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        // ActiveCampaign email store action

        if (class_exists('MetForm_Pro\Core\Integrations\Email\Activecampaign\Active_Campaign')) {
            if (isset($this->form_settings['mf_active_campaign']) && $this->form_settings['mf_active_campaign'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $active_campaign = new \MetForm_Pro\Core\Integrations\Email\Activecampaign\Active_Campaign();

                $response = $active_campaign->call_api($form_data, ['auth' => $this->form_settings, 'email_name' => $this->email_name]);

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
                $this->response->active_campaign = isset($response['msgs']) ? $response['msgs'] : [];
            }
        }

        // GetResponse email store action

        if (class_exists('\MetForm_Pro\Core\Integrations\Email\Getresponse\Get_Response')) {

            if (isset($this->form_settings['mf_get_response']) && $this->form_settings['mf_get_response'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $get_response = new \MetForm_Pro\Core\Integrations\Email\Getresponse\Get_Response();

                $response = $get_response->call_api($form_data, ['auth' => $this->form_settings, 'email_name' => $this->email_name]);

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        // data submit to zapier action and check
        if (class_exists('\MetForm_Pro\Core\Integrations\Zapier')) {
            if (isset($this->form_settings['mf_zapier']) && $this->form_settings['mf_zapier'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $url = $this->form_settings['mf_zapier_webhook'];

                if (array_key_exists('mf-listing-optin', $this->fields) && isset($form_data['mf-listing-optin'])) {

                    $zapier = new \MetForm_Pro\Core\Integrations\Zapier();
                    $response = $zapier->call_webhook($form_data, ['url' => $url, 'email_name' => $this->email_name]);
                } elseif (!array_key_exists('mf-listing-optin', $this->fields)) {

                    $zapier = new \MetForm_Pro\Core\Integrations\Zapier();
                    $response = $zapier->call_webhook($form_data, ['url' => $url, 'email_name' => $this->email_name]);
                }

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        // data submit to slack check and action
        if (class_exists('\MetForm\Core\Integrations\Slack')) {
            if (isset($this->form_settings['mf_slack']) && $this->form_settings['mf_slack'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $url = $this->form_settings['mf_slack_webhook'];

                //$this->response->data['slack_hook'] = $url;

                if (array_key_exists('mf-listing-optin', $this->fields) && isset($form_data['mf-listing-optin'])) {

                    $slack = new \MetForm\Core\Integrations\Slack();
                    $response = $slack->call_webhook($form_data, ['url' => $url, 'email_name' => $this->email_name]);
                } elseif (!array_key_exists('mf-listing-optin', $this->fields)) {

                    $slack = new \MetForm\Core\Integrations\Slack();
                    $response = $slack->call_webhook($form_data, ['url' => $url, 'email_name' => $this->email_name]);
                }

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        /**
         * Checking if convertKit is exists
         * If exists calling the api
         */
        if (class_exists('\MetForm_Pro\Core\Integrations\Convert_Kit')) {
            if (isset($this->form_settings['mf_convert_kit']) && $this->form_settings['mf_convert_kit'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $cKit = new \MetForm_Pro\Core\Integrations\Convert_Kit(false);

                $response = $cKit->call_api($form_data, ['mail_settings' => $this->form_settings, 'email_name' => $this->email_name]);

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        /*
         * Aweber integration
         *
         */
        if (class_exists('\MetForm_Pro\Core\Integrations\Aweber')) {
            if (isset($this->form_settings['mf_mail_aweber']) && $this->form_settings['mf_mail_aweber'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $aweber = new \MetForm_Pro\Core\Integrations\Aweber(false);

                $response = $aweber->call_api($form_data, ['mail_settings' => $this->form_settings, 'email_name' => $this->email_name]);

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }



        if (defined('MAILPOET_VERSION') && class_exists('\MetForm_Pro\Core\Integrations\Mail_Poet')) {
            if (isset($this->form_settings['mf_mail_poet']) && $this->form_settings['mf_mail_poet'] == '1' && $this->email_name != null && $form_data[$this->email_name] != '') {

                $mPoet = new \MetForm_Pro\Core\Integrations\Mail_Poet(false);

                $response = $mPoet->call_api($form_data, ['mail_settings' => $this->form_settings, 'email_name' => $this->email_name]);

                $this->response->status = isset($response['status']) ? $response['status'] : 0;
            }
        }

        if (class_exists('Metform_Pro\Core\Integrations\Fluent_Crm')) {
            if (isset($this->form_settings['mf_fluent']) && $this->form_settings['mf_fluent'] == '1' && $this->email_name != null && !empty($form_data[$this->email_name])) {

                \Metform_Pro\Core\Integrations\Fluent_Crm::send_data($this->form_settings['mf_fluent_webhook'], $form_data, $this->email_name);
            }
        }

        // sanitize form submitted data
        $this->sanitize($form_data);

        //set submitted data array and key to a class
        $all_data = !empty($this->form_data) && is_array($this->form_data) ? $this->form_data : [];

        if (isset($this->form_settings['store_entries']) && $this->form_settings['store_entries'] == 1) {

            $defaults = [
                'post_title' => '',
                'post_status' => 'draft',
                'post_content' => '',
                'post_type' => $this->post_type,
            ];

            $this->entry_id = wp_insert_post($defaults);

            update_post_meta($this->entry_id, 'mf_page_id', $page_id);

            $all_data = array_merge($all_data, ['mf_id' => $this->entry_id, 'mf_form_name' => $this->title]);
        }

        Metform_Shortcode::instance()->set_values($all_data);

        // Store data in database
        $this->store($form_id, $this->form_data);

        // data submit to a rest api check and action
        if (class_exists('\MetForm_Pro\Core\Integrations\Rest_Api') && isset($this->form_settings['mf_rest_api']) && ($this->form_settings['mf_rest_api_url'] != '')) {
            $url = $this->form_settings['mf_rest_api_url'];
            $method = $this->form_settings['mf_rest_api_method'];
            $rest_api = new \MetForm_Pro\Core\Integrations\Rest_Api();
            $response = $rest_api->call_api(
                [
                    'entries' => json_encode($this->form_data),
                    'entry_id' => (($this->entry_id != null) ? $this->entry_id : ''),
                    'form_id' => $form_data['id'],
                    'version' => \MetForm\Plugin::instance()->version(),
                    'file_uploads' => json_encode($this->file_upload_info),
                ],
                [
                    'url' => $url,
                    'method' => $method,
                ]
            );
            $this->response->status = isset($response['status']) ? $response['status'] : 0;
        }

        // send confirmation email to user
        if (isset($this->form_settings['enable_user_notification']) && $this->form_settings['enable_user_notification'] == 1) {

            $this->send_user_email($this->form_data, $this->file_upload_info);
        }

        // send notification email to admins
        if (isset($this->form_settings['enable_admin_notification']) && $this->form_settings['enable_admin_notification'] == 1) {

            $this->send_admin_email($this->form_data, $this->file_upload_info);
        }

        $this->response->data['message'] = isset($this->form_settings['success_message']) ? $this->form_settings['success_message'] : '';

        $paymet_method = $this->get_input_name_by_widget_type('mf-payment-method');

        if (class_exists('\MetForm_Pro\Core\Integrations\Payment\Paypal') && isset($this->form_settings['mf_paypal']) && isset($this->form_settings['mf_paypal_email']) && ($this->form_settings['mf_paypal_email'] != '') && ($paymet_method[0] != null)) {
            if (isset($this->form_data[$paymet_method[0]]) && $this->form_data[$paymet_method[0]] == 'paypal') {
                update_post_meta($this->entry_id, $this->key_payment_status, 'unpaid');
                $rest_url = get_rest_url(null, 'metform/v1/entries/');
                $this->response->data['redirect_to'] = $rest_url . "paypal/pay?entry_id=" . $this->entry_id;
                $this->response->data['message'] = $this->form_settings['success_message'] . esc_html__(' Please wait... Redirecting to paypal.', 'metform');
            }
        }

        if (!isset($paymet_method[0])) {
            $paymet_method[0] = null;
        }

        if (class_exists('\MetForm_Pro\Core\Integrations\Payment\Stripe') && ($paymet_method[0] != null)) {
            if (isset($this->form_data[$paymet_method[0]]) && $this->form_data[$paymet_method[0]] == 'stripe') {
                update_post_meta($this->entry_id, $this->key_payment_status, 'unpaid');

                $payment_widget_name = \MetForm\Core\Entries\Action::instance()->get_input_name_by_widget_type('mf-payment-method', $this->fields);
                $widget = is_array($payment_widget_name) ? current($payment_widget_name) : '';

                $amount_filed = isset($this->fields[$widget]->mf_input_payment_field_name) ? $this->fields[$widget]->mf_input_payment_field_name : '';
                $amount = isset($this->form_data[$amount_filed]) ? $this->form_data[$amount_filed] : 0;

                //$this->response->data['payment_method'] = $this->form_data[$paymet_method[0]];

                $icon_url = !empty($this->form_settings['mf_stripe_image_url']) ? $this->form_settings['mf_stripe_image_url'] : 'https://stripe.com/img/documentation/checkout/marketplace.png';

                // set key for check payment
                $livekey = isset($this->form_settings['mf_stripe_live_publishiable_key']) ? $this->form_settings['mf_stripe_live_publishiable_key'] : '';
                $livekey_test = isset($this->form_settings['mf_stripe_test_publishiable_key']) ? $this->form_settings['mf_stripe_test_publishiable_key'] : '';
                $sandbox = isset($this->form_settings['mf_stripe_sandbox']) ? true : false;

                $live_keys = ($sandbox) ? $livekey_test : $livekey;

                $payment['name_post'] = $this->form_settings['form_title'];
                $payment['description'] = $this->form_id;
                $payment['amount'] = $amount;
                $payment['currency_code'] = 'USD';
                $payment['keys'] = $live_keys;
                $payment['image_url'] = $icon_url;
                $payment['entry_id'] = $this->entry_id;
                $payment['form_id'] = $this->form_id;
                $payment['sandbox'] = $sandbox;

                $this->response->data['payment_data'] = (object) $payment;
                $rest_url = get_rest_url(null, 'metform/v1/entries/');
                $this->response->data['ajax_stripe'] = $rest_url . "stripe/pay?entry_id=" . $this->entry_id;
                $this->response->data['message'] = $this->form_settings['success_message'] . esc_html__(' Please wait... Open a Stripe Popup Box.', 'metform');
            }
        }

        /**
         * Woocommerce
         */

        if (class_exists('WooCommerce')) {

            if ( isset($_POST['mf-woo-checkout']) && $_POST['mf-woo-checkout'] == 'yes' &&  class_exists('\MetForm_Pro\Core\Integrations\Ecommerce\Woocommerce\Pay')) {

                $woo_pay = new \MetForm_Pro\Core\Integrations\Ecommerce\Woocommerce\Pay();
                $woo_pay->action($form_data, $this->entry_id);
            }

        }

        /**
         * Post submission
         */

        if (class_exists('\MetForm_Pro\Core\Integrations\Post\Form_To_Post\Post')) {
            if (isset($this->form_settings['mf_form_to_post']) && $this->form_settings['mf_form_to_post'] == 1) {
                $post_submission = new \MetForm_Pro\Core\Integrations\Post\Form_To_Post\Post();
                $post_submission->create_post(
                    $form_data,
                    $this->form_settings,
                    $this->form_id,
                    $this->entry_id,
                    $this->file_upload_info
                );
            }
        }
        if(!empty($this->entry_id) && !empty($this->form_data[$this->email_name])) {
            if(!empty($this->form_settings['email_verification_enable']) && $this->form_settings['email_verification_enable'] == 1) {
                do_action('met_form_email_verification', $this->entry_id, $this->form_data[$this->email_name], $this->form_settings);
            }
        }

        return $this->response;
    }

    public function validate_form_data($form_data) {

        $field_count = 0;
        $errors = 0;

        foreach ($form_data as $key => $value) {
            if (!is_array($value)) {
                $field_count++;
                $min = ((isset($this->fields[$key]->mf_input_min_length) && $this->fields[$key]->mf_input_min_length != '') ? $this->fields[$key]->mf_input_min_length : '');
                $max = ((isset($this->fields[$key]->mf_input_max_length) && $this->fields[$key]->mf_input_max_length != '') ? $this->fields[$key]->mf_input_max_length : '');
                $validation_type = ((isset($this->fields[$key]->mf_input_validation_type) && $this->fields[$key]->mf_input_validation_type != '') ? $this->fields[$key]->mf_input_validation_type : 'none');
                $expression = ((isset($this->fields[$key]->mf_input_validation_expression) && $this->fields[$key]->mf_input_validation_expression != '') ? $this->fields[$key]->mf_input_validation_expression : '');
                $type = str_replace(['by_', '_length'], '', $validation_type);
                $str_length = '';

                if ($validation_type == 'by_word_length') {
                    $str_length = str_word_count($value);
                } else if ($validation_type == 'by_character_length') {
                    $str_length =  strlen($value);
                    if ($this->fields[$key]->widgetType === 'mf-number') {
                        $str_length =  $value;
                    }
                }

                if ((!in_array($validation_type, ['none', 'by_expresssion_based'])) && ($min != '') && ($min > $str_length)) {
                    $errors++;
                    $this->response->status = 0;
                    $this->response->error[] = esc_html((($this->fields[$key]->mf_input_label != '') ? $this->fields[$key]->mf_input_label : $key) . " minimum input " . $min . " " . $type);
                }
                if ((!in_array($validation_type, ['none', 'by_expresssion_based'])) && ($max != '') && ($max < $str_length)) {
                    $errors++;
                    $this->response->status = 0;
                    $this->response->error[] = esc_html((($this->fields[$key]->mf_input_label != '') ? $this->fields[$key]->mf_input_label : $key) . " maximum input " . $max . " " . $type);
                }
                if (($validation_type == 'by_expresssion_based') && ($expression != '') && (!preg_match("/" . $expression . "/", $value))) {
                    $errors++;
                    $this->response->status = 0;
                    $this->response->error[] = esc_html((($this->fields[$key]->mf_input_label != '') ? $this->fields[$key]->mf_input_label : $key) . " input criteria is not matched.");
                }
            }
        }

        return (($errors > 0) ? false : true);
    }

    public function store($form_id, $form_data) {
        $this->form_id = $form_id;
        //$this->sanitize($form_data);

        if (isset($this->form_settings['store_entries']) && $this->form_settings['store_entries'] == 1) {
            $this->insert();
        }
    }

    private function insert() {
        // google sheet
        if(class_exists('\MetForm_Pro\Core\Integrations\Google_Sheet\WF_Google_Sheet')) {
            if(isset($this->form_settings['mf_google_sheet']) && $this->form_settings['mf_google_sheet'] == 1) {
                $sheet = \MetForm_Pro\Core\Integrations\Google_Sheet\WF_Google_Sheet::instance()->insert($this->form_id, $this->title, $this->form_data, $this->file_upload_info, $this->get_fields($this->form_id));
                if($sheet === false) {
                    $this->response->error[] = esc_html__('ssl certificate or google oauth credentials problem', 'metform');
                    $this->response->status = 0;
                    return $this->response;
                }

            }
        }

        $form_settings = $this->form_settings;
        $form_id = $this->form_id;

        $this->form_settings['entry_title'] = (isset($this->form_settings['entry_title']) ? $this->form_settings['entry_title'] : 'Entry # [mf_id]');

        $update = array(
            'ID' => $this->entry_id,
            'post_title' => Metform_Shortcode::instance()->get_process_shortcode($this->form_settings['entry_title']),
            'post_status' => 'publish',
            'post_content' => '',
        );

        wp_update_post($update);

        $this->response->data['form_id'] = $form_id;
        $this->response->data['entry_id'] = $this->entry_id;

        $entry_count = $this->get_entry_count();
        $entry_count++;

        update_post_meta($form_id, $this->key_form_total_entries, $entry_count);
        update_post_meta($this->entry_id, $this->key_form_id, $form_id);
        update_post_meta($this->entry_id, $this->key_form_data, $this->form_data);

        update_post_meta($this->entry_id, $this->key_form_file, $this->file_upload_info);

        if (isset($form_settings['capture_user_browser_data']) && $form_settings['capture_user_browser_data'] == '1') {
            update_post_meta($this->entry_id, $this->key_browser_data, $this->get_browser_data());
            $this->response->status = 1;
        }

        $this->response->status = 1;
        $this->response->data['store'] = $this->form_data;
    }

    private function update()
    {

        update_post_meta($this->entry_id, $this->key_form_id, $this->form_id);
        update_post_meta($this->entry_id, $this->key_form_data, $this->form_data);

        $this->response->status = 1;
    }

    public function send_user_email($form_data, $file_info)
    {

        $user_mail = (isset($form_data[$this->email_name]) ? $form_data[$this->email_name] : null);
        $subject = isset($this->form_settings['user_email_subject']) ? $this->form_settings['user_email_subject'] : get_bloginfo('name');
        $from = isset($this->form_settings['user_email_from']) ? $this->form_settings['user_email_from'] : null;
        $reply_to = isset($this->form_settings['user_email_reply_to']) ? $this->form_settings['user_email_reply_to'] : null;
        $body = nl2br(isset($this->form_settings['user_email_body']) ? $this->form_settings['user_email_body'] : null);
        $user_email_attached_submision_copy = isset($this->form_settings['user_email_attach_submission_copy']) ? $this->form_settings['user_email_attach_submission_copy'] : null;

        //replace data from shortcode
        $body = Metform_Shortcode::instance()->get_process_shortcode($body);
        $reply_to = Metform_Shortcode::instance()->get_process_shortcode($reply_to);
        $subject = Metform_Shortcode::instance()->get_process_shortcode($subject);

        $body = "<html><body><h2 style='text-align: center;'>" . get_the_title($this->form_id) . "</h2><h4 style='text-align: center;'>" . $body . "</h4>";
        $form_html = \MetForm\Core\Entries\Form_Data::format_data_for_mail($this->form_id, $form_data, $file_info);
        $body .= $form_html . "</body></html>";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        $headers .= 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $reply_to . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if (!$user_mail) {
            $this->response->status = 0;
            $this->response->error[] = esc_html__('Mail not found.', 'metform');
        } else {
            $status = wp_mail($user_mail, $subject, $body, $headers);
            $this->response->status = ($status) ? 1 : 0;
        }
    }

    public function send_admin_email($form_data, $file_info)
    {

        $subject = isset($this->form_settings['admin_email_subject']) ? $this->form_settings['admin_email_subject'] : null;
        $from = isset($this->form_settings['admin_email_from']) ? $this->form_settings['admin_email_from'] : null;
        $reply_to = isset($this->form_settings['admin_email_reply_to']) ? $this->form_settings['admin_email_reply_to'] : null;
        $body = nl2br(isset($this->form_settings['admin_email_body']) ? $this->form_settings['admin_email_body'] : null);
        $admin_email_attached_submision_copy = isset($this->form_settings['admin_email_attach_submission_copy']) ? $this->form_settings['admin_email_attach_submission_copy'] : null;

        //replace data from shortcode
        $body = Metform_Shortcode::instance()->get_process_shortcode($body);
        $from = Metform_Shortcode::instance()->get_process_shortcode($from);
        $reply_to = Metform_Shortcode::instance()->get_process_shortcode($reply_to);
        $subject = Metform_Shortcode::instance()->get_process_shortcode($subject);

        $body = "<html><body><h2 style='text-align: center;'>" . get_the_title($this->form_id) . "</h2><h4 style='text-align: center;'>" . $body . "</h4>";
        $form_html = \MetForm\Core\Entries\Form_Data::format_data_for_mail($this->form_id, $form_data, $file_info);
        $body .= $form_html;
        if (isset($this->form_settings['store_entries']) && $this->form_settings['store_entries'] == 1) {
            $edit_link = get_edit_post_link($this->entry_id);
            $body .= '<a href="' . $edit_link . '">' . $edit_link . '</a>';
        }
        $body .= "</body></html>";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        $headers .= 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $reply_to . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $mail = isset($this->form_settings['admin_email_to']) ? $this->form_settings['admin_email_to'] : null;

        if (!$mail) {
            $this->response->status = 0;
            $this->response->error[] = esc_html__('Admin mail not found to send email.', 'metform');
        } else {
            $admin_email = preg_replace('/\s+/', '', $mail);
            $admin_emails = explode(",", $admin_email);
            foreach ($admin_emails as $email) {
                $status = wp_mail($email, $subject, $body, $headers);
            }

            $this->response->status = ($status) ? 1 : 0;
        }
    }

    /**
     * Check if woocommerce is exists
     */
    public static function mf_is_woo_exists()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        }
        return false;
    }

    public function get_fields($form_id = null)
    {
        if ($form_id != null) {
            $this->form_id = $form_id;
        }

        $input_widgets = \Metform\Widgets\Manifest::instance()->get_input_widgets();

        $widget_input_data = get_post_meta($this->form_id, '_elementor_data', true);
        $widget_input_data = json_decode($widget_input_data);

        return Map_El::data($widget_input_data, $input_widgets)->get_el();
    }

    public function sanitize($form_data, $fields = null)
    {
        if ($fields == null) {
            $fields = $this->fields;
        }

        foreach ($form_data as $key => $value) {

            if (isset($fields[$key])) {
                $this->form_data[$key] = $value;

                /**
                 * Credit card value sanitizaton & type insertion
                 */
                if ($fields[$key]->widgetType == "mf-credit-card") {

                    $this->form_data[$key] = str_repeat('*', strlen(str_replace(' ', '', $value)) - 4) . substr(str_replace(' ', '', $value), -4);
                    $this->form_data[$key . '--type'] = $form_data[$key . '--type']; #insert credit card type
                }
            }
        }

        $repeaters = $this->get_input_name_by_widget_type('mf-simple-repeater');

        $repeaters = (is_array($repeaters) ? $repeaters : []);

        foreach ($repeaters as $repeater) {
            if (isset($this->form_data[$repeater])) {
                $repeater_process_data = $this->process_repeater_data($this->form_data[$repeater]);
                $this->form_data[$repeater] = $repeater_process_data;
            }
        }
    }

    public function upload_file($file_data, $file_input_names)
    {

        foreach ($file_input_names as $i => $input_name) {
            // initial upload status, status use as array for multiple file
            $upload[$input_name]['status'] = false;
            // empty file upload check by file name
            if (isset($file_data[$input_name]) && $file_data[$input_name]['name'] != '') {
                $file_data[$input_name]['name'] = time() . "-" . $file_data[$input_name]['name'];
                $upload[$input_name] = wp_upload_bits($file_data[$input_name]['name'], null, file_get_contents($file_data[$input_name]['tmp_name']), time());
                // status updated as true if file uploaded
                $upload[$input_name]['status'] = true;
            } else {
                // status updated as false for showing file not uploaded
                $upload[$input_name]['status'] = false;
            }
            if (isset($upload['error']) && $upload['error'] != 0) {
                $this->response->status = 0;
                $this->response->error[] = esc_html__('There was an error uploading your file. The error is: ', 'metform') . $upload['error'];
            } else {
                $this->file_upload_info = $upload;
                $this->response->status = 1;
            }
        }
    }

    /**
     * Converting an png image string to png image file.
     *
     * @param $input
     * @param $b64string
     *
     * @return array
     */
    public function covert_base64_to_png($input, $b64string)
    {
        $status = [];

        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];

        $bin = str_replace('data:image/png;base64,', '', $b64string, $ct);

        $decoded = base64_decode($bin);

        $img_name = '/' . $input . time() . '.png';

        $img_file = $upload_path . $img_name;
        $img_url = $upload_url . $img_name;

        $img = file_put_contents($img_file, $decoded);

        if ($img) {
            $status['status'] = true;
            $status['url'] = $img_url;
        } else {
            $status['status'] = false;
        }

        return $status;
    }

    public function get_entry_count($form_id = null)
    {

        if ($form_id != null) {
            $this->form_id = $form_id;
        }
        global $wpdb;
        $entry_count = $wpdb->get_results(" SELECT COUNT( `post_id` )  as `count`  FROM `" . $wpdb->prefix . "postmeta` WHERE `meta_key` LIKE 'metform_entries__form_id' AND `meta_value` = $this->form_id ", OBJECT);

        $entry_count = $entry_count[0]->count;

        $this->entry_count = $entry_count;

        return $entry_count;
    }

    public function get_browser_data()
    {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);

        return [
            'IP' => $ip,
            'User_Agent' => $user_agent,
        ];
    }

    public function get_input_name_by_widget_type($widget_type, $fields = null)
    {
        global $w;
        if ($fields != null) {
            $this->fields = $fields;
        }
        $response = [];

        $w = $widget_type;
        $files = array_values(array_filter($this->fields, function ($v) {
            global $w;
            if ($v->widgetType == $w) {
                return $v;
            }
        }));
        foreach ($files as $file) {
            $response[] = $file->mf_input_name;
        }

        if (!empty($response)) {
            return $response;
        } else {
            return null;
        }
    }

    public function process_repeater_data($repeater_data)
    {
        $data = [];
        if (is_array($repeater_data)) {
            foreach ($repeater_data as $index => $value) {
                if (is_array($value)) {
                    foreach ($value as $input_name => $input_value) {
                        $proc_key = $input_name . "-" . ($index + 1);
                        if (is_array($input_value)) {
                            $data[$proc_key] = implode(', ', $input_value);
                        } else {
                            $data[$proc_key] = $input_value;
                        }
                    }
                }
            }
        }

        return $data;
    }
}
