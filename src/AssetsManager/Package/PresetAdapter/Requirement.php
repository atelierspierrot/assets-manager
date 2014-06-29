<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Package\PresetAdapter;

use \AssetsManager\Package\PresetAdapterInterface;
use \AssetsManager\Package\PresetInterface;
use \AssetsManager\Loader;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class Requirement
    implements PresetAdapterInterface
{

    /**
     * @var array|string
     */
    protected $data;

    /**
     * @var \AssetsManager\Package\PresetInterface
     */
    protected $preset;

    /**
     * @var array
     */
    protected $dependencies;

    /**
     * @param array|string $data The preset data
     * @param \AssetsManager\Package\PresetInterface $preset
     */
    public function __construct(array $data, PresetInterface $preset)
    {
        $this->data     = $data;
        $this->preset   = $preset;
    }

    /**
     * Return the parsed and transformed statement array
     *
     * @return  array
     * @throws  \Exception : any caught exception thrown by `self::parse()`
     * @see     self::parse()
     */
    public function getData()
    {
        try {
            $this->parse();
            $organized_statements = array();
            foreach ($this->dependencies as $name=>$preset) {
                foreach ($preset->getStatements() as $type=>$statements) {
                    if (!isset($organized_statements[$type])) {
                        $organized_statements[$type] = array();
                    }
                    foreach ($statements as $statement) {
                        $organized_statements[$type][] = $statement;
                    }
                }
            }
            foreach ($organized_statements as $type=>$stacks) {
                $organized_statements[$type] = $preset->getOrderedStatements($stacks);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $organized_statements;
    }

    /**
     * @return void
     * @throws \Exception caught calling required preset
     */
    public function parse()
    {
        if (!empty($this->dependencies)) return;

        $this->dependencies = array();
        $data = !is_array($this->data) ? array( $this->data ) : $this->data;
        foreach ($data as $preset_requires) {
            try {
                $preset = Loader::findPreset($preset_requires);
            } catch(\Exception $e) {
                throw new \Exception(
                    sprintf('An error occured trying to load a dependency for preset "%s" : "%s"',
                        $this->preset->getName(), $e->getMessage())
                );
            }
            $this->dependencies[$preset_requires] = $preset;
        }
    }

    /**
     * Returns the src path of the preset statement
     *
     * @return  string
     */
    public function __toString()
    {
        try {
            $str = '';
            foreach ($this->getData() as $type=>$statements) {
                foreach ($statements as $statement) {
                    $str .= $statement->__toString().' ';
                }
            }
        } catch (\Exception $e) {
            $str = $e->getMessage();
        }
        return $str;
    }

    /**
     * Returns the full HTML `script`
     *
     * @return string
     */
    public function __toHtml()
    {
        try {
            $str = '';
            foreach ($this->getData() as $type=>$statements) {
                foreach ($statements as $statement) {
                    $str .= $statement->__toHtml();
                }
            }
        } catch (\Exception $e) {
            $str = $e->getMessage();
        }
        return $str;
    }
}

// Endfile