<?php

/**
 * This file contains test code that illustrates how the SugestioClient object can
 * be used in a project. Visit the website for tutorials and general API documentation.
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


$test = new SugestioClientTest();

try {

	//$test->testAddUser();
	//$test->testAddItem();
	//$test->testAddConsumption();
	//$test->testGetRecommendations();
	//$test->testGetSimilarItems();
	//$test->testDeleteRecommendation();
	//$test->testGetAnalytics();
	//$test->testGetSimilarUsers();
	//$test->testDeleteItem();
	//$test->testDeleteUser();
	
} catch (Exception $e) {
	echo $e->getMessage();
} 

/**
 * This class will test the SugestioClient.php functions
 */
class SugestioClientTest {

	private $client;

	public function __construct() {
		$this->client = new SugestioClient();
	}


	public function testAddUser() {

		$user = new SugestioUser(123);

		$user->gender = 'M';
		$user->birthday = '1975-04-09';
		$user->location_simple = 'US';
		$user->location_latlong = '40.446195,-79.948862';
		$user->apml = "http://...";
		$user->foaf = "http://...";

		$result = $this->client->addUser($user);

		echo "addUser response code: $result";

	}

	public function testAddItem() {

		$item = new SugestioItem('1234-AAA-5678');

		$item->description_short = 'short description';
		$item->description_full = 'full description';
		$item->permalink = 'http://...';

		$item->available = 'N';
		$item->from = '2010-01-01T00:00:00';
		$item->until = '2010-12-31T00:00:00';

		$item->location_simple = 'NY';
		$item->location_latlong = '40.446195,-79.948862';

		$item->category[] = 'Pop'; // category, creator, segment and tag are arrays
		$item->category[] = 'Rock';
		$item->creator[] = 'John Smith';
		$item->creator[] = 'James Smith';
		$item->segment = array('en-US', 'en-UK');
		$item->tag = array('tag1', 'tag2');

		$result = $this->client->addItem($item);

		echo "addItem response code: $result";
	}

	public function testAddConsumption() {

		$c = new SugestioConsumption('1', 'abcd'); // userid, itemid

		$c->date = 'NOW'; // automatically assign the current date
		$c->type = 'RATING';
		$c->detail = 'THUMB:UP';
		$c->location_simple = 'home';
		$c->location_latlong = '40.446195,-79.948862';

		$result = $this->client->addConsumption($c);

		echo "addConsumption response code: $result";
	}

	public function testGetRecommendations() {

		//$recommendations = $this->client->getRecommendations(1, array('category' => 'music'));
		//$recommendations = $this->client->getRecommendations(1, array('segment' => 'en-US'));
		$recommendations = $this->client->getRecommendations(1);

		echo '<pre>';
		print_r($recommendations);
		echo '</pre>';
	}

	public function testGetSimilarItems() {

		$similar = $this->client->getSimilarItems(1);

		echo '<pre>';
		print_r($similar);
		echo '</pre>';
	}

	public function testDeleteRecommendation() {

		$userid = 1;
		$itemid = 'abcd';

		$result = $this->client->deleteRecommendation($userid, $itemid);

		echo "deleteRecommendation response code: $result";
	}

	public function testGetAnalytics() {

		$result = $this->client->getAnalytics(5); // 5 most recent log entries

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	public function testGetSimilarUsers() {
		
		$result = $this->client->getSimilarUsers(1);

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}
	
	public function testDeleteItem() {
		
		$result = $this->client->deleteItem("x");

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}
	
	public function testDeleteUser() {
		
		$result = $this->client->deleteUser("x");

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}
}

?>
