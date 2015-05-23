<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class SaveShippingMethod extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Shipping method save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        $data = $this->getRequest()->getPost('shipping_method', '');
        $result = $this->getOnepage()->saveShippingMethod($data);
        // $result will contain error data if shipping method is empty
        if (!$result) {
            $this->_eventManager->dispatch(
                'checkout_controller_onepage_save_shipping_method',
                ['request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()]
            );
            $this->getOnepage()->getQuote()->collectTotals();

            $result['goto_section'] = 'payment';
            $result['update_section'] = [
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml(),
            ];
            $result['update_progress'] = ['html' => $this->getProgressHtml($result['goto_section'])];
        }
        $this->quoteRepository->save($this->getOnepage()->getQuote()->collectTotals());
        return $this->resultJsonFactory->create()->setData($result);
    }
}
