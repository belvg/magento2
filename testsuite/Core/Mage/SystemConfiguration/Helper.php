<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_SystemConfiguration
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_SystemConfiguration_Helper extends Mage_Selenium_AbstractHelper
{
    /**
     * System Configuration
     *
     * @param array|string $parameters
     */
    public function configure($parameters)
    {
        if (is_string($parameters)) {
            $elements = explode('/', $parameters);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $parameters = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $chooseScope = (isset($parameters['configuration_scope'])) ? $parameters['configuration_scope'] : null;
        if ($chooseScope) {
            $this->selectStoreScope('dropdown', 'current_configuration_scope', $chooseScope);
        }
        foreach ($parameters as $value) {
            if (!is_array($value)) {
                continue;
            }
            $tab = (isset($value['tab_name'])) ? $value['tab_name'] : null;
            $settings = (isset($value['configuration'])) ? $value['configuration'] : null;
            if ($tab) {
                $this->openConfigurationTab($tab);
                foreach ($settings as $fieldsetName => $fieldsetData) {
                    $formLocator = $this->getControlElement('fieldset', $fieldsetName);
                    if ($formLocator->name() == 'fieldset') {
                        $fieldsetLink = $this->getControlElement('link', $fieldsetName . '_link');
                        if (strpos($fieldsetLink->attribute('class'), 'open') === false) {
                            $this->focusOnElement($fieldsetLink);
                            $fieldsetLink->click();
                            $this->clearActiveFocus();
                        }
                        $this->fillFieldset($fieldsetData, $fieldsetName);
                        $this->focusOnElement($fieldsetLink);
                        $fieldsetLink->click();
                        $this->clearActiveFocus();
                    } else {
                        $this->fillFieldset($fieldsetData, $fieldsetName);
                    }
                }
                $this->saveForm('save_config');
                $this->assertMessagePresent('success', 'success_saved_config');
                foreach ($settings as $fieldsetName => $fieldsetData) {
                    $formLocator = $this->getControlElement('fieldset', $fieldsetName);
                    if ($formLocator->name() == 'fieldset') {
                        $fieldsetLink = $this->getControlElement('link', $fieldsetName . '_link');
                        if (strpos($fieldsetLink->attribute('class'), 'open') === false) {
                            $this->focusOnElement($fieldsetLink);
                            $fieldsetLink->click();
                            $this->clearActiveFocus();
                        }
                    }
                    $this->verifyForm($fieldsetData, $tab);
                }
                if ($this->getParsedMessages('verification')) {
                    $messages = $this->getParsedMessages('verification');
                    $this->clearMessages('verification');
                    foreach ($messages as $errorMessage) {
                        if (!preg_match('#|(\!\= \'\*\*)#i', $errorMessage)) {
                            //if (preg_match('#(\'all\' \!\=)|(\!\= \'\*\*)|(\'all\')#i', $errorMessage)) {
                            $this->addVerificationMessage($errorMessage);
                        }
                    }
                    $this->assertEmptyVerificationErrors();
                }
            }
        }
    }

    /**
     * Open tab on Configuration page
     *
     * @param string $tab
     */
    public function openConfigurationTab($tab)
    {
        if (!$this->controlIsPresent('tab', $tab)) {
            $this->fail($this->locationToString() . "Tab '$tab' is not present on the page");
        }
        $this->defineParameters('tab', $tab, 'href');
        $url = $this->getControlElement('tab', $tab)->attribute('href');
        $this->url($url);
    }

    /**
     * Define Url Parameters for System Configuration page
     *
     * @param string $controlType
     * @param string $controlName
     * @param string $attribute
     *
     * @return void
     */
    public function defineParameters($controlType, $controlName, $attribute)
    {
        $params = $this->getControlAttribute($controlType, $controlName, $attribute);
        $params = explode('/', $params);
        foreach ($params as $key => $value) {
            if ($value == 'section' && isset($params[$key + 1])) {
                $this->addParameter('tabName', $params[$key + 1]);
            }
            if ($value == 'website' && isset($params[$key + 1])) {
                $this->addParameter('webSite', $params[$key + 1]);
            }
            if ($value == 'store' && isset($params[$key + 1])) {
                $this->addParameter('storeName', $params[$key + 1]);
            }
        }
    }

    /**
     * Enable/Disable option 'Use Secure URLs in Admin/Frontend'
     *
     * @param string $path
     * @param string $useSecure
     */
    public function useHttps($path = 'admin', $useSecure = 'Yes')
    {
        $this->admin('system_configuration');
        $this->openConfigurationTab('general_web');
        $fieldsetLink = $this->getControlElement('link', 'secure_link');
        if (strpos($fieldsetLink->attribute('class'), 'open') === false) {
            $fieldsetLink->click();
        }
        $secureBaseUrl = $this->getControlAttribute('field', 'secure_base_url', 'value');
        $data = array('secure_base_url'             => preg_replace('/http(s)?/', 'https', $secureBaseUrl),
                      'use_secure_urls_in_' . $path => ucwords(strtolower($useSecure)));
        $this->fillFieldset($data, 'secure');
        $this->clickButton('save_config');
        $this->assertTrue($this->verifyForm($data, 'general_web'), $this->getParsedMessages());
    }

    /**
     * @param $parameters
     */
    public function configurePaypal($parameters)
    {
        $this->configure($parameters);
    }

}