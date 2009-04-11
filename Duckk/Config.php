<?php

class Duckk_Config
{
    /**
     * Store configs here - woot
     *
     * @var array
     */
    private static $configs = array();

    /**
     * Configuration info
     *
     * @var array
     */
    private $config = array();

    /**
     * Is APC enabled?
     *
     * @var bool
     */
    private static $isAPCEnabled = null;

    /**
     * Prefix for keys used when storing configs in APC
     *
     * @var string
     */
    const APC_PREFIX = 'duckk.config.';

    /**
     * TTL for configs in APC
     *
     * 5 minutes
     *
     * @var string
     */
    const APC_TTL = 300;

    /**
     * Protected constructor
     *
     * The constructor will check if apc is enabled and use it if it can. If
     * it can't use APC (not installed or disabled) then it will go ahead and
     * make sure all the files get parsed and will store the config as a private
     * member.
     *
     * @param string $configFilePath The path to the config file
     *
     * @return void
     */
    protected function __construct($configFilePath)
    {
        $apcKey = self::APC_PREFIX . $configFilePath;

        if (self::isAPCEnabled() && apc_fetch($apcKey) !== false) {
            $this->config = apc_fetch($apcKey);
        } else {
            $pathInfo  = pathinfo($configFilePath);
            $nameParts = array_reverse(explode('.', $pathInfo['filename']));
            $numParts  = count($nameParts);
            $allConfig = array();

            for ($i = $numParts - 1; $i >= 0; $i--) {
                $parts    = array_reverse(array_slice($nameParts, 0, $numParts - $i));
                $fileName = implode('.', $parts) . '.ini';
                $path     = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;

                // decide which parse<xxx> method to called based on the file extension
                $allConfig[$path] = call_user_func(
                    array($this, 'parse' . strtoupper($pathInfo['extension'])),
                    $path
                );
            }

            $this->config = (empty($allConfig))
                ? array()
                : $this->mergeConfigs($allConfig);

            if (self::isAPCEnabled()) {
                apc_store($apcKey, $this->config, self::APC_TTL);
            }
        }
    }

    /**
     * Merge an array of configuration info
     *
     * We're not always using array_merge here for a specific reason...
     * If you do the following:
     * <code>
     *  $a = array('db' => array('username' => 'root', 'password' => 'secret'));
     *  $b = array('db' => array('username' => 'app_user'));
     *  $c = array_merge($a, $b);
     * </code>
     *
     * array_merge gives you:
     * <pre>
     * Array
     * (
     *  [db] => Array
     *      (
     *          [username] => app_user
     *      )
     * )
     * </pre>
     *
     * Notice we lost the 'password' from $a. That sucks so this is how I dealt
     * with that problem. if you don't like this then override this method.
     *
     * @param array $configs An array of arrays. Each array should contain the
     *  results from from one of the parse<xxx> methods
     *
     * @return array The merged configuration
     */
    protected function mergeConfigs(array $configs, $userArrayMerge = true)
    {
        if (empty($configs)) {
            return array();
        }

        $rtn = array();

        foreach ($configs as $path => $config) {
            if (empty($config)) {
                continue;
            }

            $old  = $rtn;
            $rtn  = ($userArrayMerge)
                ? array_merge($rtn, $config)
                : $config + $rtn;

            foreach ($config as $k => $v) {
                if (is_array($v) && isset($old[$k])) {
                    $r       = $this->mergeConfigs(array($old[$k], $config[$k]), false);
                    $rtn[$k] = $r;
                }
            }

            unset($old);
        }

        return $rtn;
    }

    /**
     * Parse an ini file
     *
     * This pretty much just calls parse_ini_file with $process_sections set to
     * true.
     *
     * Feel free to override this to do special stuff
     *
     * @param string $path Path to the ini file to parse
     *
     * @return array The configuration info an an array
     */
    protected function parseINI($path)
    {
        if (! file_exists($path)) {
            return null;
        }

        return parse_ini_file($path, true);
    }

    /**
     * Get an instance of a configuration
     *
     * @return Duckk_Config
     */
    public static function getInstance($configFilePath)
    {
        if (! isset(self::$configs[$configFilePath])) {
            self::$configs[$configFilePath] = new Duckk_Config($configFilePath);
        }

        return self::$configs[$configFilePath];
    }

    /**
     * Get a config value
     *
     * @param string $paramName The config param name
     * @param string $section   The config section's name
     *
     * @return mixed The config value or null if empty or unset
     */
    public function get($paramName, $section = null)
    {
        if ($section != null) {
            return (isset($this->config[$section], $this->config[$section][$paramName]))
                ? $this->config[$section][$paramName]
                : null;
        } else {
            return ($this->config[$paramName])
                ? $this->config[$paramName]
                : null;
        }
    }

    /**
     * Find out if APC is enabled
     *
     * @return bool
     */
    public static function isAPCEnabled()
    {
        if (self::$isAPCEnabled === null) {
            $keySuffix = (PHP_SAPI == 'cli') ? '_cli' : '';
            $isEnabled = (bool) ini_get('apc.enabled' . $keySuffix);

            self::$isAPCEnabled = $isEnabled;
        }

        return self::$isAPCEnabled;
    }
}


?>