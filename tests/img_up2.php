<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multi File Upload with Preview</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
    <style>
        input[type="file"] {
            display: block;
        }
        .imageThumb {
            max-height: 75px;
            border: 2px solid;
            padding: 1px;
            cursor: pointer;
        }
        .pip {
            display: inline-block;
            margin: 10px 10px 0 0;
        }
        .remove {
            display: block;
            background: #444;
            border: 1px solid black;
            color: white;
            text-align: center;
            cursor: pointer;
        }
        .remove:hover {
            background: white;
            color: black;
        }
    </style>
</head>
<body>

<div class="field" align="left">
    <h3>Upload your images</h3>
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" id="files" name="files[]" multiple class="multi" accept="image/*">
        <button type="submit">Upload Files</button>
    </form>
</div>

<div id="preview"></div>

<script>
    $(document).ready(function () {
        $('#files').MultiFile({
            max: 5,
            STRING: {
                remove: '[ลบ]',
                selected: 'เลือกไฟล์: $file'
            },
            afterFileSelect: function (element, value, master_element) {
                const files = element.files;
                $.each(files, function (index, file) {
                    let fileReader = new FileReader();
                    fileReader.onload = function (e) {
                        $("<span class=\"pip\">" +
                            "<img class=\"imageThumb\" src=\"" + e.target.result + "\" title=\"" + file.name + "\"/>" +
                            "<br/><span class=\"remove\">Remove image</span>" +
                            "</span>").insertAfter("#files");

                        $(".remove").last().click(function () {
                            $(this).parent(".pip").remove();
                        });
                    };
                    fileReader.readAsDataURL(file);
                });
            }
        });

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'upload.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    alert(response);
                },
                error: function (xhr, status, error) {
                    alert("มีข้อผิดพลาดในการอัปโหลดไฟล์");
                }
            });
        });
    });
</script>

</body>
</html>
