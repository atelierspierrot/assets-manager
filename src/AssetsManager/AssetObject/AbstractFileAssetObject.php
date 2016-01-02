<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\AssetObject;

use \AssetsManager\AssetObject\AbstractAssetObject;
use \AssetsManager\Compressor\Compressor;
use \AssetsManager\Loader;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
abstract class AbstractFileAssetObject
    extends AbstractAssetObject
{

    /**
     * The merger class object
     *
     * @var \AssetsManager\Compressor\Compressor
     */
    protected $__compressor;

    /**
     * Constructor
     *
     * @param \AssetsManager\Loader $_loader The whole template object
     */
    public function __construct(Loader $_loader)
    {
        parent::__construct($_loader);
        $this->__compressor     = new Compressor;
        $this->__compressor
            ->setWebRootPath( $this->__assets_loader->getDocumentRoot() )
            ->setDestinationDir( $this->__assets_loader->getCachePath() );
        $this->init();
    }

    /**
     * Merge a stack of files
     *
     * @param array $stack The stack to clean
     * @param bool $silent Set up the Compressor $silence flag (default is true)
     * @param bool $direct_output Set up the Compressor $direct_output flag (default is false)
     * @return array Return the extracted stack
     * @throws \Exception any caught exception
     */
    protected function mergeStack(array $stack, $silent = true, $direct_output = false)
    {
        $this->__compressor->reset();
        if (false===$silent)
            $this->__compressor->setSilent(false);
        if (true===$direct_output)
            $this->__compressor->setDirectOutput(true);

        try {
            $this->__compressor
                ->setFilesStack( $stack )
                ->merge();
            return $this->__compressor
                ->getDestinationWebPath();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Minify a stack of files
     *
     * @param array $stack The stack to clean
     * @param bool $silent Set up the Compressor $silence flag (default is true)
     * @param bool $direct_output Set up the Compressor $direct_output flag (default is false)
     * @return array Return the extracted stack
     * @throws \Exception any caught exception
     */
    protected function minifyStack(array $stack, $silent = true, $direct_output = false)
    {
        $this->__compressor->reset();
        if (false===$silent)
            $this->__compressor->setSilent(false);
        if (true===$direct_output)
            $this->__compressor->setDirectOutput(true);

        try {
            $this->__compressor
                ->setFilesStack( $stack )
                ->minify();
            return $this->__compressor
                ->getDestinationWebPath();
        } catch (\Exception $e) {
            throw $e;
        }
    }

}

