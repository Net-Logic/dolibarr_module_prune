<?php
/**
 * CorrectViewRequest
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swagger Codegen team <apihelp@docusign.com>
 * @license  The DocuSign eSignature PHP Client SDK is licensed under the MIT License.
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * DocuSign REST API
 *
 * The DocuSign REST API provides you with a powerful, convenient, and simple Web services API for interacting with DocuSign.
 *
 * OpenAPI spec version: v2.1
 * Contact: devcenter@docusign.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.21-SNAPSHOT
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace DocuSign\eSign\Model;

use \ArrayAccess;
use DocuSign\eSign\ObjectSerializer;

/**
 * CorrectViewRequest Class Doc Comment
 *
 * @category    Class
 * @package     DocuSign\eSign
 * @author      Swagger Codegen team <apihelp@docusign.com>
 * @license     The DocuSign eSignature PHP Client SDK is licensed under the MIT License.
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class CorrectViewRequest implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'correctViewRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'return_url' => '?string',
        'suppress_navigation' => '?string',
        'view_url' => '?string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'return_url' => null,
        'suppress_navigation' => null,
        'view_url' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'return_url' => 'returnUrl',
        'suppress_navigation' => 'suppressNavigation',
        'view_url' => 'viewUrl'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'return_url' => 'setReturnUrl',
        'suppress_navigation' => 'setSuppressNavigation',
        'view_url' => 'setViewUrl'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'return_url' => 'getReturnUrl',
        'suppress_navigation' => 'getSuppressNavigation',
        'view_url' => 'getViewUrl'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['return_url'] = isset($data['return_url']) ? $data['return_url'] : null;
        $this->container['suppress_navigation'] = isset($data['suppress_navigation']) ? $data['suppress_navigation'] : null;
        $this->container['view_url'] = isset($data['view_url']) ? $data['view_url'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets return_url
     *
     * @return ?string
     */
    public function getReturnUrl()
    {
        return $this->container['return_url'];
    }

    /**
     * Sets return_url
     *
     * @param ?string $return_url The url used after correct/send view session has ended. DocuSign redirects to the url and includes an event parameter that can be used by your app. The event parameters returned are:   * send (user corrected and sent the envelope) * save (user saved the envelope) * cancel (user canceled the transaction.) * error (there was an error when performing the correct or send) * sessionEnd (the session ended before the user completed a different action)  ###### Note: Include https:// in the URL or the redirect might not succeed on some browsers.
     *
     * @return $this
     */
    public function setReturnUrl($return_url)
    {
        $this->container['return_url'] = $return_url;

        return $this;
    }

    /**
     * Gets suppress_navigation
     *
     * @return ?string
     */
    public function getSuppressNavigation()
    {
        return $this->container['suppress_navigation'];
    }

    /**
     * Sets suppress_navigation
     *
     * @param ?string $suppress_navigation Specifies whether the window is displayed with or without dressing.
     *
     * @return $this
     */
    public function setSuppressNavigation($suppress_navigation)
    {
        $this->container['suppress_navigation'] = $suppress_navigation;

        return $this;
    }

    /**
     * Gets view_url
     *
     * @return ?string
     */
    public function getViewUrl()
    {
        return $this->container['view_url'];
    }

    /**
     * Sets view_url
     *
     * @param ?string $view_url 
     *
     * @return $this
     */
    public function setViewUrl($view_url)
    {
        $this->container['view_url'] = $view_url;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}

