<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\AssetObject;

use \AssetsManager\AssetObject\AssetObjectInterface;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
interface FileAssetObjectInterface
    extends AssetObjectInterface
{

    /**
     * Add an entry if file exists
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function addIfExists( $arg );

    /**
     * Merge the files if possible and loads them in files_merged stack
     * @return self Must return the object itself for method chaining
     */
    public function merge();

    /**
     * Add an merged file
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function addMerged( $arg );

    /**
     * Set a stack of merged files
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function setMerged( array $arg );

    /**
     * Get the stack of merged files
     */
    public function getMerged();

    /**
     * Write merged versions of the files stack in the cache directory
     * @param string $mask A mask to write each line via "sprintf()"
     * @return string Must return a string ready to write
     */
    public function writeMerged( $mask = '%s' );

    /**
     * Minify the files if possible and loads them in files_minified stack
     * @return self Must return the object itself for method chaining
     */
    public function minify();

    /**
     * Add an minified file
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function addMinified( $arg );

    /**
     * Set a stack of minified files
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function setMinified( array $arg );

    /**
     * Get the stack of minified files
     */
    public function getMinified();

    /**
     * Write minified versions of the files stack in the cache directory
     * @param string $mask A mask to write each line via "sprintf()"
     * @return string Must return a string ready to write
     */
    public function writeMinified( $mask = '%s' );

}

