<?php
namespace Elementor;
defined( 'ABSPATH' ) || exit;

Class MetForm_Input_Select extends Widget_Base{

    use \MetForm\Traits\Common_Controls;
    use \MetForm\Traits\Conditional_Controls;
    use \MetForm\Widgets\Widget_Notice;

    public function get_name() {
		return 'mf-select';
    }
    
	public function get_title() {
		return esc_html__( 'Select', 'metform' );
    }

    public function show_in_panel() {
        return 'metform-form' == get_post_type();
    }
    
    public function get_categories() {
		return [ 'metform' ];
	}
    
	public function get_keywords() {
        return ['metform', 'input', 'select', 'dropdown'];
    }

    protected function register_controls() {
        
        $this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'metform' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
        );

        $this->input_content_controls();

        $input_fields = new Repeater();

        $input_fields->add_control(
            'mf_input_option_text', [
                'label' => esc_html__( 'Input Field Text', 'metform' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Input Text' , 'metform' ),
                'label_block' => true,
                'description' => esc_html__('Select list text that will be show to user.', 'metform'),
            ]
        );
        $input_fields->add_control(
            'mf_input_option_value', [
                'label' => esc_html__( 'Input Field Value', 'metform' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Input Value' , 'metform' ),
                'label_block' => true,
                'description' => esc_html__('Select list value that will be store/mail to desired person.', 'metform'),
            ]
        );

        $input_fields->add_control(
            'mf_input_option_status', [
                'label' => esc_html__( 'Status', 'metform' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
					'' => esc_html__( 'Enable', 'metform' ),
					'disabled'  => esc_html__( 'Disable', 'metform' ),
                ],
                'description' => esc_html__('Want to make a option? which user can see the option but can\'t select it. make it disable.', 'metform'),
            ]
        );

        $input_fields->add_control(
            'mf_input_option_selected', [
                'label' => esc_html__( 'Select it default ? ', 'metform' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
					'selected' => esc_html__( 'Yes', 'metform' ),
					''  => esc_html__( 'No', 'metform' ),
                ],
                'description' => esc_html__('Make this option default selected', 'metform'),
            ]
        );

        $this->add_control(
            'mf_input_list',
            [
                'label' => esc_html__( 'Dropdown List', 'metform' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $input_fields->get_controls(),
                'title_field' => '{{{ mf_input_option_text }}}',
                'default' => [
                    [
                        'mf_input_option_text' => 'Item 1',
                        'mf_input_option_value' => 'value-1',
                        'mf_input_option_status' => '',
                    ],
                    [
                        'mf_input_option_text' => 'Item 2',
                        'mf_input_option_value' => 'value-2',
                        'mf_input_option_status' => '',
                    ],
                    [
                        'mf_input_option_text' => 'Item 3',
                        'mf_input_option_value' => 'value-3',
                        'mf_input_option_status' => '',
                    ],
                ],
                'description' => esc_html__('You can add/edit here your selector options.', 'metform'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
			'settings_section',
			[
				'label' => esc_html__( 'Settings', 'metform' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->input_setting_controls();

        $this->add_control(
            'mf_input_validation_type',
            [
                'label' => __( 'Validation Type', 'metform' ),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => 'none',
            ]
        );

        $this->end_controls_section();

        if(class_exists('\MetForm_Pro\Base\Package')){
			$this->input_conditional_control();
		}

        $this->start_controls_section(
			'label_section',
			[
				'label' => esc_html__( 'Label', 'metform' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition'    => [
                    'mf_input_label_status' => 'yes',
                ],
			]
        );

		$this->input_label_controls();

        $this->end_controls_section();

        $this->start_controls_section(
			'input_section',
			[
				'label' => esc_html__( 'Select', 'metform' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
        );
            $this->input_controls();
        $this->end_controls_section();

        $this->start_controls_section(
			'options_section',
			[
				'label' => esc_html__( 'Options', 'metform' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
        );

		$this->add_responsive_control(
			'mf_select_options_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'metform' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px','%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					]
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .mf-input-select .mf_select__menu' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: auto;',
				],
			]
		);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'mf_select_options_border',
                'label' => esc_html__( 'Border', 'metform' ),
                'selector' => '{{WRAPPER}} .mf-input-select .mf_select__menu, {{WRAPPER}} .mf-input-select .mf_select__option',
            ]
        );
        
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mf_select_options_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'metform' ),
                'selector' => '{{WRAPPER}} .mf-input-select .mf_select__menu',
			]
		);

        $this->start_controls_tabs( 'mf_select_option_style' );

        $this->start_controls_tab(
            'mf_select_option_tabnormal',
            [
                'label' =>esc_html__( 'Normal', 'metform' ),
            ]
        );

        $this->add_control(
            'mf_select_option_colornormal',
            [
                'label' => esc_html__( 'Color', 'metform' ),
                'type' => Controls_Manager::COLOR,
                'scheme' => [
                    'type' => \Elementor\Core\Schemes\Color::get_type(),
                    'value' => \Elementor\Core\Schemes\Color::COLOR_1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mf-input-select .mf_select__option' => 'color: {{VALUE}}',
                ],
                'default' => '#000000',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'mf_select_option_backgroundnormal',
                'label' => esc_html__( 'Background', 'metform' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mf-input-select .mf_select__option',
            ]
        );
	
        $this->end_controls_tab();

        $this->start_controls_tab(
            'mf_select_option_tabhover',
            [
                'label' =>esc_html__( 'Hover', 'metform' ),
            ]
        );

        $this->add_control(
            'mf_select_option_colorhover',
            [
                'label' => esc_html__( 'Color', 'metform' ),
                'type' => Controls_Manager::COLOR,
                'scheme' => [
                    'type' => \Elementor\Core\Schemes\Color::get_type(),
                    'value' => \Elementor\Core\Schemes\Color::COLOR_1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mf-input-select .mf_select__option:hover, {{WRAPPER}} .mf-input-select .mf_select__option.mf_select__option--is-focused' => 'color: {{VALUE}}',
                ],
                'default' => '#000000',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'mf_select_option_backgroundhover',
                'label' => esc_html__( 'Background', 'metform' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mf-input-select .mf_select__option:hover, {{WRAPPER}} .mf-input-select .mf_select__option.mf_select__option--is-focused',
            ]
        );
	
        $this->end_controls_tab();

        $this->start_controls_tab(
            'mf_select_option_tabactive',
            [
                'label' =>esc_html__( 'Selected', 'metform' ),
            ]
        );

        $this->add_control(
            'mf_select_option_coloractive',
            [
                'label' => esc_html__( 'Color', 'metform' ),
                'type' => Controls_Manager::COLOR,
                'scheme' => [
                    'type' => \Elementor\Core\Schemes\Color::get_type(),
                    'value' => \Elementor\Core\Schemes\Color::COLOR_1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .mf-input-select .mf_select__option.mf_select__option--is-selected' => 'color: {{VALUE}}',
                ],
                'default' => '#000000',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'mf_select_option_backgroundactive',
                'label' => esc_html__( 'Background', 'metform' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .mf-input-select .mf_select__option.mf_select__option--is-selected',
            ]
        );
	
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
			'placeholder_section',
			[
				'label' => esc_html__( 'Place Holder', 'metform' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->input_place_holder_controls();

		$this->end_controls_section();

        $this->start_controls_section(
			'help_text_section',
			[
				'label' => esc_html__( 'Help Text', 'metform' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
					'mf_input_help_text!' => ''
				]
			]
		);
		
		$this->input_help_text_controls();

        $this->end_controls_section();

        $this->insert_pro_message();
	}

    protected function render($instance = []){
        $settings = $this->get_settings_for_display();
        $inputWrapStart = $inputWrapEnd = '';
        extract($settings);
        
		$render_on_editor = true;
        $is_edit_mode = 'metform-form' === get_post_type() && \Elementor\Plugin::$instance->editor->is_edit_mode();

		/**
		 * Loads the below markup on 'Editor' view, only when 'metform-form' post type
		 */
		if ( $is_edit_mode ):
			$inputWrapStart = '<div class="mf-form-wrapper"></div><script type="text" class="mf-template">return html`';
			$inputWrapEnd = '`</script>';
		endif;

        $class = (isset($settings['mf_conditional_logic_form_list']) ? 'mf-conditional-input' : '');
        
        $configData = [
            'message' 		=> $errorMessage 	= isset($mf_input_validation_warning_message) ? !empty($mf_input_validation_warning_message) ? $mf_input_validation_warning_message : esc_html__('This field is required.', 'metform') : esc_html__('This field is required.', 'metform'),
            'minLength'		=> isset($mf_input_min_length) ? $mf_input_min_length : 1,
            'maxLength'		=> isset($mf_input_max_length) ? $mf_input_max_length : '',
            'type'			=> isset($mf_input_validation_type) ? $mf_input_validation_type : '',
            'required'		=> isset($mf_input_required) && $mf_input_required == 'yes' ? true : false,
        ];

        $mf_default_input_list = array();
        $mf_input_list_array = array();

        foreach ($mf_input_list as $key => $value):
            $mf_input_list_array[$key] = array();
            $mf_input_list_array[$key]['label'] = $value['mf_input_option_text'];
            $mf_input_list_array[$key]['value'] = $value['mf_input_option_value'];
            $mf_input_list_array[$key]['isDisabled'] = $value['mf_input_option_status'] == "disabled" ? true : false ;
            
            if ( $value['mf_input_option_selected'] ) $mf_default_input_list = $mf_input_list_array[$key];
        endforeach;
       
        ?>

        <?php echo $inputWrapStart; ?>

		<div className="mf-input-wrapper">
			<?php if ( 'yes' == $mf_input_label_status ): ?>
				<label className="mf-input-label" htmlFor="mf-input-select-<?php echo esc_attr( $this->get_id() ); ?>">
					<?php echo \MetForm\Utils\Util::react_entity_support( esc_html($mf_input_label), $render_on_editor ); ?>
					<span className="mf-input-required-indicator"><?php echo esc_html( ($mf_input_required === 'yes') ? '*' : '' );?></span>
				</label>
            <?php endif; ?>

            <${props.Select}
                className=${"mf-input mf-input-select <?php echo $class; ?> " + ( validation.errors['<?php echo esc_attr($mf_input_name); ?>'] ? 'mf-invalid' : '' )}
                classNamePrefix="mf_select"
                name="<?php echo esc_attr($mf_input_name); ?>"
                placeholder="<?php echo \MetForm\Utils\Util::react_entity_support( esc_html($mf_input_placeholder), $render_on_editor ); ?>"
                isSearchable=${false}
                options=${<?php echo json_encode($mf_input_list_array); ?>}
                value=${parent.getValue("<?php echo esc_attr($mf_input_name); ?>") ? <?php echo json_encode($mf_input_list); ?>.filter(item => item.value === parent.getValue("<?php echo esc_attr($mf_input_name); ?>"))[0] : <?php echo json_encode( $mf_default_input_list ); ?>}
                onChange=${parent.handleSelect}
                ref=${() => {
                    register({ name: "<?php echo esc_attr($mf_input_name); ?>" }, parent.activateValidation(<?php echo json_encode($configData); ?>));
                    if ( parent.getValue("<?php echo esc_attr($mf_input_name); ?>") === '' && <?php echo (count($mf_default_input_list) > 0) ? 'true' : 'false'; ?> ) {
                        parent.handleChange({
                            target: {
                                name: '<?php echo esc_attr($mf_input_name); ?>',
                                value: '<?php echo (count($mf_default_input_list) > 0) ? esc_attr( $mf_default_input_list["value"] ) : ''; ?>'
                            }
                        });
                        parent.setValue( '<?php echo esc_attr($mf_input_name); ?>', '<?php echo (count($mf_default_input_list) > 0) ? esc_attr( $mf_default_input_list["value"] ) : ''; ?>', true );
                    }
                }}
                />

            <?php if ( !$is_edit_mode ) : ?>
				<${validation.ErrorMessage}
					errors=${validation.errors}
					name="<?php echo esc_attr( $mf_input_name ); ?>"
					as=${html`<span className="mf-error-message"></span>`}
					/>
			<?php endif; ?>

            <?php echo '' != $mf_input_help_text ? '<span className="mf-input-help">'. \MetForm\Utils\Util::react_entity_support( esc_html($mf_input_help_text), $render_on_editor ) .'</span>' : ''; ?>
		</div>

		<?php echo $inputWrapEnd; ?>

        <?php
    }
}
