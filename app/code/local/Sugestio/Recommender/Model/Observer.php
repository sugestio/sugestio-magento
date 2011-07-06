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

class Sugestio_Recommender_Model_Observer {

	public function __construct() {
	}

	/**
	 * Implementation of hook catalog_product_save_after
	 */
	public function productSaved($observer) {
		
		$event = $observer->getEvent();
		$product = $event->getProduct();
		$productid = md5($product->getSku());
		$category = $product->getCategoryIds();
		
		$tagModel = Mage::getModel('tag/tag');		
		$tagCollection = $tagModel->getResourceCollection()
			->joinRel()
			->addProductFilter($product->getId())
			->addTagGroup()
			->load()
			->getItems();

		foreach($tagCollection as $tag){
			$tags[]=$tag->getName();
		}

		require_once('app/code/local/Sugestio/Recommender/lib/SugestioItem.php');
		$item = new SugestioItem($productid);
		$item->category = $category;
		$item->tag = $tags;
		$item->permalink = $product->getSku();
		
		$this->sugestio_execute($item, 'PRODUCT');
	}

	/**
	 * Implementation of hook catalog_controller_product_delete
	 */
	function productDeleted($observer)
	{
		//NOT YET SUPPORTED
		/*$event = $observer->getEvent();
		$product = $event->getProduct();
		Mage::log($product->getName()." deleted.");*/
	}
	
	/**
	 * Implementation of hook customer_save_after or adminhtml_customer_save_after
	 * 
	 */
	function customerSaved($observer) {
		/*
		$event = $observer->getEvent();
		$customer = $event->getCustomer();

		require_once('app/code/local/Sugestio/Recommender/lib/SugestioUser.php');
		$user = new SugestioUser(md5($customer->getEmail()));
		$user->gender = $customer->getGender();
		$user->birthday = $customer->getDob();

		$this->sugestio_execute($user, 'CUSTOMER');
		*/
	}

	/**
	 * Implementation of hook adminhtml_customer_delete_after
	 */
	function customerDeleted_Adminhtml($observer) {
		//NOT YET SUPPORTED
		/*$event = $observer->getEvent();
		$customer = $event->getCustomer();
		Mage::log($customer->getEmail()." deleted in backend.");*/
	}

	/**
	 * Implementation of hook customer_delete_after
	 */
	function customerDeleted_Frontend($observer) {
		//NOT YET SUPPORTED
		/*$event = $observer->getEvent();
		$customer = $event->getCustomer();
		Mage::log($customer->getEmail()." deleted in frontend.");*/
	}

	/**
	 * Implementation of hook catalog_controller_product_view
	 */
	function productViewed($observer) {
		//$this->sugestio_consumption($observer,'VIEW');
	}

	/**
	 * Implementation of hook rating_vote_added
	 * The event rating_vote_added isn't triggered by the core modules. The Sugestio_Recommender_Rating module overrides the Rating_Option model of the Mage_Rating module in order to get this event triggered.
	 */
	function productRated($observer) {
		$this->sugestio_consumption($observer,'RATING');
	}
	
	/**
	 * Implementation of hook catalog_product_compare_add_product
	 * Compare is handled as a View-type
	 */
	function productCompared($observer) {
		//$this->sugestio_consumption($observer,'VIEW');
	}

	/**
	 * Implementation of hook wishlist_add_product
	 */
	function productAddedToWishlist($observer) {
		//$this->sugestio_consumption($observer,'WISHLIST');
	}

	/**
	 * Implementation of hook checkout_cart_product_add_after
	 */
	function productAddedToCart($observer) {
		//$this->sugestio_consumption($observer,'BASKET');
	}

	/**
	 * Implementation of hook checkout_type_onepage_save_order_after
	 */
	function checkedOut_onepage($observer) {
		Mage::log("onepage");
		$this->sugestio_consumption($observer,'PURCHASE');
	}

	/**
	 * Implementation of hook checkout_type_multishipping_create_orders_single
	 */
	function checkedOut_multishipping($observer) {
		Mage::log("multishipping");
		$this->sugestio_consumption($observer,'PURCHASE');
	}

	/**
	 * Gets the params of the consumption for sugestio_execute
	 * @param string $type type of the consumption in capital letters, same types as in the API documentation of Sugestio
	 */
	private function sugestio_consumption($observer, $type) {
		
		require_once('app/code/local/Sugestio/Recommender/lib/SugestioConsumption.php');
		
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			$event = $observer->getEvent();
			$product = $event->getProduct();
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			
			switch($type){
				case "VIEW":
				case "WISHLIST":
				case "BASKET":
					$consumption = new SugestioConsumption(md5($customer->getEmail()), md5($product->getSku()));
					$consumption->type = $type;
					$this->sugestio_execute($consumption, 'CONSUMPTION');
					break;
				case "PURCHASE":
					$order = $event->getOrder();
					$items = $order->getAllVisibleItems();
					//$detail = "";
					foreach($items as $product) {
						//$detail = $product->getQtyToShip();
						$consumption = new SugestioConsumption(md5($customer->getEmail()), md5($product->getSku()));
						$consumption->type = $type;
						$this->sugestio_execute($consumption, 'CONSUMPTION');
					}
					break;
				case "RATING":
					$ratingOption = $event->getRating();
					$productId = $ratingOption->getEntityPkValue();
					$product = Mage::getModel('catalog/product')->load($productId);
					$ratingData = Mage::getResourceModel('rating/rating_option')->load($ratingOption->getId());
					$detail = "STAR:5:1:".$ratingData['value'];
					$ratingModel = Mage::getModel('rating/rating')->load($ratingData['rating_id']);
					$ratingAspect = strtoupper($ratingModel->getRatingCode());
					$consumption = new SugestioConsumption(md5($customer->getEmail()), md5($product->getSku()));
					$consumption->type = $type . ':' . $ratingAspect;
					$consumption->detail = $detail;
					$this->sugestio_execute($consumption, 'CONSUMPTION');
					break;
			}
		}
	}

	/**
	 * Submits the event to the Sugestio webservice
	 * @param array $data Object to give to Sugestio controller
	 * @param string $type type of the event in capital letters, e.g. PRODUCT, CUSTOMER, PURCHASE,...
	 * @param bool $cronned true if the call comes from the cron
	 * @return bool true if the execution succeeded. False if httpCode != 202
	 */
	private function sugestio_execute($data, $type, $cronned=FALSE) {

		if(Mage::getStoreConfigFlag('sugestio/configuration/realtime') || $cronned) {

			$client = $this->sugestio_get_controller();
			$httpCode=0;
			$txtSuccess="";
			
			try{
				switch($type) {
					case 'PRODUCT':
						$httpCode = $client->addItem($data);
						$txtSuccess = "Product ".$data->id." saved. HTTP code: ".$httpCode;
						break;
					case 'CUSTOMER':
						$httpCode = $client->addUser($data);
						$txtSuccess = "Customer ".$data->id." saved. HTTP code: ".$httpCode;
						break;
					default:
						//consumption
						if (!isset($data->id)) {
							// assign a consumption id so ratings can be updated
							$data->id = md5($data->userid . '-' . $data->itemid . '-' . $data->type);
						}
						$httpCode = $client->addConsumption($data);
						$txtSuccess= "Consumption: product ".$data->itemid.", customer ".$data->userid.", type ".$type." HTTP code: ".$httpCode;
						break;
				}
				
			} catch(Exception $e) {
				$txtSuccess = $e;
			}
			
			$txtError = "ERROR: ".$txtSuccess;

			if($httpCode == 202) {
				Mage::log($txtSuccess);
				return TRUE;
			} else {
				Mage::log($txtError);
			}
		}

		//add the consumption to the sugestio_cron if it doesn't exist in the table yet		
		$sdata = serialize($data);
		$this->save_to_cron($sdata, $type);
		
		return false;
	}

	/**
	 * If the execution to the Sugestio webservice fails, save_to_cron will save the data en the type in the DB (table sugestio_cron).
	 * @param serialized array $sdata Serialized array of the params to give to the Sugestio controller except the type
	 * @param string $type type of the event in capital letters
	 */
	private function save_to_cron($sdata, $type) {
		$cronItem = Mage::getModel('sugestio/cron');
		$cronItem->loadByDat($sdata);
		if(!$cronItem->getId()) {
			$cronItem->setDat($sdata)->setType($type)->save();
		}
	}

	/**
	 * The magento cron will execute this method periodically
	 */
	public function cron() {
		$cronItems = Mage::getModel('sugestio/cron')
			->getResourceCollection()
			->load()
			->getItems();
		
		foreach($cronItems as $item){
			$sdata = $item->getDat();
			$data = unserialize($sdata);
			$id = $item->getId();
			$type = $item->getType();
			
			if($this->sugestio_execute($data, $type, TRUE)){
				// execution succeeded, remove from cron table
				$item->delete();
			}
		}
	}

	/**
	 * Returns a Controller object that interfaces with
	 * the external Sugestio recommendation engine.
	 */
	private function sugestio_get_controller() {
		$sugestio_url = Mage::getStoreConfig('sugestio/configuration/url');
		$sugestio_account = Mage::getStoreConfig('sugestio/configuration/account');
		$sugestio_key = Mage::getStoreConfig('sugestio/configuration/key');
		$sugestio_security_on = Mage::getStoreConfigFlag('sugestio/configuration/security_on');

		require_once('app/code/local/Sugestio/Recommender/lib/SugestioClient.php');
		$cont = new SugestioClient($sugestio_account, $sugestio_key, $sugestio_url);
		return $cont;
	}
}
