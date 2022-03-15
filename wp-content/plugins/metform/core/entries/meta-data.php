<?php

namespace MetForm\Core\Entries;

defined('ABSPATH') || exit;

class Meta_Data
{

    private $browser_data = null;
    private $file_meta_data = null;

    private $form_id;
    private $form_data;
    private $form_settings;

    private $fields;

    public function __construct()
    {
        $this->cpt = new Cpt();
        add_action('save_post', [$this, 'store_form_data_cmb']);
        add_action('add_meta_boxes', [$this, 'add_form_id_cmb']);
        add_action('add_meta_boxes', [$this, 'add_form_data_cmb']);

        add_action('add_meta_boxes', [$this, 'add_browser_data_cmb']);
        add_action('add_meta_boxes', [$this, 'add_file_upload_cmb']);
        add_action('add_meta_boxes', [$this, 'add_form_payment_status_cmb']);
        add_action('add_meta_boxes', [$this, 'add_woo_payment_status_cmb']);
    }

    function add_form_id_cmb()
    {
        add_meta_box(
            'metform_entries__form_id',
            esc_html__('Info', 'metform'),
            [$this, 'show_form_id_cmb'],
            $this->cpt->get_name(),
            'side',
            'high'
        );
    }

    function show_form_id_cmb($post)
    {
        wp_nonce_field('meta_nonce', 'meta_nonce');

        $this->form_id = get_post_meta($post->ID, 'metform_entries__form_id', true);
        // get fields by form id for further use
        $this->fields = Action::instance()->get_fields($this->form_id);
        $form_title = get_the_title((int)$this->form_id);

        ?>

        <div class="metform-entry-data container">
            <table class='mf-entry-data' cellpadding="5" cellspacing="0">
                <tbody>
                <tr class="mf-data-label">
                    <td colspan='2'><strong><?php esc_html_e('Form Name ', 'metform'); ?></strong></td>
                </tr>
                <tr class='mf-data-value'>
                    <td><?php echo esc_attr($form_title); ?></td>
                </tr>
                <tr class="mf-data-label">
                    <td colspan='2'><strong><?php esc_html_e('Entry ID', 'metform'); ?></strong></td>
                </tr>
                <tr class='mf-data-value'>
                    <td><?php echo esc_attr($post->ID); ?></td>
                </tr>

                </tbody>
            </table>
        </div>

        <?php
    }

    function add_form_data_cmb()
    {
        add_meta_box(
            'metform_entries__form_data',
            esc_html__('Data', 'metform'),
            [$this, 'show_form_data_cmb'],
            $this->cpt->get_name(),
            'normal',
            'high'
        );
    }

    function add_browser_data_cmb()
    {
        // call browser data meta when browser data present
        $post_id = (isset($_GET['post']) ? $_GET['post'] : '');
        $form_id = get_post_meta($post_id, 'metform_entries__form_id', true);
        $form_settings = \MetForm\Core\Forms\Action::instance()->get_all_data($form_id);

        $this->browser_data = get_post_meta($post_id, 'metform_form__entry_browser_data', true);
        if ($this->browser_data == '' && !isset($form_settings['capture_user_browser_data'])) {
            return;
        }

        add_meta_box(
            'metform_form__entry_browser_data',
            esc_html__('Browser Data', 'metform'),
            [$this, 'show_browser_data_cmb'],
            $this->cpt->get_name(),
            'side',
            'low'
        );
    }

    function show_browser_data_cmb($post)
    {
        if ($this->browser_data != '') {
            ?>
            <div class="metform-entry-data container">
                <table class='mf-entry-data' cellpadding="5" cellspacing="0">
                    <?php
                    foreach ($this->browser_data as $key => $value) {
                        ?>
                        <tbody>
                        <tr class="mf-data-label">
                            <td colspan='2'><strong><?php echo esc_attr($key); ?></strong></td>
                        </tr>
                        <tr class='mf-data-value'>
                            <td><?php echo esc_attr($value); ?></td>
                        </tr>
                        </tbody>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <?php
        } else {
            echo esc_html__('Browser data not captured.', 'metform');
        }
    }

    function add_file_upload_cmb()
    {
        // call file meta when file data present
        $post_id = (isset($_GET['post']) ? $_GET['post'] : '');

        $this->file_meta_data = get_post_meta($post_id, 'metform_entries__file_upload', true);
        if (!is_array($this->file_meta_data)) {
            return;
        }

        add_meta_box(
            'metform_entries__file_upload',
            esc_html__('Files', 'metform'),
            [$this, 'show_file_upload_cmb'],
            $this->cpt->get_name(),
            'normal',
            'low'
        );
    }

    function show_form_data_cmb($post)
    {
        wp_nonce_field('meta_nonce', 'meta_nonce');

        $this->form_data = get_post_meta($post->ID, 'metform_entries__form_data', true);
        $this->form_data = (isset($this->form_data)) ? $this->form_data : "";
        // format all form data into html table
        if ($this->form_data != '') {
            $form_html = \MetForm\Core\Entries\Form_Data::format_form_data($this->form_id, $this->form_data);
            echo $form_html;
        }
    }

    function show_file_upload_cmb($post)
    {
        echo "<div class='mf-file-show'>";
        foreach ($this->file_meta_data as $key => $value) {
            if (empty($this->fields)) {
                return;
            }
            echo "<p><i class='mf mf-file-2'></i> ";
            $file_url = isset($value['url']) ? $value['url'] : '';
            $file_type = isset($value['type']) ? $value['type'] : '';
            if ($file_url != '') {
                echo esc_html(((isset($this->fields[$key]->mf_input_label)) ? $this->fields[$key]->mf_input_label : $key) . " : ") . "<a target='_blank' class='mf-file-url' href=" . esc_url($file_url) . " download>" . esc_html__('Download', 'metform') . "</a>";
                echo((in_array($file_type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/ico'])) ? ' | <a href="#" class="" data-toggle="modal" data-target="#mfFileUploadModal' . $key . '">' . esc_html__('View', 'metform') . '</a>' : '');
            } else {
                echo esc_html(((isset($this->fields[$key]->mf_input_label)) ? $this->fields[$key]->mf_input_label : $key) . " : ") . esc_html__('This file is not uploaded.', 'metform');
            }

            ?>
                <!-- Modal -->
                <div class="attr-modal attr-fade mf-modal-container" id="mfFileUploadModal<?php echo $key ?>" tabindex="-1"
                    role="dialog" aria-labelledby="mfFileUploadodalMLabel" aria-hidden="true">
                    <div class="attr-modal-dialog" role="document">
                        <div class="attr-modal-content">
                            <div class="attr-modal-header">
                                <button type="button" class="attr-close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="attr-modal-body">
                                <img class="attr-img-responsive" src="<?php echo $file_url ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </p>
            <?php
        }
        echo "</div>";
    }

    function store_form_data_cmb($post_id)
    {

        // add save code here

    }

    function add_woo_payment_status_cmb()
    {
        add_meta_box(
            'metform_entries__woo_checkout_status',
            esc_html__('Woocommerce Checkout', 'metform'),
            [$this, 'show_woo_checkout_status_cmb'],
            $this->cpt->get_name(),
            'side',
            'high'
        );
    }

    function show_woo_checkout_status_cmb($post)
    {
        $order_id = get_post_meta($post->ID, 'mf_woo_order_id', true);
        if($order_id == null) {
            return;
        }
        $order = wc_get_order($order_id);
        $order_url = get_admin_url() . 'post.php?post=' . $order_id . '&action=edit';
        ?>

        <div class="metform-entry-data container">
            <table class='mf-entry-data' cellpadding="5" cellspacing="0">
                <tbody>
                <tr class="mf-data-label">
                    <td colspan='2'><strong><?php esc_html_e('Order ID ', 'metform'); ?></strong></td>
                </tr>
                <tr class='mf-data-value'>
                    <td><?php echo esc_attr($order_id); ?></td>
                </tr>
                <tr class="mf-data-label">
                    <td colspan='2'><strong><?php esc_html_e('Order Status', 'metform'); ?></strong></td>
                </tr>
                <tr class='mf-data-value'>
                    <td><?php echo esc_attr($order->get_status()); ?></td>
                </tr>

                <tr class="mf-data-label">
                    <td colspan='2'><strong><?php esc_html_e('Total', 'metform'); ?></strong></td>
                </tr>
                <tr class='mf-data-value'>
                    <td><?php echo esc_attr($order->get_total() . ' ' . $order->get_currency()); ?></td>
                </tr>

                <tr class="mf-data-label">

                </tr>
                <tr class='mf-data-value'>
                    <td><a class="button" href="<?php echo $order_url; ?>" target="__blank">Order Details</a></td>
                </tr>

                </tbody>
            </table>
        </div>


        <?php

    }

    function add_form_payment_status_cmb()
    {

        add_meta_box(
            'metform_entries__payment_status',
            esc_html__('Payment', 'metform'),
            [$this, 'show_form_payment_status_cmb'],
            $this->cpt->get_name(),
            'side',
            'high'

        );
    }

    function show_form_payment_status_cmb($post)
    {
        echo "Status : " . get_post_meta($post->ID, 'metform_entries__payment_status', true) . "<br>";

        if (get_post_meta($post->ID, 'metform_entries__payment_invoice', true)) {
            echo "Invoice : ";
            echo get_post_meta($post->ID, 'metform_entries__payment_invoice', true) . "<br>";
        }

    }
}
