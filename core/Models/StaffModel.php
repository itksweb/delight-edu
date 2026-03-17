<?php
namespace DelightEDU\Models;

class StaffModel {
    private $table_name;
    protected $table_caps;


    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'dedu_staff';
        $this->table_caps  = $wpdb->prefix . 'dedu_staff_capabilities';
    }

    /**
     * Generates a unique Staff ID based on prefix and year.
     * Checks against database to prevent collisions.
     */
    private function generate_unique_id($prefix) {
        global $wpdb;

        $is_unique = false;
        $final_id  = '';
        $attempts  = 0;

        while (!$is_unique && $attempts < 10) {
            // Generate potential ID: PREFIX-YY-RAND(3)
            $potential_id = $prefix . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            // Check database
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE staff_id_number = %s",
                $potential_id
            ));

            if ($exists == 0) {
                $final_id = $potential_id;
                $is_unique = true;
            }
            $attempts++;
        }

        // fallback if for some crazy reason 10 random attempts fail
        return $is_unique ? $final_id : $prefix . time();
    }

    /**
     * Create a new Staff Member
     */
    public function create($dataArr) {
        global $wpdb;
        $data = $dataArr[0];
        $formats = $dataArr[1];

        // 1. Check if email already exists in WordPress to prevent fatal errors
        if (email_exists($_POST['email']) || username_exists($_POST['email'])) {
           return new WP_Error('email_exists', 'This email is already registered in the system.');
        }
       
        // 2. Create the WordPress User first
        $user_id = wp_create_user( $data['email'], $_POST['password'], $data['email'] );
        if (is_wp_error($user_id)) return $user_id;

        $prep = [];
        $prep["wp_user_id"] = $user_id;

        $prefix = get_option('dedu_staff_id_prefix', 'EDU');
        $prefix = rtrim($prefix, '-') . '-' . date('y') . '-';
        $staff_id_number = $this->generate_unique_id($prefix);
        $prep['staff_id_number'] = $staff_id_number;

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

    public function update($staff_id, $data) {
        global $wpdb;

        if (isset($_POST['staff_permissions']) && is_array($_POST['staff_permissions'])) {
            $table_staff_caps = $wpdb->prefix . 'dedu_staff_capabilities'; 
            
            // 1. Clear old overrides for this staff
            $wpdb->delete($table_staff_caps, ['staff_id' => $staff_id], ['%d']);
            
            // 2. Insert new overrides
            foreach ($_POST['staff_permissions'] as $cap) {
                $wpdb->insert($table_staff_caps, [
                    'staff_id' => $staff_id,
                    'capability' => sanitize_text_field($cap)
                ]);
            }
        }

        $update_data = [
            'first_name'     => sanitize_text_field($data['first_name']),
            'last_name'      => sanitize_text_field($data['last_name']),
            'email'          => sanitize_email($data['email']),
            'phone'          => sanitize_text_field($data['phone'] ?? ''),
            'role_id'        => !empty($data['role_id']) ? absint($data['role_id']) : null,
            'is_teacher'     => isset($data['is_teacher']) ? 1 : 0,
            'class_id'       => !empty($data['class_id']) ? absint($data['class_id']) : null,
            'status'         => sanitize_text_field($data['status'] ?? 'active'),
        ];

        // Only update password if a new one is provided
        if (!empty($_POST['password'])) $_POST['password'] = wp_hash_password($_POST['password']);
        // if (!empty($_FILES['staff_photo']['name'])) {
        //     $new_url = $this->upload_photo_get_its_url('staff_photo');
        //     if ($new_url) {
        //         $update_data['profile_picture'] = $new_url;
        //     }
        // }

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
    /**
     * Needed for the individual permission overrides
     */
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
}