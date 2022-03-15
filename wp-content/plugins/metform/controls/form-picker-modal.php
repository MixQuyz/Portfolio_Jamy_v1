<?php defined('ABSPATH') || exit; ?>

<div id="metform-open-content-editor" style="display:none;">
    <div class="metform-open-content-inner">
        <div class="metform-content">
		<ul class="metform-content-editor-tab">
            <li class="metform-content-editor-tab-item active" data-target="metform-select-form-content">
                <label>
                    <input class="metform-content-editor-radio" name="metform-editor-tab" type="radio" checked value="saved">

                    <div class="metform-content-editor-radio-data">
                        <p><?php esc_html_e('Select Form', 'metform') ?></p>
                        <span><?php esc_html_e('Select saved form', 'metform') ?></span>
                    </div>
                </label>
            </li>

            <li class="metform-content-editor-tab-item" data-target="metform-templates-content">
                <label>
                    <input class="metform-content-editor-radio" name="metform-editor-tab" type="radio" value="template">

                    <div class="metform-content-editor-radio-data">
                        <p><?php esc_html_e('New', 'metform') ?></p>
                        <span><?php esc_html_e('Create new form', 'metform') ?></span>
                    </div>
                </label>
            </li>
        </ul>

        <div class="metform-editor-tab-content">
            <div class="metform-editor-tab-content-item active" id="metform-select-form-content">
                <select name="metform-saved-form" class="metform-open-content-editor-templates metform-editor-input"></select>
            </div>
            <div class="metform-editor-tab-content-item" id="metform-templates-content">

				<div class="metform-template-input-con">
					<input type="text" class="metform-editor-input" placeholder="<?php esc_html_e('Enter a form name', 'metform'); ?>">
				</div>

                <ul class="metform-templates-list">
                    <li>
                        <label>
                            <input class="metform-template-radio" name="metform-editor-template" type="radio" value="0" checked>
                            <div class="metform-template-radio-data"> </div>
                        </label>
                    </li>

                    <?php foreach(\MetForm\Templates\Base::instance()->get_templates() as $template): ?>
                        <li class="metform-template-item<?php echo isset($template['package']) ? ' metform-template-item--' . esc_attr($template['package']) : ''; ?> <?php echo (isset($template['package']) && $template['file'] === '') ? ' metform-template-item--go_pro' : ''; ?>">
                            <label>
                                <input class="metform-template-radio" name="metform-editor-template" type="radio" value="<?php echo ($template['file'] != '') ? esc_attr($template['id']) : ''; ?>" <?php echo $template['file'] === '' ? 'disabled=disabled' : '' ?>>
                                <div class="metform-template-radio-data">
                                    <img src="<?php echo esc_url($template['preview-thumb']); ?>" alt="<?php echo esc_attr($template['title']) ?>">

                                        <?php if(isset($template['package']) && $template['package'] === 'pro') : ?>
                                            <div class="metform-template-radio-data--tag">
                                                <span class="metform-template-radio-data--pro_tag"><?php echo esc_html(ucfirst($template['package'])); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="metform-template-footer-content">
                                            <?php if(isset($template['title']) && $template['title'] != '') : ?>
                                                <div class="metform-template-footer-title">
                                                    <h2><?php echo esc_html($template['title']); ?></h2>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="metform-template-footer-links">
                                                <?php if(isset($template['package']) && $template['package'] === 'pro' && isset($template['file']) && $template['file'] == '') : ?>
                                                    <a target="_blank" href="https://wpmet.com/metform-pricing/" class="metform-template-footer-links--pro_tag"><i class="metform-template-footer-links--icon fas fa-external-link-square-alt"></i><?php echo esc_html__('Buy Pro', 'metform'); ?></a>
                                                <?php endif; ?>

                                                <?php if(isset($template['demo-url']) && $template['demo-url'] != '') : ?>
                                                    <a target="_blank" class="metform-template-footer-links--demo_link" href="<?php echo esc_attr(ucfirst($template['demo-url'])); ?>"><i class="metform-template-footer-links--icon far fa-eye"></i><?php echo esc_html__('Demo', 'metform'); ?></a>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                </div>
                            </label>
                        </li>
                    <?php endforeach; ?>

                </ul>

            </div>
		</div>
		
		<button resturl="<?php echo get_rest_url() ?>metform/v1/forms/"  class="metform-open-content-editor-button"><span class="eicon-elementor"></span><?php esc_html_e('Edit form', 'metform') ?></button>

		<span class="metform-close-editor-modals metform-picker-close"><?php esc_html_e('Save & close', 'metform'); ?></span>
        
        <i class="eicon-close metform-close-editor-modals" aria-hidden="true" title="<?php echo esc_attr__('Close', 'metform'); ?>"></i>
		</div>
    </div>
</div>
