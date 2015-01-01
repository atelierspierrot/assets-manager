<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (ↄ) 2013-2015 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AssetsManager\Package;

use \InvalidArgumentException;
use \AssetsManager\Config;
use \AssetsManager\Package\AssetsPackage;
use \AssetsManager\Package\AssetsPackageInterface;
use \AssetsManager\Package\AssetsPresetInterface;

/**
 * Preset
 *
 * This class is the "presets" manager for predefined assets plugins to use in views with
 * the `_use()` method.
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class Preset
    implements AssetsPresetInterface
{

    /**
     * First positioned file in a file types stack
     */
    const FILES_STACK_FIRST = 100;

    /**
     * Last positioned file in a file types stack
     */
    const FILES_STACK_LAST  = -1;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \AssetsManager\Package\AssetsPackage
     */
    protected $package;

    /**
     * @var array
     */
    protected $_statements;

    /**
     * @param string $preset_name
     * @param array $preset_data
     * @param \AssetsManager\Package\AssetsPackageInterface $package
     */
    public function __construct($preset_name, array $preset_data, AssetsPackageInterface $package)
    {
        $this
            ->setName($preset_name)
            ->setData($preset_data)
            ->setPackage($package);
    }

    /**
     * Parse and load an assets file in a template object
     *
     * @param string $path
     * @return void
     */
    public function findInPackage($path)
    {
        return $this->getPackage()->findInPackage($path);
    }

    /**
     * Automatic assets loading from an Assets package declare in a `composer.json`
     *
     * @return void
     * @throws \DomainException if the preset doesn't exist or doesn't implement required interface
     * @throws \LogicException if the statement doesn't exist
     */
    public function load()
    {
        if (!empty($this->_statements)) return;

        foreach ($this->data as $type=>$item) {
            if (!is_array($item)) $item = array( $item );
            $use_statements = Config::get('use-statements');
            $adapter_name = isset($use_statements[$type]) ? $use_statements[$type] : null;
            if (!empty($adapter_name)) {
                $cls_name = 'AssetsManager\Package\PresetAdapter\\'.$adapter_name;
                if (@class_exists($cls_name)) {
                    $interfaces = class_implements($cls_name);
                    $config_interface = Config::getInternal('assets-preset-adapter-interface');
                    if (in_array($config_interface, $interfaces)) {
                        if (!isset($this->_statements[$type])) {
                            $this->_statements[$type] = array();
                        }
                        foreach ($item as $item_ctt) {
                            if (!is_array($item_ctt)) $item_ctt = array( $item_ctt );
                            $statement = new $cls_name($item_ctt, $this);
                            $statement->parse();
                            $this->_statements[$type][] = $statement;
                        }
                    } else {
                        throw new \DomainException(
                            sprintf('Preset statement class "%s" must implement interface "%s"!',
                                $cls_name, $config_interface)
                        );
                    }
                } else {
                    throw new \DomainException(
                        sprintf('Preset statement class "%s" not found!', $cls_name)
                    );
                }
            } else {
                throw new \LogicException(
                    sprintf('Unknown preset statement type "%s" in preset "%s"!', $type, $this->getName())
                );
            }
        }
    }

    /**
     * @return  string
     * @throws  \Exception : any caught exception during `self::load()`
     * @see     self::load()
     */
    public function __toHtml()
    {
        try {
            $str = '';
            foreach ($this->getOrganizedStatements() as $type=>$statements) {
                foreach ($statements as $statement) {
                    $str .= $statement->__toHtml().PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            $str = $e->getMessage();
        }
        return $str;
    }

// -------------------------
// Setters / Getters
// -------------------------

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \AssetsManager\Package\AssetsPackage $package
     * @return self
     */
    public function setPackage(AssetsPackage $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return \AssetsManager\Package\AssetsPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get the preset's statements array
     *
     * @return array
     * @throws  \Exception : any caught exception during `self::load()`
     * @see     self::load()
     */
    public function getStatements()
    {
        try {
            $this->load();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this->_statements;
    }

    /**
     * Get one preset's statement entry
     *
     * @param   string  $name   The statement name
     * @return  array|null
     * @throws  \Exception : any caught exception during `self::load()`
     * @see     self::load()
     */
    public function getStatement($name)
    {
        try {
            $this->load();
        } catch (\Exception $e) {
            throw $e;
        }
        return isset($this->_statements[$name]) ? $this->_statements[$name] : null;
    }

// -------------------------
// Statements management
// -------------------------

    /**
     * Organize each statements item by position & requirements
     *
     * @return  array
     * @throws  \Exception : any caught exception during `self::load()`
     * @see     self::load()
     */
    public function getOrganizedStatements()
    {
        $organized_statements = array();
        if (empty($this->_statements)) {
           $this->load();
           try {
               $this->load();
           } catch (\Exception $e) {
               throw $e;
           }
        }
        $statements = $this->_statements;

        if (!empty($statements['require'])) {
            foreach ($statements['require'] as $statement) {
                $data = $statement->getData();
                foreach ($statement->getData() as $type=>$stack) {
                    if (!isset($statements[$type])) {
                        $statements[$type] = array();
                    }
                    $statements[$type] = array_merge($statements[$type], $stack);
                }
            }
            unset($statements['require']);
        }
        
        foreach ($statements as $type=>$statement_stack) {
            if (!isset($organized_statements[$type])) {
                $organized_statements[$type] = array();
            }
            foreach ($statement_stack as $statement) {
                $organized_statements[$type][] = $statement;
            }
        }

        foreach ($organized_statements as $type=>$stacks) {
            $organized_statements[$type] = $this->getOrderedStatements($stacks);
        }

        return $organized_statements;
    }

    /**
     * Internal function to order a statements stack
     *
     * @param array $statements
     * @return array
     */
    public static function getOrderedStatements(array $statements)
    {
        $ordered = array();
        foreach ($statements as $index=>$statement) {
            $data = $statement->getData();
            $ordered[$index] = isset($data['position']) ? $data['position'] : count($ordered)+1;
        }
        array_multisort($ordered, SORT_DESC, SORT_NUMERIC, $statements);
        return $statements;
    }

}

// Endfile