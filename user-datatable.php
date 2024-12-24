<?php
/**
 * Plugin Name: User Data Table
 * Description: Display a table with user data using jQuery DataTables.
 * Version: 1.0
 * Author: Your Name
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Enqueue scripts and styles
function udt_enqueue_scripts() {
    if ( is_admin() ) {
        wp_enqueue_style( 'udt-datatables-css', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'udt-datatables-js', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.4', true );
        wp_enqueue_script( 'udt-responsive-js', 'https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js', array( 'jquery', 'udt-datatables-js' ), '2.4.1', true );

        // wp_enqueue_style( 'udt-responsive-css', 'https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css', array(), '2.4.1' );
        wp_enqueue_script( 'udt-custom-js', plugin_dir_url( __FILE__ ) . 'user-datatable.js', array( 'jquery', 'udt-datatables-js' ), '1.0', true );
        wp_enqueue_style( 'udt-custom-css', plugin_dir_url( __FILE__ ) . 'user-datatable.css', );
        wp_localize_script( 'udt-custom-js', 'udt_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'udt_ajax_nonce' ),
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'udt_enqueue_scripts' );

// Create admin menu page
function eudt_create_menu() {
    add_menu_page(
        'User Data Table',
        'User Data Table',
        'manage_options',
        'user-data-table',
        'eudt_render_page',
        'dashicons-admin-users',
        25
    );
    add_submenu_page(
        'user-data-table',
        'PHP Data',
        'PHP Data',
        'manage_options',
        'user-data-table',
        'eudt_render_page'
    );
    add_submenu_page(
        'user-data-table',
        'Ajax Data',
        'Ajax Data',
        'manage_options',
        'user-data-table-ajax',
        'udt_ajax_render_page'
    );
}
add_action( 'admin_menu', 'eudt_create_menu' );

// Render the admin page
function eudt_render_page() {
    ?>
    <div class="wrap">
        <h1>Enhanced User Data Table</h1>
        <table id="user-data-table" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Data</th>
                    <th>Role</th>
                    <th>Post Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;

                // Query users and their metadata
                // $users = $wpdb->get_results(
                //     "SELECT u.ID, u.user_email, u.display_name, u.user_login,
                //     (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = 'first_name') as first_name,
                //     (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = 'last_name') as last_name,
                //     (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = '{$wpdb->prefix}capabilities') as role
                //     FROM {$wpdb->users} u"
                // );
                // $role = maybe_unserialize( $user->role );

                // Get all users
                $users = get_users();

                foreach ( $users as $user ) {
                    $user_id = $user->ID;
                    $user_email = $user->user_email;
                    $display_name = $user->display_name;
                    $avatar = get_avatar( $user_id, 32 );
                    $roles = implode( ', ', $user->roles );
                    $post_count = count_user_posts( $user_id );

                    // Links for action buttons
                    $edit_link = get_edit_user_link( $user_id );
                    $delete_link = admin_url( "users.php?action=delete&user=$user_id" );
                    $author_link = get_author_posts_url( $user_id );

                    echo '<tr>';
                    echo '<td>' . esc_html( $user_id ) . '</td>';
                    echo '<td>' . $avatar . ' <strong>' . esc_html( $display_name ) . '</strong><br><a href="mailto:' . esc_attr( $user_email ) . '">' . esc_html( $user_email ) . '</a></td>';
                    echo '<td>' . esc_html( $roles ) . '</td>';
                    echo '<td>' . esc_html( $post_count ) . '</td>';
                    echo '<td>
                        <a href="' . esc_url( $edit_link ) . '" class="button button-primary">Edit</a>
                        <a href="' . esc_url( $delete_link ) . '" class="button button-danger" onclick="return confirm(\'Are you sure you want to delete this user?\')">Delete</a>
                        <a href="' . esc_url( $author_link ) . '" class="button button-secondary">View Posts</a>
                    </td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Render the admin page
function udt_ajax_render_page() {
    ?>
    <div class="wrap">
        <h1>AJAX User Data Table</h1>
        <table id="user-data-table-ajax" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Data</th>
                    <th>Role</th>
                    <th>Post Count</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <?php
}

// Handle AJAX request to fetch user data
function udt_ajax_get_users() {
    check_ajax_referer( 'udt_ajax_nonce', 'nonce' );

    global $wpdb;

    // Fetch users
    $users = $wpdb->get_results(
        "SELECT u.ID, u.user_email, u.display_name, 
        (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = 'first_name') AS first_name,
        (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = 'last_name') AS last_name,
        (SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = u.ID AND meta_key = '{$wpdb->prefix}capabilities') AS role
        FROM {$wpdb->users} u"
    );

    $data = array();

    foreach ( $users as $user ) {
        $first_name = esc_html( $user->first_name );
        $last_name = esc_html( $user->last_name );
        $role = maybe_unserialize( $user->role );
        $role = is_array( $role ) ? ucfirst( key( $role ) ) : 'Subscriber';
        $post_count = count_user_posts( $user->ID );

        $avatar = get_avatar( $user->ID, 32 );
        $edit_link = get_edit_user_link( $user->ID );
        $delete_link = esc_url( admin_url( "user-edit.php?user_id={$user->ID}&action=delete" ) );
        $archive_link = esc_url( get_author_posts_url( $user->ID ) );

        $actions = '<a href="' . esc_url( $edit_link ) . '" class="button button-primary" style="margin-right:5px;">Edit</a>';
        $actions .= '<a href="' . esc_url( $delete_link ) . '" class="button button-secondary" style="margin-right:5px;">Delete</a>';
        $actions .= '<a href="' . esc_url( $archive_link ) . '" class="button button-secondary">View Posts</a>';

        $data[] = array(
            'ID'         => $user->ID,
            'user_data'  => $avatar . '<br><strong>' . esc_html( $user->display_name ) . '</strong><br>' . esc_html( $user->user_email ),
            'role'       => $role,
            'post_count' => $post_count,
            'action'     => $actions,
        );
    }

    wp_send_json( array( 'data' => $data ) );
}
add_action( 'wp_ajax_udt_ajax_get_users', 'udt_ajax_get_users' );