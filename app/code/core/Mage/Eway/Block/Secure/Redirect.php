<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Eway
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Eway_Block_Secure_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $shared = Mage::getModel('eway/secure');

        $form = new Varien_Data_Form();
        $form->setAction($shared->getEwaySecureUrl())
            ->setId('eway_secure_checkout')
            ->setName('eway_secure_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($shared->getSharedFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to eWAY 3D-Secure in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("eway_secure_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}