<?php
add_action('rest_api_init', 'sb_media_api_hooks', 0);
function sb_media_api_hooks()
{

    /*get media item detail*/
    register_rest_route('assignment/v1', '/get_media_data/', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'sb_get_media_data_callback',
        'permission_callback' => function () {
            return true;
        },
    )
    );

    /*Delete media item*/

    register_rest_route('assignment/v1', '/delete_media/', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'sb_delete_media_callback',
        'permission_callback' => function () {
            return true;
        },
    )
    );

}

function sb_get_media_data_callback($request)
{
    $json_data = $request->get_json_params();
    $id = (isset($json_data['id'])) ? $json_data['id'] : '';

    $attachment = get_post($id);
    if ($attachment && $attachment->post_type == 'attachment') {
        $attachment_data = array(
            'id' => $attachment->ID,
            'date' => $attachment->post_date,
            'slug' => $attachment->post_name,
            'type' => $attachment->post_mime_type,
            'link' => wp_get_attachment_url($id),
            'alt_text' => get_post_meta($id, '_wp_attachment_image_alt', true),
            'attached_object' => sb_get_all_attached_post($attachment->ID, 'api'),
        );
        wp_send_json_success($attachment_data);
    } else {
        wp_send_json_error(array('message' => 'Invalid attachment ID.'));
    }

}

if (!function_exists('sb_delete_media_callback')) {

    function sb_delete_media_callback($request)
    {
        $json_data = $request->get_json_params();
        $attachment_id = isset($json_data['id']) ? intval($json_data['id']) : 0;

        if ( !current_user_can( 'administrator' ) ) {
          wp_send_json_error(array('message' => __('You are not allowed to delete this','sb-media-deletion')));
        }

        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error(array('message' => __('Invalid attachment ID.','sb-media-deletion')));
        }

        $attached_object = sb_get_all_attached_post($attachment_id, 'api');
        if (is_array($attached_object) && !empty($attached_object)) {

            wp_send_json_error(array(
                'message' => __('You cannot delete this media, it is assigned to some objects.', 'sb-media-deletion'),
                'attached_object' => $attached_object,
            ));
        } else {

            if (wp_delete_attachment($attachment_id)) {

                wp_send_json_success(array(
                    'message' => __('Attachment deleted successfully.', 'sb-media-deletion'),
                ));
            } else {

                wp_send_json_error(array(
                    'message' => __('Something went wrong.', 'sb-media-deletion'),
                ));
            }
        }
    }
}