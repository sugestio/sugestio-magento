<?php

/**
 * Sample code from the online tutorial.
 *
 * The MIT License
 *
 * Copyright (c) 2010 Sugestio
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once dirname(__FILE__) . '/SugestioClient.php';
require_once dirname(__FILE__) . '/SugestioUser.php';
require_once dirname(__FILE__) . '/SugestioItem.php';
require_once dirname(__FILE__) . '/SugestioConsumption.php';

$client = new SugestioClient();


try {
	
	// Uncomment the feature that you want to try out

	//getRecommendations();
	//getSimilarItems();
	//getSimilarUsers();
	
	//addConsumption();
	//addItem();
	//addUser();
	
	//getAnalytics();
	
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}

function getRecommendations() {
	
	global $client;
	
	// get personal recommendations for user with id '1'
	$recommendations = $client->getRecommendations(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
}

function getSimilarItems() {

	global $client;
	
	// get items that are similar to the item with id '1'
	$recommendations = $client->getSimilarItems(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
}

function getSimilarUsers() {

	global $client;
	
	// get users that are similar to the user with id '1'
	$recommendations = $client->getSimilarUsers(1);
	
	echo '<pre>';
	print_r($recommendations);
	echo '</pre>';
	
}

function addConsumption() {
	
	global $client;
	
	$consumption = new SugestioConsumption(1, 'A'); // userid, itemid
	$consumption->type = 'RATING';
	$consumption->detail = 'STAR:5:1:3';
	$consumption->date = 'NOW'; 
	$result = $client->addConsumption($consumption);

	echo "addConsumption response code: $result";
	
}

function addItem() {
	
	global $client;
	
	$item = new SugestioItem('A');
	$item->tag = array('tag1', 'tag2');
	$item->category = array('category1', 'category2');
	$item->creator = array('artist1');	
	$item->location_latlong = '40.446195,-79.948862';	
	$result = $client->addItem($item);
	
	echo "addItem response code: $result";
}

function addUser() {
	
	global $client;
	
	$user = new SugestioUser(1);
	$user->gender = 'M';
	$user->birthday = '1974-03-20';
	$result = $client->addUser($user);
	
	echo "addUser response code: $result";
}

function getAnalytics() {
	
	global $client;
	
	$analytics = $client->getAnalytics(2);
	
	echo '<pre>';
	print_r($analytics);
	echo '</pre>';
}

?>