<?php
/**
 * Model Flag
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Saas_Index_Model_Flag extends Mage_Core_Model_Flag
{
    const STATE_QUEUED     = 1;
    const STATE_PROCESSING = 2;
    const STATE_FINISHED   = 3;
    const STATE_NOTIFIED   = 4;

    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'refresh_search_index';

    /**
     * Is need to display Index Outdated message
     *
     * @return bool
     */
    public function isShowIndexNotification()
    {
        $state = $this->getState();
        return self::STATE_NOTIFIED == $state || null === $state;
    }

    /**
     * @return bool
     */
    public function isTaskAdded()
    {
        return in_array($this->getState(), array(self::STATE_QUEUED, self::STATE_PROCESSING));
    }

    /**
     * @return bool
     */
    public function isTaskFinished()
    {
        return $this->getState() == self::STATE_FINISHED;
    }

    /**
     * @return bool
     */
    public function isTaskProcessing()
    {
        return $this->getState() == self::STATE_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isTaskNotified()
    {
        return $this->getState() == self::STATE_NOTIFIED;
    }

    /**
     * Change status to self::STATE_NOTIFIED and save
     */
    public function saveAsNotified()
    {
        $this->setState(self::STATE_NOTIFIED)
            ->save();
    }
}