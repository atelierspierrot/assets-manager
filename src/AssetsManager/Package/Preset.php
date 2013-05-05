<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager\Package;

use InvalidArgumentException;

use AssetsManager\Config,
    AssetsManager\Package\AssetsPackage,
    AssetsManager\Package\AssetsPackageInterface,
    AssetsManager\Package\AssetsPresetInterface;

/**
 * Preset
 *
 * This class is the "presets" manager for predefined assets plugins to use in views with
 * the `_use()` method.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Preset implements AssetsPresetInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var AssetsManager\Package\AssetsPackage
     */
    protected $package;

    /**
     * @var array
     */
    protected $_statements;

    /**
     * @param string $package_name
     * @param array $package_data
     * @param object $package AssetsManager\Package\AssetsPackage
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
	 * @param string $package_name The name of the package to use
	 * @return void
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
                            sprintf('Preset statement class "%s" must implements interface "%s"!',
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
     * @return string
     */
    public function __toHtml()
    {
        $str = '';
        foreach ($this->getOrganizedStatements() as $type=>$statements) {
            foreach ($statements as $statement) {
                $str .= $statement->__toHtml();
            }
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
     * @param object $package AssetsManager\Package\AssetsPackage
     * @return self
     */
    public function setPackage(AssetsPackage $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return object AssetsManager\Package\AssetsPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    public function getStatements()
    {
        $this->load();
        return $this->_statements;
    }

    public function getStatement($name)
    {
        $this->load();
        return isset($this->_statements[$name]) ? $this->_statements[$name] : null;
    }

// -------------------------
// Statements management
// -------------------------

    public function getOrganizedStatements()
    {
        $organized_statements = array();
	    if (empty($this->_statements)) $this->load();
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

    public static function getOrderedStatements(array $statements)
    {
        $ordered = array();
        foreach ($statements as $index=>$statement) {
            $data = $statement->getData();
            $ordered[$index] = $data['position'];
        }
        array_multisort($ordered, SORT_DESC, SORT_NUMERIC, $statements);
        return $statements;
    }

}

// Endfile