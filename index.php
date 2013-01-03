<?php

/*
  Plugin Name: WP Pricing Tables
  Plugin URI: -
  Description: Create Dyamic Pricing Tables for WordPress.
  Author: Rakhitha Nimesh
  Version: 1.0
  Author URI: -
 */

/*
 * Registering and saving pricing tables 
 */

add_action('init', 'wppt_register_cpt_pricing_tables');

function wppt_register_cpt_pricing_tables() {

    $labels = array(
        'name' => _x('Pricing Tables', 'pricing_tables'),
        'singular_name' => _x('Pricing Table', 'pricing_tables'),
        'add_new' => _x('Add New', 'pricing_tables'),
        'add_new_item' => _x('Add New Pricing Table', 'pricing_tables'),
        'edit_item' => _x('Edit Pricing Table', 'pricing_tables'),
        'new_item' => _x('New Pricing Table', 'pricing_tables'),
        'view_item' => _x('View Pricing Table', 'pricing_tables'),
        'search_items' => _x('Search Pricing Tables', 'pricing_tables'),
        'not_found' => _x('No Pricing Tables found', 'pricing_tables'),
        'not_found_in_trash' => _x('No Pricing Tables found in Trash', 'pricing_tables'),
        'parent_item_colon' => _x('Parent Pricing Table:', 'pricing_tables'),
        'menu_name' => _x('Pricing Tables', 'pricing_tables'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Pricing Tables',
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('pricing_tables', $args);
}

add_action('add_meta_boxes', 'wppt_pricing_tables_meta_boxes');

function wppt_pricing_tables_meta_boxes() {

    add_meta_box("pricing-table-info", "Pricing Table Info", 'wppt_generate_pricing_table_info', "pricing_tables", "normal", "high");
}

function wppt_generate_pricing_table_info() {
    global $post;

    $table_packages = get_post_meta($post->ID, "_table_packages", true);
    $table_packages = ($table_packages == '') ? array() : json_decode($table_packages);

    $query = new WP_Query(array('post_type' => 'pricing_packages',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'post_date',
                'order' => 'ASC'
                    )
    );

    $html = '<input type="hidden" name="pricing_table_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    $html .= '<table class="form-table">';
    $html .= "<tr><th style=''>
                    Package Status
              </th>
              <td>
                    Package Name
              </td></tr>";

    while ($query->have_posts()) : $query->the_post();
        $checked_status = (in_array($query->post->ID, $table_packages)) ? "checked" : "";

        $html .= "<tr><th style=''>
                    <input type='checkbox' name='pricing_table_packages[]' $checked_status value='" . $query->post->ID . "' />
              </th><td>" . $query->post->post_title . "
              </td></tr>";

    endwhile;

    $html .= '</table>';

    echo $html;
}

add_action('save_post', 'wppt_save_pricing_tables');

function wppt_save_pricing_tables($post_id) {

    if (!wp_verify_nonce($_POST['pricing_table_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('pricing_tables' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

        $pricing_table_packages = (isset($_POST['pricing_table_packages']) ? $_POST['pricing_table_packages'] : array());
        $pricing_table_packages = json_encode($pricing_table_packages);

        update_post_meta($post_id, "_table_packages", $pricing_table_packages);
    } else {
        return $post_id;
    }
}

/*
 * Registering and saving pricing packages
 */

add_action('init', 'wppt_register_cpt_pricing_packages');

function wppt_register_cpt_pricing_packages() {

    $labels = array(
        'name' => _x('Pricing Packages', 'pricing_packages'),
        'singular_name' => _x('Pricing Package', 'pricing_packages'),
        'add_new' => _x('Add New', 'pricing_packages'),
        'add_new_item' => _x('Add New Pricing Package', 'pricing_packages'),
        'edit_item' => _x('Edit Pricing Package', 'pricing_packages'),
        'new_item' => _x('New Pricing Package', 'pricing_packages'),
        'view_item' => _x('View Pricing Package', 'pricing_packages'),
        'search_items' => _x('Search Pricing Packages', 'pricing_packages'),
        'not_found' => _x('No Pricing Packages found', 'pricing_packages'),
        'not_found_in_trash' => _x('No Pricing Packages found in Trash', 'pricing_packages'),
        'parent_item_colon' => _x('Parent Pricing Package:', 'pricing_packages'),
        'menu_name' => _x('Pricing Packages', 'pricing_packages'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Pricing Packages',
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('pricing_packages', $args);
}

add_action('add_meta_boxes', 'wppt_pricing_packages_meta_boxes');

function wppt_pricing_packages_meta_boxes() {

    add_meta_box("pricing-package-info", "Pricing Package Info", 'wppt_generate_pricing_package_info', "pricing_packages", "normal", "high");
    add_meta_box("pricing-features-info", "Pricing Features", 'wppt_generate_pricing_features_info', "pricing_packages", "normal", "high");
}

function wppt_generate_pricing_package_info() {
    global $post;

    $package_price = get_post_meta($post->ID, "_package_price", true);
    $package_buy_link = get_post_meta($post->ID, "_package_buy_link", true);

    $html = '<input type="hidden" name="pricing_package_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    $html .= '<table class="form-table">';

    $html .= "<tr><th style=''>
                    <label for='Price'>Package Price *</label>
              </th>
              <td>
                    <input name='package_price' id='package_price' type='text' value='$package_price' />
              </td></tr>";

    $html .= "<tr><th style=''>
                    <label for='Buy Now'>Buy Now Link *</label>
              </th>
              <td>
                    <input name='package_buy_link' id='package_buy_link' type='text' value='$package_buy_link' />
              </td></tr>";




    $html .= "</tr>";
    $html .= '</table>';

    echo $html;
}

function wppt_generate_pricing_features_info() {

    global $post;

    $package_features = get_post_meta($post->ID, "_package_features", true);
    $package_features = ($package_features == '') ? array() : json_decode($package_features);



    $html .= '<table class="form-table">';

    $html .= "<tr><th style=''>
                    <label for='Price'>Add Package Features</label>
              </th>
              <td>
                    <input name='package_feature' id='package_feature' type='text'  />
                    <input type='button' id='add_features' value='Add Features' />
              </td></tr>";

    $html .= "<tr><td>
                    <ul id='package_features_box' name='package_features_box' >";
    foreach ($package_features as $package_feature) {
        $html .= "<li><input type='hidden' name='package_features[]' value='$package_feature' />$package_feature
        <a href='javascript:void(0);'>Delete</a></li>";
    }
    $html .= "</ul>
              </td></tr>";

    $html .= '</table>';

    echo $html;
}

add_action('save_post', 'wppt_save_pricing_packages');

function wppt_save_pricing_packages($post_id) {

    if (!wp_verify_nonce($_POST['pricing_package_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('pricing_packages' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {
        $package_price = (isset($_POST['package_price']) ? $_POST['package_price'] : '');
        $package_buy_link = (isset($_POST['package_buy_link']) ? $_POST['package_buy_link'] : '');

        $package_features = (isset($_POST['package_features']) ? $_POST['package_features'] : array());
        $package_features = json_encode($package_features);

        update_post_meta($post_id, "_package_price", $package_price);
        update_post_meta($post_id, "_package_buy_link", $package_buy_link);
        update_post_meta($post_id, "_package_features", $package_features);
    } else {
        return $post_id;
    }
}

/*
 * WordPress Admin Dashborad Scripts and Styles
 */

function wppt_pricing_admin_scripts() {

    wp_register_script('pricing-admin', plugins_url('js/pricing_admin.js', __FILE__), array('jquery'));
    wp_enqueue_script('pricing-admin');
}

add_action('admin_enqueue_scripts', 'wppt_pricing_admin_scripts');



/*
 * Pricing Table Frontend Scripts
 */

function wppt_pricing_front_scripts() {

    wp_register_style('pricing-base', plugins_url('css/base.css', __FILE__));
    wp_enqueue_style('pricing-base');

    wp_register_style('pricing-layout', plugins_url('css/layout.css', __FILE__));
    wp_enqueue_style('pricing-layout');

    wp_register_style('pricing-fluid-skeleton', plugins_url('css/fluid_skeleton.css', __FILE__));
    wp_enqueue_style('pricing-fluid-skeleton');

    wp_register_style('pricing-table', plugins_url('css/pricing_table.css', __FILE__));
    wp_enqueue_style('pricing-table');
}

add_action('wp_enqueue_scripts', 'wppt_pricing_front_scripts');


/*
 * Adding Custom Columns to Pricing Table List with Soratable Functionality
 */

add_filter('manage_edit-pricing_tables_columns', 'wppt_edit_pricing_tables_columns');

function wppt_edit_pricing_tables_columns($columns) {

    $columns = array(
        'cb' => '<input type="checkbox" />',
        'ID' => __('Pricing Table No'),
        'title' => __('Pricing Table Name'),
        'date' => __('Date')
    );

    return $columns;
}

add_action('manage_pricing_tables_posts_custom_column', 'wppt_manage_pricing_tables_columns', 10, 2);

function wppt_manage_pricing_tables_columns($column, $post_id) {
    global $post;

    switch ($column) {


        case 'ID' :


            $pricing_id = $post_id;


            if (empty($pricing_id))
                echo __('Unknown');


            else
                printf($pricing_id);

            break;


        default :
            break;
    }
}

add_filter('manage_edit-pricing_tables_sortable_columns', 'wppt_pricing_tables_sortable_columns');

function wppt_pricing_tables_sortable_columns($columns) {

    $columns['ID'] = 'ID';

    return $columns;
}

/*
 * Pricing table shortcode
 */
add_shortcode("wppt_show_pricing", "wppt_generate_pricing_table");

function wppt_generate_pricing_table($atts) {
    global $post;

    extract(shortcode_atts(array(
		'pricing_id' => '0',
	), $atts));

    $table_packages = get_post_meta($pricing_id, "_table_packages", true);
    $table_packages = ($table_packages == '') ? array() : json_decode($table_packages);

    $html = '<div class="container">';

    $pricing_index = 0;
    foreach ($table_packages as $table_package) {
        $pricing_index++;

        $plan_title = get_the_title($table_package);

        $package_price = get_post_meta($table_package, "_package_price", true);
        $package_buy_link = get_post_meta($table_package, "_package_buy_link", true);

        $package_features = get_post_meta($table_package, "_package_features", true);
        $package_features = ($package_features == '') ? array() : json_decode($package_features);

        $html .= '<div id="pricing_plan' . $pricing_index . '" class="four columns">';
        $html .= '<dl class="plans" >
                    <dd class="plan_title">
                    ' . $plan_title . '
                    </dd>
                    <dd class="plan_price">
                        $' . $package_price . '
                    </dd>
                  </dl>';
        $html .= '<dl class="plan" id="pr' . $pricing_index . '">
                    <dt class="plan_more">View<a href="#pr' . $pricing_index . '" class="more_icon"></a>
                        <a href="#" class="less_icon"></a>
                    </dt>';

        foreach ($package_features as $package_feature) {

            $html .= '<dd class="plan_features">
                        <div class="feature_desc"><span class="highlight">' . $package_feature . '</span></div>
                      </dd>';
        }

        $html .= '<dd class="plan_buy">
                    <a href="' . $package_price . '" class="buy" >Buy Now</a>
                  </dd>
                  </dl>';
        $html .= '</div>';
    }
    $html .= '</div>';

    echo  $html;
}
