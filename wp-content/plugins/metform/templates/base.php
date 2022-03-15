<?php
namespace MetForm\Templates;
defined( 'ABSPATH' ) || exit;

Class Base{
    use \MetForm\Traits\Singleton;

    public function get_templates(){
        $template_list = [
            'template-1' => [
                'id' => 1,
                'package' => 'free',
                'title' => 'Simple Contact Form 1',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/1/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/contact-form-1/',
                'file' => \MetForm\Plugin::instance()->plugin_dir() . 'templates/1/content.json',
            ],
            'template-2' => [
                'id' => 2,
                'package' => 'free',
                'title' => 'Simple Contact Form 2',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/2/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/contact-form-2/',
                'file' => \MetForm\Plugin::instance()->plugin_dir() . 'templates/2/content.json',
                
            ],
            'template-3' => [
                'id' => 3,
                'package' => 'free',
                'title' => 'Simple Contact Form 3',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/3/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/contact-form-3/',
                'file' => \MetForm\Plugin::instance()->plugin_dir() . 'templates/3/content.json',
            ],
            'template-4' => [
                'id' => 4,
                'package' => 'free',
                'title' => 'Admission Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/4/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/admission-form/',
                'file' => \MetForm\Plugin::instance()->plugin_dir() . 'templates/4/content.json',
            ],
            'template-5' => [
                'id' => 5,
                'package' => 'free',
                'title' => 'Booking Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/5/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/booking-form/',
                'file' => \MetForm\Plugin::instance()->plugin_dir() . 'templates/5/content.json',
            ],
            'template-6' => [
                'id' => 6,
                'package' => 'pro',
                'title' => 'Event Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/6/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/event-form/',
                'file' => '',
            ],
            'template-7' => [
                'id' => 7,
                'package' => 'pro',
                'title' => 'Job Application Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/7/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/job-application-form/',
                'file' => '',
            ],
            'template-8' => [
                'id' => 8,
                'package' => 'pro',
                'title' => 'Job Listing Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/8/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/job-listing-form/',
                'file' => '',
            ],
            'template-9' => [
                'id' => 9,
                'package' => 'pro',
                'title' => 'Loan Application Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/9/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/loan-application-form/',
                'file' => '',
            ],
            'template-10' => [
                'id' => 10,
                'package' => 'pro',
                'title' => 'Newsletter Signup Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/10/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/newsletter-signup-form/',
                'file' => '',
            ],
            'template-11' => [
                'id' => 11,
                'package' => 'pro',
                'title' => 'Patient Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/11/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/patient-form/',
                'file' => '',
            ],
            'template-12' => [
                'id' => 12,
                'package' => 'pro',
                'title' => 'Personal Data Erasure Request',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/12/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/personal-data-erasure-request/',
                'file' => '',
            ],
            'template-13' => [
                'id' => 13,
                'package' => 'pro',
                'title' => 'Product Order Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/13/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/product-order-form/',
                'file' => '',
            ],
            'template-14' => [
                'id' => 14,
                'package' => 'pro',
                'title' => 'Rating Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/14/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/rating-form/',
                'file' => '',
            ],
            'template-15' => [
                'id' => 15,
                'package' => 'pro',
                'title' => 'Report A Bug Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/15/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/report-a-bug-form/',
                'file' => '',
            ],
            'template-16' => [
                'id' => 16,
                'package' => 'pro',
                'title' => 'Request For Leave Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/16/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/request-for-leave-form/',
                'file' => '',
            ],
            'template-17' => [
                'id' => 17,
                'package' => 'pro',
                'title' => 'Request For Quote Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/17/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/request-for-quote-form/',
                'file' => '',
            ],
            'template-18' => [
                'id' => 18,
                'package' => 'pro',
                'title' => 'Restaurant Reservation Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/18/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/restaurant-reservation-form/',
                'file' => '',
            ],
            'template-19' => [
                'id' => 19,
                'package' => 'pro',
                'title' => 'Suggestion Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/19/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/suggestion-form/',
                'file' => '',
            ],
            'template-20' => [
                'id' => 20,
                'package' => 'pro',
                'title' => 'Support Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/20/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/support-form/',
                'file' => '',
            ],
            'template-21' => [
                'id' => 21,
                'package' => 'pro',
                'title' => 'Volunteer Application Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/21/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/volunteer-application-form/',
                'file' => '',
            ],
            'template-22' => [
                'id' => 22,
                'package' => 'pro',
                'title' => 'Website Feedback',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/22/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/website-feedback/',
                'file' => '',
            ],
            'template-23' => [
                'id' => 23,
                'package' => 'pro',
                'title' => 'Subscribe Form 1',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/23/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/demos/subscribe-form-1/',
                'file' => '',
            ],
            'template-24' => [
                'id' => 24,
                'package' => 'pro',
                'title' => 'Subscribe Form 2',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/24/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/demos/subscribe-form-2/',
                'file' => '',
            ],
            'template-25' => [
                'id' => 25,
                'package' => 'pro',
                'title' => 'Food Order Form',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/25/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/food-order-form/',
                'file' => '',
            ],
            'template-26' => [
                'id' => 26,
                'package' => 'pro',
                'title' => 'Conditional Form 1',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/26/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-1/',
                'file' => '',
            ],
            'template-27' => [
                'id' => 27,
                'package' => 'pro',
                'title' => 'Conditional Form 2',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/27/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-2/',
                'file' => '',
            ],
            'template-28' => [
                'id' => 28,
                'package' => 'pro',
                'title' => 'Conditional Form 3',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/28/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-3/',
                'file' => '',
            ],
            'template-29' => [
                'id' => 29,
                'package' => 'pro',
                'title' => 'Conditional Form 4',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/29/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-4/',
                'file' => '',
            ],
            'template-30' => [
                'id' => 30,
                'package' => 'pro',
                'title' => 'Conditional Form 5',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/30/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-5/',
                'file' => '',
            ],
            'template-31' => [
                'id' => 31,
                'package' => 'pro',
                'title' => 'Conditional Form 6',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/31/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/conditional-form-6/',
                'file' => '',
            ],
            'template-32' => [
                'id' => 32,
                'package' => 'pro',
                'title' => 'Calculation Form 1',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/32/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/calculation-form-1/',
                'file' => '',
            ],
            'template-33' => [
                'id' => 33,
                'package' => 'pro',
                'title' => 'Calculation Form 2',
                'preview-thumb' => \MetForm\Plugin::instance()->plugin_url() . 'templates/33/preview-thumb.svg',
                'demo-url'  => 'https://products.wpmet.com/metform/pro-demos/calculation-form-2/',
                'file' => '',
            ],
        ];

        return apply_filters( 'metform/editor/templates', $template_list );
    }

    public function get_template_contents($id){
        if(!array_key_exists($id, $this->get_templates()) || !file_exists($this->get_templates()[$id]['file'])){
            return null;
        }

        $content = file_get_contents($this->get_templates()[$id]['file']);
        $content = json_decode($content);
        
        return (!isset($content->content)) ? null : $content->content;
    }

}