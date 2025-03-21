<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * from `response_list` where id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>
<div class="content px-2 py-5 bg-gradient-primary">
    <h4 class="my-3"><b><?= !isset($id) ? "Create New Knowledges" : "Update Knowledges Details" ?></b></h4>
</div>
<div class="row mt-n5 justify-content-center">
    <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12">
        <div class="card rounded-0 shadow">
            <div class="card-body">
                <div class="container-fluid">
                    <form action="" id="response-form">
                    <div class="form-group">
                            <label for="pdf_file" class="control-label">Upload PDF</label>
                            <div class="input-group">
                                <input type="file" name="pdf_file" id="pdf_file" class="form-control form-control-sm rounded-0" accept="application/pdf">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary btn-sm" id="upload_pdf">Upload & Extract</button>
                                </div>
                            </div>
                            <small id="upload_status" class="text-muted"></small>
                        </div>
                        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
                        <div class="form-group">
                            <label for="response" class="control-label">Knowledges</label>
                            <textarea type="text" name="response" id="response" class="form-control form-control-sm rounded-0" required><?php echo isset($response) ? $response : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="status" class="control-label">Status</label>
                            <select name="status" id="status" class="form-control form-control-sm rounded-0" required>
                                <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="clear-fix mt-3"></div>
                        <div class="row bg-gradient-primary">
                            <div class="col-11 border m-0 px-2 py-1">Keyword</div>
                            <div class="col-1 border m-0 px-2 py-1">Action</div>
                        </div>
                        <div id="keyword-list" class="mb-3">
                            <?php if (isset($id)): ?>
                                <?php
                                $kw_qry = $conn->query("SELECT * FROM `keyword_list` where response_id = '{$id}'");
                                while ($row = $kw_qry->fetch_assoc()):
                                ?>
                                    <div class="row bg-gradient-light align-items-center kw-item" style="height:4.5em">
                                        <div class="col-11 border m-0 px-2 py-1 h-100">
                                            <textarea name="keyword[]" cols="30" rows="2" class="form-control form-control-sm rounded-0" required="required" style="resize:none"><?= $row['keyword'] ?></textarea>
                                        </div>
                                        <div class="col-1 border m-0 px-2 py-1 text-center align-items-center h-100 justify-content-center d-flex">
                                            <div class="col-auto">
                                                <button class="btn-outline-danger btn btn-sm rounded-0 rem-item" type="button"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="row bg-gradient-light align-items-center kw-item" style="height:4.5em">
                                    <div class="col-11 border m-0 px-2 py-1 h-100">
                                        <textarea name="keyword[]" cols="30" rows="2" class="form-control form-control-sm rounded-0" required="required" style="resize:none"></textarea>
                                    </div>
                                    <div class="col-1 border m-0 px-2 py-1 text-center align-items-center h-100 justify-content-center d-flex">
                                        <div class="col-auto">
                                            <button class="btn-outline-danger btn btn-sm rounded-0 rem-item" type="button"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary btn-sm rounded-0" type="button" id="add_kw_item"><i class="far fa-plus-square mb-n2 mr-1"></i>Add Keyword Item</button>
                        </div>
                        <div class="clear-fix mt-3"></div>
                        <div class="row bg-gradient-primary">
                            <div class="col-11 border m-0 px-2 py-1">Suggestion</div>
                            <div class="col-1 border m-0 px-2 py-1">Action</div>
                        </div>
                        <div id="suggestion-list" class="mb-3">
                            <?php if (isset($id)): ?>
                                <?php
                                $sg_qry = $conn->query("SELECT * FROM `suggestion_list` where response_id = '{$id}'");
                                while ($row = $sg_qry->fetch_assoc()):
                                ?>
                                    <div class="row bg-gradient-light align-items-center sg-item" style="height:4.5em">
                                        <div class="col-11 border m-0 px-2 py-1 h-100">
                                            <textarea name="suggestion[]" cols="30" rows="2" class="form-control form-control-sm rounded-0" style="resize:none"><?= $row['suggestion'] ?></textarea>
                                        </div>
                                        <div class="col-1 border m-0 px-2 py-1 text-center align-items-center h-100 justify-content-center d-flex">
                                            <div class="col-auto">
                                                <button class="btn-outline-danger btn btn-sm rounded-0 rem-item" type="button"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                            <div class="row bg-gradient-light align-items-center sg-item" style="height:4.5em">
                                <div class="col-11 border m-0 px-2 py-1 h-100">
                                    <textarea name="suggestion[]" cols="30" rows="2" class="form-control form-control-sm rounded-0" style="resize:none"></textarea>
                                </div>
                                <div class="col-1 border m-0 px-2 py-1 text-center align-items-center h-100 justify-content-center d-flex">
                                    <div class="col-auto">
                                        <button class="btn-outline-danger btn btn-sm rounded-0 rem-item" type="button"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary btn-sm rounded-0" type="button" id="add_suggestion_item"><i class="far fa-plus-square mb-n2 mr-1"></i>Add Suggestion Item</button>
                        </div>

                    </form>
                </div>
            </div>
            <div class="card-footer py-1 text-center">
                <button class="btn btn-sm btn-primary bg-gradient-primary rounded-0" form="response-form"><i class="fa fa-save"></i> Save</button>
                <a class="btn btn-sm btn-light bg-gradient-light border rounded-0" href="./?page=responses"><i class="fa fa-angle-left"></i> Cancel</a>
            </div>
        </div>
    </div>
</div>
<script>
    /* upload file - start */

    $(document).ready(function() {
        $('#upload_pdf').click(function() {
            var fileInput = $('#pdf_file')[0];
            if (fileInput.files.length === 0) {
                alert("Please select a PDF file.");
                return;
            }
            var formData = new FormData();
            formData.append('pdf_file', fileInput.files[0]);

            $.ajax({
                url: '../classes/upload_pdf.php', // Path to your PHP script
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#upload_status').text('Uploading and extracting...').removeClass('text-danger text-success').addClass('text-info');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#upload_status').text('PDF uploaded and extracted successfully!').addClass('text-success');

                        // Populate the description field using summernote
                        $('#response').summernote('code', response.response);

                        // Clear existing keywords
                        $('#keyword-list').empty();

                        // Populate keyword area dynamically
                        response.keywords.forEach(function(kw) {
                            $('#keyword-list').append(`
                <div class="row bg-gradient-light align-items-center kw-item" style="height:4.5em">
                    <div class="col-11 border m-0 px-2 py-1 h-100">
                        <textarea name="keyword[]" cols="30" rows="2" class="form-control form-control-sm rounded-0" required>${kw}</textarea>
                    </div>
                    <div class="col-1 border m-0 px-2 py-1 text-center align-items-center h-100 justify-content-center d-flex">
                        <button class="btn-outline-danger btn btn-sm rounded-0 rem-item" type="button"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            `);
                        });

                        // Add remove functionality for keywords
                        $('#keyword-list .rem-item').click(function() {
                            $(this).closest('.kw-item').remove();
                        });

                    } else {
                        $('#upload_status').text(response.message).addClass('text-danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    $('#upload_status').text('An error occurred while uploading.').addClass('text-danger');
                }
            });
        });
    });
    /* upload file - end */
    $(document).ready(function() {
        $('#keyword-list .kw-item').find('.rem-item').click(function() {
            if ($('#keyword-list .kw-item').length > 1) {
                $(this).closest('.kw-item').remove()
            } else {
                $(this).closest('.kw-item').find('[name="keyword[]"]').val('').focus()
            }
        })
        $('#suggestion-list .sg-item').find('.rem-item').click(function() {
            if ($('#suggestion-list .sg-item').length > 1) {
                $(this).closest('.sg-item').remove()
            } else {
                $(this).closest('.sg-item').find('[name="suggestion[]"]').val('').focus()
            }
        })
        $('#add_kw_item').click(function() {
            var item = $('#keyword-list .kw-item:nth-child(1)').clone()
            item.find('[name="keyword[]"]').val('').removeClass('border-danger')
            $('#keyword-list').append(item)
            item.find('[name="keyword[]"]').focus()
            item.find('.rem-item').click(function() {
                if ($('#keyword-list .kw-item').length > 1) {
                    item.remove()
                } else {
                    item.find('[name="keyword[]"]').val('').focus()
                }
            })
        })
        $('#add_suggestion_item').click(function() {
            var item = $('#suggestion-list .sg-item:nth-child(1)').clone()
            item.find('[name="suggestion[]"]').val('').removeClass('border-danger')
            $('#suggestion-list').append(item)
            item.find('[name="suggestion[]"]').focus()
            item.find('.rem-item').click(function() {
                if ($('#suggestion-list .sg-item').length > 1) {
                    item.remove()
                } else {
                    item.find('[name="suggestion[]"]').val('').focus()
                }
            })
        })
        $('#response').summernote({
            height: "15em",
            toolbar: [
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ol', 'ul', 'paragraph', 'height']],
                ['view', ['undo', 'redo']]
            ]
        })
        $('#response-form').submit(function(e) {
            e.preventDefault();
            var _this = $(this)
            $('.err-msg').remove();
            $('.border-danger').removeClass('border-danger')
            var el = $('<div>')
            el.addClass("alert alert-danger err-msg")
            el.hide()
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_response",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err)
                    alert_toast("An error occured", 'error');
                    end_loader();
                },
                success: function(resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        location.href = './?page=responses/view_response&id=' + resp.rid
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        if ('kw_index' in resp) {
                            $('[name="keyword[]"]').eq(resp.kw_index).addClass('border-danger').focus()
                            $('[name="keyword[]"]').eq(resp.kw_index)[0].setCustomValidity(resp.msg)
                            $('[name="keyword[]"]').eq(resp.kw_index)[0].reportValidity()
                            $('[name="keyword[]"]').eq(resp.kw_index).on('input', function() {
                                $(this).removeClass('border-danger')
                                $(this)[0].setCustomValidity("")
                            })
                        } else {
                            el.text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            $("html, body,.modal").scrollTop(0);
                        }
                    } else {
                        alert_toast("An error occured", 'error');
                    }
                    end_loader()
                }
            })
        })

    })
</script>