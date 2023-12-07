# Faktor E JSON API Utilites package

## Purpose

This TYPO3 extension helps extension authors with setting up a simple JSON-based API.
It provides some helper methods and classes that can:

* parse the JSON request data
* build, enrich and send a JSON response
* transform TYPO3-specific models and object storages into structured arrays

## Motivation

Several of our recent projects have demands for widgets and components implemented in a frontend framework such as Vue.
To effectively utilize these frontend components, communication with a TYPO3 backend is usually done via JSON requests & responses.

Since these are usually very isolated widgets that don't require a fully fledged (headless) API, extensions like TYPO3 headless or 
t3api are very impressive, but they would be overkill in our use context. Instead we decided to go with this lightweight, self-maintainable solution.

## Usage

### JSON Requests

If you want to handle POST data from a request, create a new instance and make sure to use the ```parseJsonBody()``` and ```getDecodedData()``` methods.
Usage is very basic - Example in an Extbase Controller context:

```php

use Faktore\FeJsonApiUtilities\Utility\JsonRequestUtility;

class EventsJsonApiController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected JsonRequestUtility $jsonRequestUtility;
    
    // constructor with dependency injection
    public function __construct(JsonRequestUtility $jsonRequestUtility)
    {
        $this->jsonRequestUtility = $jsonRequestUtility;
    }
    
    public function searchAction(): ResponseInterface
    {
        // initialize the request utility
        $this->jsonRequestUtility->initialize();
        
        // get JSON POST data and store it in an array
        $postData = $this->jsonRequestUtility->parseJsonBody()->getDecodedData();
        
        // ... implement your handling of data
    }
}

$postData = $this->jsonRequestUtility->parseJsonBody()->getDecodedData();
```

### JSON Responses

Responses can be built with the JsonResponseUtility. You can add response data (in array format), keep track of errors and success status, all in this object.

```php

use Faktore\FeJsonApiUtilities\Utility\JsonResponseUtility;

class EventsJsonApiController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected JsonResponseUtility $jsonResponseUtility;
    
    // constructor with dependency injection
    public function __construct(JsonResponseUtility $jsonResponseUtility)
    {
        $this->jsonResponseUtility = $jsonResponseUtility;
    }
    
    public function searchAction(): ResponseInterface
    {
        // ... implement your handling of data
        
        // for example get events for a calendar using a repository
        // $events = $this->eventRepository->findByDemand($filterDemand);
        
        // convert your result to array first.
        // Implementing the convertToArray method will be your responsibility.
        // The ConvertUtilities provide a lot of useful functions to help with that.
        $events = $this->convertToArray($events);

        if ($events) {
            // assign data to the response
            $this->jsonResponseUtility->assignData('events', $output);
            // keep track of success status for the response
            $this->jsonResponseUtility->setSuccess(true);
        } else {
            // add an error
            $this->jsonResponseUtility->addError('no events found');
            // keep track of success status for the response
            $this->jsonResponseUtility->setSuccess(false);
        }
        
        // ... do more checks and validations to add errors if necessary.
        
        // Lastly, make sure to return a PSR-compliant response. The JsonResponse works just fine in Controller context.
        return $this->jsonResponse(
            $this->jsonResponseUtility->getOutput()
        )->withStatus(200);
    }
}

```

### Object & Property conversion utilities

Here is where you have a ConvertUtility class that can do a lot of magic for you.

#### Flattening FAL objects or FAL Storages

Use ```ConvertUtility::flattenFileStorage($imageFileStorage, $properties, $useAbsoluteUrl)```.

Note that in addition to the provided properties, every file in the storage will always yield the key 'publicUrl':

**New in 1.4.0**: Added a third optional parameter to flattenFileStorage. If true, the 'publicUrl' will be made absolute.

```php

use Faktore\FeJsonApiUtilities\Utility\ConvertUtility;

// ...

$result = [
    'title' => $event->getTitle() ?? '',
    // get FAL storages flattened
    'images' => ConvertUtility::flattenFileStorage(
            $event->getImages(),
            ['title', 'description', 'alternative'],
            true
        ) ?? '',
]; 

/* will return:
 * [
 *   'title' => 'foo'
 *   'images' => [
 *     0 => [
 *       'title' => bar,
 *       'publicUrl' => 'https://my.domain.com/puppies.jpg'
 *   ]
 * ]
 */

```

#### Flattening object storages

this will utilize the propertymapper to access gettable properties of an object

```php

use Faktore\FeJsonApiUtilities\Utility\ConvertUtility;

// ...

$result = ConvertUtility::flattenObjectStorage(
            $this->getCategories(),
            ['title', 'uid']
        );
        
/* $result will contain:
 * [
 *   0 => [
 *     'title' => 'foo',
 *     'uid' => 1234,
 *   ],
 *   1 => [
 *     'title' => 'bar',
 *     'uid' => 5678,
 *   ]
 * ]
 */

```

#### Others

There are a few other utility methods for converting your extbase models to arrays. Feel free to explore!

## Disclaimer

This utility library is very limited in scope, and it does not provide anything close to a full API for you. 
Making use of it in a real-world TYPO3 application will still require some work on your processing of requests and responses.

**Important:** Use at your own risk. There may be bugs. Remember to handle matters of security in your own code.