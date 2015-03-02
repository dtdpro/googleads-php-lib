## The Google Ads APIs PHP Client Library

This project hosts the PHP client library for the DFP SOAP-based Ads APIs, This is
forked from the offical Google Ads PHp API.

https://github.com/googleads/googleads-php-lib


### Features

* Uses auth information from Google PHP API Client
* Provides easy SOAP logging of API calls.
* Automatic handling of SOAP headers.


### Getting started


1\. Map your OAuth 2 and account credentials.

$oauth2['client_id'] = CLIENT ID;
$oauth2['client_secret'] = CLIENT SECRET;
$oauth2['access_token'] = ACCESS TOKEN;
$oauth2['token_type'] = TOKEN TYPE;
$oauth2['expires_in'] = EXPIRES IN;
$oauth2['created'] = CREATED;
$oauth2['refresh_token'] = REFRESH TOKEN;



2\. Run an example.

Create DEFUser object

$user = new DfpUser($oauth2,$appname,$networkcode);

3\. Install the client library.

To install the client library, either copy the lib/ folder into where you store
your PHP source files that is on your PHP include path, or add the current
directory path of the lib/ folder to your PHP include path.


### How do I enable logging?

The client library uses a custom class for all logging purposes and is exposed
through the Logger.php file. There are two loggers within this class described
below.

  - REQUEST_INFO_LOG: Logs all requests from the client library along
    with information such as the timestamp, email, service, method,
    request Id, response time and which server was used. The default
    behavior is to log this information to "logs/request_info.log" relative to
    your project's home directory.

  - SOAP_XML_LOG: Logs all incoming and outgoing SOAP requests/responses. The
    default behavior is to log this information to "logs/soap_xml.log" relative
    to your project's home directory. Sensitive information, such as
    authentication tokens, will be stripped.

Logging can be enabled using the following methods.

  - $user->LogDefaults(): Logs request information for all requests, but only
    logs SOAP XML for requests that resulted in an error.

  - $user->LogErrors(): Only logs request information and SOAP XML for requests
    that resulted in an error.

  - $user->LogAll(): Logs request information and SOAP XML for all requests.

You can use the methods of the Logger class directly for even more control over
how requests are logged.


