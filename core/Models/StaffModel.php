<?php
namespace DelightEDU\Models;
use DelightEDU\Assets\Admin\Helpers;



class StaffModel {
    private $table_name;
    protected $table_caps;


    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'dedu_staff';
        $this->table_caps  = $wpdb->prefix . 'dedu_staff_capabilities';
    }

    public function get_staff_schema() {
        return [
            'profile_picture_id'    => ['filter' => 'absint',  'format' => '%d'],
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
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

    
    /**
     * Create a new Staff Member
     */
    public function create() {
        global $wpdb;  
        
        $user = [];
        $user['email'] = sanitize_user($_POST['email']);
        $user['password'] = !empty($_POST['password']) ? $_POST['password'] : wp_generate_password();

       
        // 1. Create the WordPress User first
        $user_id = Helpers::create_wp_user($user);
        if (is_wp_error($user_id)) return $user_id;
        
        // Sanitize the data
        $schema = $this->get_staff_schema();
        $photoKey = !isset($_POST['staff_photo']) ? 'staff_photo':'';
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);

        //  Generate Staff ID Number
        $prefix = get_option('dedu_staff_id_prefix', 'EDU');
        $prefix = rtrim($prefix, '-') . '-' . date('y') . '-';
        $staff_id_number = Helpers::generate_unique_id($prefix);

        $prep = [];
        $prep["wp_user_id"] = $user_id;
        $prep['staff_id_number'] = $staff_id_number;

        
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        $data = $prep + $data;
        $formats = ['%d', '%s'] + $formats;


        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table_name, $data, $formats );
        if ($inserted){
            $staff_id = $wpdb->insert_id;
            $permissions = isset($_POST['staff_permissions']) ? (array) $_POST['staff_permissions'] : [];
            $table_staff_caps = $wpdb->prefix . 'dedu_staff_capabilities'; 
            
            // 1. Clear old overrides for this staff
            $wpdb->delete($table_staff_caps, ['staff_id' => $staff_id], ['%d']);
            
            // 2. Insert new overrides
            foreach ($permissions as $cap) {
                $wpdb->insert($table_staff_caps, [
                    'staff_id' => $staff_id,
                    'capability' => sanitize_text_field($cap)
                ]);
            }
        }
        return $inserted ? $wpdb->insert_id : false;
    }

    public function delete( $id ) {
        global $wpdb;

        // 1. Fetch the WP User ID BEFORE deleting the staff record
        $staff_record = $wpdb->get_row( $wpdb->prepare(
            "SELECT wp_user_id FROM {$this->table_name} WHERE id = %d", 
            $id 
        ));

        // 2. Delete associated capabilities (Cleanup Orphans)
        $wpdb->delete(
            $this->table_caps, 
            [ 'staff_id' => $id ], 
            [ '%d' ]
        );

        // 3. Delete the staff record from your custom table
        $result = $wpdb->delete(
            $this->table_name,
            [ 'id' => $id ],
            [ '%d' ]
        );

        // 4. Now delete the actual WordPress User if it exists
        if ( ! empty( $staff_record->wp_user_id ) ) {
            // Note: wp_delete_user needs the ID, and optionally a 'reassign' ID
            require_once( ABSPATH . 'wp-admin/includes/user.php' ); // Ensure function is loaded
            wp_delete_user( $staff_record->wp_user_id );
        }

        return false !== $result;
    }

    public function get_all() {
        global $wpdb;
        $role_table = $wpdb->prefix . 'dedu_staff_roles';
        
        return $wpdb->get_results("
            SELECT s.*, r.role_name 
            FROM {$this->table_name} s
            LEFT JOIN $role_table r ON s.role_id = r.id
            ORDER BY s.created_at DESC
        ");
    }

    public function update($staff_id) {
        global $wpdb;

         // Sanitize the data
        $schema = $this->get_staff_schema();
        $photoKey = !isset($_POST['staff_photo']) ? 'staff_photo':'';
        $user_id = isset($_POST['wp_user_id']) ? absint($_POST['wp_user_id']): null;
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);

        $data = $sanitized_data[0];

        if (isset($_POST['staff_permissions']) && is_array($_POST['staff_permissions'])) {
            $table_staff_caps = $wpdb->prefix . 'dedu_staff_capabilities'; 
            
            // 1. Clear old permissions for this staff
            $wpdb->delete($table_staff_caps, ['staff_id' => $staff_id], ['%d']);
            
            // 2. Insert new permissions
            foreach ($_POST['staff_permissions'] as $cap) {
                $wpdb->insert($table_staff_caps, [
                    'staff_id' => $staff_id,
                    'capability' => sanitize_text_field($cap)
                ]);
            }
        }

        return $wpdb->update(
            $this->table_name,
            $data,
            ['id' => $staff_id], // The WHERE clause
            null,          // Format (auto-detected usually)
            ['%d']         // Format of the WHERE clause
        );
    }
    
    public function get_staff_by_id($id) {
        global $wpdb;
        $staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
        // $staff = $wpdb->get_row($wpdb->prepare(
        //     "SELECT * FROM {$this->table_name} WHERE id = %d",
        //     $id
        // ));
        if ($staff) {
            // Fix "Zero Dates" for the frontend
            $date_fields = ['date_of_birth', 'joining_date'];
            foreach ($date_fields as $field) {
                if (empty($staff[$field]) || $staff[$field] === '0000-00-00') {
                    $staff[$field] = ''; // Set to empty string so the HTML input stays blank
                }
            }
        }
        return $staff;
    }
    
    public function get_staff_permissions($staff_id) {
        global $wpdb;
        $table_staff_caps = $wpdb->prefix . 'dedu_staff_capabilities';
        
        // Use get_col to get a simple flat array of strings
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT capability FROM $table_staff_caps WHERE staff_id = %d",
            $staff_id
        ));

        return $results ? $results : [];
    }

    
    public function get_staff_column($staff_id, $column_name) {
        global $wpdb;
        
        // Use %i for identifiers (column names) and %d for the ID
        return $wpdb->get_var($wpdb->prepare(
            "SELECT $column_name FROM {$this->table_name} WHERE id = %d", 
            $staff_id
        ));
    }
}