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

class Sugestio_Recommender_Model_Cron extends Mage_Core_Model_Abstract
{

    const EXCEPTION_CODE_NOT_SALABLE            = 901;
    const EXCEPTION_CODE_HAS_REQUIRED_OPTIONS   = 902;
    const EXCEPTION_CODE_IS_GROUPED_PRODUCT     = 903;

   /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sugestio_cron';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'cron';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('sugestio/cron');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return WiCa_Sugestio_Model_Mysql4_Cron
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Load cron item by dat
     *
     * @param serialized array $data
     * @return WiCa_Sugestio_Model_Cron
     */
    public function loadByDat($data)
    {
    $this->_getResource()->loadByDat($this, $data);
        $this->_afterLoad();
        $this->setOrigData();

        return $this;
    }
}
