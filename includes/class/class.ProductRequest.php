<?php
/**
 * Request
 *
 * @package WP_wpsync
 */

namespace WP_Wpsync\App;

use QM;
use WC_Product;
use WP_REST_Request;
use WP_Wpsync\Request\Request;

if (!defined('ABSPATH')) {
    exit;
}


class ProductRequest extends Request
{

    /**
     * @var ProductRequest
     */
    public static $instance;
    /**
     * @var string
     */
    private $api_id;
    /**
     * @var string[]
     */
    private $sku;

    /**
     * ProductRequest constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Instance.
     *
     * @return ProductRequest
     */

    public static function instance()
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;

    }

    /**
     * Process the request.
     *
     * @param WP_REST_Request $request
     *
     * @return mixed
     */

    public function request()
    {

        $response = $this->get_products();

        Log::write('product-request', [
            'api_id' => $this->api_id,
            'response' => $response
        ], 'Response');

        return rest_ensure_response($response);

    }

    /**
     * Get products.
     *
     * @return mixed
     */

    public function get_products()
    {

        if ($products_data = $this->get_data()) {

            $this->compare_sku_collections($products_data);

            foreach ($products_data as $product_data) {

                $product = new Product($product_data);
                if ($product->update()) {
                    Log::write('import', $product, 'Success');
                } else {
                    Log::write('import', $product, 'Failed');
                }

            }
        }

        return false;

    }

    /**
     * Compare two SKU collections and delete products that are not in the first collection.
     *
     * @param array $data The first SKU collection.
     */
    public function compare_sku_collections($data)
    {
        $sku = $this->get_sku_collections($data);
        $product_sku = $this->get_product_skus();

        $diff = array_diff($product_sku, $sku);

        QM::info($diff);

        if (!empty($diff) && !empty($product_sku))
            foreach ($diff as $sku_field) {
                if (in_array($sku_field, $product_sku)) {
                    $product = new WC_Product($this->get_product_by_sku($sku_field));
                    $product->delete(true);
                    QM::info($sku_field);
                    QM::info(in_array($sku_field, $sku));
                }
            }
    }

    /**
     * @param $data
     *
     * @return array|string[]
     */

    public function get_sku_collections($data)
    {
        $this->sku = array_map(function ($item) {
            if (!empty($item))
                return $this->sanitize_data('text', $item['sku']);
        }, $data);

        return $this->sku;
    }

    /**
     * @access public
     * @return array
     * @since 1.0.0
     */
    function get_product_skus()
    {
        global $wpdb;

        $query = "
        SELECT meta_value AS sku
        FROM {$wpdb->prefix}postmeta
        WHERE meta_key = '_sku'
        AND post_id IN (
            SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type = 'product'
        )
        ";

        $results = $wpdb->get_results($query);

        if (!empty($results)) {
            return array_map(function ($product) {
                return $product->sku;
            }, $results);
        }

        return array();
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

}