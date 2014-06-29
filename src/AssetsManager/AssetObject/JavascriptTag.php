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
use \AssetsManager\AssetObject\AssetObjectInterface;
use \Library\Helper\Html;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class JavascriptTag
    extends AbstractAssetObject
    implements AssetObjectInterface
{

// ------------------------
// AssetObjectInterface
// ------------------------

    /**
     * Init the object
     */
    public function init()
    {
        $this->reset();
    }
    
    /**
     * Reset the object
     *
     * @return self 
     */
    public function reset()
    {
        $this->__registry->js_entries = array();
        return $this;
    }
    
    /**
     * Add a link header attribute
     *
     * @param array $tag_content The link tag attributes
     * @return self 
     */
    public function add($tag_content)
    {
        if (!empty($tag_content)) {
            $this->__registry->addEntry( $tag_content, 'js_entries');
        }
        return $this;
    }
    
    /**
     * Set a full links header stack
     *
     * @param array $tags An array of tags definitions
     * @return self 
     * @see self::add()
     */
    public function set(array $tags)
    {
        if (!empty($tags)) {
            foreach($tags as $_tag) {
                $this->add( $_tag );
            }
        }
        return $this;
    }
    
    /**
     * Get the header link tags stack
     *
     * @return array The stack of header link tags
     */
    public function get()
    {
        return $this->__registry->getEntry( 'js_entries', false, array() );
    }
    
    /**
     * Write the Asset Object strings ready for view display
     *
     * @param string $mask A mask to write each line via "sprintf()"
     * @return string The string to display fot this template object
     */
    public function write($mask = '%s')
    {
        $content='';
        foreach($this->get() as $entry) {
            $content .= $entry."\n";
        }
        $str = sprintf($mask, Html::writeHtmlTag( 'script', $content ));
        return $str;
    }

}

// Endfile