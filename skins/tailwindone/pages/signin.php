<?= Form::open(['class' => 'mt-8 space-y-6']) ?>
<input type="hidden" name="postback" value="1" />

<!-- Login -->
<div class="relative">
    <label class="ml-3 text-sm font-bold text-gray-700 tracking-wide">
        <?= e(trans('backend::lang.account.login_placeholder')) ?>
    </label>
    <input
        type="text"
        name="login"
        value="<?= e(post('login')) ?>"
        placeholder="<?= e(trans('backend::lang.account.login_placeholder')) ?>"
        class="w-full text-base px-4 py-2 border-b border-gray-300 focus:outline-none rounded-2xl focus:border-indigo-500"
        autocomplete="off"
        maxlength="255"
    />
</div>

<!-- Password -->
<div class="mt-8 content-center">
    <label class="ml-3 text-sm font-bold text-gray-700 tracking-wide">
        <?= e(trans('backend::lang.account.password_placeholder')) ?>
    </label>
    <input
        type="password"
        name="password"
        value=""
        placeholder="<?= e(trans('backend::lang.account.password_placeholder')) ?>"
        class="w-full content-center text-base px-4 py-2 border-b rounded-2xl border-gray-300 focus:outline-none focus:border-indigo-500"
        autocomplete="off"
        maxlength="255"
    />
</div>

<div class="flex items-center justify-between">
    <div class="flex items-center">
        <?php if (is_null(config('cms.backendForceRemember', true))): ?>
        <!-- Remember checkbox -->
        <input
            type="checkbox"
            id="remember"
            name="remember"
            class="h-4 w-4 bg-blue-500 focus:ring-blue-400 border-gray-300 rounded"
        />
        <label for="remember" class="ml-2 block text-sm text-gray-900">
            <?= e(trans('backend::lang.account.remember_me')) ?>
        </label>
        <?php endif; ?>
    </div>
    <!-- Forgot your password? -->
    <div class="text-sm">
        <a href="<?= Backend::url('backend/auth/restore') ?>" class="text-indigo-400 hover:text-blue-500">
            <?= e(trans('backend::lang.account.forgot_password')) ?>
        </a>
    </div>
</div>
<div>
    <!-- Submit Login -->
    <button type="submit" class="w-full flex justify-center bg-gradient-to-r from-indigo-500 to-blue-600 hover:bg-gradient-to-l hover:from-blue-500 hover:to-indigo-600 text-gray-100 p-4 rounded-full tracking-wide font-semibold shadow-lg cursor-pointer transition ease-in duration-500">
        <?= e(trans('backend::lang.account.login')) ?>
    </button>
</div>
<?= Form::close() ?>
<?= $this->fireViewEvent('backend.auth.extendSigninView') ?>

