<?php
/**
 * {license_notice}
 *
 * @category    Social
 * @package     Social_Facebook
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Facebook resource
 *
 * @category   Social
 * @package    Social_Facebook
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Social_Facebook_Model_Resource_Facebook extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('social_facebook_actions', 'entity_id');
    }

    /**
     * Get Count of all Users by Action, Product Id
     *
     * @param int $facebookAction
     * @param int $productId
     *
     * @return int
     */
    public function getCountByActionProduct($facebookAction, $productId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('main_table' => $this->getMainTable()), array('count(*)'))
            ->where('facebook_action = ?', $facebookAction)
            ->where('item_id = ?', $productId);

        return $read->fetchOne($select);
    }

    /**
     * Load User by Action and Facebook Id
     *
     * @param int $facebookAction
     * @param int $facebookId
     * @param int $productId
     *
     * @return Social_Facebook_Model_Facebook
     */
    public function loadUserByActionId($facebookAction, $facebookId, $productId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('main_table' => $this->getMainTable()), array('*'))
            ->where('facebook_id = ?', $facebookId)
            ->where('facebook_action = ?', $facebookAction)
            ->where('item_id = ?', $productId);

        return $read->fetchRow($select);
    }

    /**
     * Get Count of all Users by Product Id
     *
     * @param int $productId
     *
     * @return int
     */
    public function getCountByProduct($productId)
    {
        $actions = Mage::helper('Social_Facebook_Helper_Data')->getAllActions();
        $actionArray = array();
        foreach ($actions as $action) {
            $actionArray[] = $action['action'];
        }
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('main_table' => $this->getMainTable()), array('count(*)'))
            ->where('facebook_action in (?)', $actionArray)
            ->where('item_id = ?', $productId);

        return $read->fetchOne($select);
    }

    /**
     * Get Linked Facebook Friends
     *
     * @param array $friends
     * @param int $productId
     * @param string $facebookAction
     * @return array
     */
    public function getLinkedFriends($friends, $productId, $facebookAction)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('main_table' => $this->getMainTable()), array('facebook_id', 'facebook_name'))
            ->where('facebook_id in (?)', $friends)
            ->where('facebook_action = ?', $facebookAction)
            ->where('item_id = ?', $productId)
            ->order(array('entity_id DESC'))
            ->limit(Mage::helper('Social_Facebook_Helper_Data')->getAppFriendCount($facebookAction))
            ->group('facebook_id');

        return $read->fetchPairs($select);
    }
}