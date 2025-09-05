<?php

use yii\helpers\Url;

$this->title = 'Administrar Archivos';
?>

<div class="box-body">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">

                    <div class="form-group pull-right">

                        <div class="file-manager" style="">
                            <button class="btn btn-success" id="uploadFileBtn">Upload PDF</button>
                        </div>

                    </div>
                    <br>
                    <br>
                    <table class="kv-grid-table table table-hover table-bordered table-striped">
                        <thead class="kv-table-header grid kv-float-header">
                            <tr>
                                <th class="kartik-sheet-style kv-align-center kv-align-middle kv-merged-header" style="width: 2.59%;">#</th>
                                <th class="kv-align-center" style="width: 5.62%;">Nombre</th>
                                <th class="kartik-sheet-style kv-align-center kv-align-middle skip-export kv-merged-header" style="width: 7.62%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="fileList">
                            <?php foreach ($files as $index => $file): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= basename($file) ?></td>
                                    <td>
                                        <a class="btn btn-xs btn-danger btn-flat deleteFileBtn" data-filename="<?= basename($file) ?>" href="#" title="Eliminar">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


                    <!-- Modal for Upload -->
                    <div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Subir PDF</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="file" id="pdfFileInput" accept="application/pdf">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" id="uploadPdfBtn">Subir</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$uploadUrl = Url::to(['file/upload']);
$deleteUrl = Url::to(['file/delete']);
$csrfToken = Yii::$app->request->csrfToken;

$this->registerJs(
    <<<JS
    
    $('#uploadFileBtn').on('click', function () {
        $('#uploadFileModal').modal('show');
    });

    $('#uploadPdfBtn').on('click', function () {
        var fileInput = $('#pdfFileInput')[0].files[0];

        if (!fileInput) {
            alert('Por favor seleccione un archivo.');
            return;
        }

        if (fileInput.type !== 'application/pdf') {
            alert('Solo se permiten archivos PDF.');
            return;
        }

        var formData = new FormData();
        formData.append('pdfFile', fileInput);
        formData.append('_csrf', '$csrfToken');

        $.ajax({
            url: '$uploadUrl',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#fileList').append(
                        '<tr>' +
                            '<td>' + response.index + '</td>' +
                            '<td>' + response.fileName + '</td>' +
                            '<td>' +
                                '<a class="btn btn-xs btn-danger btn-flat deleteFileBtn" data-filename="' + response.fileName + '" href="#" title="Eliminar">' +
                                    '<span class="glyphicon glyphicon-trash"></span>' +
                                '</a>' +
                            '</td>' +
                        '</tr>'
                    );
                    $('#uploadFileModal').modal('hide'); // Cierra el modal
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Error al subir el archivo.');
            }
        });
    });


    // Delegación de eventos para manejar elementos dinámicos
    $(document).on('click', '.deleteFileBtn', function (e) {
        e.preventDefault();
        var filename = $(this).data('filename');

        if (confirm('¿Seguro desea eliminar este archivo?')) {
            $.ajax({
                url: '$deleteUrl',
                type: 'POST',
                data: {
                    filename: filename, 
                    _csrf: '$csrfToken'
                },
                success: function (response) {
                    if (response.success) {
                        $('a[data-filename="' + filename + '"]').closest('tr').remove();
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert('Error al eliminar el archivo.');
                }
            });
        }
    });

JS
);
?>