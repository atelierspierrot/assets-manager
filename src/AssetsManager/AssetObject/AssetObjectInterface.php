<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\AssetObject;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
interface AssetObjectInterface
{

    /**
     * Init the object
     */
    public function init();

    /**
     * Reset the object
     * @return self Must return the object itself for method chaining
     */
    public function reset();

    /**
     * Add an entry
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function add( $arg );

    /**
     * Set a stack of entries
     * @param mixed $arg
     * @return self Must return the object itself for method chaining
     */
    public function set( array $arg );

    /**
     * Get the stack of entries
     */
    public function get();

    /**
     * Write the Asset Object strings ready for view display
     * @param string $mask A mask to write each line via "sprintf()"
     * @return string Must return a string ready to write
     */
    public function write( $mask = '%s' );

}

// Endfile