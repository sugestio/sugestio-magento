# Overview

This is a PHP library for interfacing with the [Sugestio](http://www.sugestio.com) 
recommendation service. Data is submitted as JSON data. The library uses 
[oauth-php](http://code.google.com/p/oauth-php/) to create the OAuth-signed requests. 
Our Drupal and Magento reference implementations are built on top of this generic library.

## About Sugestio

Sugestio is a scalable and fault tolerant service that now brings the power of 
web personalisation to all developers. The RESTful web service provides an easy to use 
interface and a set of developer libraries that enable you to enrich 
your content portals, e-commerce sites and other content based websites.

### Access credentials and the Sandbox

To access the Sugestio service, you need an account name and a secret key. 
To run the examples from the tutorial, you can use the following credentials:

* account name: <code>sandbox</code>
* secret key: <code>demo</code>

The Sandbox is a *read-only* account. You can use these credentials to experiment 
with the service. The Sandbox can give personal recommendations for users 1 through 5, 
and similar items for items 1 through 5.

When you are ready to work with real data, you may apply for a developer account through 
the [Sugestio website](http://www.sugestio.com).  

## About this library

### Features

The following [API](http://www.sugestio.com/documentation) features are implemented:

* get personalized recommendations for a given user
* get items that are similar to a given item
* get users that are similar to a given user
* (bulk) submit user activity (consumptions): clicks, purchases, ratings, ...
* (bulk) submit item metadata: description, location, tags, categories, ...  	
* (bulk) submit user metadata: gender, location, birthday, ...
* delete consumptions
* delete item metadata
* delete user metadata
* get performance data (analytics): precision, recall, ...

### Requirements

[oauth-php](http://code.google.com/p/oauth-php/) uses cURL for communicating 
with the recommendation service. As such, your system needs to have a current 
version of cURL installed. In addition, your PHP installation must include the 
php-curl extension and JSON support.

This distribution includes a slightly modified copy of 
[oauth-php](http://code.google.com/p/oauth-php/):

* OAuthRequester::doRequest does not throw an OAuthException2 on response codes >= 400
* Workaround for [Issue #66](http://code.google.com/p/oauth-php/issues/detail?id=66)

# Quick start

This section provides a quick demonstration of the Sugestio client object. A more detailed, step-by-step 
tutorial can be found further along this document.

	require_once dirname(__FILE__) . '/SugestioClient.php';
	require_once dirname(__FILE__) . '/SugestioUser.php';
	require_once dirname(__FILE__) . '/SugestioItem.php';
	require_once dirname(__FILE__) . '/SugestioConsumption.php';
	
	$client = new SugestioClient('sandbox', 'demo');
	
	// user X has consumed item Y
	$consumption = new SugestioConsumption('X', 'Y'); // userid, itemid
	$client->addConsumption($consumption);
	
	// submit consumptions in bulk
	$consumption1 = new SugestioConsumption('X', 'Y');
	$consumption2 = new SugestioConsumption('X', 'Z');
	$consumptions = array($consumption1, $consumption2);
	$client->addConsumptions($consumptions);
	
	// delete all consumptions made by user X
	$client->deleteUserConsumptions('X');
	
	// submit item metadata for item with ID Y
	$item = new SugestioItem('Y');
	$item->title = 'Item Y';
	$item->category = array('A', 'B');
	$client->addItem($item);
	
	// get recommendations for user 1
	$recommendations = $client->getRecommendations(1);
	
	// two best recommendations for user 1
	$recommendations = $client->getRecommendations(1, array('limit'=>2));
	
	// only recommendations from category 'music'
	$recommendations = $client->getRecommendations(1, array('category'=>'music'));
	
	// only recommendations within 1 mile of this location
	$queryParams = array(
		'location_latlong'=>'40.688889,-74.045111',
		'location_radius'=>'1',
		'location_unit'=>'mi');
	$recommendations = $client->getRecommendations(1, $queryParams);
	
	// combined filter
	$queryParams = array(
		'limit'=>5, // best five
		'category'=>'A,!B,C' // from category A or C, but not B
		'location_latlong'=>'40.688889,-74.045111',
		'location_radius'=>'1',
		'location_unit'=>'mi');
	$recommendations = $client->getRecommendations(1, $queryParams);

# Tutorial and sample code

<code>Tutorial.php</code> contains sample code that illustrates how you can use the library. 
You can uncomment the function that you want to try out. The rest of this section shows you 
how to use the <code>SugestioClient</code> object's public methods and what kind of response
to expect from the service. There are also some pointers on how to integrate the library into
an existing e-commerce application.

## Configuration

Access credentials can be statically configured through <code>Settings.php</code>:

	$account = 'sandbox';
	$secretkey = 'demo';

Alternatively, you can provide your account name and secret key when you 
create an instance of the <code>SugestioClient</code> class. Constructor 
arguments override <code>Settings.php</code>:

	$client = new SugestioClient(); // use values from Settings.php
	$client = new SugestioClient('sandbox', 'demo'); // override Settings.php

## Personal recommendations

Suppose we want to generate a personalized content offer for user 1.

### Code
	
	$recommendations = $client->getRecommendations(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
### Response

The client responds with an indexed array of associative arrays. Each associative array 
represents a recommendation. Recommendations are sorted by descending score. In other words, 
the item that best fits this user's taste is listed first. The *item* element is only present 
if the service has metadata for the item in question.

	Array
	(
		[0] => Array
			(
				[itemid] => 1
				[score] => 0.9
				[algorithm] => Sandbox
				[certainty] => 0.1
				[item] => Array
					(
						[id] => 1
						[title] => Item 1
						[permalink] => http://localhost/pages/1
						[category] => Array
							(
								[0] => A
								[1] => B
							)
					)

			)

		[1] => Array
			(
				[itemid] => 2
				[score] => 0.8
				[algorithm] => Sandbox
				[certainty] => 0.1
				[item] => Array
					(
						[id] => 2
						[title] => Item 2
						[permalink] => http://localhost/pages/2
						[category] => Array
							(
								[0] => B
								[1] => C
							)
					)
			)
			
			...
		)

	
### Integration

Let's assume our web application stores the ID of the current user in a session variable 
<code>$_SESSION['userid']</code>. We can use this variable to retrieve personal recommendations 
for this user. The title attribute of the *item* element can be used to easily visualize the 
recommendations.

	function showRecommendations() {

		echo "<h2>You may like these products:</h2>";

		global $client;
		$recommendations = $client->getRecommendations($_SESSION['userid']);

		foreach ($recommendations as $recommendation) {
			$title = $recommendation['item']['title'];
			$link = "/productdetails.php?id=" . $recommendation['itemid'];
			echo "<a href=\"$link\">$title</a><br/>";
		}
		
	}


## Similar items

Suppose we want to show similar products on our product pages. To get items that are similar
to item 1:

### Code

	$recommendations = $client->getSimilarItems(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
### Response

Like with <code>getRecommendations()</code>, the client responds with an indexed array 
of associative arrays. Each associative array represents an item recommendation. These are again
sorted by descending score. In other words, the item that is the most similar to item 1, 
is listed first.

	Array
	(
		[0] => Array
			(
				[itemid] => 2
				[score] => 0.8
				[certainty] => 0.5
				[algorithm] => Sandbox
				[item] => Array
					(
						[id] > 2
						[title] => Item 2
						[permalink] => http://localhost/pages/2
						[category] => Array
							(
								[0] => B
								[1] => C
							)
					)
			)
	
		[1] => Array
			(
				[itemid] => 3
				[score] => 0.7
				[certainty] => 0.5
				[algorithm] => Sandbox
				[item] => Array
					(
						[id] => 3
						[title] => Item 3
						[permalink] => http://localhost/pages/3
						[category] => Array
							(
								[0] => C
								[1] => D
							)
					)
			)
	
		...
	
	)

### Integration

Suppose the user is currently viewing a product page. The URL for this product page might look 
like this: <code>productdetails.php?id=1</code>. Displaying a list of products which are similar 
could go like this:

	function showSimilar() {

		echo "<h2>People who bought this product, also bought:</h2>";

		global $client;
		$recommendations = $client->getSimilarItems($_GET['id']);

		foreach ($recommendations as $recommendation) {
			$title = $recommendation['item']['title'];
			$link = "/productdetails.php?id=" . $recommendation['itemid'];
			echo "<a href=\"$link\">$title</a><br/>";
		}
		
	}

## Similar users

Collaborative filtering algorithms find clusters of users with a similar consumption behaviour.
These users are called _neighbours_. Run the following code to get the users that are similar to user 1:

### Code

	$recommendations = $client->getSimilarUsers(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
### Response

As usual, the client responds with an indexed array of associative arrays. 
Each associative array represents a neighbour of user 1. These user recommendations are again
sorted by descending score. In other words, the user that is the most similar to user 1, 
is listed first.

	Array
	(
		[0] => Array
			(
				[userid] => 2
				[score] => 0.9
				[certainty] => 0.5
				[algorithm] => Sandbox
			)
	
		[1] => Array
			(
				[userid] => 3
				[score] => 0.8
				[certainty] => 0.5
				[algorithm] => Sandbox
			)
	
		...
	
	)

### Integration

Clusters of similar users can be useful input for other processes.

## Submit a consumption

Consumptions are user-item interactions. You may want to submit a consumption anytime somebody
places an order or rates a product. Consumption data is essential to generating good 
recommendations. Collaborative filtering algorithms can function on just consumption data.

Here, we introduce a new class, <code>SugestioConsumption</code>. The constructor takes 
two arguments; user id and item id.
  

### Code
 
In the following code fragment, we record that user 1 has consumed item A.

	$consumption = new SugestioConsumption(1, 'A'); // userid, itemid
	$result = $client->addConsumption($consumption);
	
	echo "addConsumption response code: $result";
 
It can be beneficial to submit more information about a consumption. In the following example, 
we specify that the user purchased product A. We also set the time of consumption through 
the <code>date</code> property. The service automatically replaces 'NOW' with the current
date.

	$consumption = new SugestioConsumption(1, 'A'); // userid, itemid
	$consumption->type = 'PURCHASE';
	$consumption->date = 'NOW';
	$result = $client->addConsumption($consumption);
	
	echo "addConsumption response code: $result";
	
Item ratings can provide a good basis for quality recommendations. Suppose users can give 
a star rating from one to five. In the following example, user 1 gives three stars to product A: 

	$consumption = new SugestioConsumption(1, 'A'); // userid, itemid
	$consumption->type = 'RATING';
	$consumption->detail = 'STAR:5:1:3';
	$consumption->date = 'NOW';	
	$result = $client->addConsumption($consumption);
	
	echo "addConsumption response code: $result";

See the [API documentation](http://www.sugestio.com/documentation) for information on 
supported date formats and consumption types.

### Response	

The web service responds with standard 
[HTTP status codes](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html):

* 202 Accepted
* 400 Bad Request
* 401 Unauthorized
* 500 Internal Server Error

The example above will generate the following output:

	addConsumption response code: 202
	
This indicates that the consumption submission was well-formed and that it was succesfully 
processed by the Sugestio service.

The following code *omits the required field user id* and will be rejected by the service:

	$consumption = new SugestioConsumption(null, 'A'); // userid, itemid
	$result = $client->addConsumption($consumption);
	
	echo "addConsumption response code: $result";
	
The output will read:
	
	addConsumption response code: 400

### Integration

Suppose our e-commerce application has a method <code>placeOrder($customer, $basket)</code> 
which is called when an order has been finalized. <code>$basket</code> contains a list of all
the products that are part of this order. Each of these product purchases will represent 
a new consumption:

	function placeOrder($customer, $basket) {

		// existing application logic		
		// ...

		// the customer has ordered one or more products
		// the order has been correctly stored in our local database
		// now submit the consumption data to the recommendation service

		global $client;

		foreach ($basket->getProducts() as $product) {			
			$consumption = new Consumption($customer['id'], $product['id']);
			$consumption->date = 'NOW';
			$consumption->type = 'PURCHASE';
			$client->addConsumption($consumption);
		}		
	}
	
## Submit item metadata

Content-based algorithms use item metadata to generate recommendations. The 
<code>SugestioItem</code> class lets you assign all kinds of metadata to items. 
Some examples are:

* title
* permalink
* tags
* category information
* availability
* location data
* a short textual description   
* ...

Some attributes, like location, take a scalar value. Because items can have multiple tags or 
categories associated with them, we have to assign an indexed array to these attributes. Title 
and permalink can be used to easily visualize the recommendations, so it's a good idea to always
submit these attributes.

For a full list of item attributes and how to use them, see the 
[API documentation](http://www.sugestio.com/documentation).

### Code

The <code>SugestioItem</code> constructor takes a single value, the item id. We also assign 
values to various attributes, both scalar and non-scalar. 

	$item = new SugestioItem('A');
	$item->title = "Item A";
	$item->permalink = "http://localhost/products/A";
	$item->tag = array('tag1', 'tag2');
	$item->category = array('category1', 'category2');
	$item->creator = array('artist1');	
	$item->location_latlong = '40.446195,-79.948862';
	$result = $client->addItem($item);
	
	echo "addItem response code: $result";
	
### Response

The web service responds with standard 
[HTTP status codes](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html):

* 202 Accepted
* 400 Bad Request
* 401 Unauthorized
* 500 Internal Server Error

The example above will generate the following output:

	addItem response code: 202
	
This indicates that the item submission was well-formed and that it was succesfully 
processed by the Sugestio service.

### Integration

Let's assume our e-commerce application has a control panel for adding new products to the 
catalog. A method <code>createProduct($productInfo)</code> gets its input from a product input form and 
creates a new record in the Product table of our SQL database. Once the database has generated 
a unique id for this product, we can submit metadata to the recommendation service:

	function createProduct($productInfo) {

		// existing application logic
		// ...
		$newProductId = $db->add($productInfo);
	
		// a new row has been added to our Product table
		// now we submit the product metadata to the recommendation service
	
		global $client;
		
		$item = new SugestioItem($newProductId);
		$item->title = $productInfo['name'];
		$item->tag = $productInfo['tag'];
		$item->category = $productInfo['category'];
		$client->addItem($item);
	}

## Submit user metadata

Basic demographic information about a user can be useful when generating recommendations. 
The <code>SugestioUser</code> class lets you assign all kinds of metadata to users. 
Some examples are:

* Gender
* Birthday
* Location
* ...

For a full list of user attributes and how to use them, see the 
[API documentation](http://www.sugestio.com/documentation).

### Code

The <code>SugestioUser</code> constructor takes a single value, the user id. 
We also assign values to the gender and birthday attributes.

	$user = new SugestioUser(1);
	$user->gender = 'M';
	$user->birthday = '1974-03-20';
	$result = $client->addUser($user);
	
	echo "addUser response code: $result";

### Response

The web service responds with standard 
[HTTP status codes](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html):

* 202 Accepted
* 400 Bad Request
* 401 Unauthorized
* 500 Internal Server Error

The example above will generate the following output:

	addUser response code: 202
	
This indicates that the user submission was well-formed and that it was succesfully processed by the Sugestio service.

### Integration

Let's assume our e-commerce application has a method <code>createCustomer($customerInfo)</code> that gets its
input from a registration form and creates a new record in the User table of our SQL database. 
Once the database has generated a unique id for this customer, we can transmit some metadata to
the recommendation service:

	function createCustomer($customerInfo) {

		// existing application logic
		// ...
		$newCustomerId = $db->add($customerInfo);
	
		// a new row has been added to our User table
		// now we submit the user data to the recommendation service
	
		global $client;
		
		$user = new SugestioUser($newCustomerId);
		$user->gender = $customerInfo['gender'];
		$user->birthday = $customerInfo['birthday'];
		$client->addUser($user);
	}

## Analytics

Periodically, the available consumption data is divided into a training set and a test set. 
The service then calculates a number of performance metrics. The Sugestio website visualizes
this data as a chart, but you can also get the raw data through the API.

### Code

The <code>getAnalytics</code> method takes an optional <code>$limit</code> argument. Here, we
request the five most recent reports: 

	$analytics = $client->getAnalytics(5);
	
	echo '<pre>';
	print_r($analytics);
	echo '</pre>';

### Response

The client responds with an indexed array of associative arrays. Each associative array 
represents a single analytics report. For brevity, only partial output is shown below.

	Array
	(
		[0] => Array
			(
				[id] => f4fc18fa-3c7a-4daf-8807-01e4235d78ad
				// ...
				[evaluation_F1] => 0.0206286837
				[evaluation_Precision] => 0.0257037944
				[evaluation_Recall] => 0.0172272354
				[evaluation_Recommendations] => 4085
				[evaluation_RelevantRecommendations] => 105
				[evaluation_TestSetFrom] => 2010-07-03T00:00:00
				[evaluation_TestSetUntil] => 2010-08-03T00:00:00
				[evaluation_TrainingSetFrom] => 2009-07-31T00:00
				[evaluation_TrainingSetUntil] => 2010-07-03T00:00:00
				// ...
				[sparsity_AvgConsumptionsPerItemInTestSet] => 1.6850981476
				[sparsity_AvgConsumptionsPerUserInTestSet] => 7.4602203182
				[sparsity_ConsumptionsInTrainingSet] => 159803
				[sparsity_ItemsInTestSet] => 3617
				[sparsity_ItemsInTrainingSet] => 29748
				[sparsity_PercTrainingUsersConsumingInTestSet] => 0.0459375879
				// ...
			)
			
		[1] => Array
			(
				//...
			)
			
		// ...
	)
	

### Integration

The data can be imported into statistical tools for further analysis, or visualized as a graph. 
Google's [Chart API](http://code.google.com/apis/chart/) is a great tool 
for charting dynamic data.
