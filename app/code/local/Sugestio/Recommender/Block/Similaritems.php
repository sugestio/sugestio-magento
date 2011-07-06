<?php

/**
 *
 * The MIT License
 *
 * Copyright (c) 2011 Sugestio
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

/**
 * Similar items view block
 *
 * @codepool	Local
 * @category	Fido
 * @package		Fido_Example
 * @module		Example
 */
class Sugestio_Recommender_Block_SimilarItems extends Sugestio_Recommender_Block_Abstract {

	public function __construct() {
		parent::__construct();
		$cacheTtl = $this->getCacheTtl()*60*60; //TTL in seconds
		if(parent::isCacheEnabled()){
			$this->addData(array(
				'cache_lifetime' => $cacheTtl,
				'cache_tags' => array('magento','similar_items'),
				'cache_key' => $this->getCacheId("similaritems")
			));
		}
	}
	/**
	 * Gets the similar items of the item
	 * @return array of similaritem arrays
	 */
	private function getSimilar() {
		
		$similarItems = array();
		$product = Mage::registry('current_product');
		if(!empty($product)) {
			$itemid = md5($product->getSku());
			try{
				$similarItems = parent::getController()->getSimilarItems($itemid);
			}
			catch(Exception $e){
			}
		}
		return $similarItems;
	}
	/**
	 * Gets the similar items of the product
	 * @return Array of Mage_Catalog_Model_Product
	 */
	public function getSimilarItems() {
		
		if(empty($this->_collection)) {
			$return = array();
			Mage::log("getting similar items");
			//if(!parent::isCacheEnabled() || $this->loadFromCache("similaritems")==null){
			$recs = $this->getSimilar();
			
			if(!empty($json_items)) {
				
				$count = 0;
				foreach ($recs as $rec) {
					
					if ($count >= parent::getNumberItems()) {
						break;
					}
					if (!empty($rec['item']['permalink'])) {
						$itemid = $rec['item']['permalink'];
						$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $itemid);
						if(!empty($product)){
						$return[] = $product;
							$count++;
						}
					}
				}
			}
			//}
			//else{
			//$return = $this->loadFromCache("similaritems");
				//age::log("loaded from cache");
				//}
			$this->_collection = $return;
			return $return;
		}
		return $this->_collection;
	}

	public function countSimilarItems() {
		return count($this->getSimilarItems());
	}

	public function hasSimilarItems() {
		return $this->countSimilarItems()>0;
	}

	protected function _toHtml() {
		return parent::_toHtml();
	}
}

