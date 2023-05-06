<?php
    $author = $skin->getConfigValue('author');
?>

<div class="layout-cell min-height theme-thumbnail">
    <div class="thumbnail-container"><img src="<?= $skin->getPreviewImageUrl() ?>" alt="" /></div>
</div>
<div class="layout-cell min-height theme-description">
    <h3><?= e($skin->getConfigValue('name', $skin->getDirName())) ?></h3>
    <?php if (strlen($author)): ?>
        <p class="author"><?= trans('cms::lang.theme.by_author', ['name' => '<a href="'.e($skin->getConfigValue('homepage', '#')).'">'.e($author).'</a>']) ?></p>
    <?php endif ?>
    <p class="description">
        <?= e($skin->getConfigValue('description', 'The theme description is not provided.')) ?>
    </p>
    <div class="controls">

        <?php if ($skin->isActiveSkin()): ?>
            <button
                type="submit"
                disabled
                class="btn btn-secondary btn-disabled">
                <i class="icon-star"></i>
                <?= e(trans('cms::lang.theme.active_button')) ?>
            </button>
        <?php else: ?>
            <button
                type="submit"
                data-request="onSetActiveSkin"
                data-request-data="theme: '<?= e($skin->getDirName()) ?>'"
                data-stripe-load-indicator
                class="btn btn-primary">
                <i class="icon-check"></i>
                <?= e(trans('cms::lang.theme.activate_button')) ?>
            </button>
        <?php endif ?>

        <?php if ($skin->hasCustomData()): ?>
            <a
                href="<?= Backend::url('summer/login/skinoptions/update/'.$skin->getDirName()) ?>"
                class="btn btn-secondary<?= $skin->isActiveSkin() === false ? ' disabled' : '' ?>">
                <i class="icon-paint-brush"></i>
                <?= e(trans('cms::lang.theme.customize_button')) ?>
            </a>
        <?php endif ?>
    </div>
</div>
