<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\Controller\Adminhtml\System\Account
{
    /**
     * Saving edited user information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $userId = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getId();
        $password = (string)$this->getRequest()->getParam('password');
        $passwordConfirmation = (string)$this->getRequest()->getParam('password_confirmation');
        $interfaceLocale = (string)$this->getRequest()->getParam('interface_locale', false);

        /** @var $user \Magento\User\Model\User */
        $user = $this->_objectManager->create('Magento\User\Model\User')->load($userId);

        $user->setId($userId)
            ->setUsername($this->getRequest()->getParam('username', false))
            ->setFirstname($this->getRequest()->getParam('firstname', false))
            ->setLastname($this->getRequest()->getParam('lastname', false))
            ->setEmail(strtolower($this->getRequest()->getParam('email', false)));

        if ($this->_objectManager->get('Magento\Framework\Locale\Validator')->isValid($interfaceLocale)) {
            $user->setInterfaceLocale($interfaceLocale);
            /** @var \Magento\Backend\Model\Locale\Manager $localeManager */
            $localeManager = $this->_objectManager->get('Magento\Backend\Model\Locale\Manager');
            $localeManager->switchBackendInterfaceLocale($interfaceLocale);
        }
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $currentUserPassword = $this->getRequest()->getParam($currentUserPasswordField);
        $isCurrentUserPasswordValid = !empty($currentUserPassword) && is_string($currentUserPassword);
        try {
            if (!($isCurrentUserPasswordValid && $user->verifyIdentity($currentUserPassword))) {
                throw new AuthenticationException(__('You have entered an invalid password for current user.'));
            }
            if ($password !== '') {
                $user->setPassword($password);
                $user->setPasswordConfirmation($passwordConfirmation);
            }
            $user->save();
            /** Send password reset email notification only when password was changed */
            if ($password !== '') {
                $user->sendPasswordResetNotificationEmail();
            }
            $this->messageManager->addSuccess(__('The account has been saved.'));
        } catch (\Magento\Framework\Validator\Exception $e) {
            $this->messageManager->addMessages($e->getMessages());
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->getDefaultResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*');
    }
}
