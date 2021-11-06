<?php
/**
 * BrandResources
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
 * BrandResources Class Doc Comment
 *
 * @category    Class
 * @package     DocuSign\eSign
 * @author      Swagger Codegen team <apihelp@docusign.com>
 * @license     The DocuSign eSignature PHP Client SDK is licensed under the MIT License.
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class BrandResources implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'brandResources';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'created_by_user_info' => '\DocuSign\eSign\Model\UserInfo',
        'created_date' => '?string',
        'data_not_saved_not_in_master' => '?string[]',
        'modified_by_user_info' => '\DocuSign\eSign\Model\UserInfo',
        'modified_date' => '?string',
        'modified_templates' => '?string[]',
        'resources_content_type' => '?string',
        'resources_content_uri' => '?string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'created_by_user_info' => null,
        'created_date' => null,
        'data_not_saved_not_in_master' => null,
        'modified_by_user_info' => null,
        'modified_date' => null,
        'modified_templates' => null,
        'resources_content_type' => null,
        'resources_content_uri' => null
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
        'created_by_user_info' => 'createdByUserInfo',
        'created_date' => 'createdDate',
        'data_not_saved_not_in_master' => 'dataNotSavedNotInMaster',
        'modified_by_user_info' => 'modifiedByUserInfo',
        'modified_date' => 'modifiedDate',
        'modified_templates' => 'modifiedTemplates',
        'resources_content_type' => 'resourcesContentType',
        'resources_content_uri' => 'resourcesContentUri'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'created_by_user_info' => 'setCreatedByUserInfo',
        'created_date' => 'setCreatedDate',
        'data_not_saved_not_in_master' => 'setDataNotSavedNotInMaster',
        'modified_by_user_info' => 'setModifiedByUserInfo',
        'modified_date' => 'setModifiedDate',
        'modified_templates' => 'setModifiedTemplates',
        'resources_content_type' => 'setResourcesContentType',
        'resources_content_uri' => 'setResourcesContentUri'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'created_by_user_info' => 'getCreatedByUserInfo',
        'created_date' => 'getCreatedDate',
        'data_not_saved_not_in_master' => 'getDataNotSavedNotInMaster',
        'modified_by_user_info' => 'getModifiedByUserInfo',
        'modified_date' => 'getModifiedDate',
        'modified_templates' => 'getModifiedTemplates',
        'resources_content_type' => 'getResourcesContentType',
        'resources_content_uri' => 'getResourcesContentUri'
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
        $this->container['created_by_user_info'] = isset($data['created_by_user_info']) ? $data['created_by_user_info'] : null;
        $this->container['created_date'] = isset($data['created_date']) ? $data['created_date'] : null;
        $this->container['data_not_saved_not_in_master'] = isset($data['data_not_saved_not_in_master']) ? $data['data_not_saved_not_in_master'] : null;
        $this->container['modified_by_user_info'] = isset($data['modified_by_user_info']) ? $data['modified_by_user_info'] : null;
        $this->container['modified_date'] = isset($data['modified_date']) ? $data['modified_date'] : null;
        $this->container['modified_templates'] = isset($data['modified_templates']) ? $data['modified_templates'] : null;
        $this->container['resources_content_type'] = isset($data['resources_content_type']) ? $data['resources_content_type'] : null;
        $this->container['resources_content_uri'] = isset($data['resources_content_uri']) ? $data['resources_content_uri'] : null;
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
     * Gets created_by_user_info
     *
     * @return \DocuSign\eSign\Model\UserInfo
     */
    public function getCreatedByUserInfo()
    {
        return $this->container['created_by_user_info'];
    }

    /**
     * Sets created_by_user_info
     *
     * @param \DocuSign\eSign\Model\UserInfo $created_by_user_info created_by_user_info
     *
     * @return $this
     */
    public function setCreatedByUserInfo($created_by_user_info)
    {
        $this->container['created_by_user_info'] = $created_by_user_info;

        return $this;
    }

    /**
     * Gets created_date
     *
     * @return ?string
     */
    public function getCreatedDate()
    {
        return $this->container['created_date'];
    }

    /**
     * Sets created_date
     *
     * @param ?string $created_date 
     *
     * @return $this
     */
    public function setCreatedDate($created_date)
    {
        $this->container['created_date'] = $created_date;

        return $this;
    }

    /**
     * Gets data_not_saved_not_in_master
     *
     * @return ?string[]
     */
    public function getDataNotSavedNotInMaster()
    {
        return $this->container['data_not_saved_not_in_master'];
    }

    /**
     * Sets data_not_saved_not_in_master
     *
     * @param ?string[] $data_not_saved_not_in_master 
     *
     * @return $this
     */
    public function setDataNotSavedNotInMaster($data_not_saved_not_in_master)
    {
        $this->container['data_not_saved_not_in_master'] = $data_not_saved_not_in_master;

        return $this;
    }

    /**
     * Gets modified_by_user_info
     *
     * @return \DocuSign\eSign\Model\UserInfo
     */
    public function getModifiedByUserInfo()
    {
        return $this->container['modified_by_user_info'];
    }

    /**
     * Sets modified_by_user_info
     *
     * @param \DocuSign\eSign\Model\UserInfo $modified_by_user_info modified_by_user_info
     *
     * @return $this
     */
    public function setModifiedByUserInfo($modified_by_user_info)
    {
        $this->container['modified_by_user_info'] = $modified_by_user_info;

        return $this;
    }

    /**
     * Gets modified_date
     *
     * @return ?string
     */
    public function getModifiedDate()
    {
        return $this->container['modified_date'];
    }

    /**
     * Sets modified_date
     *
     * @param ?string $modified_date 
     *
     * @return $this
     */
    public function setModifiedDate($modified_date)
    {
        $this->container['modified_date'] = $modified_date;

        return $this;
    }

    /**
     * Gets modified_templates
     *
     * @return ?string[]
     */
    public function getModifiedTemplates()
    {
        return $this->container['modified_templates'];
    }

    /**
     * Sets modified_templates
     *
     * @param ?string[] $modified_templates 
     *
     * @return $this
     */
    public function setModifiedTemplates($modified_templates)
    {
        $this->container['modified_templates'] = $modified_templates;

        return $this;
    }

    /**
     * Gets resources_content_type
     *
     * @return ?string
     */
    public function getResourcesContentType()
    {
        return $this->container['resources_content_type'];
    }

    /**
     * Sets resources_content_type
     *
     * @param ?string $resources_content_type 
     *
     * @return $this
     */
    public function setResourcesContentType($resources_content_type)
    {
        $this->container['resources_content_type'] = $resources_content_type;

        return $this;
    }

    /**
     * Gets resources_content_uri
     *
     * @return ?string
     */
    public function getResourcesContentUri()
    {
        return $this->container['resources_content_uri'];
    }

    /**
     * Sets resources_content_uri
     *
     * @param ?string $resources_content_uri 
     *
     * @return $this
     */
    public function setResourcesContentUri($resources_content_uri)
    {
        $this->container['resources_content_uri'] = $resources_content_uri;

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

