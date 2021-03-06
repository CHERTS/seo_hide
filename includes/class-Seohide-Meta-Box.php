<?php

class Seohide_Metabox
{
    protected $version;
    protected $text_domain;
    protected $post_type;
    protected $meta_box_id;

    function __construct($text_domain,$version, $base_path_url)
    {
        $this->base_path_url= $base_path_url;
        $this->version = $version;
        $this->text_domain = $text_domain;
        $this->post_type = 'post';
        $this->meta_box_id = 'seo_hide_metabox';
        $this->post_prefix = 'seo_hided';
        $this->post_nonce = 'seo_hided_nonce';
        add_action('add_meta_boxes', array($this, 'my_extra_fields'), 1);
        add_action('save_post', array($this, 'update_meta_box'), 0);
        add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'), 10);
    }

    function add_admin_scripts(){
        wp_register_script('seo-hide-admin-meta-box-js', $this->base_path_url . 'admin/js/seo-hide-meta-box.js', array('jquery'), $this->version, true);
        wp_enqueue_script('seo-hide-admin-meta-box-js');

        wp_register_style('seo-hide-admin-meta-box-css', $this->base_path_url . 'admin/css/seo-hide-meta-box.css', array(), $this->version);
        wp_enqueue_style('seo-hide-admin-meta-box-css');
    }

    function my_extra_fields()
    {
        add_meta_box($this->meta_box_id, __('Seo hide settings', $this->text_domain), array($this, 'get_meta_box'), $this->post_type, 'normal', 'high');
    }

    function get_meta_box($post)
    {
        $html_name = $this->post_prefix;
        $type_hide = get_post_meta($post->ID, '_seo_hide-type', 1);
        $type_hide= 'hide_all_links_on_post';
        ?>
        <div class="block__seo-hide-meta-box">
            <div class="block__seo-hide-meta-box-item radio">
                <label>
                    <input type="radio"
                           name="<?php echo $html_name; ?>[_seo_hide-type]" <?php checked($type_hide, 'hide_all_links_on_post'); ?>
                           value="all_links_on_post">
                    <?php _e('Hide all links on post', $this->text_domain); ?>
                </label>

                <label>
                    <input type="radio"
                           name="<?php echo $html_name; ?>[_seo_hide-type]" <?php checked($type_hide, 'show_all_links_on_post'); ?>
                           value="all_links_on_post">
                    <?php _e('Show all links on post', $this->text_domain); ?>
                </label>

                <label>
                    <input type="radio"
                           name="<?php echo $html_name; ?>[_seo_hide-type]" <?php checked($type_hide, 'black_list_pattern'); ?>
                           value="black_list_pattern">
                    <?php _e('Black list pattern', $this->text_domain); ?>
                </label>

                <label>
                    <input type="radio"
                           name="<?php echo $html_name; ?>[_seo_hide-type]" <?php checked($type_hide, 'white_list_pattern'); ?>
                           value="white_list_pattern">
                    <?php _e('White list pattern', $this->text_domain); ?>
                </label>
            </div>
            <div class="block__seo-hide-meta-box-item list">
                <textarea name="<?php echo $html_name; ?>[_seo_hide-type-help-list]"></textarea>
            </div>
        </div>


        <input type="hidden" name="<?php echo $this->post_prefix; ?>_nonce" value="<?php echo $this->post_nonce; ?>"/>
        <?php
    }

    function update_meta_box($post_id)
    {
        if (!wp_verify_nonce($_POST[$this->post_prefix . '_nonce'], $this->post_nonce)) {
            return false;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        if (!isset($_POST['extra'])) {
            return false;
        };

        $_POST[$this->post_prefix] = array_map('trim', $_POST[$this->post_prefix]);
        foreach ($_POST[$this->post_prefix] as $key => $value) {
            if (empty($value)) {
                delete_post_meta($post_id, $key);
                continue;
            }
            update_post_meta($post_id, $key, $value);
        }
        return $post_id;
    }

}