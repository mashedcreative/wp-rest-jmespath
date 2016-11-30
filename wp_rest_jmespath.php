<?php
/**
 * Adds support for [JMESPath](http://jmespath.org) queries to all WP REST API
 * endpoints.  This allows clients to request only the fields that they need,
 * minimising the data transferred and more clearly indicating data
 * dependencies.
 *
 * Note that this bundles the PHP JMESPath library; WP lacks a sane way to
 * bundle third party libraries in plugins so it's possible (but unlikely) that
 * this will subtly conflict with other plugins bundling the same library.
 *
 * @author Liam O'Boyle <liam@elyobo.net>
 */
/*
 * Plugin Name: WP REST JMESPath
 * Description: Add support for JMESPath queries to the WP REST API.
 */

function elyobo_wp_rest_jmespath_apply($query, $data) {
    // Filter the data using the given path
    require_once __DIR__ . '/vendor/autoload.php';
    try {
        $result = JmesPath\search($query, $data);
    } catch (Exception $e) {
        $result = array(
            'code'    => 400,
            'message' => 'Unable to apply JMESPath query.',
            'data'    => ['query'  => $query, 'error' => $e->getMessage()],
        );
    }

    return $result;
}

function elyobo_wp_rest_jmespath_response($response, $server, $request) {
    if ($response->is_error()) {
        // Don't process error responses
        return $response;
    }

    $params = $request->get_query_params();

    if (isset($params['_embed'])) {
        // Run later for embedded queries
        return $response;
    }

    if (!($query = @$params['_query'])) {
        // No requested query, leave response as is
        return $response;
    }

    $response->set_data(elyobo_wp_rest_jmespath_apply($query, $response->get_data()));

    return $response;
}

// Copied from protected WP_REST_Server::get_json_last_error
function elyobo_wp_rest_get_json_last_error() {
    // See https://core.trac.wordpress.org/ticket/27799.
    if (!function_exists('json_last_error')) {
        return false;
    }

    $last_error_code = json_last_error();

    if ((defined('JSON_ERROR_NONE') && JSON_ERROR_NONE === $last_error_code) || empty($last_error_code)) {
        return false;
    }

    return json_last_error_msg();
}

function elyobo_wp_rest_jmespath_serve($served, $response, $request, $server) {
    if ($served) {
        return $served;
    }

    if ($response->is_error()) {
        // Don't process error responses
        return $response;
    }

    $params = $request->get_query_params();

    if (!isset($params['_embed'])) {
        // If not embedded, we've already run
        return $served;
    }

    if (!($query = @$params['_query'])) {
        // No requested query, leave response as is
        return $served;
    }

    if ('HEAD' === $request->get_method()) {
        return $served;
    }

    // Embed links inside the request.
    $result = $server->response_to_data($response, true);
    $result = elyobo_wp_rest_jmespath_apply($query, $result);
    $result = wp_json_encode($result);

    $json_error_message = elyobo_wp_rest_get_json_last_error();
    if ($json_error_message) {
        $result = wp_json_encode(array(
            'status' => 500,
            'message' => $json_error_message,
        ));
    }

    $jsonp_callback = @$params['_jsonp'];
    if (wp_check_jsonp_callback($jsonp_callback) && $jsonp_callback) {
        // Prepend '/**/' to mitigate possible JSONP Flash attacks
        // https://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
        echo '/**/' . $jsonp_callback . '(' . $result . ')';
    } else {
        echo $result;
    }

    return true;
}

add_filter('rest_post_dispatch', 'elyobo_wp_rest_jmespath_response', 20, 3);
add_filter('rest_pre_serve_request', 'elyobo_wp_rest_jmespath_serve', 20, 4);
