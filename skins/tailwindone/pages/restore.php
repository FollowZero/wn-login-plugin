<?= Form::open(['class' => 'mt-8 space-y-6']) ?>
    <input type="hidden" name="postback" value="1" />

    <!-- Login -->
    <div class="relative">
        <label class="ml-3 text-sm font-bold text-gray-700 tracking-wide">
            <?= e(trans('backend::lang.account.enter_login')) ?>
        </label>
        <input
            type="text"
            id="login"
            name="login"
            value="<?= e(post('login')) ?>"
            class="w-full text-base px-4 py-2 border-b border-gray-300 focus:outline-none rounded-2xl focus:border-indigo-500"
            required
            autocomplete="email"
            maxlength="255"
        />
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
        </div>
        <!-- Forgot your password? -->
        <div class="text-sm">
            <a href="<?= Backend::url('backend/auth') ?>" class="text-indigo-400 hover:text-blue-500">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </a>
        </div>
    </div>

    <div>
        <!-- Submit -->
        <button type="submit" class="w-full flex justify-center bg-gradient-to-r from-indigo-500 to-blue-600 hover:bg-gradient-to-l hover:from-blue-500 hover:to-indigo-600 text-gray-100 p-4 rounded-full tracking-wide font-semibold shadow-lg cursor-pointer transition ease-in duration-500">
            <?= e(trans('backend::lang.account.restore')) ?>
        </button>
    </div>

<?= Form::close() ?>

<?= $this->fireViewEvent('backend.auth.extendRestoreView') ?>
