<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * A custom "Import" adapter for Mage_ImportExport module that allows generating arbitrary data rows
 */
class Magento_ImportExport_Fixture_Generator extends Mage_ImportExport_Model_Import_SourceAbstract
{
    /**
     * Data row pattern
     *
     * @var array
     */
    protected $_pattern = array();

    /**
     * Which columns are determined as dynamic
     *
     * @var array
     */
    protected $_dynamicColumns = array();

    /**
     * @var int
     */
    protected $_limit = 0;

    /**
     * Read the row pattern to determine which columns are dynamic, set the collection size
     *
     * @param array $rowPattern
     * @param int $limit how many records to generate
     */
    public function __construct(array $rowPattern, $limit)
    {
        foreach ($rowPattern as $key => $value) {
            if (is_callable($value) || is_string($value) && (false !== strpos($value, '%s'))) {
                $this->_dynamicColumns[$key] = $value;
            }
        }
        $this->_pattern = $rowPattern;
        $this->_limit = (int)$limit;
        parent::__construct(array_keys($rowPattern));
    }

    /**
     * Whether limit of generated elements is reached (according to "Iterator" interface)
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_key + 1 <= $this->_limit;
    }

    protected function _getNextRow()
    {
        $row = $this->_pattern;
        foreach ($this->_dynamicColumns as $key => $dynamicValue) {
            $index = $this->_key + 1;
            if (is_callable($dynamicValue)) {
                $row[$key] = call_user_func($dynamicValue, $index);
            } else {
                $row[$key] = str_replace('%s', $index, $dynamicValue);
            }
        }
        return $row;
    }
}