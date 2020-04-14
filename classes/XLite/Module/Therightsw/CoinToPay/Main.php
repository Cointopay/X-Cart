<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) The right software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Therightsw\CoinToPay;

/**
 * Main module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'Cointopay';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Cointopay International B.V.';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'The Modern Currency Payment Provider you can trust. Supporting Bitcoin (Cash), Ethereum, Litecoin, Decred, Monero, Mooncoin and many more.';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.4';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '0';
    }

    /**
     * Get module type
     *
     * @return integer
     */
    public static function getModuleType()
    {
        return static::MODULE_TYPE_PAYMENT;
    }

}