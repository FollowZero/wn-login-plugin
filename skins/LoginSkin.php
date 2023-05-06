<?php namespace Summer\Login\Skins;

use Backend\Skins\Standard as BackendSkin;
use Summer\Login\Classes\Skin as LoginSkinClass;

/**
 * 登录皮肤
 */
class LoginSkin extends BackendSkin
{
    /**
     * {@inheritDoc}
     */
    public function getLayoutPaths()
    {
        $activeSkin=LoginSkinClass::getActiveSkin();
        $activeSkinName=$activeSkin->getDirName();
        return [
            plugins_path('/summer/login/skins/'.$activeSkinName.'/layouts'),
            $this->skinPath . '/layouts'
        ];
    }
}
