<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//github.com/fyneworks/multifile/blob/master/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>

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
<script>
    let selectedfiles;
    let selectedfile;
    let fileIndex;
    function removeImage(name) {
        selectedfiles = document.getElementById("files").files;
        let final = [];
        $.each(selectedfiles, function (index, value) {
            if (value.name !== name) {
                console.log(value);
                final.push(value);
            }
        });
        console.log('List', final);
        document.getElementById("files").files = new FileListItem(final);
    }

    function FileListItem(a) {
        a = [].slice.call(Array.isArray(a) ? a : arguments)
        for (let c, b = c = a.length, d = !0; b-- && d;) d = a[b] instanceof File
        if (!d) throw new TypeError("expected argument to FileList is File or array of File objects")
        for (b = (new ClipboardEvent("")).clipboardData || new DataTransfer; c--;) b.items.add(a[c])
        return b.files
    }

</script>

<div class="field" align="left">
    <h3>Upload your images</h3>
    <input type="file" id="files" name="files[]" multiple />
</div>

<script>
    $(document).ready(function () {

        if (window.File && window.FileList && window.FileReader) {
            $("#files").on("change", function (e) {
                let files = e.target.files,
                    fileNames = []; // เก็บชื่อไฟล์สำหรับส่งไปยังฐานข้อมูล

                filesLength = files.length;
                for (let i = 0; i < filesLength; i++) {
                    let selectedfile = files[i];
                    fileNames.push(selectedfile.name); // เพิ่มชื่อไฟล์ลงในอาร์เรย์

                    let fileReader = new FileReader();
                    fileReader.onload = (function (e) {
                        $("<span class=\"pip\">" +
                            "<img class=\"imageThumb\" src=\"" + e.target.result +
                            "\" title=\"" + selectedfile.name + "\"/>" +
                            "<br/><span class=\"remove\">Remove image</span>" +
                            "</span>").insertAfter("#files");

                        $(".remove").click(function () {
                            $(this).parent(".pip").remove();
                        });
                    });
                    fileReader.readAsDataURL(selectedfile);
                }

                // ส่งชื่อไฟล์ไปยัง PHP เพื่อบันทึกลงฐานข้อมูล
                $.ajax({
                    url: 'save_filenames.php',  // ไฟล์ PHP สำหรับบันทึกชื่อไฟล์ลงฐานข้อมูล
                    type: 'POST',
                    data: { fileNames: fileNames },
                    success: function (response) {
                        console.log("ไฟล์ถูกบันทึกลงฐานข้อมูลเรียบร้อย: ", response);
                    },
                    error: function (xhr, status, error) {
                        console.error("เกิดข้อผิดพลาดในการบันทึกชื่อไฟล์ลงฐานข้อมูล: ", error);
                    }
                });
            });
        } else {
            alert("Your browser doesn't support to File API");
        }
    });

</script>