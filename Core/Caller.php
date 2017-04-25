<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Core;

/**
 * PayPal caller service class
 */
class Caller
{
    /**
     * Service call parameters.
     *
     * @var array
     */
    protected $_aParams = array();

    /**
     * PayPal logger.
     *
     * @var \OxidEsales\PayPalModule\Core\Logger
     */
    protected $_oLogger = null;

    /**
     * PayPal curl object.
     *
     * @var \OxidEsales\PayPalModule\Core\Curl
     */
    protected $_oCurl = null;

    /**
     * Setter for logger.
     *
     * @param \OxidEsales\PayPalModule\Core\Logger $oLogger logger
     */
    public function setLogger($oLogger)
    {
        $this->_oLogger = $oLogger;
    }

    /**
     * Getter for PayPal logger.
     *
     * @return \OxidEsales\PayPalModule\Core\Logger
     */
    public function getLogger()
    {
        return $this->_oLogger;
    }

    /**
     * Sets PayPal curl object.
     *
     * @param \OxidEsales\PayPalModule\Core\Curl $oPayPalCurl PayPal curl object.
     */
    public function setCurl($oPayPalCurl)
    {
        $this->_oCurl = $oPayPalCurl;
    }

    /**
     * Returns curl instance
     *
     * @return \OxidEsales\PayPalModule\Core\Curl
     */
    public function getCurl()
    {
        if (is_null($this->_oCurl)) {
            $oCurl = oxNew(\OxidEsales\PayPalModule\Core\Curl::class);
            $this->setCurl($oCurl);
        }

        return $this->_oCurl;
    }

    /**
     * PayPal request parameters setter.
     *
     * @param string $sParamName  parameter name
     * @param mixed  $mParamValue parameter value
     */
    public function setParameter($sParamName, $mParamValue)
    {
        $this->_aParams[$sParamName] = $mParamValue;
    }

    /**
     * PayPal request parameters setter.
     *
     * @param array $aParameters parameters to use to build request.
     */
    public function setParameters($aParameters)
    {
        $this->_aParams = array_merge($this->_aParams, $aParameters);
    }

    /**
     * Returns PayPal request parameters array.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_aParams;
    }

    /**
     * Calls given remote PayPal method.
     *
     * @param string $sMethodName .
     *
     * @return array
     */
    public function call($sMethodName = null)
    {
        $this->_setMethod($sMethodName);

        $oCurl = $this->getCurl();
        $oCurl->setParameters($this->getParameters());

        $this->log($this->getParameters(), 'Request to PayPal');

        $aResponse = $oCurl->execute();

        $this->log($aResponse, 'Response from PayPal');

        $this->_validateResponse($aResponse);

        return $aResponse;
    }

    /**
     * Set method name to execute like DoExpressCheckoutPayment or GetExpressCheckoutDetails.
     *
     * @param string $sName Name of a method
     */
    protected function _setMethod($sName)
    {
        if (!is_null($sName)) {
            $this->setParameter("METHOD", $sName);
        }
    }

    /**
     * Validates response from PayPal errors.
     *
     * @param array $aResponse
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException if response has error from PayPal
     */
    protected function _validateResponse($aResponse)
    {
        if ('Failure' == $aResponse['ACK']) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException $oException
             */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException::class, $aResponse['L_LONGMESSAGE0'], $aResponse['L_ERRORCODE0']);
            throw $oException;
        }
    }

    /**
     * Outputs given request data.
     *
     * @param string $sMethodName
     *
     * @return string
     */
    public function getCallBackResponse($sMethodName)
    {
        $this->setParameter("METHOD", $sMethodName);

        $oCurl = $this->getCurl();
        $oCurl->setParameters($this->getParameters());
        $sRequest = $oCurl->getQuery();

        $this->log($sRequest, 'Callback response from eShop to PayPal');

        return $sRequest;
    }

    /**
     * Logs given request and responds parameters to log file.
     *
     * @param array  $aValue request / response parameters
     * @param string $sTitle section title in log file
     */
    public function log($aValue, $sTitle = '')
    {
        if (!is_null($this->getLogger())) {
            $this->getLogger()->setTitle($sTitle);
            $this->getLogger()->log($aValue);
        }
    }

    /**
     * Set parameter from request.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest request
     */
    public function setRequest(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest)
    {
        $this->setParameters($oRequest->getData());
    }
}