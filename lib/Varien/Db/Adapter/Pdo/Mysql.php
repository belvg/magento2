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
 * @category   Varien
 * @package    Varien_Db
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Varien_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
    const ISO_DATE_FORMAT       = 'yyyy-MM-dd';
    const ISO_DATETIME_FORMAT   = 'yyyy-MM-dd HH-mm-ss';

    protected $_transactionLevel=0;
    protected $_connectionFlagsSet=false;

    public function beginTransaction()
    {
        if ($this->_transactionLevel===0) {
            parent::beginTransaction();
        }
        $this->_transactionLevel++;
        return $this;
    }

    public function commit()
    {
        if ($this->_transactionLevel===1) {
            parent::commit();
        }
        $this->_transactionLevel--;
        return $this;
    }

    public function rollback()
    {
        if ($this->_transactionLevel===1) {
            return parent::rollback();
        }
        $this->_transactionLevel--;
        return $this;
    }

    public function convertDate($date)
    {
        if ($date instanceof Zend_Date) {
            return $date->toString(self::ISO_DATE_FORMAT);
        }
        return strftime('%Y-%m-%d', strtotime($date));
    }

    public function convertDateTime($datetime)
    {
        if ($datetime instanceof Zend_Date) {
            return $datetime->toString(self::ISO_DATETIME_FORMAT);
        }
        return strftime('%Y-%m-%d %H:%M:%S', strtotime($datetime));
    }


    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        if (!extension_loaded('pdo_mysql')) {
            throw new Zend_Db_Adapter_Exception('pdo_mysql extension is not installed');
        }

        if (strpos($this->_config['host'], '/')!==false) {
            $this->_config['unix_socket'] = $this->_config['host'];
            $this->_config['host'] = null;
        } elseif (strpos($this->_config['host'], ':')!==false) {
            list($this->_config['host'], $this->_config['port']) = explode(':', $this->_config['host']);
        }

        parent::_connect();

        /** @link http://bugs.mysql.com/bug.php?id=18551 */
        $this->_connection->query("SET SQL_MODE=''");

        if (!$this->_connectionFlagsSet) {
            $this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            #$this->_connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $this->_connectionFlagsSet = true;
        }
    }

    public function raw_query($sql)
    {
        do {
            $retry = false;
            $tries = 0;
            try {
                $result = $this->getConnection()->query($sql);
            } catch (PDOException $e) {
                if ($e->getMessage()=='SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query') {
                    $retry = true;
                } else {
                    throw $e;
                }
                $tries++;
            }
        } while ($retry && $tries<10);

        return $result;
    }

    public function raw_fetchRow($sql, $field=null)
    {
        if (!$result = $this->raw_query($sql)) {
            return false;
        }
        if (!$row = $result->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }
        if (empty($field)) {
            return $row;
        } else {
            return isset($row[$field]) ? $row[$field] : false;
        }
    }

    public function multi_query($sql)
    {
        #$this->beginTransaction();
        try {
            $stmts = $this->_splitMultiQuery($sql);
            foreach ($stmts as $stmt) {
                $result = $this->raw_query($stmt);
            }
            #$this->commit();
        } catch (Exception $e) {
            #$this->rollback();
            throw $e;
        }
        #$result = $this->raw_query($sql);
        return $result;
    }

    /**
     * Split multi statement query
     *
     * @author Moshe Gurvich
     * @param $sql string
     * @return array
     */
    protected function _splitMultiQuery($sql)
    {
        $parts = preg_split('#(;|\'|"|\\\\|//|--|\n|/\*|\*/)#', $sql, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        $q = false;
        $c = false;
        $stmts = array();
        $s = '';

        foreach ($parts as $i=>$part) {
            // strings
            if (($part==="'" || $part==='"') && ($i===0 || $parts[$i-1]!=='\\')) {
                if ($q===false) {
                    $q = $part;
                } elseif ($q===$part) {
                    $q = false;
                }
            }

            // single line comments
            if (($part==='//' || $part==='--') && ($i===0 || $parts[$i-1]==="\n")) {
                $c = $part;
            } elseif ($part==="\n" && ($c==='//' || $c==='--')) {
                $c = false;
                #$stmts[] = trim($s);
                #$s = '';
                #continue;
            }

            // multi line comments
            if ($part==='/*' && $c===false) {
                $c = '/*';
            } elseif ($part==='*/' && $c==='/*') {
                $c = false;
                #$stmts[] = trim($s.$part);
                #$s = '';
                #continue;
            }

            // statements
            if ($part===';' && $q===false && $c===false) {
                if (trim($s)!=='') {
                    $stmts[] = trim($s);
                    $s = '';
                }
            } else {
                $s .= $part;
            }
        }
        if (trim($s)!=='') {
            $stmts[] = trim($s);
        }

        return $stmts;
    }

    public function dropForeignKey($table, $fk)
    {
        $create = $this->raw_fetchRow("show create table `$table`", 'Create Table');
        if (strpos($create, "CONSTRAINT `$fk` FOREIGN KEY (")!==false) {
            return $this->raw_query("ALTER TABLE `$table` DROP FOREIGN KEY `$fk`");
        }
        return true;
    }

    public function dropKey($table, $key)
    {
        $create = $this->raw_fetchRow("show create table `$table`", 'Create Table');
        if (strpos($create, "KEY `$key` (")!==false) {
            return $this->raw_query("ALTER TABLE `$table` DROP KEY `$key`");
        }
        return true;
    }

    public function dropColumn($tableName, $columnName)
    {
        $columnExist = false;
        foreach ($this->fetchAll('DESCRIBE `'.$tableName.'`') as $row) {
            if ($row['Field'] == $columnName) {
                $columnExist = true;
            }
        }
        /**
         * if column doesn't exist
         */
        if (!$columnExist) {
            return true;
        }

        $create = $this->raw_fetchRow('SHOW CREATE TABLE `'.$tableName.'`', 'Create Table');

        $alterDrop = array();
        $alterDrop[] = 'DROP COLUMN `'.$columnName.'`';

        /**
         * find foreign keys for column
         */
        preg_match_all('/CONSTRAINT `([^`]*)` FOREIGN KEY \(`([^`]*)`\)/', $create, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[2] == $columnName) {
                $alterDrop[] = 'DROP FOREIGN KEY `'.$match[1].'`';
            }
        }

        return $this->raw_fetchRow('ALTER TABLE `'.$tableName.'` ' . join(', ', $alterDrop));
    }

    /**
     * Creates and returns a new Zend_Db_Select object for this adapter.
     *
     * @return Varien_Db_Select
     */
    public function select()
    {
        return new Varien_Db_Select($this);
    }

}