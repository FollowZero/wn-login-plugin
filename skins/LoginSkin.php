<?php namespace Summer\Login\Skins;

use Backend\Skins\Standard as BackendSkin;
use Summer\Login\Models\Settings as LoginSteeings;

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
        $activeSkin=LoginSteeings::get('active_skin');
        return [
            plugins_path('/summer/login/skins/'.$activeSkin.'/layouts'),
            $this->skinPath . '/layouts'
        ];
    }
}
