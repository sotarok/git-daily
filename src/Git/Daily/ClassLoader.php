<?php
/**
 *
 * Forked from SplClassLoader only for PHP 5.2.
 * @see https://gist.github.com/221634
 */

class Git_Daily_ClassLoader
{
    private $prefix;
    private $include_path;

    /**
     * Creates a new <tt>SplClassLoader</tt> that loads classes of the
     * specified namespace.
     *
     * @param string $ns The namespace to use.
     */
    public function __construct($prefix = null, $include_path = null)
    {
        $this->prefix = $prefix;
        $this->include_path = $include_path;
    }

    /**
     * Sets the base include path for all class files in the namespace of this class loader.
     *
     * @param string $include_path
     */
    public function setIncludePath($include_path)
    {
        $this->include_path = $include_path;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $include_path
     */
    public function getIncludePath()
    {
        return $this->include_path;
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class_name The name of the class to load.
     * @return void
     */
    public function loadClass($class_name)
    {
        if (null === $this->prefix
            || $this->prefix === substr($class_name, 0, strlen($this->prefix)))
        {
            $fn = $this->getFilePath($class_name);

            if (file_exists($fn)) {
                require $fn;
            }
        }
    }

    /**
     * @param  string   $class_name     class name
     * @return string   file path
     */
    public function getFilePath($class_name)
    {
        $fn = $this->include_path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
        return $fn;
    }

    /**
     * @static
     * @return string
     */
    public static function getSrcPath()
    {
        return dirname(dirname(dirname(__FILE__)));
    }
}
