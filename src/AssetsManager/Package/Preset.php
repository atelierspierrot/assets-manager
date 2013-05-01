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

use AssetsManager\Package\AssetsPackage;

/**
 * Preset
 *
 * This class is the "presets" manager for predefined assets plugins to use in views with
 * the `_use()` method.
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Preset
{

    /**
     * Composition of a `assets_presets` statement in `composer.json`
     * @static array
     */
    public static $use_statements = array( 'css', 'js', 'jsfiles_footer', 'jsfiles_header', 'require' );

    /**
     * @var string
     */
    protected $preset_name;

    /**
     * @var array
     */
    protected $preset_data;

    /**
     * @var Assets\Package
     */
    protected $package;

    /**
     * @param string $package_name
     * @param object $loader Assets\Loader
     * @throws `InvalidArgumentException` if the preset can't be found
     */
    public function __construct($preset_name, AssetsPackage $package)
    {
        $this->package = $package;
        $this->preset_name = $preset_name;

        $data = $this->_findPresetData();
        if (!empty($data)) {
            $this->cluster = Cluster::newClusterFromAssetsLoader($this->assets_loader);
            $this->cluster->loadClusterFromArray(
                $this->_findPresetPackageData()
            );
            $this->preset_data = $data;
        } else {
            throw new InvalidArgumentException(
                sprintf('Unknown preset "%s" !', $this->preset_name)
            );
        }
    }

    /**
     * Parse and load an assets file in a template object
     *
     * @param string $path
     * @param object $object The template object to work on
     * @return void
     */
    public function parse($path, AbstractTemplateObject $object)
    {
        $package = $this->_findPresetPackageName();
        if (substr($path, 0, strlen('min:'))=='min:') {
            $file_path = $this->assets_loader->findInPackage(substr($path, strlen('min:')), $package);
            $object->addMinified($file_path);
        } elseif (substr($path, 0, strlen('pack:'))=='pack:') {
            $file_path = $this->assets_loader->findInPackage(substr($path, strlen('pack:')), $package);
            $object->addMinified($file_path);
        } else {
            $file_path = $this->assets_loader->findInPackage($path, $package);
            $object->add($file_path);
        }
    }

	/**
	 * Automatic assets loading from an Assets package declare in a `composer.json`
	 *
	 * @param string $package_name The name of the package to use
	 * @return void
	 */
	public function load()
	{
        foreach ($this->preset_data as $type=>$data) {
            if ('css'===$type) {
                foreach ($data as $path) {
                    $this->parse($path, $this->template_engine->getTemplateObject('CssFile'));
                }
            } elseif ('jsfiles_header'===$type) {
                foreach ($data as $path) {
                    $this->parse($path, $this->template_engine->getTemplateObject('JavascriptFile', 'jsfiles_header'));
                }
            } elseif ('jsfiles_footer'===$type) {
                foreach ($data as $path) {
                    $this->parse($path, $this->template_engine->getTemplateObject('JavascriptFile', 'jsfiles_footer'));
                }
            }
        }
	}

    /**
     * Find the data array defining a preset from the object `$preset_name`
     *
     * @return array|null
     */
    protected function _findPresetData()
    {
        foreach ($this->assets_loader->getAssetsDb() as $package=>$config) {
            if (!empty($config['assets_presets']) && array_key_exists($this->preset_name, $config['assets_presets'])) {
                return $config['assets_presets'][$this->preset_name];
            }
        }
        return null;
    }

    /**
     * Find the data array defining the package of a preset from the object `$preset_name`
     *
     * @return array|null
     */
    protected function _findPresetPackageData()
    {
        foreach ($this->assets_loader->getAssetsDb() as $package=>$config) {
            if (!empty($config['assets_presets']) && array_key_exists($this->preset_name, $config['assets_presets'])) {
                return $config;
            }
        }
        return null;
    }

    /**
     * Find the name of the package of a preset from the object `$preset_name`
     *
     * @return string|null
     */
    protected function _findPresetPackageName()
    {
        foreach ($this->assets_loader->getAssetsDb() as $package=>$config) {
            if (!empty($config['assets_presets']) && array_key_exists($this->preset_name, $config['assets_presets'])) {
                return $package;
            }
        }
        return null;
    }

}

// Endfile