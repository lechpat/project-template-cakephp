<?php
$tableName = $field['model'];
if ($field['plugin']) {
    $tableName = $field['plugin'] . '.' . $tableName;
}

$renderOptions = ['entity' => $options['entity']];

$label = $factory->renderName($tableName, $field['name'], $renderOptions);
$value = $factory->renderValue($tableName, $field['name'], $options['entity'], $renderOptions);
$value = empty($value) ? '&nbsp;' : $value;

// append translation modal button
$value .= $this->element('Module/Menu/translations', [
    'options' => $options,
    'field' => $field,
    'tableName' => $tableName
]);

// calculate column width
$columnWidth = (int)floor(12 / $fieldCount);
$columnWidth = 6 < $columnWidth ? 6 : $columnWidth; // max-supported input size is half grid
?>
<?php if (2 >= $fieldCountMax) : // horizontal style ?>
    <div class="col-xs-4 col-md-2 text-right"><strong><?= $label ?>:</strong></div>
    <div class="col-xs-8 col-md-4"><?= $value ?></div>
<?php endif ?>
<?php if (2 < $fieldCountMax) : // default style ?>
    <div class="col-xs-12 col-md-<?= $columnWidth ?>">
        <div class="form-group">
            <label class="control-label"><?= $label ?></label><br />
            <?= $value ?>
        </div>
    </div>
<?php endif ?>