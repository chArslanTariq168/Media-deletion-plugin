<?php
define('DEFAULT_IMAGE_PATH', SB_DIR_URL . "img/placeholder.png");
  if (!in_array('sb_media_deletion-rest-api/index.php ', apply_filters('active_plugins', get_option('active_plugins')))) {
  add_action('admin_init', 'sb_media_deletion_image_actions');
  
  }
function sb_media_deletion_image_actions() {
    global $adforespro_theme;
    $sb_media_deletion_taxonomies = get_taxonomies();
    if (is_array($sb_media_deletion_taxonomies)) {
        foreach ($sb_media_deletion_taxonomies as $sb_media_deletion_taxonomy) {
            if ($sb_media_deletion_taxonomy == "category") {
                add_action($sb_media_deletion_taxonomy . '_add_form_fields', 'sb_media_deletion_add_texonomy_field');
                add_action($sb_media_deletion_taxonomy . '_edit_form_fields', 'sb_media_deletion_edit_texonomy_field');
                add_filter('manage_edit-' . $sb_media_deletion_taxonomy . '_columns', 'sb_media_deletion_taxonomy_img_columns');
                add_filter('manage_' . $sb_media_deletion_taxonomy . '_custom_column', 'sb_media_deletion_taxonomy_img_column', 10, 3);
            }
        }
    }
}

if (!function_exists('sb_media_deletion_add_style')) {

    function sb_media_deletion_add_style() {
        echo '<style type="text/css" media="screen">th.column-thumb {width:60px;}.form-field img.taxonomy-image {border:1px solid #eee;max-width:300px;max-height:300px;}.inline-edit-row fieldset .thumb label span.title {width:48px;height:48px;border:1px solid #eee;display:inline-block;}.column-thumb span {width:48px;height:48px;border:1px solid #eee;display:inline-block;}.inline-edit-row fieldset .thumb img,.column-thumb img {width:48px;height:48px;}</style>';
    }

}
// add image field in add form
if (!function_exists('sb_media_deletion_add_texonomy_field')) {
    function sb_media_deletion_add_texonomy_field() {
        wp_enqueue_media();
        echo '<div class="form-field">
		<label for="taxonomy_image">' . __('Imagefor theme', 'sb_media_deletion') . '</label>
		<input type="text" name="taxonomy_image" id="taxonomy_image" value="" />
		<br/>
		<button class="sb_media_deletion_upload_image_button button">' . __('Upload/Add image', 'sb_media_deletion') . '</button>
	</div>' . sb_media_deletion_termMedia_script();
    }
}
if (!function_exists('sb_media_deletion_edit_texonomy_field')) {
    function sb_media_deletion_edit_texonomy_field($taxonomy) {
        wp_enqueue_media();
        $image_url = sb_media_deletion_taxonomy_image_url($taxonomy->term_id, NULL, TRUE);
        $image_url = (sb_media_deletion_taxonomy_image_url($taxonomy->term_id, NULL, TRUE) == DEFAULT_IMAGE_PATH) ? "" : $image_url;

        echo '<tr class="form-field"><th scope="row" valign="top"><label for="taxonomy_image">' . __('Image', 'sb_media_deletion') . '</label></th><td><img class="taxonomy-image theme-cat-image" src="' . sb_media_deletion_taxonomy_image_url($taxonomy->term_id, 'medium', TRUE) . '"/><br/><input type="text" name="taxonomy_image" id="taxonomy_image" value="' . $image_url . '" /><br /><button class="sb_media_deletion_remove_image_button button">' . __('Remove image', 'sb_media_deletion') . '</button><button class="sb_media_deletion_upload_image_button button">' . __('Upload/Add image', 'sb_media_deletion') . '</button></td></tr>' . sb_media_deletion_termMedia_script();
    }
}
add_action('edit_term', 'sb_media_deletion_save_taxonomy_image');
add_action('create_term', 'sb_media_deletion_save_taxonomy_image');
if (!function_exists('sb_media_deletion_save_taxonomy_image')) {
    function sb_media_deletion_save_taxonomy_image($term_id) {
        if (isset($_POST['taxonomy_image']))
            update_option('sb_media_deletion_taxonomy_image' . $term_id, $_POST['taxonomy_image'], NULL);
    }
}
if (!function_exists('sb_media_deletion_get_attachment_id_by_url')) {

    function sb_media_deletion_get_attachment_id_by_url($image_src) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $image_src);
        $id = $wpdb->get_var($query);
        return (!empty($id)) ? $id : NULL;
    }

}
if (!function_exists('sb_media_deletion_taxonomy_image_url')) {

    function sb_media_deletion_taxonomy_image_url($term_id = NULL, $size = 'full', $return_placeholder = false) {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_url = get_option('sb_media_deletion_taxonomy_image' . $term_id);
        if (!empty($taxonomy_image_url)) {
            $attachment_id = sb_media_deletion_get_attachment_id_by_url($taxonomy_image_url);
            if (!empty($attachment_id)) {
                $taxonomy_image_url = wp_get_attachment_image_src($attachment_id, $size);
                $taxonomy_image_url = $taxonomy_image_url[0];
            }
        }

        if ($return_placeholder == true) {
            global $adforespro_theme;

            $default_url = DEFAULT_IMAGE_PATH;
            $termData_taxonomy = '';
            $termData = get_term_by('id', $term_id, 'ad_cats');
            if ($termData) {
                $termData_taxonomy = $termData->taxonomy;
            } else {
                $termData = get_term_by('id', $term_id, 'ad_country');
                if ($termData) {
                    $termData_taxonomy = $termData->taxonomy;
                }
            }
            if ($termData_taxonomy == "ad_cats") {
                if (isset($adforespro_theme['sb_media_deletion-api-ad-cats-default-icon']['url']) && $adforespro_theme['sb_media_deletion-api-ad-cats-default-icon']['url'] != "") {
                    $icon_url = $adforespro_theme['sb_media_deletion-api-ad-cats-default-icon']['url'];
                    $default_url = $icon_url;
                }
            }
            if ($termData_taxonomy == "ad_country") {
                if (isset($adforespro_theme['sb_media_deletion-api-ad-location-default-icon']['url']) && $adforespro_theme['sb_media_deletion-api-ad-location-default-icon']['url'] != "") {
                    $icon_url = $sb_media_deletion['sb_media_deletion-api-ad-location-default-icon']['url'];
                    $default_url = $icon_url;
                }
            }


            return ($taxonomy_image_url != '') ? $taxonomy_image_url : $default_url;
        } else {
            return $taxonomy_image_url;
        }
    }

}
if (!function_exists('sb_media_deletion_quickEditCustomBox')) {

    function sb_media_deletion_quickEditCustomBox($column_name, $screen, $name) {
        if ($column_name == 'thumb')
            echo '<fieldset>
		<div class="thumb inline-edit-col">
			<label>
				<span class="title"><img src="" alt="Thumbnail"/></span>
				<span class="input-text-wrap"><input type="text" name="taxonomy_image" value="" class="tax_list" /></span>
				<span class="input-text-wrap">
					<button class="sb_media_deletion_upload_image_button button">' . __('Upload/Add image', 'sb_media_deletion') . '</button>
					<button class="sb_media_deletion_remove_image_button button">' . __('Remove image', 'sb_media_deletion') . '</button>
				</span>
			</label>
		</div>
	</fieldset>';
    }

}
if (!function_exists('sb_media_deletion_taxonomy_img_columns')) {

    function sb_media_deletion_taxonomy_img_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['thumb'] = __('Image', 'sb_media_deletion');
        unset($columns['cb']);
        return array_merge($new_columns, $columns);
    }

}
if (!function_exists('sb_media_deletion_taxonomy_img_column')) {

    function sb_media_deletion_taxonomy_img_column($columns, $column, $id) {
        if ($column == 'thumb')
            $columns = '<span><img src="' . esc_url(sb_media_deletion_taxonomy_image_url($id, 'thumbnail', TRUE)) . '" alt="' . esc_attr__('Thumbnail', 'sb_media_deletion') . '" class="wp-post-image" /></span>';

        return $columns;
    }

}
if (!function_exists('sb_media_deletion_replaceBtnText')) {

    function sb_media_deletion_replaceBtnText($safe_text, $text) {
        return str_replace("Insert into Post", "Use this image", $text);
    }

}
if (strpos($_SERVER['SCRIPT_NAME'], 'edit-tags.php') > 0) {
    add_action('admin_head', 'sb_media_deletion_add_style');
    add_action('quick_edit_custom_box', 'sb_media_deletion_quickEditCustomBox', 10, 3);
    add_filter("attribute_escape", "sb_media_deletion_replaceBtnText", 10, 2);
}
if (!function_exists('sb_media_deletion_taxonomy_image')) {

    function sb_media_deletion_taxonomy_image($term_id = NULL, $size = 'full', $attr = NULL, $echo = true) {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tag())
                $term_id = get_query_var('tag_id');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_url = get_option('sb_media_deletion_taxonomy_image' . $term_id);
        if (!empty($taxonomy_image_url)) {
            $attachment_id = sb_media_deletion_get_attachment_id_by_url($taxonomy_image_url);
            if (!empty($attachment_id)) {
                $taxonomy_image = wp_get_attachment_image($attachment_id, $size, FALSE, $attr);
            } else {
                $image_attr = '';
                if (is_array($attr)) {
                    $image_attr .= (!empty($attr['class'])) ? ' class="' . $attr['class'] . '" ' : '';
                    $image_attr .= (!empty($attr['height'])) ? ' height="' . $attr['height'] . '" ' : '';
                    $image_attr .= (!empty($attr['width'])) ? ' width="' . $attr['width'] . '" ' : '';
                    $image_attr .= (!empty($attr['title'])) ? ' title="' . $attr['title'] . '" ' : '';
                    $image_attr .= (!empty($attr['alt'])) ? ' alt="' . $attr['alt'] . '" ' : '';
                }
                $taxonomy_image = '<img src="' . esc_url($taxonomy_image_url) . '" ' . $image_attr . '/>';
            }
        }

        if ($echo) {
            echo sb_media_deletion_returnEcho($taxonomy_image) ;
        } else {
            return $taxonomy_image;
        }
    }

}

if (!function_exists('sb_media_deletion_termMedia_script')) {

    function sb_media_deletion_termMedia_script() {
        return '<script type="text/javascript">
	    jQuery(document).ready(function($) {
			var wordpress_ver = "' . get_bloginfo("version") . '", upload_button;
			$(".sb_media_deletion_upload_image_button").click(function(event) {
				upload_button = $(this);
				var frame;
				if (wordpress_ver >= "3.5") {
					event.preventDefault();
					if (frame) {
						frame.open();
						return;
					}
					frame = wp.media();
					frame.on( "select", function() {
						var attachment = frame.state().get("selection").first();
						frame.close();
						if (upload_button.parent().prev().children().hasClass("tax_list")) {
							upload_button.parent().prev().children().val(attachment.attributes.url);
							upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);
						}
						else
							$("#taxonomy_image").val(attachment.attributes.url);
					});
					frame.open();
				}
				else {
					tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
					return false;
				}
			});
			
			$(".sb_media_deletion_remove_image_button").click(function() {
				$(".taxonomy-image").attr("src", "' . DEFAULT_IMAGE_PATH . '");
				$("#taxonomy_image").val("");
				$(this).parent().siblings(".title").children("img").attr("src","' . DEFAULT_IMAGE_PATH . '");
				$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
				return false;
			});
			
			if (wordpress_ver < "3.5") {
				window.send_to_editor = function(html) {
					imgurl = $("img",html).attr("src");
					if (upload_button.parent().prev().children().hasClass("tax_list")) {
						upload_button.parent().prev().children().val(imgurl);
						upload_button.parent().prev().prev().children().attr("src", imgurl);
					}
					else
						$("#taxonomy_image").val(imgurl);
					tb_remove();
				}
			}
			
			$(".editinline").click(function() {	
			    var tax_id = $(this).parents("tr").attr("id").substr(4);
			    var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");

				if (thumb != "' . DEFAULT_IMAGE_PATH . '") {
					$(".inline-edit-col :input[name=\'taxonomy_image\']").val(thumb);
				} else {
					$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
				}
				
				$(".inline-edit-col .title img").attr("src",thumb);
			});
	    });
	</script>';
    }

}