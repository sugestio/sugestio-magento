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
 * Abstract recommendation view block
 *
 * @codepool	Local
 * @category	Fido
 * @package		Fido_Example
 * @module		Example
 */
abstract class Sugestio_Recommender_Block_Abstract extends Mage_Catalog_Block_Product_Abstract {

	protected $_collection;

	public function __construct() {
	}

	/**
	 * Returns a Controller object that interfaces with
	 * the external Sugestio recommendation engine.
	 */
	private function sugestio_get_controller() {
		$sugestio_url = Mage::getStoreConfig('sugestio/configuration/url');
		$sugestio_account = Mage::getStoreConfig('sugestio/configuration/account');
		$sugestio_key = Mage::getStoreConfig('sugestio/configuration/key');
		$sugestio_security_on = Mage::getStoreConfig('sugestio/configuration/security_on');

		//TODO: relative path
		require_once('app/code/local/Sugestio/Recommender/lib/SugestioClient.php');
		$cont = new SugestioClient($sugestio_account, $sugestio_key, $sugestio_url);		
		return $cont;
	}

	protected function getController() {
		return $this->sugestio_get_controller();
	}

	/**
	 * Gets the Configuration value number_items
	 */
	protected function getNumberItems() {
		return Mage::getStoreConfig('sugestio/configuration/number_items');
	}

	/**
	 * Gets the Configuration value cache_ttl
	 */
	protected function getCacheTtl() {
		return Mage::getStoreConfig('sugestio/configuration/cache_ttl');
	}

	/**
	 * Gets the Configuration value cache_enable
	 */
	protected function isCacheEnabled() {
		return Mage::getStoreConfigFlag('sugestio/configuration/cache_enable');
	}

	protected function _toHtml() {
		return parent::_toHtml();
	}
	/**
	 * Generates a cacheId
	 * @return string
	 */
	protected function getCacheId($idSuffix) {
		
		$id="";
		
		if($idSuffix == "recommendations"){
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$id = md5($customer->getEmail());
		} else {
			$product = Mage::registry('current_product');
			$id = $product->getSku();
		}
		
		return Mage::app()->getStore()->getId()."_".$id."_sugestio_".$idSuffix;
	}
}

