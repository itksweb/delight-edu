<?php
namespace DelightEDU\Controllers\Admin\MainRoot;


class SettingsController {

    public function __construct() {
        // Hook to register the settings whitelist
        add_action('admin_init', [$this, 'register_global_settings']);
    }

    public function register_global_settings() {
        $settings_data = [
            [
                'name'=> 'dedu_school_name',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'DelightEdu Model School'
            ],
            [
                'name'=> 'dedu_school_motto',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Knowledge is treasure'
            ],
            [
                'name'=> 'dedu_school_address',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '74, Ajanlekoko Street Ojo Lagos'
            ],
            [
                'name'=> 'dedu_school_phone',
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => '08098080171'
            ],
            [
                'name'=> 'dedu_current_academic_year',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => ''
            ],
            [
                'name'=> 'dedu_staff_id_prefix',
                'type'              => 'string',
                'sanitize_callback' => [$this, 'sanitize_staff_prefix'],
                'default'           => 'SCH'
            ],
            [
                'name'=> 'dedu_term_naming_label',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Term'
            ],
            [
                'name'=> 'dedu_session_divisions',
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => '3'
            ],
            [
                'name'=> 'dedu_term_naming_style',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'ordinal'
            ]
        ];
        foreach($settings_data as $setting) {
            register_setting('dedu_global_settings_group', $setting['name'], [
                'type'=> $setting['type'],
                'sanitize_callback'=> $setting['sanitize_callback'],
                'default'=> $setting['default'],
            ]);
        }
        
    }

    public function sanitize_staff_prefix($value) {
        return strtoupper(sanitize_text_field($value));
    }

    public function render_settings_page() {
        // Fetch current values
        $current_year = get_option('dedu_current_academic_year', date('Y') . '/' . (date('Y') + 1));
        $staff_prefix = get_option('dedu_staff_id_prefix', 'EDU');
        $term_label   = get_option('dedu_term_naming_label', 'Term');
        $divisions    = absint(get_option('dedu_session_divisions', 3));
        $school_name = get_option('dedu_school_name', 'Delight Precious School');
        $school_motto = get_option('dedu_school_motto', 'Knowledge is treasure');
        $school_address = get_option('dedu_school_address', 'Km5 Refinery Rd Warri');
        $school_phone = absint(get_option('dedu_school_phone', '08060719978'));
        
        include \DEDU_PATH . 'templates/admin/settings/settings-view.php';
    }
}