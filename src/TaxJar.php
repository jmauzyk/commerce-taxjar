<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\commerce\elements\Order;
use craft\commerce\events\TaxEngineEvent;
use craft\commerce\models\Address;
use craft\commerce\Plugin;
use craft\commerce\services\Taxes;
use craft\commerce\taxjar\adjusters\Tax;
use craft\commerce\taxjar\services\Api;
use craft\commerce\taxjar\services\Categories;
use craft\commerce\taxjar\engines\TaxJar as TaxJarEngine;
use yii\base\Event;


/**
 * Class TaxJar
 *
 * @author    Pixel & Tonic
 * @package   TaxJar
 * @since     1.0.0
 *
 * @property \craft\commerce\taxjar\services\Api $api
 */
class TaxJar extends BasePlugin
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     *
     */
    public function init()
    {
        $this->_setPluginComponents();
        $this->_registerRoutes();
        $this->_registerHandlers();

        parent::init();
    }

    public function _registerHandlers()
    {
        // We want to be the tax engine for commerce
        Event::on(Taxes::class, Taxes::EVENT_REGISTER_TAX_ENGINE, static function(TaxEngineEvent $e) {
            $e->engine = new TaxJarEngine;
        });

        if (true == false) {// Disable until we support order lodging
            Event::on(Order::class, Order::EVENT_AFTER_ORDER_PAID, static function(Event $e) {

                /** @var Order $order */
                $order = $e->sender;

                /** @var Address $address */
                $address = $order->getShippingAddress();

                if (Plugin::getInstance()->getSettings()->useBillingAddressForTax) {
                    $address = $order->getBillingAddress();
                }

                if (!$address) {
                    Craft::error('No address on order, can not submit tax transaction to TaxJar', 'commerce-taxjar');
                    return;
                }

                $storeLocation = Plugin::getInstance()->getAddresses()->getStoreLocationAddress();

                if (!$storeLocation) {
                    Craft::error('No store location set up, can not submit tax transaction to TaxJar', 'commerce-taxjar');
                    return;
                }

                $lineItems = [];

                foreach ($order->getLineItems() as $item) {
                    $lineItems[] = [
                        'quantity' => $item->qty,
                        'product_identifier' => $item->SKU,
                        'description' => $item->description,
                        'unit_price' => $item->salePrice
                    ];
                }

                $taxJarOrder = TaxJar::getInstance()->getApi()->getClient()->createOrder([
                    'transaction_id' => $order->id,
                    'transaction_date' => $order->datePaid,
                    'from_state' => $storeLocation->getState()->abbreviation ?? $storeLocation->getStateText(),
                    'from_zip' => $storeLocation->zipCode,
                    'from_country' => $storeLocation->getCountry()->iso ?? '',
                    'to_country' => $address->getCountry()->iso ?? '',
                    'to_zip' => $address->zipCode,
                    'to_state' => $address->getState()->abbreviation ?? $address->getStateText(),
                    'shipping' => $order->getTotalShippingCost(),
                    'sales_tax' => $order->getTotalTax(),
                    'line_items' => $lineItems
                ]);
            });
        }
    }

    /**
     * Registered the routes
     */
    private function _registerRoutes()
    {

    }

    /**
     * Returns the categories service
     *
     * @return Api The cart service
     * @throws \yii\base\InvalidConfigException
     */
    public function getApi()
    {
        return $this->get('api');
    }

    // Private Methods
    // =========================================================================

    /**
     * Sets the components of the commerce plugin
     */
    private function _setPluginComponents()
    {
        $this->setComponents([
            'api' => Api::class,
        ]);
    }
}
