<?php 

function add_attachment_post_field( $fields, $post ) {
    $post_titles = "";
    $attachment_id = $post->ID;   
   
  $post_titles = sb_get_all_attched_post($attachment_id);
 $fields['media_posts'] = array(
                    'show_in_edit'  => true,
                    'tr'            => '
                    <div id="add_attched_object">
                        <label class="setting">
                        <span class="name">Attached post</span>
                        <span class="value" style="width: 63%">
                           '.$post_titles.'
                        </span>
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


function sb_get_all_attched_post($attachment_id){
    
    $post_titles = "";

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
    }
}
    return $post_titles;
}


add_filter('pre_delete_attachment', 'prevent_media_deletion');

function prevent_media_deletion($attachment_id) {
    // Check if the attachment is attached to any post
 
     $attachment_posts  =   sb_get_all_attched_post($attachment_id);
     if($attachment_posts != ""){
          wp_die(__('This media file is attached to a some posts please deatached them first to delete this item.' , 'sb-media-deletion'));

    }
    // Return the original $deleted value if the attachment is not attached to any post
    return true;
}