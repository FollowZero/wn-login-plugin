<!DOCTYPE html>
<html lang="<?= App::getLocale() ?>">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?= $this->makeLayoutPartial('head_auth') ?>
    <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'auth']) ?>
    <link rel="stylesheet" href="<?= Url::asset('/plugins/summer/login/skins/tailwindone/assets/css/tailwind.min.css'); ?>">
    <link rel="stylesheet" href="<?= Url::asset('/plugins/summer/login/skins/tailwindone/assets/css/style.css'); ?>">
</head>
<body>
<div class="relative min-h-screen flex">
    <div class="flex flex-col sm:flex-row items-center md:items-start sm:justify-center md:justify-start flex-auto min-w-0 bg-white">
        <div style="background-image: url(<?= Url::asset('/plugins/summer/login/skins/tailwindone/assets/img/img.jpg'); ?>)" class="sm:w-1/2 xl:w-3/5 h-full hidden md:flex flex-auto items-center justify-center p-10 overflow-hidden bg-purple-900 text-white bg-no-repeat bg-cover relative">
            <div class="absolute bg-gradient-to-b from-indigo-600 to-blue-500 opacity-75 inset-0 z-0">
            </div>
            <div class="w-full max-w-md z-10">
                <div class="sm:text-4xl xl:text-5xl font-bold leading-tight mb-6">
                    <?= e(Backend\Models\BrandSetting::get('app_name')) ?>
                </div>
                <div class="sm:text-sm xl:text-md text-gray-200 font-normal">
                    <?= e(Backend\Models\BrandSetting::get('app_tagline')); ?>
                </div>
            </div>
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
        <div class="md:flex md:items-center md:justify-center w-full sm:w-auto md:h-full w-2/5 xl:w-2/5 p-8 md:p-10 lg:p-14 sm:rounded-lg md:rounded-none bg-white">
            <div class="max-w-md w-full mx-auto space-y-8">
                <div class="text-center">
                    <h2 class="mt-6 text-3xl font-bold text-gray-900">
                        <?= e(Backend\Models\BrandSetting::get('app_name')) ?>
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        <?= e(Backend\Models\BrandSetting::get('app_tagline')); ?>
                    </p>
                </div>
                <?= Block::placeholder('body') ?>
            </div>
        </div>
    </div>
</div>
<!-- Flash Messages -->
<style>
    div#layout-flash-messages + div {
        display: none;
    }
</style>
<div id="layout-flash-messages"><?= $this->makeLayoutPartial('flash_messages') ?></div>
</body>
</html>
