<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;

?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_inner',
    'widgetBody' => '.container-sectorLocations',
    'widgetItem' => '.sectorLocation-item',
    'limit' => 4,
    'min' => 1,
    'insertButton' => '.add-sectorLocation',
    'deleteButton' => '.remove-sectorLocation',
    'model' => $modelsSectorLocation[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'code',
        'name'
    ],
]); ?>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Ubicaciones</th>
            <th class="text-center">
                <button type="button" class="add-sectorLocation btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>
            </th>
        </tr>
        </thead>
        <tbody class="container-sectorLocations">
        <?php foreach ($modelsSectorLocation as $indexSectorLocation => $modelSectorLocation): ?>
            <tr class="sectorLocation-item">
                <td class="vcenter">
                    <?php
                    // necessary for update action.
                    if (! $modelSectorLocation->isNewRecord) {
                        echo Html::activeHiddenInput($modelSectorLocation, "[{$indexSector}][{$indexSectorLocation}]id");
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($modelSectorLocation, "[{$indexSector}][{$indexSectorLocation}]code")->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-8">
                            <?= $form->field($modelSectorLocation, "[{$indexSector}][{$indexSectorLocation}]name")->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </td>
                <td class="text-center vcenter" style="width: 90px;">
                    <button type="button" class="remove-sectorLocation btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php DynamicFormWidget::end(); ?>