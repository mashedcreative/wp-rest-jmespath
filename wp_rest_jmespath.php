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
function elyobo_wp_rest_jmespath_response ($response, $server, $request) {
    if ($response->is_error()) {
        // Don't process error responses
        return $response;
    }

    if (!($query = @$request->get_query_params()['_query'])) {
        // No requested query, leave response as is
        return $response;
    }

    // Filter the data using the given path
    require_once __DIR__ . '/vendor/autoload.php';
    try {
        $response->set_data(JmesPath\search($query, $response->get_data()));
    } catch (Exception $e) {
        $message = sprintf(
            'Unable to apply JMESPath query.',
            $query,
            $e->getMessage()
        );
        $response->set_data(array(
            'code'    => 400,
            'message' => $message,
            'data'    => ['query'  => $query, 'error' => $e->getMessage()],
        ));
    }

    return $response;
}

add_filter('rest_post_dispatch', 'elyobo_wp_rest_jmespath_response', 20, 3);
