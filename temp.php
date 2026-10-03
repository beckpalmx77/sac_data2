<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<style>
    .table-scroll {
        position:relative;
        max-width:600px;
        margin:auto;
        overflow:hidden;
        border:1px solid #000;
    }
    .table-wrap {
        width:100%;
        overflow:auto;
    }
    .table-scroll table {
        width:100%;
        margin:auto;
        border-collapse:separate;
        border-spacing:0;
    }
    .table-scroll th, .table-scroll td {
        padding:5px 10px;
        border:1px solid #000;
        background:#fff;
        white-space:nowrap;
        vertical-align:top;
    }
    .table-scroll thead, .table-scroll tfoot {
        background:#f9f9f9;
    }
    .clone {
        position:absolute;
        top:0;
        left:0;
        pointer-events:none;
    }
    .clone th, .clone td {
        visibility:hidden
    }
    .clone td, .clone th {
        border-color:transparent
    }
    .clone tbody th {
        visibility:visible;
        color:red;
    }
    .clone .fixed-side {
        border:1px solid #000;
        background:#eee;
        visibility:visible;
    }
    .clone thead, .clone tfoot{background:transparent;}
</style>

<script>
    // requires jquery library
    jQuery(document).ready(function() {
        jQuery(".main-table").clone(true).appendTo('#table-scroll').addClass('clone');
    });

</script>

<div id="table-scroll" class="table-scroll">
    <div class="table-wrap">
        <table class="main-table">
            <thead>
            <tr>
                <th class="fixed-side" scope="col">&nbsp;</th>
                <th scope="col">Header 2</th>
                <th scope="col">Header 3</th>
                <th scope="col">Header 4</th>
                <th scope="col">Header 5</th>
                <th scope="col">Header 6</th>
                <th scope="col">Header 7</th>a
                <th scope="col">Header 8</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content<br>
                    test</td>
                <td><a href="#">Cell content longer</a></td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content</td>
                <td>Cell content longer</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content</td>
                <td>Cell content longer</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content</td>
                <td>Cell content longer</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content</td>
                <td>Cell content longer</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            <tr>
                <th class="fixed-side">Left Column</th>
                <td>Cell content</td>
                <td>Cell content longer</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
                <td>Cell content</td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th class="fixed-side">&nbsp;</th>
                <td>Footer 2</td>
                <td>Footer 3</td>
                <td>Footer 4</td>
                <td>Footer 5</td>
                <td>Footer 6</td>
                <td>Footer 7</td>
                <td>Footer 8</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>



<p>See <a href="https://codepen.io/paulobrien/pen/LBrMxa" target="blank">position Sticky version </a>with no JS</p>