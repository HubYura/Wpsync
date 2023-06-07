<?php

namespace WP_Wpsync\App;

use QM;
use WC_Data_Exception;
use WC_Product;
use WC_Product_Simple;

if (!defined('ABSPATH')) {
    exit;
}

class Product
{

    /**
     * @var WC_Product_Simple
     */
    private $product;

    /**
     * @var array
     */
    private $product_data;

    /**
     * Product constructor.
     *
     * @param $product_data
     */
    public function __construct($product_data)
    {
        $this->product_data = $product_data;
    }

    /**
     * Update a single product.
     */
    public function update()
    {
        if ($product_id = $this->get_product_by_sku($this->product_data['sku'])) {
            Log::write('save', "[$product_id] " . $this->product_data['sku'], 'SKU already exist');
            QM::notice("[$product_id] " . $this->product_data['sku'] . ' SKU already exist');
            return $this->update_meta($product_id);
        } else {
            QM::notice("create product");
            return $this->create();
        }
    }

    /**
     * Get product by SKU.
     *
     * @param string $sku The SKU.
     *
     * @return string|null
     */
    private function get_product_by_sku($sku)
    {
        global $wpdb;
        $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));
        if ($product_id)
            return $product_id;
        return null;
    }

    /**
     * Update product meta.
     *
     * @return bool|string
     */
    public function update_meta($product_id)
    {
        $product = new WC_Product($product_id);

        if (empty($product))
            return false;

        $compare_data = array(
            'name' => [$product->get_name(), $this->product_data['name']],
            'description' => [$product->get_description(), $this->product_data['description']],
            'price' => [$product->get_price(), $this->product_data['price']],
            'regular_price' => [$product->get_regular_price(), $this->product_data['price']],
            'stock' => [$product->get_description(), $this->product_data['in_stock']],
        );

        foreach ($compare_data as $compare => $data) {
            if (!$this->array_has_duplicate($data))
                $this->update_post_meta($product_id, $compare, end($data));
        }

        return $product_id;
    }

    /**
     * Check if an array has duplicate values.
     *
     * @param array $array The array to check.
     * @return bool True if the array has duplicates, false otherwise.
     */
    function array_has_duplicate($array)
    {
        return count($array) !== count(array_unique($array));
    }

    /**
     * Update post meta.
     *
     * @return bool
     */
    private function update_post_meta($product_id, $compare, $data)
    {
        $compare = trim($compare);

        if (empty($compare)) {
            return false;
        }

        switch ($compare) {

            case 'name':
                $post_update = array(
                    'ID' => $product_id,
                    'post_title' => $data,
                );
                wp_update_post($post_update);
                break;

            case 'description':
                $post_update = array(
                    'ID' => $product_id,
                    'post_content' => $data
                );
                wp_update_post($post_update);
                break;

            case 'price':
                update_post_meta($product_id, '_price', $data);
                break;

            case 'regular_price':
                update_post_meta($product_id, '_regular_price', $data);
                break;

            case 'stock':
                update_post_meta($product_id, '_stock', $data);
                break;
        }

        return true;
    }

    /**
     * Create a new product.
     *
     * @return int The ID of the created product.
     * @throws WC_Data_Exception
     */
    public function create()
    {
        try {
            $image_id = $this->upload_file_by_url($this->product_data['picture']);

            $this->product = new WC_Product_Simple();
            $this->product->set_sku($this->product_data['sku']);
            $this->product->set_name($this->product_data['name']);
            $this->product->set_description($this->product_data['description']);
            $this->product->set_status('publish');
            $this->product->set_catalog_visibility('visible');
            $this->product->set_price($this->product_data['price']);
            $this->product->set_regular_price($this->product_data['price']);
            $this->product->set_manage_stock(true);
            $this->product->set_stock_quantity($this->product_data['in_stock']);
            $this->product->set_image_id($image_id);
            $id = $this->product->save();
            Log::write('save', $id, 'Product saved');

            return $id;
        } catch (Exception $e) {
            Log::write('save', $e, 'Save Error');
        }

    }

    /**
     * Upload image from URL
     * @return int|false
     */
    function upload_file_by_url($image_url)
    {

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            Log::write('upload', $temp_file, 'Upload Error');
            return false;
        }

        $file = array(
            'name' => basename($image_url) . ".jpeg",
            'type' => mime_content_type($temp_file),
            'tmp_name' => $temp_file,
            'size' => filesize($temp_file),
        );
        $sideloading = wp_handle_sideload(
            $file,
            array(
                'test_form' => false
            )
        );

        if (!empty($sideloading['error'])) {
            Log::write('upload', $sideloading['error'], 'Sideload Error');
            return false;
        }

        $attachment_id = wp_insert_attachment(
            array(
                'guid' => $sideloading['url'],
                'post_mime_type' => $sideloading['type'],
                'post_title' => basename($sideloading['file']),
                'post_content' => '',
                'post_status' => 'inherit',
            ),
            $sideloading['file']
        );

        if (is_wp_error($attachment_id) || !$attachment_id) {
            Log::write('upload', $attachment_id, 'Attachment id Error');
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $sideloading['file'])
        );

        return $attachment_id;

    }

}