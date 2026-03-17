<?php
namespace DelightEDU\Assets\Admin;

class Helpers {

    
    public function get_staff_schema() {
        return [
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'profile_picture'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'password'         => ['filter' => 'wp_hash_password',      'format' => '%s'],
            'phone'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'gender'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'marital_status'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'date_of_birth'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'role_id'       => ['filter' => 'absint',              'format' => '%d'],
            'position'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'working_hours'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'is_teacher'       => ['filter' => 'absint',              'format' => '%d'],
            'salary_amount' => ['filter' => 'floatval',            'format' => '%f'],
            'joining_date'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'class_id'       => ['filter' => 'absint',              'format' => '%d'],
        ];
    }

    public function get_stafff_schema() {
        return [
            'staff_id_number' => 'sanitize_text_field',
            'first_name'      => 'sanitize_text_field',
            'last_name'       => 'sanitize_text_field',
            'email'           => 'sanitize_email',
            // Use a closure for fields with custom logic
            'password'        => function($val) { return !empty($val) ? wp_hash_password($val) : null; },
            'phone'           => 'sanitize_text_field',
            'gender'          => 'sanitize_text_field',
            'marital_status'  => 'sanitize_text_field',
            'date_of_birth'   => function($val) { return !empty($val) ? sanitize_text_field($val) : null; },
            'role_id'         => function($val) { return !empty($val) ? absint($val) : null; },
            'position'        => 'sanitize_text_field',
            'working_hours'   => function($val) {return !empty($val) ? sanitize_text_field($val) : 'full-time';},
            'is_teacher'      => function($val) { return isset($val) ? 1 : 0; },
            'salary_amount'   => 'floatval',
            'joining_date'    => function($val) { return !empty($val) ? sanitize_text_field($val) : current_time('mysql', 1); },
            'class_id'        => 'absint',
        ];
    }

    public function get_stav_schema($context = 'create') {
        // 1. Define common fields that appear in both
        $schema = [
            'first_name' => 'sanitize_text_field',
            'last_name'  => 'sanitize_text_field',
            'email'      => 'sanitize_email',
            'password'        => function($val) { return !empty($val) ? wp_hash_password($val) : null; },
            'phone'           => 'sanitize_text_field',
            'gender'          => 'sanitize_text_field',
            'marital_status'  => 'sanitize_text_field',
            'date_of_birth'   => function($val) { return !empty($val) ? sanitize_text_field($val) : null; },
            'role_id'         => function($val) { return !empty($val) ? absint($val) : null; },
            'position'        => 'sanitize_text_field',
            'working_hours'   => function($val) {return !empty($val) ? sanitize_text_field($val) : 'full-time';},
            'is_teacher'      => function($val) { return isset($val) ? 1 : 0; },
            // ... etc
        ];

        // 2. Add 'create'-only fields
        if ($context === 'create') {
            $schema['joining_date'] = function($val) { return !empty($val) ? sanitize_text_field($val) : current_time('mysql', 1); };
            $schema['staff_id_number'] = 'sanitize_text_field';
        } 
        
        // 3. Add 'update'-only logic (e.g., optional password)
        if ($context === 'update') {
            // $schema['password'] = function($val) { return !empty($val) ? wp_hash_password($val) : null; };
        }

        return $schema;
    }

    /**
     * Sanitizes input based on a mapping schema
     * @param array $input The raw data (usually $_POST)
     * @param array $schema Keys are form fields, values are sanitizer functions
     */
    public function sanitize_data() {
        $schema = $this->get_staff_schema();
        $data_to_save = [];
        $format_array = [];

        foreach ($schema as $column => $rules) {
            if (isset($_POST[$column])) {
                $data_to_save[$column] = call_user_func($rules['filter'], $_POST[$column]);
                $format_array[] = $rules['format']; // Automatically adds the right %s, %d, or %f
            }
        }
        return [$data_to_save, $format_array];
    }
}