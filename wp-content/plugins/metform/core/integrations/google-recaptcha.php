<?php
namespace MetForm\Core\Integrations;
defined( 'ABSPATH' ) || exit;

class Google_Recaptcha {

    use \MetForm\Traits\Singleton;

    private $return;

    public function verify_captcha_v2( $form_data, $form_settings ){

        $secretKey = ((isset($form_settings['mf_recaptcha_secret_key']) && ($form_settings['mf_recaptcha_secret_key'] != '')) ? $form_settings['mf_recaptcha_secret_key'] : '');
            
        $captcha = (isset($form_data['g-recaptcha-response']) ? $form_data['g-recaptcha-response'] : '');
        
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $secretKey,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        
        $curlConfig = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, $curlConfig);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $responseKeys = json_decode($response,true);
        $this->return['responseKeys'] = $responseKeys;

        if($responseKeys["success"]) {
            $this->return['status'] = 1;
        } else {
            $this->return['status'] = 0;
            $this->return['error'] = esc_html__('Captcha is not verified.','metform');
        }

        return $this->return;
    }

    public function verify_captcha_v3( $form_data, $form_settings ){
        
        $secretKey = ((isset($form_settings['mf_recaptcha_secret_key_v3']) && ($form_settings['mf_recaptcha_secret_key_v3'] != '')) ? $form_settings['mf_recaptcha_secret_key_v3'] : '');
            
        $captcha = (isset($form_data['g-recaptcha-response-v3']) ? $form_data['g-recaptcha-response-v3'] : '');
        
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $secretKey,
            'response' => $captcha,
        );
        
        $curlConfig = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, $curlConfig);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $responseKeys = json_decode($response,true);
        $this->return['responseKeys'] = $responseKeys;

        if($responseKeys["success"]) {
            $this->return['status'] = 1;
        } else {
            $this->return['status'] = 0;
            $this->return['error'] = esc_html__('Captcha is not verified.','metform');
        }

        return $this->return;
    }
}