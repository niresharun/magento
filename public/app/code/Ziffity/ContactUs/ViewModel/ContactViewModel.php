<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ziffity\ContactUs\ViewModel;

use Magento\Contact\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use \Magento\Customer\Model\Session;
/**
 * Provides the user data to fill the form.
 */
class ContactViewModel implements ArgumentInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * UserDataProvider constructor.
     * @param Data $helper
     */
    public function __construct(
        Session $customersession,
        Data $helper
    ) {
        $this->customerSession = $customersession;
        $this->helper = $helper;
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getFirstName()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        $customer = $this->customerSession->getCustomerDataObject();

        return $customer->getFirstname();
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getLastName()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        $customer = $this->customerSession->getCustomerDataObject();

        return $customer->getLastname();
    }

    /**
     * Get business name
     *
     * @return string
     */
    public function getBusinessName()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        $customer = $this->customerSession->getCustomerDataObject();

        return '';
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->helper->getPostValue('email') ?: $this->helper->getUserEmail();
    }

    /**
     * Get user telephone
     *
     * @return string
     */
    public function getUserTelephone()
    {
        return $this->helper->getPostValue('telephone');
    }

    /**
     * Get user comment
     *
     * @return string
     */
    public function getUserComment()
    {
        return $this->helper->getPostValue('comment');
    }
}
