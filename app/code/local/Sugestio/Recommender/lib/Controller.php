<?php

/**
 * THIS CLASS IS DEPRECATED
 * 
 * A wrapper class for the SugestioClient. Provides backwards compatibility for applications 
 * that use the old RaaS PHP library. New projects should use SugestioClient directly.
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
require_once dirname(__FILE__) . '/SugestioConsumption.php';
require_once dirname(__FILE__) . '/SugestioUser.php';
require_once dirname(__FILE__) . '/SugestioItem.php';

/** 
 * @deprecated
 */
class Controller {

	private $client;

	/**
	 * 
	 * Creates a new instance of the Controller wrapper.
	 * 
	 * @deprecated
	 * @param string $base_url URL of the API
	 * @param string $account account key
	 * @param string $secretkey secret key
	 * @param boolean $security sign requests 
	 */
	public function __construct($base_url=null, $account=null, $secretkey=null, $security=null) {
		$this->client = new SugestioClient($account, $secretkey, $base_url);
	}

	/**
	 * 
	 * Called when a user registers on the client website.
	 * Responses:
	 * 202 Accepted: Job was put on the queue and will be processed
	 * 400 Bad Request: Required arguments are missing or malformed
	 * 401 Unauthorized: Missing or incorrect account credentials 
	 * 500 Internal Server Error: Serverside problem
	 * 
	 * @param string $id User ID on the client website (required)
	 * @param string $location_simple country, ...
	 * @param string $location_latlong GPS coordinates (latitude, longitude)
	 * @param string $gender 'M' or 'F'
	 * @param string $birthday expressed as a UTC timestamp
	 * @return int response HTTP response code
	 */
	public function addUser($id, $location_simple, $location_latlong, $gender, $birthday) {

		$user = new SugestioUser($id);

		$user->gender = $gender;
		$user->birthday = $birthday;
		$user->location_simple = $location_simple;
		$user->location_latlong = $location_latlong;

		$result = $this->client->addUser($user);

		return $result;
	}

	/**
	 * 
	 * Called when an item is added to the client website.
	 * Responses:
	 * 202 Accepted: Job was put on the queue and will be processed
	 * 400 Bad Request: Required arguments are missing or malformed
	 * 401 Unauthorized: Missing or incorrect account credentials 
	 * 500 Internal Server Error: Serverside problem
	 *
	 * @param string $id Item ID on the client website (required)
	 * @param string $from indicating from when this item may be recommended. Ex: 2004-09-16T17:55:43.54Z
	 * @param string $until indicating until when this item may be recommended. Ex: 2004-09-16T17:55:43.54Z
	 * @param string $location_simple country, venue, ...
	 * @param string $location_latlong GPS coordinates. (latitude, longitude)
	 * @param array $creator Artist, manufacturer, uploader, ...
	 * @param array $tag 
	 * @param array $category 
	 * @return int response HTTP response code
	 */
	public function addItem($id, $from, $until, $location_simple, $location_latlong, $creator=array(), $tag=array(), $category=array()) {

		$item = new SugestioItem($id);

		$item->from = $from;
		$item->until = $until;
		$item->location_simple = $location_simple;
		$item->location_latlong = $location_latlong;
		$item->category = $category; // category, creator, segment and tag are arrays
		$item->creator = $creator;
		$item->tag = $tag;

		$result = $this->client->addItem($item);

		return $result;
	}

	
	/**
	 * 
	 * Called when a user consumes an item.
	 * Responses:
	 * 202 Accepted: Job was put on the queue and will be processed
	 * 400 Bad Request: Required arguments are missing or malformed
	 * 401 Unauthorized: Missing or incorrect account credentials 
	 * 500 Internal Server Error: Serverside problem
	 * 
	 * @param string $userid ID of the user that consumed the item (required)
	 * @param string $itemid ID of the item that was consumed (required)
	 * @param string $type The type of consumption (i.e. VIEW, BASKET, RATING)
	 * @param string $detail More information about the consumption
	 * @param string $date The moment of consumption expressed as a UTC timestamp
	 * @param string $location_simple Location where the item is consumed (home, office, ...)
	 * @param string $location_latlong GPS coordinates (latitude, longitude)
	 * @param array $extra associative array with additional parameters
	 * @return int response HTTP response code
	 */
	public function addConsumption($userid, $itemid, $type, $detail, $date, $location_simple, $location_latlong, $extra=array()) {

		$c = new SugestioConsumption($userid, $itemid); // userid, itemid

		$c->date = $date;
		$c->type = $type;
		$c->detail = $detail;
		$c->location_simple = $location_simple;
		$c->location_latlong = $location_latlong;
		$c->extra = $extra;

		$result = $this->client->addConsumption($c);

		return $result;
	}

	/**
	 * 
	 * Called when a user consumes an item.
	 * Responses:
	 * 202 Accepted: Job was put on the queue and will be processed
	 * 400 Bad Request: Required arguments are missing or malformed
	 * 401 Unauthorized: Missing or incorrect account credentials 
	 * 500 Internal Server Error: Serverside problem
	 * 
	 * @param string $userid ID of the user that consumed the item (required)
	 * @param string $itemid ID of the item that was consumed (required)
	 * @param string $type The type of consumption (i.e. VIEW, BASKET, RATING)
	 * @param string $detail More information about the consumption
	 * @param string $date The moment of consumption expressed as a UTC timestamp
	 * @param string $location_simple Location where the item is consumed (home, office, ...)
	 * @param string $location_latlong GPS coordinates (latitude, longitude)
	 * @param array $extra associative array with additional parameters
	 * @return int response HTTP response code
	 */
	public function addConsumptionExtra($userid, $itemid, $type, $detail, $date, $location_simple, $location_latlong, $extra) {
			return $this->addConsumption($userid, $itemid, $type, $detail, $date, $location_simple, $location_latlong, $extra);
	}

	/**
	 * 
	 * Returns recommendations for the given user. Recommendations consist of an Item ID and a score. 
	 * Recommendations are sorted by descending score.
	 * @param string $userid ID of the user (required)
	 * @return array (itemid=>string, score=>double, certainty=>double, algorithm=>string)
	 */
	public function getRecommendations($userid) {

		try {
			$recommendations = $this->client->getRecommendations($userid);
		} catch (Exception $e) {
			$recommendations = array();
		}

		return $recommendations;
	}

	/**
	 * 
	 * A (negative) consumption is created so that the item won't return the next time recommendations are calculated.
	 * Responses:
	 * 202 Accepted: Job was put on the queue and will be processed
	 * 400 Bad Request: Required arguments are missing or malformed
	 * 401 Unauthorized: Missing or incorrect account credentials 
	 * 500 Internal Server Error: Serverside problem
	 * 
	 * @param string $userid
	 * @param string $itemId
	 * @return int HTTP response code
	 */
	public function deleteRecommendation($userid, $itemId) {
		return $this->client->deleteRecommendation($userid, $itemId);
	}

	/**
	 * 
	 * Returns similar items for the given item. Recommendations consist of an Item ID and a score. 
	 * Recommendations are sorted by descending score.
	 * 
	 * @param string $itemid the item ID
	 * @return array (itemid=>string, score=>double, certainty=>double, algorithm=>string)
	 */
	public function getSimilar($itemid) {

		try {
			$similar = $this->client->getSimilarItems($itemid);
		} catch (Exception $e) {
			$similar = array();
		}

		return $similar;
	}

	public function getRecommendationsXml($userid) {
		return $this->getRecommendations($userid);
	}

	public function getRecommendationsJson($userid) {
		
		$recs = $this->getRecommendations($userid); 
		
		for ($i=0; $i<count($recs); $i++) {
			$recs[$i]['itemid'] = array($recs[$i]['itemid']);
			$recs[$i]['score'] = array($recs[$i]['score']);
			$recs[$i]['algorithm'] = array($recs[$i]['algorithm']);
		}
		
		return json_encode($recs);
	}

	public function getRecommendationsCsv($userid) {
		return $this->getRecommendations($userid);
	}

	public function getSimilarXml($itemid) {
		return $this->getSimilarItems($itemid);
	}

	public function getSimilarJson($itemid) {
		
		$recs = $this->getSimilarItems($itemid);
		
		for ($i=0; $i<count($recs); $i++) {
			$recs[$i]['itemid'] = array($recs[$i]['itemid']);
			$recs[$i]['score'] = array($recs[$i]['score']);
			$recs[$i]['algorithm'] = array($recs[$i]['algorithm']);
		}
		
		return json_encode($recs);
	}

	public function getSimilarCsv($itemid) {
		return $this->getSimilarItems($itemid);
	}

}

?>
