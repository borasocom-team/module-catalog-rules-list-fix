<?php

namespace Boraso\CatalogRulesListFix\Plugin\Magento\Catalog\Block\Product;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;

class ListProductPlugin
{

    const JOIN_CATALOG_PRODUCT_PRICE = 'join_catalog_product_price';

    protected $stockItem;
    protected $customerSession;
    protected $registry;

    public function __construct(
        CustomerSession $customerSession,
        Registry $registry
    ) {
        $this->customerSession = $customerSession;
        $this->registry        = $registry;
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        if (empty($this->registry->registry(self::JOIN_CATALOG_PRODUCT_PRICE))) {
            /** @var \Magento\Framework\Data\Collection\AbstractDb $result */
            $resultSelect    = $result->getSelect();
            $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

            $resultSelect->joinLeft(
                'catalogrule_product_price',
                'e.entity_id = `catalogrule_product_price`.`product_id` and `catalogrule_product_price`.`rule_date` = CURRENT_DATE() and `catalogrule_product_price`.`customer_group_id` = ' . $customerGroupId,
                'rule_price'
            );

            $columns = $resultSelect->getPart('columns');
            foreach ($columns as $key => $column) {
                if ($column[1] == 'min_price') {
                    $newPricePart = [
                        'catalogrule_product_price',
                        new \Zend_Db_Expr('if(`catalogrule_product_price`.`rule_price` is not null, `catalogrule_product_price`.`rule_price`, `price_index`.`min_price`)'),
                        'min_price'
                    ];

                    $columns[$key] = $newPricePart;
                }

                if ($column[1] == 'max_price') {
                    $newPricePart = [
                        'catalogrule_product_price',
                        new \Zend_Db_Expr('if(`catalogrule_product_price`.`rule_price` is not null, `catalogrule_product_price`.`rule_price`, `price_index`.`max_price`)'),
                        'max_price'
                    ];

                    $columns[$key] = $newPricePart;
                }

                $orders = $resultSelect->getPart('order');
                foreach ($orders as $key => $order) {
                    if ($order[0] == 'price_index.min_price') {
                        $orders[$key][0] = 'min_price';
                    }

                    if ($order[0] == 'price_index.max_price') {
                        $orders[$key][0] = 'max_price';
                    }
                }
                $resultSelect->setPart('order', $orders);

            }
            $resultSelect->setPart('columns', $columns);

            $this->registry->register(self::JOIN_CATALOG_PRODUCT_PRICE, true);
        }

        return $result;
    }

}