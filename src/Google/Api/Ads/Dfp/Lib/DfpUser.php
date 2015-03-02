<?php
/**
 * User class for the DoubleClick for Publishers API to create SOAP clients to
 * the available API services.
 *
 * PHP version 5
 *
 * Copyright 2011, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsDfp
 * @subpackage Lib
 * @category   WebServices
 * @copyright  2011, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 * @author     Adam Rogal
 * @author     Eric Koleda
 * @author     Vincent Tsao
 */
require_once 'Google/Api/Ads/Common/Lib/AdsUser.php';
require_once 'Google/Api/Ads/Common/Util/ApiPropertiesUtils.php';
require_once 'Google/Api/Ads/Common/Util/DeprecationUtils.php';
require_once 'Google/Api/Ads/Common/Util/Logger.php';
require_once 'Google/Api/Ads/Dfp/Lib/DfpSoapClientFactory.php';

/**
 * User class for the DoubleClick for Publishers API to create SOAP clients to
 * the available API services.
 * @package GoogleApiAdsDfp
 * @subpackage Lib
 */
class DfpUser extends AdsUser {

  const OAUTH2_SCOPE = 'https://www.googleapis.com/auth/dfp';
  const OAUTH2_HANDLER_CLASS = 'SimpleOAuth2Handler';

  /**
   * The name of the SOAP header that represents the user agent making API
   * calls.
   * @var string
   */
  const USER_AGENT_HEADER_NAME = 'applicationName';

  const DEFAULT_APPLICATION_NAME = 'INSERT_APPLICATION_NAME_HERE';

  private $libVersion;
  private $libName;

  private $applicationName;


  /**
   * The DfpUser constructor.
   * <p>The DfpUser class can be configured in one way:
   * <ol>
   * <li>Using an array with authentication info</li>
   * </ol></p>
   * <p></p>
   * @param array $oauth2Info the OAuth 2.0 information to use for requests
   * @param string $applicationName the application name (required header). Will
   *     be prepended with the library name and version. Will also overwrite the
   *     applicationName entry in any INI file
   * @param string $networkCode the network code the user belongs to
   *     (optional header). Can be left <var>NULL</var> if the user only belongs
   *     to one network. Will overwrite the networkCode entry in any INI
   *     file
   */
    public function __construct($oauth2Info = NULL, $applicationName = NULL, $networkCode = NULL) {
        parent::__construct();


        $buildIniDfp = parse_ini_file(dirname(__FILE__) . '/build.ini',
            false);
        $buildIniCommon = parse_ini_file(dirname(__FILE__) .
            '/../../Common/Lib/build.ini', false);
        $this->libName = $buildIniDfp['LIB_NAME'];
        $this->libVersion = $buildIniCommon['LIB_VERSION'];

        $apiProps = ApiPropertiesUtils::ParseApiPropertiesFile(dirname(__FILE__) .
            '/api.properties');
        $versions = explode(',', $apiProps['api.versions']);
        $defaultVersion = $versions[count($versions) - 1];
        $defaultServer = $apiProps['api.server'];

        $this->SetOAuth2Info($oauth2Info);
        $this->SetApplicationName($applicationName);
        $this->SetClientLibraryUserAgent($applicationName);
        $this->SetNetworkCode($networkCode);

        if (!isset($settingsIniPath)) {
            $settingsIniPath = dirname(__FILE__) . '/../settings.ini';
        }

        $this->loadSettings($settingsIniPath,
            $defaultVersion,
            $defaultServer,
            getcwd(), dirname(__FILE__));
    }



  /**
   * Gets the service by its service name.
   * @param $serviceName the service name
   * @param string $version the version of the service to get. If
   *     <var>NULL</var>, then the default version will be used
   * @param string $server the server to make the request to. If
   *     <var>NULL</var>, then the default server will be used
   * @param SoapClientFactory $serviceFactory the factory to create the client.
   *     If <var>NULL</var>, then the built-in SOAP client factory will be used
   * @return SoapClient the instantiated service
   * @throws ServiceException if an error occurred when getting the service
   */
  public function GetService($serviceName, $version = NULL, $server = NULL,
      SoapClientFactory $serviceFactory = NULL) {
    $this->ValidateUser();
    if (!isset($serviceFactory)) {
      if (!isset($version)) {
        $version = $this->GetDefaultVersion();
      }

      if (!isset($server)) {
        $server = $this->GetDefaultServer();
      }

      $serviceFactory = new DfpSoapClientFactory($this, $version, $server);
    }

    return parent::GetServiceSoapClient($serviceName, $serviceFactory);
  }

  /**
   * Gets the code for the network that this user belongs to.
   * @return string the network code
   */
  public function GetNetworkCode() {
    return $this->GetHeaderValue('networkCode');
  }

  /**
   * Sets the code for the network that this user belongs to.
   * @param string $networkCode the network code
   */
  public function SetNetworkCode($networkCode) {
    $this->SetHeaderValue('networkCode', $networkCode);
  }

  /**
   * Gets the raw application name for this user.
   * @return string The raw application name.
   */
  public function GetApplicationName() {
    return $this->applicationName;
  }

  /**
   * Sets the raw application name for this user.
   * @param string $applicationName The raw application name.
   */
  public function SetApplicationName($applicationName) {
    $this->applicationName = $applicationName;
  }

  /**
   * @see AdsUser::GetUserAgentHeaderName()
   */
  public function GetUserAgentHeaderName() {
    return self::USER_AGENT_HEADER_NAME;
  }

  /**
   * @see AdsUser::GetClientLibraryNameAndVersion()
   */
  public function GetClientLibraryNameAndVersion() {
    return array($this->libName, $this->libVersion);
  }

  /**
   * Get the default OAuth2 Handler for this user.
   * @param NULL|string $className the name of the oauth2Handler class or NULL
   * @return mixed the configured OAuth2Handler class
   */
  public function GetDefaultOAuth2Handler($className = NULL) {
    $className = !empty($className) ? $className : self::OAUTH2_HANDLER_CLASS;
    return new $className($this->GetAuthServer(), self::OAUTH2_SCOPE);
  }


  /**
   * Validates the user and throws a validation error if there are any errors.
   * @throws ValidationException if there are any validation errors
   */
  public function ValidateUser() {
    parent::ValidateOAuth2Info();

    if ($this->GetApplicationName() === NULL
        || trim($this->GetApplicationName()) === ''
        || strpos($this->GetApplicationName(),
            self::DEFAULT_APPLICATION_NAME) !== false) {
      throw new ValidationException('applicationName', NULL,
          sprintf("The property applicationName is required and cannot be "
              . "NULL, the empty string, or the default [%s]",
              self::DEFAULT_APPLICATION_NAME));
    }

  }

  /**
   * Handles calls to undefined methods.
   * @param string $name the name of the method being called
   * @param array $arguments the arguments passed to the method
   * @return mixed the result of the correct method call, or nothing if there
   *     is no correct method
   */
  public function __call($name, $arguments) {
    // Handle calls to legacy Get*Service() methods.
    if (preg_match('/^Get(\w+Service)$/i', $name, $matches)) {
      $serviceName = $matches[1];
      array_unshift($arguments, $serviceName);
      return call_user_func_array(array($this, 'GetService'), $arguments);
    }
  }
}

