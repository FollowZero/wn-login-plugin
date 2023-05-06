<?php namespace Summer\Login\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Summer\Login\Classes\Skin as LoginSkinClass;
use System\Classes\SettingsManager;

class Skins extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->addCss('/modules/cms/assets/css/winter.theme-selector.css', 'core');

        BackendMenu::setContext('Winter.System', 'system', 'settings');
        SettingsManager::setContext('Summer.Login', 'skins');
    }

    public function index()
    {
        $this->bodyClass = 'compact-container';
    }

    public function index_onSetActiveSkin()
    {
        LoginSkinClass::setActiveSkin(post('theme'));

        return [
            '#theme-list' => $this->makePartial('skin_list')
        ];
    }
}
