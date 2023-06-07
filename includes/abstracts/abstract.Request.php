<?php

namespace WP_Wpsync\Request;

use QM;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_Wpsync\App\Log;

if (!defined('ABSPATH')) {
    exit;
}

abstract class Request
{

    /**
     * @var WP_REST_Request
     */
    private $request;

    /**
     * @var WP_HTTP_Response
     */
    private $response;

    /**
     * Set Request
     *
     * @param WP_REST_Request $request
     */

    public function set_request(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the product data.
     *
     * @return mixed Response data.
     */
    public function get_data()
    {
        return $this->check_response();
    }

    /**
     * Check the product data.
     *
     * @return mixed.
     */
    public function check_response()
    {
        $this->response = $this->get_response();
        $data = $this->response->get_data();

        if (!isset($this->response)) {
            Log::write('response', $this->response, 'Error Curl');
            return false;
        }

        if ($data['error'] == 1 || empty($data) || empty($data['data'])) {
            QM::error($data['message']);
            Log::write('response', $data['message'], 'Error API');
            return false;
        }

        Log::write('response', $data['message'], 'API Success');
        QM::info($data['message']);
        return $data['data'];
    }

    /**
     * Get data from API.
     *
     * @return WP_HTTP_Response|false $response
     */
    public function get_response()
    {

        $url = get_option('wpsync_api_url');

        if (empty($url))
            return false;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return new WP_HTTP_Response($response);
    }

    /**
     * Sanitize key.
     *
     * @param $key
     *
     * @return bool|float|int|string
     */

    public function sanitize_key($key)
    {

        if (is_string($key)) {
            return $this->sanitize_data('string', $key);
        }

        if (is_int($key)) {
            return intval($key);
        }

        die(__('A valid array is required!!', 'wpsync-webspark'));

    }

    /**
     * Sanitize data.
     *
     * @param $sanitize_callback
     * @param $value
     *
     * @return bool|float|int|string
     */

    public function sanitize_data($sanitize_callback, $value)
    {

        $value = trim($value);

        if (empty($value)) {
            return '';
        }

        switch ($sanitize_callback) {

            case 'bool':
                $clean_value = boolval($value);
                break;

            case 'float':
                $clean_value = floatval($value);
                break;

            case 'int':
                $clean_value = intval($value);
                break;

            case 'numeric':
                $clean_value = sanitize_text_field($value);
                break;

            case 'email':
                $clean_value = sanitize_email($value);
                break;

            case 'key':
                $clean_value = sanitize_key($value);
                break;

            case 'html':
                // If we have some html from an editor, let's use allowed post html.
                // All scripts, videos, etc... will be removed.
                $clean_value = wp_kses_post($value);
                break;

            case 'url':
                $clean_value = sanitize_url($value);
                break;

            case 'title':
                $clean_value = sanitize_title($value);
                break;

            case 'filename':
                $clean_value = sanitize_file_name($value);
                break;

            default:
                $clean_value = sanitize_text_field($value);

        }

        $encoding = mb_detect_encoding($clean_value, 'auto');

        if ('ASCII' !== $encoding) {

            Log::write('encoding', [
                'encoding' => $encoding,
                'clean_value' => $clean_value
            ]);

        }

        return apply_filters('wp_wpsync_clean_value', $clean_value, $sanitize_callback);

    }

    /**
     * Sanitize callback.
     *
     * @param $key
     *
     * @return mixed|void
     */

    public function sanitize_callback($key)
    {

        switch ($key) {

            case 'post_content' :
            case 'post_excerpt'    :
                $sanitize_callback = 'html';
                break;

            case 'image_url' :
                $sanitize_callback = 'url';
                break;

            default :
                $sanitize_callback = 'string';

        }

        Log::write('sanitize-callback', "$key - $sanitize_callback");

        return apply_filters('wp_wpsync_sanitize_callback', $sanitize_callback, $key);

    }
}