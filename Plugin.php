<?php namespace Summer\Login;

use File;
use Config;
use Request;
use System\Classes\PluginBase;
use Backend\Controllers\Auth as AuthController;
use Summer\Login\Models\Settings as LoginSteeings;


class Plugin extends PluginBase
{
    public $elevated = true;
    public function registerComponents()
    {
    }
    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => '登录主题',
                'description' => '登录主题',
                'category'    => 'Summer',
                'icon'        => 'icon-sign-in',
                'class'       => 'Summer\Login\Models\Settings',
                'order'       => 600,
            ]
        ];
    }
    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        //是否启用登录主题
        $enabledSkin=LoginSteeings::get('enabled_skin');
        if($enabledSkin){
            // 只有在登录的路由组设置该皮肤类
            if (Request::is('backend/backend/auth/*')) {
                $this->applyBackendSkin();
                $this->extendBackendAuthController();
            }
        }
    }
    /**
     * Apply the Summer loginSkin as the selected backend skin
     */
    protected function applyBackendSkin()
    {
        $activeSkin=LoginSteeings::get('active_skin');
        //设置登录皮肤类
        Config::set('cms.backendSkin', \Summer\Login\Skins\LoginSkin::class);
        //设置背景图片
        if (File::exists(plugins_path('/summer/login/skins').'/'.$activeSkin.'/assets/img/background.png')) {
            Config::set('brand.backgroundImage', '/plugins/summer/login/skins/'.$activeSkin.'/assets/img/background.png');
        }
        //设置logo图片
        if (empty(Config::get('brand.logoPath'))) {
            if (File::exists(plugins_path('/summer/login/skins').'/'.$activeSkin.'/assets/img/logo.png')) {
                Config::set('brand.logoPath', '/plugins/summer/login/skins/'.$activeSkin.'/assets/img/logo.png');
            }
        }
    }
    protected function extendBackendAuthController(): void
    {
        AuthController::extend(function ($controller) {
            $controller->bindEvent('page.beforeDisplay', function () use ($controller) {
                $activeSkin=LoginSteeings::get('active_skin');
                //根据选中的皮肤设置模版路径
                $controller->addViewPath('$/summer/login/skins/'.$activeSkin.'/pages');
            });
        });
    }
}
