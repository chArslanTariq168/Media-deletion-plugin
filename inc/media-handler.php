<?php

function add_attachment_post_field($fields, $post)
{
    $post_titles = "";
    $attachment_id = $post->ID;
    $post_titles = sb_get_all_attached_post($attachment_id);
    $warning_message = "";
    if ($post_titles != "") {
        $warning_message = '<span style="width: 63%">you can not delete this media until you deatched it from above posts</span>';
    }

    $fields['media_posts'] = array(
        'show_in_edit' => true,
        'tr' => '
                    <div id="add_attched_object">
                        <label class="setting">
                        <span class="name">Attached object</span>
                        <span class="value" style="width: 63%">
                           ' . $post_titles . '
                        </span>
                        ' . $warning_message . '
                        </label>
                        <div class="clear"></div>
                    </div>',
    );
    return $fields;
}
add_filter('attachment_fields_to_edit', 'add_attachment_post_field', 10, 2);
function my_media_columns($columns)
{
    $columns['attached_post'] = __('Attached object', 'my-textdomain');
    return $columns;
}
add_filter('manage_media_columns', 'my_media_columns');
// Display custom column content
function my_media_column_content($column_name, $attachment_id)
{
    if ($column_name == 'attached_post') {

        echo $post_titles = sb_get_all_attached_post($attachment_id);
    }
}
add_filter('manage_media_custom_column', 'my_media_column_content', 10, 2);
function sb_get_all_attached_post($attachment_id, $request_from = "")
{
    $post_titles = "";
    $post_ids = [];
    $args = array(
        'post_type' => 'any',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => '_thumbnail_id',
                'value' => $attachment_id,
                'compare' => '=',
            ),

        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_title = get_the_title();
            $edit_post_link = get_edit_post_link($post_id);
            $post_titles .= '<a href="' . esc_url($edit_post_link) . '">' . esc_html($post_title) . '</a><br>';
            $post_ids[] = $post_id;
        }
    }

    wp_reset_postdata();
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
            $post_titles .= '<a href="' . esc_url($edit_post_link) . '">' . esc_html($post_title) . '</a><br>';
            $post_ids[] = $post_id;
        }
    }

   /*check term in which this media exist*/
   $args = array(
    'taxonomy' => 'category', // specify the taxonomy to search in
    'meta_query' => array(
        array(
            'key' => 'sb_media_deletion_taxonomy_image', // your specific term meta key
            'value' => wp_get_attachment_url($attachment_id), // the meta value you want to search for
            'compare' => '=' // the comparison operator, in this case we want to find exact matches for the meta value
        )
    )
);

$terms = get_terms( $args );

if(!empty($terms)){
foreach ( $terms as $term ) {
  $edit_url = get_edit_term_link( $term->term_id, $term->taxonomy ); // get the edit link for the term
  $post_titles .= '<a href="' . $edit_url . '">' . $term->name . '</a><br>'; // create the anchor link and append it to $pos_titles
  $post_ids[] = $term->term_id;
}
}

    if ($request_from == "api") {
        return $post_ids;
    }
    return $post_titles;
}

add_filter('delete_attachment', 'prevent_media_deletion',10,1);

function prevent_media_deletion($attachment_id) {


     $attachment_posts  =   sb_get_all_attached_post($attachment_id);

     if($attachment_posts != ""){
          wp_die(__('This media file is attached to a some posts please deatached them first to delete this item.' , 'sb-media-deletion'));

    }
    // Return the original $deleted value if the attachment is not attached to any post
    return true;
}