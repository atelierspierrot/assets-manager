<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Package\PresetAdapter;

use AssetsManager\Package\PresetAdapterInterface;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Javascript implements PresetAdapterInterface
{

    public static $defaults = array(
        'src'=>null,
        'type'=>'text/javascript',
        'position'=>0,
        'minified'=>false,
        'packed'=>false,
    );

    protected $data;
    protected $preset;
    protected $transformed_data;

    /**
     * @param array|string $data The preset data
     * @param object $preset AssetsManager\Package\AssetsPresetInterface
     */
    public function __construct(array $data, \AssetsManager\Package\AssetsPresetInterface $preset)
    {
        $this->data = $data;
        $this->preset = $preset;
    }

    /**
     * Return the parsed and tranformed statement array
     * @return array
     */
    public function getData()
    {
        $this->parse();
        return $this->transformed_data;
    }

    /**
     * Parse and tranform the preset statement to a ready-to-use information
     *
     * The statement string can be constructed as (without spaces):
     *
     *     position : info : src
     *
     * where `position` can be an integer or a string like `top` or `bottom`, `info` can be
     * a string like `pack` for packed javascript or `min` for already minified scripts and
     * `src` is the relative path of the file in the original package. Position is a [-1;100]
     * integer range where 100 is the top of the stack (first files to include).
     *
     * By default, position is 0 (the file is added to the stack), and the script is considered
     * not minified neither as packed.
     *
     * @return void
     */
    public function parse()
    {
        if (!empty($this->transformed_data)) return;

        $data = $this->data;
        if (count($data)===1 && !isset($data['src']) && isset($data[0])) {
            $data = array( 'src' => $data[0] );
        }
        $data = array_merge(self::$defaults, $data);

        $src = $data['src'];
        if (!empty($src)) {
            if (substr_count($src, ':')) {
                unset($data['src']);
                $substrs = explode(':', $src);
                if (count($substrs)>3) {
                    throw new \Exception(
                        sprintf('Statement js of preset "%s" is malformed (%s)!', $this->preset->getName(), $src)
                    );
                }
                foreach ($substrs as $substr) {
                    switch ($substr) {
                        case 'top': $data['position'] = 100; break;
                        case 'bottom': $data['position'] = -1; break;
                        case 'min': $data['minified'] = true; $data['packed'] = false; break;
                        case 'pack': $data['minified'] = false; $data['packed'] = true; break;
                        default:
                            if (is_numeric($substr)) {
                                if (!((-1 <= $substr) && (100 >= $substr))) {
                                    throw new \Exception(
                                        sprintf('A position must be in range [-1;100] for js statement of preset "%s" (got %s)!',
                                            $this->preset->getName(), $substr)
                                    );
                                }
                                $data['position'] = $substr;
                            } else {
                                if (empty($data['src'])) { 
                                    $data['src'] = $substr;
                                } else {
                                    throw new \Exception(
                                        sprintf('Misunderstood information "%s" for js statement of preset "%s"!',
                                            $substr, $this->preset->getName())
                                    );
                                }
                            }
                            break;
                    }
                }
            }
        } else {
            throw new \Exception(
                sprintf('No source file defined for statement js of preset "%s"!', $this->preset->getName())
            );
        }

        $this->transformed_data = $data;
        if (!\Library\Helper\Url::isUrl($this->transformed_data['src'])) {
            $this->transformed_data['src'] = $this->preset->findInPackage($this->transformed_data['src']);        
        }
    }

    /**
     * Returns the src path of the preset statement
     * @return string
     */
    public function __toString()
    {
        $this->parse();
        return $this->transformed_data['src'];
    }

    /**
     * Returns the full HTML `script`
     * @return string
     */
    public function __toHtml()
    {
        $this->parse();
        return \Library\Helper\Html::writeHtmlTag(
            'script', null, array(
                'src'=>$this->transformed_data['src'],
                'type'=>$this->transformed_data['type']
            ), true
        );
    }

}

// Endfile