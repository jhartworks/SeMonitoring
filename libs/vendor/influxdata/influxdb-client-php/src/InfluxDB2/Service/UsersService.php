<?php
/**
 * UsersService
 * PHP version 5
 *
 * @category Class
 * @package  InfluxDB2
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * InfluxDB OSS API Service
 *
 * The InfluxDB v2 API provides a programmatic interface for all interactions with InfluxDB. Access the InfluxDB API using the `/api/v2/` endpoint.
 *
 * OpenAPI spec version: 2.0.0
 * 
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 3.3.4
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace InfluxDB2\Service;

use InfluxDB2\DefaultApi;
use InfluxDB2\HeaderSelector;
use InfluxDB2\ObjectSerializer;

/**
 * UsersService Class Doc Comment
 *
 * @category Class
 * @package  InfluxDB2
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class UsersService
{
    /**
     * @var DefaultApi
     */
    protected $defaultApi;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @param DefaultApi $defaultApi
     * @param HeaderSelector  $selector
     */
    public function __construct(DefaultApi $defaultApi)
    {
        $this->defaultApi = $defaultApi;
        $this->headerSelector = new HeaderSelector();
    }


    /**
     * Operation deleteUsersID
     *
     * Delete a user
     *
     * @param  string $user_id The ID of the user to delete. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deleteUsersID($user_id, $zap_trace_span = null)
    {
        $this->deleteUsersIDWithHttpInfo($user_id, $zap_trace_span);
    }

    /**
     * Operation deleteUsersIDWithHttpInfo
     *
     * Delete a user
     *
     * @param  string $user_id The ID of the user to delete. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deleteUsersIDWithHttpInfo($user_id, $zap_trace_span = null)
    {
        $request = $this->deleteUsersIDRequest($user_id, $zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        return [null, $response->getStatusCode(), $response->getHeaders()];
    }

    /**
     * Create request for operation 'deleteUsersID'
     *
     * @param  string $user_id The ID of the user to delete. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function deleteUsersIDRequest($user_id, $zap_trace_span = null)
    {
        // verify the required parameter 'user_id' is set
        if ($user_id === null || (is_array($user_id) && count($user_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $user_id when calling deleteUsersID'
            );
        }

        $resourcePath = '/api/v2/users/{userID}';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }

        // path params
        if ($user_id !== null) {
            $resourcePath = str_replace(
                '{' . 'userID' . '}',
                ObjectSerializer::toPathValue($user_id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('DELETE', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation getFlags
     *
     * Return the feature flags for the currently authenticated user
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return map[string,object]|\InfluxDB2\Model\Error
     */
    public function getFlags($zap_trace_span = null)
    {
        list($response) = $this->getFlagsWithHttpInfo($zap_trace_span);
        return $response;
    }

    /**
     * Operation getFlagsWithHttpInfo
     *
     * Return the feature flags for the currently authenticated user
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of map[string,object]|\InfluxDB2\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getFlagsWithHttpInfo($zap_trace_span = null)
    {
        $request = $this->getFlagsRequest($zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = 'map[string,object]';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'getFlags'
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getFlagsRequest($zap_trace_span = null)
    {

        $resourcePath = '/api/v2/flags';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('GET', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation getMe
     *
     * Retrieve the currently authenticated user
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \InfluxDB2\Model\User|object|\InfluxDB2\Model\Error|string
     */
    public function getMe($zap_trace_span = null)
    {
        list($response) = $this->getMeWithHttpInfo($zap_trace_span);
        return $response;
    }

    /**
     * Operation getMeWithHttpInfo
     *
     * Retrieve the currently authenticated user
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \InfluxDB2\Model\User|object|\InfluxDB2\Model\Error|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function getMeWithHttpInfo($zap_trace_span = null)
    {
        $request = $this->getMeRequest($zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = '\InfluxDB2\Model\User';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'getMe'
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getMeRequest($zap_trace_span = null)
    {

        $resourcePath = '/api/v2/me';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('GET', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation getUsers
     *
     * List users
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  int $offset The offset for pagination. The number of records to skip. (optional)
     * @param  int $limit Limits the number of records returned. Default is &#x60;20&#x60;. (optional, default to 20)
     * @param  string $after Resource ID to seek from. Results are not inclusive of this ID. Use &#x60;after&#x60; instead of &#x60;offset&#x60;. (optional)
     * @param  string $name name (optional)
     * @param  string $id id (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \InfluxDB2\Model\Users|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|string
     */
    public function getUsers($zap_trace_span = null, $offset = null, $limit = 20, $after = null, $name = null, $id = null)
    {
        list($response) = $this->getUsersWithHttpInfo($zap_trace_span, $offset, $limit, $after, $name, $id);
        return $response;
    }

    /**
     * Operation getUsersWithHttpInfo
     *
     * List users
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  int $offset The offset for pagination. The number of records to skip. (optional)
     * @param  int $limit Limits the number of records returned. Default is &#x60;20&#x60;. (optional, default to 20)
     * @param  string $after Resource ID to seek from. Results are not inclusive of this ID. Use &#x60;after&#x60; instead of &#x60;offset&#x60;. (optional)
     * @param  string $name (optional)
     * @param  string $id (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \InfluxDB2\Model\Users|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function getUsersWithHttpInfo($zap_trace_span = null, $offset = null, $limit = 20, $after = null, $name = null, $id = null)
    {
        $request = $this->getUsersRequest($zap_trace_span, $offset, $limit, $after, $name, $id);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = '\InfluxDB2\Model\Users';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'getUsers'
     *
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  int $offset The offset for pagination. The number of records to skip. (optional)
     * @param  int $limit Limits the number of records returned. Default is &#x60;20&#x60;. (optional, default to 20)
     * @param  string $after Resource ID to seek from. Results are not inclusive of this ID. Use &#x60;after&#x60; instead of &#x60;offset&#x60;. (optional)
     * @param  string $name (optional)
     * @param  string $id (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getUsersRequest($zap_trace_span = null, $offset = null, $limit = 20, $after = null, $name = null, $id = null)
    {
        if ($offset !== null && $offset < 0) {
            throw new \InvalidArgumentException('invalid value for "$offset" when calling UsersService.getUsers, must be bigger than or equal to 0.');
        }

        if ($limit !== null && $limit > 100) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling UsersService.getUsers, must be smaller than or equal to 100.');
        }
        if ($limit !== null && $limit < 1) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling UsersService.getUsers, must be bigger than or equal to 1.');
        }


        $resourcePath = '/api/v2/users';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($offset !== null) {
            $queryParams['offset'] = ObjectSerializer::toQueryValue($offset);
        }
        // query params
        if ($limit !== null) {
            $queryParams['limit'] = ObjectSerializer::toQueryValue($limit);
        }
        // query params
        if ($after !== null) {
            $queryParams['after'] = ObjectSerializer::toQueryValue($after);
        }
        // query params
        if ($name !== null) {
            $queryParams['name'] = ObjectSerializer::toQueryValue($name);
        }
        // query params
        if ($id !== null) {
            $queryParams['id'] = ObjectSerializer::toQueryValue($id);
        }
        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('GET', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation getUsersID
     *
     * Retrieve a user
     *
     * @param  string $user_id The user ID. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \InfluxDB2\Model\User|string
     */
    public function getUsersID($user_id, $zap_trace_span = null)
    {
        list($response) = $this->getUsersIDWithHttpInfo($user_id, $zap_trace_span);
        return $response;
    }

    /**
     * Operation getUsersIDWithHttpInfo
     *
     * Retrieve a user
     *
     * @param  string $user_id The user ID. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \InfluxDB2\Model\User|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function getUsersIDWithHttpInfo($user_id, $zap_trace_span = null)
    {
        $request = $this->getUsersIDRequest($user_id, $zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = '\InfluxDB2\Model\User';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'getUsersID'
     *
     * @param  string $user_id The user ID. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getUsersIDRequest($user_id, $zap_trace_span = null)
    {
        // verify the required parameter 'user_id' is set
        if ($user_id === null || (is_array($user_id) && count($user_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $user_id when calling getUsersID'
            );
        }

        $resourcePath = '/api/v2/users/{userID}';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }

        // path params
        if ($user_id !== null) {
            $resourcePath = str_replace(
                '{' . 'userID' . '}',
                ObjectSerializer::toPathValue($user_id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('GET', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation patchUsersID
     *
     * Update a user
     *
     * @param  string $user_id The ID of the user to update. (required)
     * @param  \InfluxDB2\Model\PostUser $post_user User update to apply (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \InfluxDB2\Model\User|string
     */
    public function patchUsersID($user_id, $post_user, $zap_trace_span = null)
    {
        list($response) = $this->patchUsersIDWithHttpInfo($user_id, $post_user, $zap_trace_span);
        return $response;
    }

    /**
     * Operation patchUsersIDWithHttpInfo
     *
     * Update a user
     *
     * @param  string $user_id The ID of the user to update. (required)
     * @param  \InfluxDB2\Model\PostUser $post_user User update to apply (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \InfluxDB2\Model\User|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchUsersIDWithHttpInfo($user_id, $post_user, $zap_trace_span = null)
    {
        $request = $this->patchUsersIDRequest($user_id, $post_user, $zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = '\InfluxDB2\Model\User';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'patchUsersID'
     *
     * @param  string $user_id The ID of the user to update. (required)
     * @param  \InfluxDB2\Model\PostUser $post_user User update to apply (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function patchUsersIDRequest($user_id, $post_user, $zap_trace_span = null)
    {
        // verify the required parameter 'user_id' is set
        if ($user_id === null || (is_array($user_id) && count($user_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $user_id when calling patchUsersID'
            );
        }
        // verify the required parameter 'post_user' is set
        if ($post_user === null || (is_array($post_user) && count($post_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_user when calling patchUsersID'
            );
        }

        $resourcePath = '/api/v2/users/{userID}';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }

        // path params
        if ($user_id !== null) {
            $resourcePath = str_replace(
                '{' . 'userID' . '}',
                ObjectSerializer::toPathValue($user_id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;
        if (isset($post_user)) {
            $_tempBody = $post_user;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('PATCH', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation postUsers
     *
     * Create a user
     *
     * @param  \InfluxDB2\Model\PostUser $post_user The user to create. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \InfluxDB2\Model\User|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|string
     */
    public function postUsers($post_user, $zap_trace_span = null)
    {
        list($response) = $this->postUsersWithHttpInfo($post_user, $zap_trace_span);
        return $response;
    }

    /**
     * Operation postUsersWithHttpInfo
     *
     * Create a user
     *
     * @param  \InfluxDB2\Model\PostUser $post_user The user to create. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \InfluxDB2\Model\User|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|\InfluxDB2\Model\Error|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function postUsersWithHttpInfo($post_user, $zap_trace_span = null)
    {
        $request = $this->postUsersRequest($post_user, $zap_trace_span);

        $response = $this->defaultApi->sendRequest($request);

        $returnType = '\InfluxDB2\Model\User';
        $responseBody = $response->getBody();
        if ($returnType === '\SplFileObject') {
            $content = $responseBody; //stream goes to serializer
        } else {
            $content = $responseBody->getContents();
        }

        return [
            ObjectSerializer::deserialize($content, $returnType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    /**
     * Create request for operation 'postUsers'
     *
     * @param  \InfluxDB2\Model\PostUser $post_user The user to create. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function postUsersRequest($post_user, $zap_trace_span = null)
    {
        // verify the required parameter 'post_user' is set
        if ($post_user === null || (is_array($post_user) && count($post_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_user when calling postUsers'
            );
        }

        $resourcePath = '/api/v2/users';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }


        // body params
        $_tempBody = null;
        if (isset($post_user)) {
            $_tempBody = $post_user;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('POST', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation postUsersIDPassword
     *
     * Update a password
     *
     * @param  string $user_id The user ID. (required)
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body New password (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return void
     */
    public function postUsersIDPassword($user_id, $password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        $this->postUsersIDPasswordWithHttpInfo($user_id, $password_reset_body, $zap_trace_span, $authorization);
    }

    /**
     * Operation postUsersIDPasswordWithHttpInfo
     *
     * Update a password
     *
     * @param  string $user_id The user ID. (required)
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body New password (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function postUsersIDPasswordWithHttpInfo($user_id, $password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        $request = $this->postUsersIDPasswordRequest($user_id, $password_reset_body, $zap_trace_span, $authorization);

        $response = $this->defaultApi->sendRequest($request);

        return [null, $response->getStatusCode(), $response->getHeaders()];
    }

    /**
     * Create request for operation 'postUsersIDPassword'
     *
     * @param  string $user_id The user ID. (required)
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body New password (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function postUsersIDPasswordRequest($user_id, $password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        // verify the required parameter 'user_id' is set
        if ($user_id === null || (is_array($user_id) && count($user_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $user_id when calling postUsersIDPassword'
            );
        }
        // verify the required parameter 'password_reset_body' is set
        if ($password_reset_body === null || (is_array($password_reset_body) && count($password_reset_body) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $password_reset_body when calling postUsersIDPassword'
            );
        }

        $resourcePath = '/api/v2/users/{userID}/password';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = ObjectSerializer::toHeaderValue($authorization);
        }

        // path params
        if ($user_id !== null) {
            $resourcePath = str_replace(
                '{' . 'userID' . '}',
                ObjectSerializer::toPathValue($user_id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;
        if (isset($password_reset_body)) {
            $_tempBody = $password_reset_body;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        // this endpoint requires HTTP basic authentication
        if ($this->config->getUsername() !== null || $this->config->getPassword() !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }
        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('POST', $resourcePath, $httpBody, $headers, $queryParams);
    }

    /**
     * Operation putMePassword
     *
     * Update a password
     *
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body The new password. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return void
     */
    public function putMePassword($password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        $this->putMePasswordWithHttpInfo($password_reset_body, $zap_trace_span, $authorization);
    }

    /**
     * Operation putMePasswordWithHttpInfo
     *
     * Update a password
     *
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body The new password. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InfluxDB2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function putMePasswordWithHttpInfo($password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        $request = $this->putMePasswordRequest($password_reset_body, $zap_trace_span, $authorization);

        $response = $this->defaultApi->sendRequest($request);

        return [null, $response->getStatusCode(), $response->getHeaders()];
    }

    /**
     * Create request for operation 'putMePassword'
     *
     * @param  \InfluxDB2\Model\PasswordResetBody $password_reset_body The new password. (required)
     * @param  string $zap_trace_span OpenTracing span context (optional)
     * @param  string $authorization An auth credential for the Basic scheme (optional)
     *
     * @throws \InvalidArgumentException
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function putMePasswordRequest($password_reset_body, $zap_trace_span = null, $authorization = null)
    {
        // verify the required parameter 'password_reset_body' is set
        if ($password_reset_body === null || (is_array($password_reset_body) && count($password_reset_body) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $password_reset_body when calling putMePassword'
            );
        }

        $resourcePath = '/api/v2/me/password';
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // header params
        if ($zap_trace_span !== null) {
            $headerParams['Zap-Trace-Span'] = ObjectSerializer::toHeaderValue($zap_trace_span);
        }
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = ObjectSerializer::toHeaderValue($authorization);
        }


        // body params
        $_tempBody = null;
        if (isset($password_reset_body)) {
            $_tempBody = $password_reset_body;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
            }
        }

        // this endpoint requires HTTP basic authentication
        if ($this->config->getUsername() !== null || $this->config->getPassword() !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }
        $headers = array_merge(
            $headerParams,
            $headers
        );

        return $this->defaultApi->createRequest('PUT', $resourcePath, $httpBody, $headers, $queryParams);
    }

}