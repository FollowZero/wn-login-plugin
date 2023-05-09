# wn-login-plugin
自定义选择后台登录主题

## 安装

由于包名的原因需要在项目根目录的 composer.json 文件中添加有自定义安装路径的代码
``` 
.
.
.
"extra": {
        "installer-paths": {
            "plugins/summer/{$name}/": ["vendor:summercms"]
        }
    }
.
.
.
```

```
composer require summercms/wn-login-plugin
```

```
php artisan winter:up
```
## 使用
后台 > 设置 > SUMMER > 登录主题

启用登录主题

选中登录主题

## 自定义扩展主题
当前版本1.0.1提供了两款皮肤，可供选择。可自己扩展主题。

### 登录主题皮肤目录
```
skins/tailwindone
├─assets //资源文件夹
│  ├─css //css资源文件夹
│  ├─js  //js资源文件夹
│  └─img //图片文件夹
│     └─skin-preview.png //主题预览图片
├─layouts //布局文件夹
│  ├─_head_auth.php //基本通用的头部文件
│  └─auth.php //布局文件
├─pages //页面文件夹
│  ├─reset.php //重置密码页面
│  ├─restore.php //忘记密码页面
│  └─login.php //登录页面
└─skin.yaml //主题配置
```

### skin.yaml 文件说明
```
name: Tailwind主题一 //主题名称
description: 'Tailwind主题一' //主题介绍
author: Summer CMS
homepage: 'https://www.summercms.com'
code: ''
```

## 其他

### 知识点
做每个插件都有好的 **想法**,去实现的时候会遇到各种 **问题**,但最终都会有解决的 **方法**，有可能这些方法不是最优解，但也有必要记录下来，方便学习和日后的升级。

我想要的效果有

像前端主题那样,可以自定义主题，写好的登录主题放到skins文件夹，就能作为一个主题让选择。//已实现。参考 Cms\Classes\Theme 写的 Summer\Login\Classes\Skin

每个主题有自己的属性，有通用属性，如背景图片，按钮颜色。也有特有属性，如有的有飘浮物，额外的标语口号。//v2版本，已实现

因为结构和颜色的不同，有可能背景图片的大小，logo的颜色需要单独设置。就需要能整体设置也需要每个主题能单独设置。没有单独设置的默认整体的设置。//v2版本，已实现

主要参考 [wn-tailwindui-plugin](https://github.com/wintercms/wn-tailwindui-plugin) 这个后台皮肤的插件。其中包含了后台登录的页面。为了不与这个插件有冲突，只有在
'backend/backend/auth/*' 的路由页面下才单独继承 BackendSkin 类。之前想着用是否后台登录去做判断。但是是不管用的，应该是因为插件定义了 [$elevated](https://wintercms.com/docs/plugin/registration#elevated-plugin)
属性为 true ,权限优先级提升了。

登录主题的选择用的是表单的下拉菜单 [Dropdown](https://wintercms.com/docs/backend/forms#field-dropdown) 她支持添加图标或图片。配合这表单字段选项中的 cssClass ，可自定义css样式。
但是用的是 Plugin Settings 我暂时不知道如何添加css文件，就用表单的 partial 把用到的css写到了对应的部分里
```
fields:
    dropdownimg:
        type: partial
        path: ~/plugins/summer/login/models/settings/_dropdownimg.htm //css样式暂时写到这个部分里
    active_skin:
        label: summer.login::lang.form.active_skin
        cssClass: dropdownimg //添加classs属性
        span: full
        type: dropdown
```



