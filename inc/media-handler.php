<?php 

function add_attachment_post_field( $fields, $post ) {
    $post_titles = "";
    $attachment_id = $post->ID;   
   
  $post_titles = sb_get_all_attched_post($attachment_id);

   $warning_message  =   "";
  if($post_titles != ""){

     $warning_message =   '<span style="width: 63%">you can not delete this media until you deatched it from above posts</span>';

  }

 $fields['media_posts'] = array(
                    'show_in_edit'  => true,
                    'tr'            => '
                    <div id="add_attched_object">
                        <label class="setting">
                        <span class="name">Attached post</span>
                        <span class="value" style="width: 63%">
                           '.$post_titles.'
                        </span>
                        '.$warning_message.'
                        </label>
                        <div class="clear"></div>
                    </div>'
                );

    return $fields;
}
add_filter( 'attachment_fields_to_edit', 'add_attachment_post_field', 10, 2 );


function my_media_columns($columns) {
    $columns['attached_post'] = __('Attached Post', 'my-textdomain');
    return $columns;
}
add_filter('manage_media_columns', 'my_media_columns');

// Display custom column content
function my_media_column_content($column_name, $attachment_id) {
    if ($column_name == 'attached_post') {
       
      echo $post_titles = sb_get_all_attched_post($attachment_id);
    }
}
add_filter('manage_media_custom_column', 'my_media_column_content', 10, 2);


function sb_get_all_attched_post($attachment_id , $request_from = ""){
    
    $post_titles = "";
    $post_ids  =  [];

    $args = array(
    'post_type' => 'any',
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => '_thumbnail_id',
            'value' => $attachment_id,
            'compare' => '='
        ),
    
    )
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
           $post_id = get_the_ID();
    $post_title = get_the_title();
    $edit_post_link = get_edit_post_link($post_id);
    $post_titles  .= '<a href="' . esc_url($edit_post_link) . '">' . esc_html($post_title) . '</a><br>';

      $post_ids[]   =  $post_id;
    }
}
    
wp_reset_postdata() ;
    
    /*for content media*/
      $args = array(
    'post_type' => 'any',
     's' => wp_get_attachment_url($attachment_id),
);
    
$query = new WP_Query($args);
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
         $post_id = get_the_ID();
    $post_title = get_the_title();
    $edit_post_link = get_edit_post_link($post_id);
    $post_titles  .= '<a href="' . esc_url($edit_post_link) . '">' . esc_html($post_title) . '</a><br>';
     $post_ids[]   =  $post_id;
    }
}

   if($request_from == "api"){
    
      return $post_ids;

   }

    return $post_titles;
}


add_action('rest_api_init', 'sb_media_api_hooks', 0);

function sb_media_api_hooks() {
    register_rest_route('assignment/v1', '/get_media_data/', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'sb_get_media_data_callback',
        'permission_callback' => function () {
            return true;
        },
            )
    );


}


function sb_get_media_data_callback($request) {

  $json_data = $request->get_json_params();
  $id = (isset($json_data['id'])) ? $json_data['id'] : '';
  
    $attachment = get_post( $id );
    if ( $attachment && $attachment->post_type == 'attachment' ) {
        $attachment_data = array(
            'id' => $attachment->ID,
            'date' => $attachment->post_date,
            'slug' => $attachment->post_name,
            'type' => $attachment->post_mime_type,
            'link' => wp_get_attachment_url( $id ),
            'alt_text' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
            'attached_object' => sb_get_all_attched_post($attachment->ID , 'api'),
        );
        return new WP_REST_Response( $attachment_data, 200 );
    } else {
        return new WP_Error( 'invalid_attachment', 'Invalid attachment ID.', array( 'status' => 404 ) );
    }
  

}