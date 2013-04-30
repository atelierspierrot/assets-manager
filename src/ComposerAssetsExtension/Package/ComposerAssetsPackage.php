<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace ComposerAssetsExtension\Package;

use Composer\Package\Package;
use ComposerAssetsExtension\Package\AbstractAssetsPackage;

/**
 * This class handles dependencies packages assets from a global root directory.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class ComposerAssetsPackage extends Package
{

    protected $assetsDir;
    protected $assetsVendorDir;
    protected $documentRoot;
    protected $presets;

    public function __construct($name, $version, $prettyVersion)
    {
        parent::__construct($name, $version, $prettyVersion);
        $extra = $this->getExtra();
        $this->setAssetsDir(
            isset($extra['assets-dir']) ? $extra['assets-dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_DIR
        );
        $this->setAssetsVendorDir(
            isset($extra['assets-vendor-dir']) ? $extra['assets-vendor-dir'] : AbstractAssetsPackage::DEFAULT_ASSETS_VENDOR_DIR
        );
        $this->setDocumentRoot(
            isset($extra['document-root']) ? $extra['document-root'] : AbstractAssetsPackage::DEFAULT_DOCUMENT_ROOT
        );
        $this->setPresets(
            isset($extra['assets-presets']) ? $extra['assets-presets'] : AbstractAssetsPackage::DEFAULT_PRESETS
        );
    }

// -------------------------
// Setters / Getters
// -------------------------

    /**
     * @param string $path
     */
    public function setAssetsDir($path)
    {
        $this->assetsDir = $path;
    }

    /**
     * @return string
     */
    public function getAssetsDir()
    {
        return $this->assetsDir;
    }
    
    /**
     * @param string $path
     */
    public function setAssetsVendorDir($path)
    {
        $this->assetsVendorDir = $path;
    }

    /**
     * @return string
     */
    public function getAssetsVendorDir()
    {
        return $this->assetsVendorDir;
    }
    
    /**
     * @param string $path
     */
    public function setDocumentRoot($path)
    {
        $this->documentRoot = $path;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }
    
    /**
     * @param array $presets
     */
    public function setPresets($presets)
    {
        $this->presets = $presets;
    }

    /**
     * @return array
     */
    public function getPresets()
    {
        return $this->presets;
    }
    
}

// Endfile