# Drewlabs SOAP HTTP Bindings

The library provides an SOAP HTTP client implementation

## Usage

```php
use Drewlabs\Soap\Http\SoapHttpClientFactory;

// ...

// Defines soap options
$options = [
  // ...
];
// Create request client
$factory = new SoapHttpClientFactory();
$client = $factory->create(
    new Client(),
    new StreamFactory, // Provide your own PSR7 StreamFactory or use nyholm/psr7 package
    new Psr7RequestFactory, // Provide your own PSR7 RequestFactory or use nyholm/psr7 package
    '<WSDL_URL>',
    $options,
    null // \Drewlabs\Soap\Contracts\RequestInterface::class (Class name of the class to use in parsing request parameters). 
    // You can use it to transform request body
);

// Making synchronous call
$result = $client->send($method, [
  //... SOAP parameters
]);

// Making async call
$promise = $client->sendAsync($method, [
  //... SOAP parameters
]);
$result = $promise->wait();
```
