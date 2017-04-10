# WatershedPHP
PHP Library for interacting with the Watershed API

## Introduction
Use this PHP code library to interact with the Watershed API to manage organizations, users and report cards. 

This library provides a simplified way to interact with the parts of the Watershed API most commonly required by
partner and customer applications. Please contact [Watershed support](https://watershedlrs.zendesk.com/hc/en-us/requests/new) 
for help implmenting this library or to request coverage of other parts of the API. 

Pull Requests and contributions of libraries in other languages are most welcome. 

To interact with Watershed via xAPI (for example to send tracking data), please 
use [TinCanPHP](http://rusticisoftware.github.io/TinCanPHP/).

## Installation
To install the library, simply include watershed.php in your project. 

```php
include ("watershed.php");
```

## Basic usage

### Instantiate the class
To interact with the library, first create an instance of the Watershed class. 
The examples below show instances created to connect to Watershed sandbox and production servers. You
can either provide your Watershed API username and password (example 1), or provide a complete authentication 
header (example 2). 

Example 1:
```php
$auth = array(
    "method" => "BASIC",
    "username" => "aladin@example.com",
    "password" => "open sesame"
);

$wsclient = new \WatershedClient\Watershed("https://sandbox.watershedlrs.com", $auth);
```

Example 2:
```php
$auth = array(
    "method" => "BASIC",
    "header" => "Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==",
);

$wsclient = new \WatershedClient\Watershed("https://watershedlrs.com", $auth);
```

### Create an organization
Each Watershed customer has their own organization. Watershed partner applications may have permission to create
organizations. The name of the organization must be unique. 

Record the organization Id for use in all other API calls. All ids used in the API are integers. 

```php
$orgId;
$orgName = "Name of Organization";

$response = $wsclient->createOrganization($orgName);
  if ($response["success"]) {
      $orgId = $response["orgId"];
      echo ("Org '".$orgName."'' created with id ".$orgId.".<br/>");
  } 
  else {
      echo "Failed to create org {$orgName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
  }
```

### Invite a user to an organization
Invite a user with a given email address to the organization. Possible roles are:

* owner
* admin
* user

Admins and owners are able to create reports; users are only able to view reports. 

```php
$userName = "Aladdin"
$userEmail = "aladdin@example.com"
$role = "admin";

$response = $wsclient->createInvitation($userName, $userEmail, $role, $orgId);
if ($response["success"]) {
    echo "Invite for {$userName} &lt;{$userEmail}&gt; sent.<br/>";
} 
else {
    echo "Invite for {$userName} &lt;{$userEmail}&gt; was not created. The server said: ".$response["content"]."<br/>";
}
```

### Create a set of xAPI Activity Provider Credentials
Use these details to interact with Watershed via xAPI.

```php
$APName = "Name of activity provider.";
$response = $wsclient->createActivityProvider($APName, $orgId);
if ($response["success"]) {
    $key = $response["key"];
    $secret = $response["secret"];
    $endpoint = $response["LRSEndpoint"];
    echo "Activity Provider created with key {$key} and secret {$secret}. Endpoint: {$endpoint} <br/>";
} 
else {
    echo "Failed to create Activity Provider. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```
## Card creation
When you create cards, you might like to record the card ids so that you can group them together (see below).

```php
$cardIds = array();
```

### Skills
Use the Skills report card to get an overview of usage of an activity that will be practiced multiple times.

```php
$activityName = "Some Activity";
$xAPIActivityId = "https://example.com/foo/bar";

$response = $wsclient->createSkillCard($activityName, $xAPIActivityId, $orgId);
if ($response["success"]) {
    $cardId = $response["cardId"];
    $skillId = $response["skillId"];
    echo "Skill card created for {$activityName} with id {$cardId}. <br/>";
    array_push($cardIds, $cardId);
} 
else {
    echo "Failed to create Skill card for {$activityName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```

### Activity Stream
Use the Activity Stream report card to see learner activity for all xAPI activities with ids starting with the URL 
you specify. The example below will create an activity stream filtered for all activities with ids starting "http://example.com/".

```php
$activityName = "Some Activity";
$xAPIActivityId = "https://example.com/";
$response = $wsclient->createActivityStreamCard($activityName, $xAPIActivityId, $orgId);
if ($response["success"]) {
    $cardId = $response["cardId"];
    echo "Activity Stream card created for {$activityName} with id {$cardId}. <br/>";
    array_push($cardIds, $cardId);
} 
else {
    echo "Failed to create Activity Stream card for {$activityName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```

### Activity Detail
Use the Activity Detail report card to look in detail at quizzes, assessments, tests etc. 
```php
$activityName = "Some Activity";
$xAPIActivityId = "https://example.com/foo/bar";
$response = $wsclient->createActivityDetailCard($activityName, $xAPIActivityId, $orgId);
if ($response["success"]) {
    $cardId = $response["cardId"];
    echo "Activity Detail card created for {$activityName} with id {$cardId}. <br/>";
    array_push($cardIds, $cardId);
} 
else {
    echo "Failed to create Activity Detail card for {$activityName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```

### Leaderboard
Use the Leaderboard report card to rank people, activities and time peroids by measures you create. 

#### Measures
You can include any number of measures in the Leaderboard, but we recommend no more than three. Pass 
measures as an associative array with name, match and title keys. Match and title are optional. E.g.

```php
array (
    "name" => "Success Count"
)
```

##### Name
Define measure names using a two word normal english phrase made up of an aggregation and a property. 

Allowed aggregations are:
* first
* latest
* highest
* lowest
* longest
* shortest
* average
* total
* count

The table below outlines allowed statement properties and key words;

Key word  | xAPI property
------------- | -------------
score  | result.score.scaled
scaled  | result.score.scaled
raw | result.score.raw
time  | result.durationCentiseconds
statement  | id
activity  | object.id
verb  | verb.id
completion  | result.completion
success  | result.success

Notes: 
* Some other properties are possible via the API, but not supported by this library.
* `durationCentiseconds` is an integer value calculated by Watershed based on result.duration. 

Measure phrases normally take the structure 'aggregation' 'property' and are case insensitive e.g.
* First Score 
* Shortest time
* average raw score

The exception is count aggregations, which use the opposite order to maintain natural English. You can request 
a distinct count be prepending the word "unique". For example:

* Unique activity id count
* completion count

##### Title
Normally the measure name is suitable natural english to display to the user, but if not, add a title key to the 
measure array.
```php
array (
    "name" => "Success Count",
    "title" => "Passes"
)
```

##### Match
Use the match key to have Watershed only count properties where the value you supply matches the value in the statement. 
This defaults to TRUE for success and completion properties.

```php
array (
    "name" => "Success Count",
    "match" => FALSE,
    "title" => "Fails"
)
```
#### Dimension
Possible dimensions the measures are applied on are:

* person
* activity
* activity type
* day
* week
* month
* year

#### Complete example
```php
$activityName = "Some Activity";
$xAPIActivityId = "https://example.com/foo/bar";
$measures = array(
    array (
        "name" => "First Score",
    ),
    array (
        "name" => "Success Count",
    ),
    array (
        "name" => "Verb Count",
        "match" => "http://id.tincanapi.com/verb/bookmarked",
        "title" => "Bookmarks made"
    ),
);
$dimension = "activity type";
$response = $wsclient->createLeaderBoardCard(
    $measures, 
    $dimension,
    $activityName,
    $xAPIActivityId, 
    $orgId
);
if ($response["success"]) {
    $cardId = $response["cardId"];
    echo "Leaderboard card created for {$activityName} with id {$cardId}. <br/>";
    array_push($cardIds, $cardId);
} 
else {
    echo "Failed to create Leaderboard card for {$activityName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}  
```

### Correlation
Use the Correlation card to compare two or more measures across a dimension. The function call to create a Correlation card
is very similar to the call for a Leaderboard:

```php
$activityName = "Some Activity";
$xAPIActivityId = "https://example.com/foo/bar";
$measures = array(
    array (
        "name" => "First Score",
    ),
    array (
        "name" => "Success Count",
    ),
    array (
        "name" => "Verb Count",
        "match" => "http://id.tincanapi.com/verb/bookmarked",
        "title" => "Bookmarks made"
    ),
);
$dimension = "activity type";
$response = $wsclient->createCorrelationCard(
    $measures, 
    $dimension,
    $activityName,
    $xAPIActivityId, 
    $orgId
);
if ($response["success"]) {
    $cardId = $response["cardId"];
    echo "Correlation card created for {$activityName} with id {$cardId}. <br/>";
    array_push($cardIds, $cardId);
} 
else {
    echo "Failed to create Correlation card for {$activityName}. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
} 
```

### Grouping cards
If you are creating a lot of cards, you may wish to put cards into group. We recommend grouping by activity or group of activitites 
rather than by card type. You will need to have a list of card ids to group, an organization id, a unique group name and a card title. 
In order to ensure that the group name is unqiue, we recommend prefixing the name with an id representing your application, for example "yourapp-group12345". 

```php
$groupName = "yourapp-group12345";
$groupTitle = "Some Activity";

$response = $wsclient->groupCards($cardIds, $orgId, $groupName, $groupTitle);
if ($response["success"]) {
    $cardId = $response["cardId"];
    $groupId = $response["groupId"];
    echo "Group card created with id {$cardId} for group {$groupId}. <br/>";
} 
else {
    echo "Failed to create Group card. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
} 
```

Hint: You can put existing group cards into a new group to create sub-groups.

#### Fetch a group
You can fetch a group by name, for example if you want to see if a group with a particular name already exists or get a list
of card ids contained in the group. 

```php
$response = $wsclient->getCardGroup($orgId, $courseGroupName);

if ($response['success']) {
    $cardIds = $response["cardIds"];
    $groupId = $response["groupId"];
    echo "Group found with id {$groupId}. <br/>";
}
elseif ($response['status'] == 404) {
    echo "Request successful but group not found. <br/>";
}
else {
    echo "Error when searching for group. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```

#### Move cards into a group
You can move cards into a group either from another group or from the dashboard. The example below moves a single card from the dashboard. 

```php
$newGroupName = "yourapp-group12345";
$oldGroupName = NULL;
$response = $wsclient->moveCardsGroup(array($cardId), $newGroupName, $oldGroupName, $orgId);

if ($response['success']) {
    echo "Card(s) moved successfully. <br/>";
}
else {
    echo "Fail to move card(s). Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
}
```

#### Groups within groups
Normally cards that are grouped are newly created top level cards. To create groups within an existing group, you need to pass an 
additional Parent Group Name parameter. Ensure that all cards you want to group are in the parent group before creating the grouping.

```php
$parentGroupName = "yourapp-group12345";
$groupName = "yourapp-group5678";
$groupTitle = "Some Activity";

$response = $wsclient->groupCards($cardIds, $orgId, $groupName, $groupTitle, $parentGroupName);
if ($response["success"]) {
    $cardId = $response["cardId"];
    $groupId = $response["groupId"];
    echo "Group card created with id {$cardId} for group {$groupId}. <br/>";
} 
else {
    echo "Failed to create Group card. Status: ". $response["status"].". The server said: ".$response["content"]."<br/>";
} 
```

 
