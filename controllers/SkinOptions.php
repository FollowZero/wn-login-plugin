<?php namespace Summer\Login\Controllers;

use Backend;
use BackendMenu;
use ApplicationException;
use Summer\Login\Classes\Skin as LoginSkinClass;
use Summer\Login\Models\LoginSkinDataModel;
use System\Classes\SettingsManager;
use Backend\Classes\Controller;
use Exception;

/**
 * Theme customization controller
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class SkinOptions extends Controller
{
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'summer.login::lang.skin.settings_menu';

        BackendMenu::setContext('Winter.System', 'system', 'settings');
        SettingsManager::setContext('Summer.Login', 'skins');
    }

    public function update($dirName = null)
    {
        $dirName = $this->getDirName($dirName);

        try {
            $model = $this->getSkinData($dirName);

            $this->asExtension('FormController')->update($model->id);

            $this->vars['hasCustomData'] = $this->hasSkinData($dirName);
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    public function update_onSave($dirName = null)
    {
        $model = $this->getSkinData($this->getDirName($dirName));
        $result = $this->asExtension('FormController')->update_onSave($model->id);

        // Redirect close requests to the settings index when user doesn't have access
        // to go back to the theme selection page
//        if (!$this->user->hasAccess('cms.manage_themes') && input('close')) {
        if (input('close')) {
            $result = Backend::redirect('system/settings');
        }

        return $result;
    }

    public function update_onResetDefault($dirName = null)
    {
        $model = $this->getSkinData($this->getDirName($dirName));
        $model->delete();

        return Backend::redirect('summer/login/skinoptions/update/'.$dirName);
    }

    /**
     * Add form fields defined in theme.yaml
     */
    public function formExtendFieldsBefore($form)
    {
        $model = $form->model;
        $skin = $this->findSkinObject($model->skin);
        $form->config = $this->mergeConfig($form->config, $skin->getFormConfig());
        $form->init();
    }

    //
    // Helpers
    //

    /**
     * Default to the active theme if user doesn't have access to manage all themes
     *
     * @param string $dirName
     * @return string
     */
    protected function getDirName(string $dirName = null)
    {
        /*
         * Only the active theme can be managed without this permission
         */
//        if ($dirName && !$this->user->hasAccess('cms.manage_themes')) {
//            $dirName = null;
//        }

        if ($dirName === null) {
            $dirName = LoginSkinClass::getActiveSkinCode();
        }

        return $dirName;
    }

    protected function hasSkinData($dirName)
    {
        return $this->findSkinObject($dirName)->hasCustomData();
    }

    protected function getSkinData($dirName)
    {
        $skin = $this->findSkinObject($dirName);
        return LoginSkinDataModel::forSkin($skin);
    }

    protected function findSkinObject($name = null)
    {
        if ($name === null) {
            $name = post('theme');
        }
        $skin = LoginSkinClass::load($name);
        if (!$name || !$skin) {
            throw new ApplicationException(trans('summer.login::lang.skin.not_found_name', ['name' => $name]));
        }

        return $skin;
    }
}
