<?php namespace Summer\Login\Classes;

use App;
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
     * Loads the theme.
     * @return self
     */
    public static function load($dirName, $file = null): self
    {
        $skin = new static;
        $skin->setDirName($dirName);
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

}
