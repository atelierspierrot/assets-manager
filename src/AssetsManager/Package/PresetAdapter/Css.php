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
use \AssetsManager\Package\AssetsPresetInterface;
use \AssetsManager\Package\Preset;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class Css
    implements PresetAdapterInterface
{

    /**
     * @var array
     */
    public static $defaults = array(
        'src'       => null,
        'type'      => 'text/css',
        'rel'       => 'stylesheet',
        'media'     => 'all',
        'position'  => 0,
        'minified'  => false
    );

    /**
     * @var array|string
     */
    protected $data;

    /**
     * @var \AssetsManager\Package\AssetsPresetInterface
     */
    protected $preset;

    /**
     * @var array
     */
    protected $transformed_data;

    /**
     * @param array|string $data The preset data
     * @param \AssetsManager\Package\AssetsPresetInterface $preset
     */
    public function __construct(array $data, AssetsPresetInterface $preset)
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
        } catch (\Exception $e) {
            throw $e;
        }
        return $this->transformed_data;
    }

    /**
     * Parse and transform the preset statement to a ready-to-use information
     *
     * The statement string can be constructed as (without spaces):
     *
     *     position : info : media : src
     *
     * where `position` can be an integer or a string like `top` or `bottom`, `info` can be
     * `min` for already minified stylesheets, `media` can be any media string (no validation) and
     * `src` is the relative path of the file in the original package. Position is a [-1;100]
     * integer range where 100 is the top of the stack (first files to include).
     *
     * By default, position is 0 (the file is added to the stack), and the script is considered
     * not minified neither as packed.
     *
     * @return void
     * @throws \Exception if one of the statements is malformed
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
                if (count($substrs)>4) {
                    throw new \Exception(
                        sprintf('Statement css of preset "%s" is malformed (%s)!', $this->preset->getName(), $src)
                    );
                }
                foreach ($substrs as $substr) {
                    switch ($substr) {
                        case 'min': $data['minified'] = true; break;
                        case 'first': $data['position'] = Preset::FILES_STACK_FIRST; break;
                        case 'last': $data['position'] = Preset::FILES_STACK_LAST; break;
                        default:
                            if (is_numeric($substr)) {
                                if (!((-1 <= $substr) && (100 >= $substr))) {
                                    throw new \Exception(
                                        sprintf('A position must be in range [-1;100] for css statement of preset "%s" (got %s)!',
                                            $this->preset->getName(), $substr)
                                    );
                                }
                                $data['position'] = $substr;
                            } else {
                                if (empty($data['src'])) { 
                                    $data['src'] = $substr;
                                } else {
                                    $data['media'] = $substr;
                                }
                            }
                            break;
                    }
                }
            }
        } else {
            throw new \Exception(
                sprintf('No source file defined for statement css of preset "%s"!', $this->preset->getName())
            );
        }

        $this->transformed_data = $data;
        if ( ! \AssetsManager\Loader::isUrl($this->transformed_data['src'])) {
            $this->transformed_data['src'] = $this->preset->findInPackage($this->transformed_data['src']);        
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
            $this->parse();
            $str = $this->transformed_data['src'];
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
            $this->parse();
            $str = \Library\Helper\Html::writeHtmlTag(
                'link', null, array(
                    'src'=>$this->transformed_data['src'],
                    'type'=>$this->transformed_data['type'],
                    'rel'=>$this->transformed_data['rel'],
                    'media'=>$this->transformed_data['media']
                ), true
            );
        } catch (\Exception $e) {
            $str = $e->getMessage();
        }
        return $str;
    }

}

// Endfile