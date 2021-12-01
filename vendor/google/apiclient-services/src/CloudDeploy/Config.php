<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\CloudDeploy;

class Config extends \Google\Collection
{
  protected $collection_key = 'supportedVersions';
  public $defaultSkaffoldVersion;
  public $name;
  protected $supportedVersionsType = SkaffoldVersion::class;
  protected $supportedVersionsDataType = 'array';

  public function setDefaultSkaffoldVersion($defaultSkaffoldVersion)
  {
    $this->defaultSkaffoldVersion = $defaultSkaffoldVersion;
  }
  public function getDefaultSkaffoldVersion()
  {
    return $this->defaultSkaffoldVersion;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param SkaffoldVersion[]
   */
  public function setSupportedVersions($supportedVersions)
  {
    $this->supportedVersions = $supportedVersions;
  }
  /**
   * @return SkaffoldVersion[]
   */
  public function getSupportedVersions()
  {
    return $this->supportedVersions;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Config::class, 'Google_Service_CloudDeploy_Config');
