
<?php

// function to allow the navigation to include fuction with the same taxonomy

add_filter( 'get_next_post_join', 'navigate_in_same_taxonomy_join', 20);
add_filter( 'get_previous_post_join', 'navigate_in_same_taxonomy_join', 20 );
function navigate_in_same_taxonomy_join() {
    global $wpdb;
    return " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
}

// Include the post with the same tags

add_filter( 'get_next_post_where' , 'navigate_in_same_taxonomy_where' );
add_filter( 'get_previous_post_where' , 'navigate_in_same_taxonomy_where' );
function navigate_in_same_taxonomy_where( $original ) {
    global $wpdb, $post;
    $where      = '';
    $taxonomy   = 'post_tag';
    $op         = ('get_previous_post_where' == current_filter()) ? '<' : '>';
    $where      = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );
    if ( ! is_object_in_taxonomy( $post->post_type, $taxonomy ) )
        return $original ;

    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

    $term_array = array_map( 'intval', $term_array );

    if ( ! $term_array || is_wp_error( $term_array ) )
        return $original ;

    $where      = " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
    return $wpdb->prepare( "WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish' $where", $post->post_date, $post->post_type );
}

