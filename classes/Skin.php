<?php namespace Summer\Login\Classes;

use App;
use Summer\Login\Models\LoginSkinDataModel;
use Url;
use File;
use Yaml;
use Lang;
use Cache;
use Event;
use Config;
use Schema;
use Exception;
use SystemException;
use DirectoryIterator;
use ApplicationException;
use Cms\Models\ThemeData;
use System\Models\Parameter;
use Winter\Storm\Halcyon\Datasource\DbDatasource;
use Winter\Storm\Halcyon\Datasource\FileDatasource;
use Winter\Storm\Halcyon\Datasource\DatasourceInterface;

/**
 * This class represents the CMS theme.
 * CMS theme is a directory that contains all CMS objects - pages, layouts, partials and asset files..
 * The theme parameters are specified in the theme.ini file in the theme root directory.
 *
 * @package winter\wn-cms-module
 * @author Alexey Bobkov, Samuel Georges
 */
class Skin
{
    /**
     * @var string Specifies the theme directory name.
     */
    protected $dirName;
    /**
     * @var mixed Keeps the cached configuration file values.
     */
    protected $configCache;

    /**
     * @var mixed Active theme cache in memory
     */
    protected static $activeSkinCache = false;
    /**
     * @var mixed Edit theme cache in memory
     */
    protected static $editSkinCache = false;

    const ACTIVE_KEY = 'summer.login::skin.active';
    const EDIT_KEY = 'summer.login::skin.edit';
    /**
     * Loads the theme.
     * @return self
     */
    public static function load($dirName, $file = null): self
    {
        $skin = new static;
        $skin->setDirName($dirName??'');
        $skin->getConfig();
        return $skin;
    }

    /**
     * Returns the absolute theme path.
     */
    public function getPath(?string $dirName = null): string
    {
        if (!$dirName) {
            $dirName = $this->getDirName();
        }
        return plugins_path('/summer/login/skins').'/'.$dirName;
    }

    /**
     * Sets the theme directory name.
     */
    public function setDirName(string $dirName): void
    {
        $this->dirName = $dirName;
    }

    /**
     * Returns the theme directory name.
     */
    public function getDirName(): string
    {
        return $this->dirName;
    }

    /**
     * Helper for {{ theme.id }} twig vars
     * Returns a unique string for this theme.
     */
    public function getId(): string
    {
        return snake_case(str_replace('/', '-', $this->getDirName()));
    }
    /**
     * Determines if a theme with given directory name exists
     */
    public static function exists(string $dirName): bool
    {
        $skin = static::load($dirName);
        $path = $skin->getPath();

        return File::isDirectory($path);
    }

    public function getConfig(): array
    {
        if ($this->configCache !== null) {
            return $this->configCache;
        }
        $path = $this->getPath().'/skin.yaml';
        if (!File::exists($path)) {
            throw new ApplicationException('Path does not exist: '.$path);
        }
        $config = Yaml::parseFile($path);
        return $this->configCache = $config;
    }
    /**
     * Returns a value from the theme configuration file by its name.
     */
    public function getConfigValue(string $name, mixed $default = null): mixed
    {
        return array_get($this->getConfig(), $name, $default);
    }

    /**
     * Returns an array of all themes.
     */
    public static function all(): array
    {
        $it = new DirectoryIterator(plugins_path('/summer/login/skins'));
        $it->rewind();
        $result = [];
        foreach ($it as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $theme = static::load($fileinfo->getFilename());
            $result[] = $theme;
        }
        return $result;
    }

    /**
     * Returns the theme preview image URL.
     * If the image file doesn't exist returns the placeholder image URL.
     */
    public function getPreviewImageUrl(): string
    {
        $previewPath = $this->getConfigValue('previewImage', 'assets/img/skin-preview.png');
        if (File::exists($this->getPath() . '/' . $previewPath)) {
            return Url::asset('plugins/summer/login/skins/' . $this->getDirName() . '/' . $previewPath);
        }
        return Url::asset('modules/cms/assets/images/default-theme-preview.png');
    }

    /**
     * Returns true if this theme is the chosen active theme.
     */
    public function isActiveSkin(): bool
    {
        $activeSkin = self::getActiveSkin();

        return $activeSkin && $activeSkin->getDirName() === $this->getDirName();
    }
    /**
     * Returns the active theme code.
     * By default the active theme is loaded from the cms.activeTheme parameter,
     * but this behavior can be overridden by the cms.theme.getActiveTheme event listener.
     * If the theme doesn't exist, returns null.
     */
    public static function getActiveSkinCode(): string
    {
        /**
         * @event cms.theme.getActiveTheme
         * Overrides the active theme code.
         *
         * If a value is returned from this halting event, it will be used as the active
         * theme code. Example usage:
         *
         *     Event::listen('cms.theme.getActiveTheme', function () {
         *         return 'mytheme';
         *     });
         *
         */
        $apiResult = Event::fire('summer.login.skin.getActiveSkin', [], true);
        if ($apiResult !== null) {
            return $apiResult;
        }

        // Load the active theme from the configuration
        $activeSkin = $configuredSkin = Config::get('summer.login::activeSkin');
        // Attempt to load the active theme from the cache before checking the database
        try {
            $cached = Cache::get(self::ACTIVE_KEY, null);
            if (
                is_array($cached)
                // Check if the configured theme has changed
                && $cached['config'] === $configuredSkin
            ) {
                return $cached['active'];
            }
        } catch (Exception $ex) {
            // Cache failed
        }

        // Check the database
        if (App::hasDatabase()) {
            try {
                $dbResult = Parameter::applyKey(self::ACTIVE_KEY)->value('value');
            } catch (Exception $ex) {
                $dbResult = null;
            }

            if ($dbResult !== null && static::exists($dbResult)) {
                $activeSkin = $dbResult;
            }
        }

        if (!strlen($activeSkin)) {
            throw new SystemException(Lang::get('summer.login::lang.skin.active.not_set'));
        }

        // Cache the results
        try {
            Cache::forever(self::ACTIVE_KEY, [
                'config' => $configuredSkin,
                'active' => $activeSkin,
            ]);
        } catch (Exception $ex) {
            // Cache failed
        }

        return $activeSkin;
    }

    /**
     * Returns the active theme object.
     * If the theme doesn't exist, returns null.
     */
    public static function getActiveSkin(): self
    {
        if (self::$activeSkinCache !== false) {
            return self::$activeSkinCache;
        }

        $skin = static::load(static::getActiveSkinCode());


        return self::$activeSkinCache = $skin;
    }
    /**
     * Resets any memory or cache involved with the active or edit theme.
     */
    public static function resetCache(bool $memoryOnly = false): void
    {
        self::$activeSkinCache = false;
        self::$editSkinCache = false;

        // Sometimes it may be desired to only clear the local cache of the active / edit themes instead of the persistent cache
        if (!$memoryOnly) {
            Cache::forget(self::ACTIVE_KEY);
            Cache::forget(self::EDIT_KEY);
        }
    }
    /**
     * Returns true if this theme has form fields that supply customization data.
     */
    public function hasCustomData(): bool
    {
        return (bool) $this->getConfigValue('form', false);
    }
    /**
     * Returns data specific to this theme
     */
    public function getCustomData(): LoginSkinDataModel
    {
        return LoginSkinDataModel::forSkin($this);
    }
    /**
     * Remove data specific to this theme
     */
    public function removeCustomData(): bool
    {
        if ($this->hasCustomData()) {
            return $this->getCustomData()->delete();
        }

        return true;
    }
    /**
     * Sets the active theme in the database.
     * The active theme code is stored in the database and overrides the configuration cms.activeTheme parameter.
     */
    public static function setActiveSkin(string $code): void
    {
        self::resetCache();

        Parameter::set(self::ACTIVE_KEY, $code);

        /**
         * @event cms.theme.setActiveTheme
         * Fires when the active theme has been changed.
         *
         * If a value is returned from this halting event, it will be used as the active
         * theme code. Example usage:
         *
         *     Event::listen('cms.theme.setActiveTheme', function ($code) {
         *         \Log::info("Theme has been changed to $code");
         *     });
         *
         */
        Event::fire('summer.login.skin.setActiveSkin', compact('code'));
    }

    /**
     * Themes have a dedicated `form` option that provide form fields
     * for customization, this is an immutable accessor for that and
     * also an solid anchor point for extension.
     */
    public function getFormConfig(): array
    {
        $config = $this->getConfigArray('form');

        /**
         * @event cms.theme.extendFormConfig
         * Extend form field configuration supplied by the theme by returning an array.
         *
         * Note if you are planning on using `assetVar` to inject CSS variables from a
         * plugin registration file, make sure the plugin has elevated permissions.
         *
         * Example usage:
         *
         *     Event::listen('cms.theme.extendFormConfig', function ($themeCode, &$config) {
         *          array_set($config, 'tabs.fields.header_color', [
         *              'label'           => 'Header Colour',
         *              'type'            => 'colorpicker',
         *              'availableColors' => [#34495e, #708598, #3498db],
         *              'assetVar'        => 'header-bg',
         *              'tab'             => 'Global'
         *          ]);
         *     });
         *
         */
        Event::fire('summer.login.skin.extendFormConfig', [$this->getDirName(), &$config]);

        return $config;
    }

    /**
     * Returns an array value from the theme configuration file by its name.
     * If the value is a string, it is treated as a YAML file and loaded.
     */
    public function getConfigArray(string $name): array
    {
        $result = array_get($this->getConfig(), $name, []);

        if (is_string($result)) {
            $fileName = File::symbolizePath($result);

            if (File::isLocalPath($fileName)) {
                $path = $fileName;
            }
            else {
                $path = $this->getPath().'/'.$result;
            }

            if (!File::exists($path)) {
                throw new ApplicationException('Path does not exist: '.$path);
            }

            $result = Yaml::parseFile($path);
        }

        return (array) $result;
    }

    /**
     * Implements the getter functionality.
     */
    public function __get($name)
    {
        if ($this->hasCustomData()) {
            return $this->getCustomData()->{$name};
        }

        return null;
    }

    /**
     * Determine if an attribute exists on the object.
     */
    public function __isset($key)
    {
        if ($this->hasCustomData()) {
            $skin = $this->getCustomData();
            return $skin->offsetExists($key);
        }

        return false;
    }

}
