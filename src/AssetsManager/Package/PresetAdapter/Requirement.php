<?php
/**
 * This file is part of the AssetsManager package.
 *
 * Copyleft (ↄ) 2013-2015 Pierre Cassat <me@e-piwi.fr> and contributors
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
 *
 * The source code of this package is available online at 
 * <http://github.com/atelierspierrot/assets-manager>.
 */

namespace AssetsManager\Package\PresetAdapter;

use \AssetsManager\Package\PresetAdapterInterface;
use \AssetsManager\Package\PresetInterface;
use \AssetsManager\Loader;

/**
 * @author  piwi <me@e-piwi.fr>
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
                    sprintf('An error occurred trying to load a dependency for preset "%s" : "%s"',
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