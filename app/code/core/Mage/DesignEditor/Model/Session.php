<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Design editor session model
 *
 * @method int getThemeId()
 */
class Mage_DesignEditor_Model_Session extends Mage_Backend_Model_Auth_Session
{
    /**
     * Session key that indicates whether the design editor is active
     */
    const SESSION_DESIGN_EDITOR_ACTIVE = 'DESIGN_EDITOR_ACTIVE';

    /**
     * Cookie name, which indicates whether highlighting of elements is enabled or not
     */
    const COOKIE_HIGHLIGHTING = 'vde_highlighting';

    /**
     * Check whether the design editor is active for the current session or not
     *
     * @return bool
     */
    public function isDesignEditorActive()
    {
        return $this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE) && $this->isLoggedIn();
    }

    /**
     * Activate the design editor for the current session
     */
    public function activateDesignEditor()
    {
        if (!$this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE) && $this->isLoggedIn()) {
            $this->setData(self::SESSION_DESIGN_EDITOR_ACTIVE, 1);
            Mage::dispatchEvent('design_editor_session_activate');
        }
    }

    /**
     * Deactivate the design editor for the current session
     */
    public function deactivateDesignEditor()
    {
        /*
         * isLoggedIn() is intentionally not taken into account to be able to trigger event when admin session expires
         */
        if ($this->getData(self::SESSION_DESIGN_EDITOR_ACTIVE)) {
            $this->unsetData(self::SESSION_DESIGN_EDITOR_ACTIVE);
            Mage::getSingleton('Mage_Core_Model_Cookie')->delete(self::COOKIE_HIGHLIGHTING);
            Mage::dispatchEvent('design_editor_session_deactivate');
        }
    }

    /**
     * Check whether highlighting of elements is disabled or not
     *
     * @return bool
     */
    public function isHighlightingDisabled()
    {
        $highlighting = Mage::getSingleton('Mage_Core_Model_Cookie')->get(self::COOKIE_HIGHLIGHTING);
        return 'off' == $highlighting;
    }
}
