<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Rma\Api\Data\ItemInterface;
use Magento\Rma\Model\Rma;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order\ShipmentFactory;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_fixture_store.php');

$objectManager = Bootstrap::getObjectManager();
/** @var InvoiceManagementInterface $invoiceService */
$invoiceService = $objectManager->get(InvoiceManagementInterface::class);
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000004');
//RMA requires order items to be shipped
$invoice = $invoiceService->prepareInvoice($order);
$invoice->register();
$invoice->setIncrementId($order->getIncrementId());

$order = $invoice->getOrder();
$order->setIsInProcess(true);
$order->save();

$items = [];
foreach ($order->getItems() as $item) {
    $items[$item->getId()] = $item->getQtyOrdered();
}

$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->register();
$shipment->setIncrementId($order->getIncrementId());
$shipment->save();

$orderItems = $order->getItems();
foreach ($orderItems as $item) {
    $item->setQtyShipped($item->getQtyOrdered());
}
$order->save();

$orderItem = reset($orderItems);
/** @var $rma Rma */
$rma = $objectManager->create(Rma::class);
$rma->setOrderId($order->getId());
$rma->setStoreId($order->getStoreId());
$rma->setIncrementId(2);
$rma->setStatus('pending');

$orderProduct = $orderItem->getProduct();
/** @var ItemInterface $rmaItem */
$rmaItem = $objectManager->create(ItemInterface::class);
$rmaItem->setData(
    [
        'order_item_id' => $orderItem->getId(),
        //'name' instead of 'product_name' data key is being used through frontend and backend functionality
        'name' => $orderProduct->getName(),
        'product_sku' => $orderProduct->getSku(),
        'qty_returned' => 2,
        'is_qty_decimal' => 0,
        'qty_requested' => 2,
        'qty_authorized' => 2,
        'qty_approved' => 2,
        'status' => $order->getStatus(),
    ]
);
$rma->setItems([$rmaItem]);
/** @var RmaRepositoryInterface $rmaRepository */
$rmaRepository = $objectManager->get(RmaRepositoryInterface::class);
$rmaRepository->save($rma);
