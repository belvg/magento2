<?php
/**
 * {license_notice}
 * 
 * @copyright {copyright}
 * @license   {license_link}
 */

class Mage_Backend_Model_Resource_System_Message_Collection_Synchronized
    extends Mage_Backend_Model_Resource_System_Message_Collection
{
    /**
     * Retrieve unread message list
     *
     * @var Mage_Backend_Model_System_MessageInterface[]
     */
    protected $_unreadMessages = array();

    public function _afterLoad()
    {
        $messages = $this->_messageList->asArray();
        $persisted = array();
        $unread = array();
        foreach ($messages as $message) {
            if ($message->isDisplayed()) {
                foreach ($this->_items as $persistedKey => $persistedMessage) {
                    if ($message->getIdentity() == $persistedMessage->getIdentity()) {
                        $persisted[$persistedKey] = $persistedMessage;
                        continue 2;
                    }
                }
                $unread[] = $message;
            }
        }
        $removed = array_diff_key($this->_items, $persisted);
        foreach ($removed as $removedItem) {
            $removedItem->delete();
        }
        foreach ($unread as $unreadItem ) {
            $item = $this->getNewEmptyItem();
            $item->setIdentity($unreadItem->getIdentity())
                ->setSeverity($unreadItem->getSeverity())
                ->save();
        }
        if (count($removed) || count($unread)) {
            $this->_unreadMessages = $unread;
            $this->clear();
            $this->load();
        } else {
            parent::_afterLoad();
        }
        return $this;
    }

    /**
     * @return Mage_Backend_Model_System_MessageInterface[]
     */
    public function getUnread()
    {
        return $this->_unreadMessages;
    }
}
