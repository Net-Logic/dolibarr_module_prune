<?php
/**
* Copyright (c) Microsoft Corporation.  All Rights Reserved.  Licensed under the MIT License.  See License in the project root for license information.
* 
* ManagementAction File
* PHP version 7
*
* @category  Library
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
namespace Beta\Microsoft\Graph\ManagedTenants\Model;

/**
* ManagementAction class
*
* @category  Model
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
class ManagementAction extends \Beta\Microsoft\Graph\Model\Entity
{
    /**
    * Gets the category
    *
    * @return ManagementCategory|null The category
    */
    public function getCategory()
    {
        if (array_key_exists("category", $this->_propDict)) {
            if (is_a($this->_propDict["category"], "\Beta\Microsoft\Graph\ManagedTenants\Model\ManagementCategory") || is_null($this->_propDict["category"])) {
                return $this->_propDict["category"];
            } else {
                $this->_propDict["category"] = new ManagementCategory($this->_propDict["category"]);
                return $this->_propDict["category"];
            }
        }
        return null;
    }
    
    /**
    * Sets the category
    *
    * @param ManagementCategory $val The category
    *
    * @return ManagementAction
    */
    public function setCategory($val)
    {
        $this->_propDict["category"] = $val;
        return $this;
    }
    
    /**
    * Gets the description
    *
    * @return string|null The description
    */
    public function getDescription()
    {
        if (array_key_exists("description", $this->_propDict)) {
            return $this->_propDict["description"];
        } else {
            return null;
        }
    }
    
    /**
    * Sets the description
    *
    * @param string $val The description
    *
    * @return ManagementAction
    */
    public function setDescription($val)
    {
        $this->_propDict["description"] = $val;
        return $this;
    }
    
    /**
    * Gets the displayName
    *
    * @return string|null The displayName
    */
    public function getDisplayName()
    {
        if (array_key_exists("displayName", $this->_propDict)) {
            return $this->_propDict["displayName"];
        } else {
            return null;
        }
    }
    
    /**
    * Sets the displayName
    *
    * @param string $val The displayName
    *
    * @return ManagementAction
    */
    public function setDisplayName($val)
    {
        $this->_propDict["displayName"] = $val;
        return $this;
    }
    
    /**
    * Gets the referenceTemplateId
    *
    * @return string|null The referenceTemplateId
    */
    public function getReferenceTemplateId()
    {
        if (array_key_exists("referenceTemplateId", $this->_propDict)) {
            return $this->_propDict["referenceTemplateId"];
        } else {
            return null;
        }
    }
    
    /**
    * Sets the referenceTemplateId
    *
    * @param string $val The referenceTemplateId
    *
    * @return ManagementAction
    */
    public function setReferenceTemplateId($val)
    {
        $this->_propDict["referenceTemplateId"] = $val;
        return $this;
    }
    

     /** 
     * Gets the workloadActions
     *
     * @return array|null The workloadActions
     */
    public function getWorkloadActions()
    {
        if (array_key_exists("workloadActions", $this->_propDict)) {
           return $this->_propDict["workloadActions"];
        } else {
            return null;
        }
    }
    
    /** 
    * Sets the workloadActions
    *
    * @param WorkloadAction $val The workloadActions
    *
    * @return ManagementAction
    */
    public function setWorkloadActions($val)
    {
        $this->_propDict["workloadActions"] = $val;
        return $this;
    }
    
}
