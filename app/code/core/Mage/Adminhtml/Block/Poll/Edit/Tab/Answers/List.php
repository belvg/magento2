<?php
/**
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Block_Poll_Edit_Tab_Answers_List extends Mage_Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('poll/answers/list.phtml');
    }

    public function toHtml()
    {
        if( !Mage::registry('poll_data') ) {
            $this->assign('answers', false);
            return parent::toHtml();
        }

        $collection = Mage::getModel('poll/poll_answer')
            ->getResourceCollection()
            ->addPollFilter(Mage::registry('poll_data')->getId())
            ->load();
        $this->assign('answers', $collection);

        return parent::toHtml();
    }

    protected function _initChildren()
    {
        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Delete'),
                    'onclick'   => 'answer.del(this)',
					'class' => 'delete delete-poll-answer'
                ))
        );

        $this->setChild('addButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Add New Answer'),
                    'onclick'   => 'answer.add(this)',
					'class' => 'add'
                ))
        );
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('addButton');
    }
}