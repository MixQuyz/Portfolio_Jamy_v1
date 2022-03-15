<?php

namespace MetForm\Core\Entries;

defined('ABSPATH') || exit;

class Form_Data
{
    public static function format_form_data($form_id, $form_data)
    {
        $map_data = \MetForm\Core\Entries\Action::instance()->get_fields($form_id);
        $map_data = json_decode(json_encode($map_data), true);
        ob_start();
        ?>
        <div class="metform-entry-data container">
            <table class='mf-entry-data' cellpadding="5" cellspacing="0">
                <tbody>
                    <?php
                        foreach ($map_data as $key => $value) {
                            if (in_array($value['widgetType'], ['mf-simple-captcha', 'mf-recaptcha', 'mf-file-upload'])) {
                                continue;
                            }

                            echo "<tr class='mf-data-label'>";
                            echo "<td colspan='2'><strong>" . esc_html(($map_data[$key]['mf_input_label'] != '') ? $map_data[$key]['mf_input_label'] : $key) . "</strong></td>";
                            echo "</tr>";
                            echo "<tr class='mf-data-value'>";
                            echo "<td class='mf-value-space'>&nbsp;</td>";

                            if (!in_array($value['widgetType'], ['mf-file-upload', 'mf-textarea', 'mf-simple-repeater', 'mf-signature', 'mf-like-dislike', 'mf-credit-card'])) {
                                echo "<td>" . esc_html((array_key_exists($key, $form_data) ? ((is_array($form_data[$key])) ? implode(', ', $form_data[$key]) : $form_data[$key]) : ' ')) . "</td>";
                            }

                            if ($value['widgetType'] == 'mf-signature') {
                                echo "<td><img class='signature-img' src='" . (isset($form_data[$key]) ? $form_data[$key] : '') . "'></td>";
                            }

                            if ($value['widgetType'] == 'mf-textarea') {
                                echo "<td><pre style='font:inherit;margin:0;'>" . (isset($form_data[$key]) ? $form_data[$key] : '') . "</pre></td>";
                            }
                            
                            if ($value['widgetType'] == 'mf-simple-repeater') {
                                echo "<td>";
                                $repeater_data = ((array_key_exists($key, $form_data)) ? $form_data[$key] : []);
                                foreach ($repeater_data as $k => $v) {
                                    echo "<strong>" . esc_html($k) . ": </strong>";
                                    echo "<span>" . esc_html($v) . "</span>";
                                    echo "<br>";
                                }
                                echo "</td>";
                            }

                            /**
                             * Credit Card form data entries
                             */
                            if ($value['widgetType'] == 'mf-credit-card') {
                                echo "<td><strong>Number:</strong> " . (isset($form_data[$key]) ? $form_data[$key] : ''). "</br>";
                                if ( isset($form_data[$key.'--type']) ) {
                                    $type = $form_data[$key.'--type'];
                                    $type = ( $type === "amex" ) ? 'Amerian Express' : $type;
                                    echo "<strong>Type:</strong> " . ucfirst($type) . " Card</td>";
                                }
                            }

                            if (isset($value['widgetType']) && ($value['widgetType'] == 'mf-like-dislike')) {
                                $like_dislike = (isset($form_data[$key]) ? $form_data[$key] : '');
                                echo "<td>";
                                echo (($like_dislike == '1') ? "<span class='dashicons dashicons-thumbs-up'></span>" : "");
                                echo (($like_dislike == '0') ? "<span class='dashicons dashicons-thumbs-down'></span>" : "");
                                echo "</td>";
                            }

                            echo "</tr>";
                        }
                        ?>
                </tbody>
            </table>
        </div>
    <?php
            $data_html = ob_get_contents();
            ob_end_clean();
            return $data_html;
        }

        public static function format_data_for_mail($form_id, $form_data, $file_info)
        {
            $map_data = \MetForm\Core\Entries\Action::instance()->get_fields($form_id);
            $map_data = json_decode(json_encode($map_data), true);

            //$file_meta_data = get_post_meta( $post_id, 'metform_entries__file_upload', true );

            ob_start();

            ?>
        <div style="border-left:5px solid #2EB5AB;padding-left:5px;">
            <table width="100%" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF" style="border: 1px solid #EAF2FA">
                <tbody>
                    <?php
                            foreach ($map_data as $key => $value) {

                                if (in_array($value['widgetType'], ['mf-simple-captcha', 'mf-recaptcha', 'mf-file-upload', 'mf-button'])) {
                                    continue;
                                }

                                echo "<tr bgcolor='#EAF2FA'>";
                                echo "<td colspan='2'><strong>" . esc_html(($map_data[$key]['mf_input_label'] != '') ? $map_data[$key]['mf_input_label'] : $key) . "</strong></td>";
                                echo "</tr>";
                                echo "<tr bgcolor='#FFFFFF'>";
                                echo "<td width='20'>&nbsp;</td>";

                                if (!in_array($value['widgetType'], ['mf-file-upload', 'mf-textarea', 'mf-simple-repeater', 'mf-signature', 'mf-credit-card'])) {
                                    echo "<td>" . esc_html((array_key_exists($key, $form_data) ? ((is_array($form_data[$key])) ? implode(', ', $form_data[$key]) : $form_data[$key]) : ' ')) . "</td>";
                                }

                                if ($value['widgetType'] == 'mf-textarea') {
                                    echo "<td><pre style='font:inherit;margin:0;'>" . (isset($form_data[$key]) ? $form_data[$key] : '') . "</pre></td>";
                                }

                                if ($value['widgetType'] == 'mf-signature') {
                                    echo "<td><img width='200' height='100' src='" . (isset($form_data[$key]) ? $form_data[$key] : '') . "'></td>";
                                }

                                if ($value['widgetType'] == 'mf-simple-repeater') {
                                    echo "<td>";
                                    $repeater_data = ((array_key_exists($key, $form_data)) ? $form_data[$key] : []);
                                    foreach ($repeater_data as $key => $value) {
                                        echo "<strong>" . esc_html($key) . ": </strong>";
                                        echo "<span>" . esc_html($value) . "</span>";
                                        echo "<br>";
                                    }
                                    echo "</td>";
                                }
                                
                                /**
                                 * Credit Card form data entries
                                 */
                                if ($value['widgetType'] == 'mf-credit-card') {
                                    echo "<td><strong>Number:</strong> " . (isset($form_data[$key]) ? $form_data[$key] : ''). "</br>";
                                    if ( isset($form_data[$key.'--type']) ) {
                                        $type = $form_data[$key.'--type'];
                                        $type = ( $type === "amex" ) ? 'Amerian Express' : $type;
                                        echo "<strong>Type:</strong> " . ucfirst($type) . " Card</td>";
                                    }
                                }

                                echo "</tr>";
                            }

                            if (!empty($file_info)) {
                                foreach ($file_info as $key => $file) {
                                    echo "<tr bgcolor='#EAF2FA'>";
                                    echo "<td colspan='2'><strong>" . esc_html(($map_data[$key]['mf_input_label'] != '') ? $map_data[$key]['mf_input_label'] : $key) . "</strong></td>";
                                    echo "</tr>";
                                    echo "<tr bgcolor='#FFFFFF'>";
                                    echo "<td width='20'>&nbsp;</td>";
                                    echo "<td><a href='" . (isset($file['url']) ? $file['url'] : '') . "'>Download</a></td>";
                                    echo "</tr>";
                                }
                            }

                            ?>
                </tbody>
            </table>
        </div>
<?php
        $data_html = ob_get_contents();
        ob_end_clean();
        return $data_html;
    }
}
