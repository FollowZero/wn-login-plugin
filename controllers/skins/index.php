<?= Block::put('body') ?>
    <div class="layout">
        <div class="layout-row">
            <?= Form::open(['onsubmit'=>'return false']) ?>
            <div class="layout theme-selector-layout" id="theme-list">
                <?= $this->makePartial('skin_list') ?>
            </div>
            <?= Form::close() ?>
        </div>
    </div>
<?= Block::endPut() ?>
