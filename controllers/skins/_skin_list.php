<?php
    $skins = Summer\Login\Classes\Skin::all();
?>
<?php foreach ($skins as $index => $skin): ?>

    <div id="themeListItem-<?= $skin->getId() ?>" class="layout-row min-size <?= $skin->isActiveSkin() ? 'active' : null ?>">
        <?= $this->makePartial('skin_list_item', ['skin' => $skin]) ?>
    </div>

<?php endforeach ?>

