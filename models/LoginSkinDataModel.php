<?php namespace Summer\Login\Models;

use Lang;
use Model;
use Summer\Login\Classes\Skin as LoginSkinClass;
use System\Classes\CombineAssets;
use Exception;
use System\Models\File;

/**
 * Model
 */
class LoginSkinDataModel extends Model
{
    use \Winter\Storm\Database\Traits\Validation;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'summer_login_skin_data';
    /**
     * @var array Guarded fields
     */
    protected $guarded = [];
    /**
     * @var array Fillable fields
     */
    protected $fillable = [];
    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['data'];
    /**
     * @var array Validation rules
     */
    public $rules = [];
    /**
     * @var array Relations
     */
    public $attachOne = [];
    /**
     * @var  Cached array of objects
     */
    protected static $instances = [];
    /**
     * Returns a cached version of this model, based on a Theme object.
     * @param $theme Cms\Classes\Theme
     * @return self
     */

    public function beforeSave()
    {
        /*
         * Dynamic attributes are stored in the jsonable attribute 'data'.
         */
        $staticAttributes = ['id', 'skin', 'data', 'created_at', 'updated_at'];
        $dynamicAttributes = array_except($this->getAttributes(), $staticAttributes);

        $this->data = $dynamicAttributes;
        $this->setRawAttributes(array_only($this->getAttributes(), $staticAttributes));
    }

    /**
     * Clear asset cache after saving to ensure `assetVar` form fields take
     * immediate effect.
     */
    public function afterSave()
    {
        try {
            CombineAssets::resetCache();
        }
        catch (Exception $ex) {
        }
    }

    public static function forSkin($skin)
    {
        $dirName = $skin->getDirName();
        if ($skinData = array_get(self::$instances, $dirName)) {
            return $skinData;
        }

        try {
            $skinData = self::firstOrCreate(['skin' => $dirName]);
        }
        catch (Exception $ex) {
            // Database failed
            $skinData = new self(['skin' => $dirName]);
        }

        return self::$instances[$dirName] = $skinData;
    }

    /**
     * After fetching the model, intiialize model relationships based
     * on form field definitions.
     * @return void
     */
    public function afterFetch()
    {
        $data = (array) $this->data + $this->getDefaultValues();

        foreach ($this->getFormFields() as $id => $field) {
            if (!isset($field['type'])) {
                continue;
            }

            /*
             * Repeater and nested form fields store arrays and must be jsonable.
             */
            if (in_array($field['type'], ['repeater', 'nestedform'])) {
                $this->jsonable[] = $id;
            } elseif ($field['type'] === 'fileupload') {
                if (array_get($field, 'multiple', false)) {
                    $this->attachMany[$id] = File::class;
                } else {
                    $this->attachOne[$id] = File::class;
                }
                unset($data[$id]);
            }
        }

        /*
         * Fill this model with the jsonable attributes kept in 'data'.
         */
        $this->setRawAttributes((array) $this->getAttributes() + $data, true);
    }
    /**
     * Before model is validated, set the default values.
     * @return void
     */
    public function beforeValidate()
    {
        if (!$this->exists) {
            $this->setDefaultValues();
        }
    }
    /**
     * Creates relationships for this model based on form field definitions.
     */
    public function initFormFields()
    {
    }
    /**
     * Sets default values on this model based on form field definitions.
     */
    public function setDefaultValues()
    {
        foreach ($this->getDefaultValues() as $attribute => $value) {
            $this->{$attribute} = $value;
        }
    }
    /**
     * Gets default values for this model based on form field definitions.
     * @return array
     */
    public function getDefaultValues()
    {
        $result = [];

        foreach ($this->getFormFields() as $attribute => $field) {
            if (($value = array_get($field, 'default')) === null) {
                continue;
            }

            $result[$attribute] = $value;
        }

        return $result;
    }
    /**
     * Returns all fields defined for this model, based on form field definitions.
     * @return array
     */
    public function getFormFields()
    {
        if (!$skin = LoginSkinClass::load($this->skin)) {
            throw new Exception(Lang::get('Unable to find theme with name :name', $this->skin));
        }

        $config = $skin->getFormConfig();

        return array_get($config, 'fields', []) +
            array_get($config, 'tabs.fields', []) +
            array_get($config, 'secondaryTabs.fields', []);
    }
    /**
     * Returns variables that should be passed to the asset combiner.
     * @return array
     */
    public function getAssetVariables()
    {
        $result = [];

        foreach ($this->getFormFields() as $attribute => $field) {
            if (!$varName = array_get($field, 'assetVar')) {
                continue;
            }

            $result[$varName] = $this->{$attribute};
        }

        return $result;
    }
    /**
     * Applies asset variables to the combiner filters that support it.
     * @return void
     */
    public static function applyAssetVariablesToCombinerFilters($filters)
    {
        $skin = LoginSkinClass::getActiveSkin();

        if (!$skin) {
            return;
        }

        if (!$skin->hasCustomData()) {
            return;
        }

        $assetVars = $skin->getCustomData()->getAssetVariables();

        foreach ($filters as $filter) {
            if (method_exists($filter, 'setPresets')) {
                $filter->setPresets($assetVars);
            }
        }
    }
    /**
     * Generate a cache key for the combiner, this allows variables to bust the cache.
     * @return string
     */
    public static function getCombinerCacheKey()
    {
        $skin = LoginSkinClass::getActiveTheme();
        if (!$skin->hasCustomData()) {
            return '';
        }

        $customData = $skin->getCustomData();

        return (string) $customData->updated_at ?: '';
    }
}
