<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Compressor;

use \AssetsManager\Compressor\AbstractCompressorAdapter;

/**
 * Compressor for assets optimization: combination (merge) and minification
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class Compressor
{

    /**
     * @var string  the final output
     */
    protected $output;

    /**
     * @var string  a raw header to include to final output
     */
    protected $raw_header;

    /**
     * @var array
     */
    protected $contents = array();

    /**
     * @var array   table of files to merge/minify
     */
    protected $files_stack;

    /**
     * @var string
     */
    protected $destination_dir;

    /**
     * @var string
     */
    protected $destination_file;

    /**
     * @var string
     */
    protected $web_root_path;

    /**
     * @var bool
     */
    protected $silent;

    /**
     * @var bool
     */
    protected $direct_output;

    /**
     * @var bool
     */
    protected $isCleaned_files_stack;

    /**
     * @var bool
     */
    protected $isInited;

    protected $__adapter_type;
    protected $__adapter_action;
    protected $__adapter;

    /**
     * Construction of a new Compressor object
     *
     * @param null|array $files_stack An array of the files stack to treat
     * @param null|string $destination_file The destination file name to write in
     * @param null|string $destination_dir The destination directory to force creating a file with the result
     * @param null|string $adapter_type The adapter type name (which will be guessed if empty)
     */
    public function __construct(
        array $files_stack = array(), $destination_file = null, $destination_dir = null, $adapter_type = null
    ) {
        $this->reset(true);
        if (!empty($files_stack))
            $this->setFilesStack( $files_stack );
        if (!empty($destination_file))
            $this->setDestinationFile( $destination_file );
        if (!empty($destination_dir))
            $this->setDestinationDir( $destination_dir );
        if (!empty($adapter_type))
            $this->setAdapterType( $adapter_type );
    }

    /**
     * Initialization : creation of the adapter
     *
     * @throws \RuntimeException if the adapter doesn't exist
     */
    protected function init()
    {
        if (true===$this->isInited) return;

        $this->_cleanFilesStack();

        if (empty($this->__adapter_type))
            $this->_guessAdapterType();

        if (!empty($this->__adapter_type)) {
            if (class_exists($this->__adapter_type)) {
                $this->__adapter = new $this->__adapter_type;
                if (!($this->__adapter instanceof AbstractCompressorAdapter)) {
                    throw new \LogicException(
                        sprintf('Compressor adapter must extend class "%s" (having object "%s")!', "AssetsManager\\Compressor\\AbstractCompressorAdapter", $this->__adapter_type)
                    );
                }
            } else {
                throw new \RuntimeException(
                    sprintf('Compressor adapter for type "%s" doesn\'t exist!', $this->__adapter_type)
                );
            }
        }

        $this->isInited = true;
    }

    /**
     * Reset all object properties to default or empty values
     *
     * @param bool $hard Reset all object properties (destination directory and web root included)
     * @return self
     */
    public function reset($hard = false)
    {
        $this->files_stack              = array();
        $this->contents                 = array();
        $this->silent                   = true;
        $this->direct_output            = false;
        $this->isCleaned_files_stack    = false;
        $this->isInited                 = false;
        if (true===$hard) {
            $this->raw_header           = '';
            $this->destination_dir      = '';
            $this->web_root_path        = null;
            $this->__adapter_type       = null;
            $this->__adapter_action     = 'merge';
            $this->__adapter            = null;
        }
        $this->resetOutput();
        return $this;
    }

    /**
     * Reset all object output properties to default or empty values
     *
     * @param bool $hard
     * @return self
     */
    public function resetOutput($hard = false)
    {
        $this->destination_file         = '';
        $this->output                   = '';
        return $this;
    }

// -------------------
// Getters / Setters
// -------------------

    /**
     * Set the silence object flag
     *
     * @param bool $silence True to avoid the class throwing exceptions
     * @return self
     */
    public function setSilent($silence = true)
    {
        $this->silent = (bool) $silence;
        return $this;
    }

    /**
     * Set the direct_output object flag
     *
     * @param bool $direct_output True to avoid writing of the compressed result in a file
     * @return self
     */
    public function setDirectOutput($direct_output = true)
    {
        $this->direct_output = (bool) $direct_output;
        return $this;
    }

    /**
     * Set the adapter type to use, this type will be guessed if not set
     *
     * @param string $type The type name
     * @return self
     */
    public function setAdapterType($type)
    {
        $this->__adapter_type = '\AssetsManager\Compressor\CompressorAdapter\\'.strtoupper($type);
        return $this;
    }

    /**
     * Set the adapter action to process and reset the output
     *
     * @param string $action The action name
     * @return self
     */
    public function setAdapterAction($action)
    {
        if (true===$this->isInited) {
            $this->resetOutput();
        }
        $this->__adapter_action = $action;
        return $this;
    }

    /**
     * Add a raw string output header
     *
     * @param   string  $str
     * @return  self
     */
    public function setRawHeader($str)
    {
        $this->raw_header = $str;
        return $this;
    }

    /**
     * Add raw contents to treat
     *
     * @param array $strs the contents to add
     * @return self
     */
    public function setContents(array $strs)
    {
        $this->contents[] = $strs;
        return $this;
    }

    /**
     * Add a raw content to treat
     *
     * @param   string      $str    the content to add
     * @param   null/string $index  the content index
     * @return self
     */
    public function addContent($str, $index = null)
    {
        if (!is_null($index)) {
            $this->contents[$index] = $str;
        } else {
            $this->contents[] = $str;
        }
        return $this;
    }

    /**
     * Get the contents to treat
     *
     * @return array
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Add a file to treat in the files stack
     *
     * @param string $file A file path to add in stack
     * @return self
     */
    public function addFile($file)
    {
        $this->files_stack[] = $file;
        return $this;
    }

    /**
     * Set a full files stack to treat
     *
     * @param array $files_stack An array of file paths to treat
     * @return self
     */
    public function setFilesStack(array $files_stack)
    {
        $this->isCleaned_files_stack = false;
        $this->files_stack = $files_stack;
        return $this;
    }

    /**
     * Get the files stack
     *
     * @return array The current files stack of the object
     */
    public function getFilesStack()
    {
        return $this->files_stack;
    }

    /**
     * Get the processed content
     *
     * @return string The content string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set the destination file to write the result in
     *
     * @param string $destination_file The file path or name to create and write in
     * @return self
     * @throws \InvalidArgumentException if the file name is not a string (and if $silent==false)
     */
    public function setDestinationFile($destination_file)
    {
        if (is_string($destination_file)) {
            $this->destination_file = $destination_file;
        } else {
            if (false===$this->silent) {
                throw new \InvalidArgumentException(
                    sprintf('[Compressor] Destination file name must be a string (got "%s")!', gettype($destination_file))
                );
            }
        }
        return $this;
    }

    /**
     * Get the destination file to write the result in
     *
     * @return string The file name to write in
     */
    public function getDestinationFile()
    {
        return $this->destination_file;
    }

    /**
     * Build a destination filename based on the files stack names
     *
     * @return string The file name built
     * @throws \RuntimeException if the files stack is empty (filename can not be guessed)
     */
    public function guessDestinationFilename()
    {
        if (!empty($this->files_stack)) {
            $this->_cleanFilesStack();
            $this->init();

            $_fs = array();
            foreach($this->files_stack as $_file) {
                $_fs[] = $_file->getFilename();
            }
            if (!empty($_fs)) {
                sort($_fs);
                $this->setDestinationFile(
                    md5( join('', $_fs) )
                    .'_'.$this->__adapter_action
                    .'.'.$this->__adapter->file_extension
                );
                return $this->getDestinationFile();
            }
        }
        if (false===$this->silent) {
            throw new \RuntimeException(
                '[Compressor] Destination filename can\'t be guessed because files stack is empty!'
            );
        }
        return null;
    }

    /**
     * Set the destination directory to write the destination file in
     *
     * @param string $destination_dir The directory path to create the file in
     * @return self
     * @throws \InvalidArgumentException if the directory name is not a string (and if $silent==false)
     */
    public function setDestinationDir($destination_dir)
    {
        if (is_string($destination_dir)) {
            $destination_dir = realpath($destination_dir);
            if (@file_exists($destination_dir) && @is_dir($destination_dir)) {
                $this->destination_dir = rtrim($destination_dir, '/').'/';
            } elseif (false===$this->silent) {
                throw new \InvalidArgumentException(
                    sprintf('[Compressor] Destination directory "%s" must exist!', $destination_dir)
                );
            }
        } else {
            if (false===$this->silent) {
                throw new \InvalidArgumentException(
                    sprintf('[Compressor] Destination directory must be a string (got "%s")!', gettype($destination_dir))
                );
            }
        }
        return $this;
    }

    /**
     * Get the destination directory to write the file in
     *
     * @return string The directory name to write in
     */
    public function getDestinationDir()
    {
        return $this->destination_dir;
    }

    /**
     * Set the web root path (the real path to clear in DestinationRealPath) to build web path of destination file
     *
     * @param string $path The realpath of the web root to clear it from DestinationRealPath to build DestinationWebPath
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setWebRootPath($path)
    {
        if (is_string($path)) {
            $path = realpath($path);
            if (@file_exists($path) && @is_dir($path)) {
                $this->web_root_path = rtrim($path, '/').'/';
            } elseif (false===$this->silent) {
                throw new \InvalidArgumentException(
                    sprintf('[Compressor] Web root path "%s" must exist!', $path)
                );
            }
        } else {
            if (false===$this->silent) {
                throw new \InvalidArgumentException(
                    sprintf('[Compressor] Web root path must be a string (got "%s")!', gettype($path))
                );
            }
        }
        return $this;
    }

    /**
     * Get the web root path
     *
     * @return string The current web root path set
     */
    public function getWebRootPath()
    {
        return $this->web_root_path;
    }

    /**
     * Get the destination file path ready for web inclusion
     *
     * @return string The file path to write in
     * @throws \LogicException if the web root path has not been set (and silent==false)
     */
    public function getDestinationWebPath()
    {
        if (!empty($this->web_root_path)) {
            return str_replace($this->web_root_path, '', $this->getDestinationRealPath());
        } elseif (false===$this->silent) {
            throw new \LogicException(
                '[Compressor] Can\'t create web path because "web_root_path" is not defined!'
            );
        }
        return null;
    }

    /**
     * Get the destination file absolute path
     *
     * @return string The file path to write in
     */
    public function getDestinationRealPath()
    {
        return rtrim($this->destination_dir, '/').'/'.$this->destination_file;
    }

    /**
     * Check if a destination file already exist for the current object
     *
     * @return bool True if the minified file exists
     */
    public function fileExists()
    {
        if (empty($this->destination_file))
            $this->guessDestinationFilename();
        return file_exists( $this->getDestinationRealPath() );
    }

    /**
     * Check if a destination file already exist for the current object and if it is fresher than sources
     *
     * @return bool True if the sources had been modified after minified file creation
     */
    public function mustRefresh()
    {
        if ($this->fileExists()) {
            $this->_cleanFilesStack();
            $_dest = new \SplFileInfo( $this->getDestinationRealPath() );
            if (!empty($this->files_stack)) {
                foreach($this->files_stack as $_file) {
                    if ($_file->getMTime() > $_dest->getMTime())
                        return true;
                }
                return false;
            }
        }
        return true;
    }

// -------------------
// Process stack
// -------------------

    /**
     * Prepare the current files/contents stack by populating the contents if needed
     *
     * @return self
     */
    public function prepare()
    {
        $files = $this->getFilesStack();
        if (!empty($files)) {
            foreach($files as $_file) {
                $this->addContent(
                    file_get_contents( $_file->getRealPath() ),
                    $_file->getPathname()
                );
            }
        }
    }

    /**
     * Process the current files stack
     *
     * @param bool $return define on `true` to return $this (default will return written string length)
     * @return int|self
     * @throws \RuntimeException
     */
    public function process($return = false)
    {
        $this->_cleanFilesStack();
        $this->init();

        if (empty($this->destination_file) && false===$this->direct_output)
            $this->guessDestinationFilename();

        if (!method_exists($this->__adapter, $this->__adapter_action)) {
            throw new \RuntimeException(
                sprintf('[Compressor] Action "%s" doesn\'t exist in "%s" adapter!', $this->__adapter_action, get_class($this->__adapter))
            );
        }

        if (false===$this->direct_output) {
            if (!$this->mustRefresh()) {
                $this->output = file_get_contents( $this->getDestinationRealPath() );
                return $this;
            }
        }

        $this->prepare();
        $stack = $this->getContents();
        $contents = array();
        foreach($stack as $_name=>$_content) {
            $contents[] = '';
            $contents[] = $this->__adapter->buildComment($_name);
            $contents[] = $this->__adapter->{$this->__adapter_action}($_content);
        }
        if (!empty($this->raw_header)) {
            $this->output = $this->raw_header."\n";
        }
        $this->output .= implode("\n", $contents);

        if (!empty($this->output) && false===$this->direct_output) {
            $this->_writeDestinationFile();
        }
        if (true===$return) {
            return strlen($this->output);
        } else {
            return $this;
        }
    }

    /**
     * Process a combination of the current files stack (alias of `merge`)
     *
     * @return self
     */
    public function combine()
    {
        return $this->merge();
    }

    /**
     * Process a combination of the current files stack
     *
     * @return self
     */
    public function merge()
    {
        $this->setAdapterAction('merge');
        return $this->process(true);
    }

    /**
     * Process a minification of the current files stack
     *
     * @return self
     */
    public function minify()
    {
        $this->setAdapterAction('minify');
        return $this->process(true);
    }

// -------------------
// Files stack cleaning
// -------------------

    /**
     * Rebuild the current files stack as an array of File objects
     *
     * @return void
     * @throws \RuntimeException if one of the files stack doesn't exist (and if $silent==false)
     */
    protected function _cleanFilesStack()
    {
        if (true===$this->isCleaned_files_stack) return;

        $new_stack = array();
        foreach($this->files_stack as $_file) {
            if (is_object($_file) && ($_file instanceof \SplFileInfo)) {
                $new_stack[] = $_file;
            } elseif (is_string($_file) && @file_exists($_file)) {
                $new_stack[] = new \SplFileInfo($_file);
            } elseif (false===$this->silent) {
                throw new \RuntimeException(
                    sprintf('[Compressor] Source to process "%s" not found!', $_file)
                );
            }
        }
        $this->files_stack = $new_stack;
        $this->isCleaned_files_stack = true;
    }

    /**
     * Guess the adapter type based on extension of the first file in stack
     *
     * @return bool True if the adapter type had been guessed
     * @throws \RuntimeException if no file was found in the stack
     */
    protected function _guessAdapterType()
    {
        $this->_cleanFilesStack();
        if (!empty($this->files_stack)) {
            $_fs = $this->files_stack;
            $_file = array_shift($_fs);
            $this->setAdapterType( $_file->getExtension() );
            return true;
        } elseif (false===$this->silent) {
            throw new \RuntimeException(
                '[Compressor] Trying to guess adapter from an empty files stack!'
            );
        }
        return false;
    }

// -------------------
// Utilities
// -------------------

    /**
     * Writes the compressed content in the destination file
     *
     * @return bool|string The filename if it has been created, false otherwise
     * @throws \RuntimeException if the file can't be written
     */
    protected function _writeDestinationFile()
    {
        if (empty($this->destination_file))
            $this->guessDestinationFilename();

        $content = $this->_getHeaderComment()."\n".$this->output;
        $dest_file = $this->getDestinationRealPath();
        if (false!==file_put_contents($dest_file, $content)) {
            return true;
        } else {
            if (false===$this->silent) {
                throw new \RuntimeException(
                    sprintf('[Compressor] Destination compressed file "%s" can\'t be written on disk!', $dest_file)
                );
            }
            return false;
        }
    }

    /**
     * Build the compressed content header comment information
     *
     * @return string A comment string to write in top of the content
     */
    protected function _getHeaderComment()
    {
        $this->init();
        return $this->__adapter->buildComment(
            sprintf('Generated by %s class on %s at %s', __CLASS__, date('Y-m-d'), date('H:i'))
        );
    }

}

