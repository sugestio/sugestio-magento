<?php

/**
 * This class holds the metadata for Items. Member fields are exposed rather than
 * having a getter and setter for each. See the API documentation for more information 
 * on individual fields.
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

class SugestioItem {

	public $id;
	
	public $title;
	public $permalink;
	public $thumbnail;
	
	public $description_short;
	public $description_full;
	
	public $available;
	public $from;
	public $until;
	
	public $location_simple;
	public $location_latlong;
	
	public $category;
	public $creator;
	public $segment;
	public $tag;

	/**
	 * 
	 * Creates a new Item object.
	 * 
	 * @param string $id the item id
	 */
	public function __construct($id) {
		$this->id = $id;
		$this->category = array();
		$this->creator = array();
		$this->segment = array();
		$this->tag = array();
	}

	/**
	 * 
	 * Returns an associative array containing all the member variables
	 * as name=>value pairs. Used internally by SugestioClient when issuing
	 * the addItem webservice call.
	 * 
	 * @return array(mixed)
	 */
	public function getFields() {

		$fields = array();

		$fields['id'] = $this->id;
		
		$fields['title'] = $this->title;
		$fields['permalink'] = $this->permalink;
		$fields['thumbnail'] = $this->thumbnail;
		
		$fields['description_short'] = $this->description_short;
		$fields['description_full'] = $this->description_full;
		
		$fields['available'] = $this->available;
		$fields['from'] = $this->from;
		$fields['until'] = $this->until;
		
		$fields['location_simple'] = $this->location_simple;
		$fields['location_latlong'] = $this->location_latlong;
		
		$fields['category'] = $this->category;
		$fields['creator'] = $this->creator;
		$fields['segment'] = $this->segment;
		$fields['tag'] = $this->tag;
		
		return $fields;
	}

}

?>
