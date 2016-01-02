<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Compressor;

/**
 * Compressor Adapters interface
 *
 * All Compressor adapters must extend this abstract class and defines its abstract methods
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
abstract class AbstractCompressorAdapter
    implements CompressorAdapterInterface
{

    /**
     * The file extension for destination file guessing
     * @var string
     */
    public $file_extension;

}

