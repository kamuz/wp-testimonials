<?php

/**
 * Plugin Name: IMAA Testimonials
 * Description: Custom Post Type for Testimonials
 * Author: Nick Bosswell
 * Version: 1.0
 * Text Domain: imaa-testimonials
 */

/**
 * Add image sizes for sliders
 */
add_image_size( 'testimonials', 260, 260, true );

/**
 * Load CSS and JavaScript files
 */
function imaa_testimonials_css_js() {
    wp_enqueue_style( 'imaa_testimonials_style_css', plugins_url( 'css/', __FILE__ ).'style.css' );
    wp_enqueue_script( 'imaa_testimonials_script_js', plugins_url( 'js/', __FILE__ ).'script.js', array('jquery'), '0.1', true );
}
add_action( 'wp_enqueue_scripts', 'imaa_testimonials_css_js' );

/**
 * Register Post Type Testimonials.
 */
function register_cpt_testimonials() {
    $labels = array(
        "name" => __( "Testimonials", "custom-post-type-ui" ),
        "singular_name" => __( "Testimonial", "custom-post-type-ui" ),
        'edit_item' => __( 'Edit Testimonlial' ),
        'search_items' =>  __( 'Search Testimonlial' ),
    );
    $args = array(
        "label" => __( "Testimonials", "custom-post-type-ui" ),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "delete_with_user" => false,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( "slug" => "testimonials", "with_front" => true ),
        "query_var" => true,
        "menu_icon" => "dashicons-editor-quote",
        "supports" => array( "title", "editor", "thumbnail", "excerpt" ),
    );
    register_post_type( "testimonials", $args );
}
add_action( 'init', 'register_cpt_testimonials' );

/**
 * Create custom tahonomy Programs for Testimonials
 */
function create_cpt_taxonomy() {
    $labels = array(
        'name' => _x( 'Programs', 'taxonomy general name' ),
        'singular_name' => _x( 'Program', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Programs' ),
        'all_items' => __( 'All Programs' ),
        'parent_item' => __( 'Parent Program' ),
        'parent_item_colon' => __( 'Parent Program:' ),
        'edit_item' => __( 'Edit Program' ),
        'update_item' => __( 'Update Program' ),
        'add_new_item' => __( 'Add New Program' ),
        'new_item_name' => __( 'New Photo Caregory Name' ),
        'menu_name' => __( 'Programs' ),
    );
    register_taxonomy('programs', array('testimonials'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
    ));
}
add_action( 'init', 'create_cpt_taxonomy', 0 );

/**
 * Custom Loop shortcode [imaa_testimonials]
 */
function imaa_testimonials_shortcode($atts){
    extract(shortcode_atts(array(
        'post_type' => 'testimonials',
        'orderby' => 'date',
        'posts_per_page' => 4,
    ), $atts));
    $args = array(
        'post_type' => $post_type,
        'orderby' => $orderby,
        'posts_per_page' => $posts_per_page
    );
    $posts = new WP_Query($args);
    // Form for the filter
    $output .= '<div>';
        $output .= '<form action="' . site_url() . '/wp-admin/admin-ajax.php" method="POST" id="testimonials-filter">';
            if( $terms = get_terms( 'programs' ) ){
                $output .= '<label for="testimonials-filter-select">Filter by Program</label>';
                $output .= '<select id="testimonials-filter-select" name="testimonials-filter-select"><option>All</option>';
                foreach ( $terms as $term ){
                    $output .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
                }
                $output .= '</select>';
            }
            $output .= '<input type="hidden" name="action" value="testimonials_filter">';
            $output .= '<img src="'.  plugins_url( 'img/', __FILE__ ).'/ajax-loader.gif" alt="loader" class="ajax-loader">';
        $output .= '</form>';
    $output .= '</div>';
    // List of testimonials
    if($posts->have_posts()){
        $output .= '<div class="testimonials-container">';
            $output .= '<div class="testimonials-items">';
                global $post;
                while($posts->have_posts()): $posts->the_post();
                    $output .= '<div class="testimonial-item">';
                        $output .= '<div class="testimonial-photo-tag">';
                            $posttags = get_the_tags();
                            $terms = get_the_terms( get_the_ID(), 'programs' );
                            if( ! empty( $terms ) ){
                                foreach( $terms as $term ){
                                    $termID = $term->term_id;
                                    $termMeta = get_term_meta( $termID );
                                    $tagColor = $termMeta['tpc_color'][0];
                                    $output .= '<span class="program-tag" style="background-color: ' . $tagColor . '">' . $term->name . '</span>';
                                }
                            }
                            $output .= '<div class="testimonial-photo" style="border-bottom: 6px solid ' . $tagColor . '">' . get_the_post_thumbnail( $post->ID, 'testimonials' ) . '</div>';
                        $output .= '</div>';
                        $output .= '<div class="user-name">' . get_the_title() . '</div>';
                        $output .= '<p class="user-post">' . get_the_excerpt() . '</p>';
                        $output .= '<a class="find-linkedin" href="https://www.linkedin.com/in/' . get_field('linkedin_profile') . '" target="_blank">Find ' . get_the_title() . ' on LinkedIn</a>';
                        $output .= '<a class="read-statement" href="' . get_the_permalink() . '">Read Statement &nbsp;<span class="fa fa-chevron-right"></span></a>';
                    $output .= '</div>';
                endwhile;
            $output .= '</div>';
            $output .= '<div class="more-testimonials"><a href="' . get_post_type_archive_link( 'testimonials' ) . '">More Testimonials &nbsp;&nbsp;&nbsp;<i class="fa fa-long-arrow-right"></i></a></div>';
        $output .= '</div>';
    }
    else{
        $output .= '<div class="alert alert-danger">' . esc_html__('Sorry, no posts matched your criteria.', 'imaa-past-trainings') . '</div>';
    }
    wp_reset_postdata();
    return $output;
}
add_shortcode('imaa_testimonials', 'imaa_testimonials_shortcode');

/**
 * Action of filter by program
 */
function filter_testimonials(){
    $args = array(
        'post_type' => 'testimonials',
        'posts_per_page' => -1,
    );
    // Filter by program
    if( isset( $_POST['testimonials-filter-select'] ) ){
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'programs',
                'field' => 'id',
                'terms' => $_POST['testimonials-filter-select']
            )
        );
    }
    // Update list of testimonials
    $query = new WP_Query( $args );
    if( $query->have_posts() ){
        global $post;
        while($query->have_posts()): $query->the_post();
            echo '<div class="testimonial-item">';
                echo '<div class="testimonial-photo-tag">';
                    $posttags = get_the_tags();
                    $terms = get_the_terms( get_the_ID(), 'programs' );
                    if( ! empty( $terms ) ){
                        foreach( $terms as $term ){
                            $termID = $term->term_id;
                            $termMeta = get_term_meta( $termID );
                            $tagColor = $termMeta['tpc_color'][0];
                            echo '<span class="program-tag" style="background-color: ' . $tagColor . '">' . $term->name . '</span>';
                        }
                    }
                    echo '<div class="testimonial-photo" style="border-bottom: 6px solid ' . $tagColor . '">' . get_the_post_thumbnail( $post->ID, 'testimonials' ) . '</div>';
                echo '</div>';
                echo '<div class="user-name">' . get_the_title() . '</div>';
                echo '<p class="user-post">' . get_the_excerpt() . '</p>';
                echo '<a class="find-linkedin" href="https://www.linkedin.com/in/' . get_field('linkedin_profile') . '" target="_blank">Find ' . get_the_title() . ' on LinkedIn</a>';
                echo '<a class="read-statement" href="' . get_the_permalink() . '">Read Statement &nbsp;<span class="fa fa-chevron-right"></span></a>';
            echo '</div>';
        endwhile;
        wp_reset_postdata();
    }
    die();
}
add_action('wp_ajax_testimonials_filter', 'filter_testimonials');
add_action('wp_ajax_nopriv_testimonials_filter', 'filter_testimonials');