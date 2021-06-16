<?php
/**
* Copyright (c) Microsoft Corporation.  All Rights Reserved.  Licensed under the MIT License.  See License in the project root for license information.
* 
* IdentityContainer File
* PHP version 7
*
* @category  Library
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
namespace Microsoft\Graph\Model;

/**
* IdentityContainer class
*
* @category  Model
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
class IdentityContainer extends Entity
{
    /**
    * Gets the conditionalAccess
    *
    * @return ConditionalAccessRoot|null The conditionalAccess
    */
    public function getConditionalAccess()
    {
        if (array_key_exists("conditionalAccess", $this->_propDict)) {
            if (is_a($this->_propDict["conditionalAccess"], "\Microsoft\Graph\Model\ConditionalAccessRoot") || is_null($this->_propDict["conditionalAccess"])) {
                return $this->_propDict["conditionalAccess"];
            } else {
                $this->_propDict["conditionalAccess"] = new ConditionalAccessRoot($this->_propDict["conditionalAccess"]);
                return $this->_propDict["conditionalAccess"];
            }
        }
        return null;
    }
    
    /**
    * Sets the conditionalAccess
    *
    * @param ConditionalAccessRoot $val The conditionalAccess
    *
    * @return IdentityContainer
    */
    public function setConditionalAccess($val)
    {
        $this->_propDict["conditionalAccess"] = $val;
        return $this;
    }
    

     /** 
     * Gets the apiConnectors
     *
     * @return array|null The apiConnectors
     */
    public function getApiConnectors()
    {
        if (array_key_exists("apiConnectors", $this->_propDict)) {
           return $this->_propDict["apiConnectors"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the apiConnectors
    *
    * @param IdentityApiConnector $val The apiConnectors
    *
    * @return IdentityContainer
    */
    public function setApiConnectors($val)
    {
        $this->_propDict["apiConnectors"] = $val;
        return $this;
    }
    

     /** 
     * Gets the b2xUserFlows
     *
     * @return array|null The b2xUserFlows
     */
    public function getB2xUserFlows()
    {
        if (array_key_exists("b2xUserFlows", $this->_propDict)) {
           return $this->_propDict["b2xUserFlows"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the b2xUserFlows
    *
    * @param B2xIdentityUserFlow $val The b2xUserFlows
    *
    * @return IdentityContainer
    */
    public function setB2xUserFlows($val)
    {
        $this->_propDict["b2xUserFlows"] = $val;
        return $this;
    }
    

     /** 
     * Gets the identityProviders
     *
     * @return array|null The identityProviders
     */
    public function getIdentityProviders()
    {
        if (array_key_exists("identityProviders", $this->_propDict)) {
           return $this->_propDict["identityProviders"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the identityProviders
    *
    * @param IdentityProviderBase $val The identityProviders
    *
    * @return IdentityContainer
    */
    public function setIdentityProviders($val)
    {
        $this->_propDict["identityProviders"] = $val;
        return $this;
    }
    

     /** 
     * Gets the userFlowAttributes
     *
     * @return array|null The userFlowAttributes
     */
    public function getUserFlowAttributes()
    {
        if (array_key_exists("userFlowAttributes", $this->_propDict)) {
           return $this->_propDict["userFlowAttributes"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the userFlowAttributes
    *
    * @param IdentityUserFlowAttribute $val The userFlowAttributes
    *
    * @return IdentityContainer
    */
    public function setUserFlowAttributes($val)
    {
        $this->_propDict["userFlowAttributes"] = $val;
        return $this;
    }
    
}
