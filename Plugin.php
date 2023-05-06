<?php namespace Summer\Login;

use Cms\Classes\Theme;
use File;
use Backend;
use Config;
use Request;
use System\Classes\PluginBase;
use Backend\Controllers\Auth as AuthController;
use Summer\Login\Models\Settings as LoginSteeings;
use Summer\Login\Classes\Skin as LoginSkinClass;


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
                'description' => '登录主题设置',
                'category'    => 'Summer',
                'icon'        => 'icon-sign-in',
                'class'       => 'Summer\Login\Models\Settings',
                'order'       => 600,
            ],
            'skins' => [
                'label'       => '登录主题',
                'description' => '登录主题选择',
                'category'    => 'Summer',
                'icon'        => 'icon-sign-in',
                'url'         => Backend::url('summer/login/skins'),
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
        $activeSkin=LoginSkinClass::getActiveSkin();
        $activeSkinName=$activeSkin->getDirName();
        //设置登录皮肤类
        Config::set('cms.backendSkin', \Summer\Login\Skins\LoginSkin::class);
        //设置背景图片
        //判断该主题是否有自定义背景图片的属性
        if($activeSkin->backgroundImage){
            $backgroundImagePath=$activeSkin->backgroundImage->path;
        }else{
            //默认背景图片
            $backgroundImagePath=url('/plugins/summer/login/skins'.'/'.$activeSkinName.'/assets/img/background.png');
        }
        Config::set('brand.backgroundImage', $backgroundImagePath);
        //设置logo图片
        if (empty(Config::get('brand.logoPath'))) {
            if (File::exists(plugins_path('/summer/login/skins').'/'.$activeSkinName.'/assets/img/logo.png')) {
                Config::set('brand.logoPath', '/plugins/summer/login/skins/'.$activeSkinName.'/assets/img/logo.png');
            }
        }
    }
    protected function extendBackendAuthController(): void
    {
        AuthController::extend(function ($controller) {
            $controller->bindEvent('page.beforeDisplay', function () use ($controller) {
                $activeSkin=LoginSkinClass::getActiveSkin();
                $activeSkinName=$activeSkin->getDirName();
                //根据选中的皮肤设置模版路径
                $controller->addViewPath('$/summer/login/skins/'.$activeSkinName.'/pages');
            });
        });
    }
}
