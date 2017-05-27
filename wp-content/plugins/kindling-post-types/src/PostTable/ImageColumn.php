<?php
/**
 * Post Table Image Column.
 *
 * @package Kindling
 * @author  Matchbox Design Group <info@matchboxdesigngroup.com>
 */

namespace Kindling\PostTypes\PostTable;

/**
 * Adds the image column to the posts table.
 */
trait ImageColumn
{
    /**
     * Column filter for featured image.
     *
     * @param string  $post_type Post type id.
     * @param boolean $disable   Optional, if the image column should be disabled. Default false.
     */
    public function addImageColumnAction($post_type, $disable = false)
    {
        if ($disable) {
            return;
        } // if()

        $current_post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : '';
        if ($current_post_type !== $post_type) {
            return;
        }

        switch ($post_type) {
            case 'post':
                $manage_filter = 'manage_posts_columns';
                $custom_column = 'manage_posts_custom_column';
                break;
            case 'page':
                $manage_filter = 'manage_pages_columns';
                $custom_column = 'manage_pages_custom_column';
                break;
            default:
                $manage_filter = "manage_{$post_type}_posts_columns";
                $custom_column = "manage_{$post_type}_posts_custom_column";
                break;
        } // switch()

        add_filter($manage_filter, [ &$this, 'addThumbnailColumn' ], 5);
        add_action($custom_column, [ &$this, 'displayThumbnailColumn' ], 5, 2);
    }

    /**
     * Adds the thumbnail image column.
     *
     * @param array $cols Current post table columns.
     *
     * @return array $cols The current columns with thumbnail column added.
     */
    public function addThumbnailColumn($cols)
    {
        $post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : '';

        // Make sure the post supports thumbnails.
        if (! post_type_supports($post_type, 'thumbnail')) {
            return $cols;
        } // if()

        // Get the post type object.
        $type_obj = get_post_type_object($post_type);
        if (is_null($type_obj)) {
            return $cols;
        } // if()

        // Set the column.
        $label  = (isset($type_obj->labels->featured_image)) ? $type_obj->labels->featured_image : 'Featured Image';
        $cols['mdg_post_thumb'] = __($label);

        return $cols;
    } // addThumbnailColumn()

    /**
     * Grab featured-thumbnail size post thumbnail and display it.
     *
     * @param array   $col  Current post table columns.
     * @param integer $id   The current post ID..
     */
    public function displayThumbnailColumn($col, $id)
    {
        global $mdg_thumbnail_column_image_ids;

        // Check if we should display this image.
        $post_type         = get_post_type($id);
        $column_image_ids  = (isset($mdg_thumbnail_column_image_ids)) ? $mdg_thumbnail_column_image_ids : array();
        $already_displayed = in_array($id, $column_image_ids);
        $correct_column    = ('mdg_post_thumb' === $col);

        if ($correct_column and ! $already_displayed) {
            echo get_the_post_thumbnail($id, 'admin-list-thumb');
            $column_image_ids[] = $id;
        } // if()

        $mdg_thumbnail_column_image_ids = $column_image_ids;
    }
}
