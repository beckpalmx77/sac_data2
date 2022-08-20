        <div class="card-body">

            <!--?php for ($x = 1; $x <= 4; $x++) {  ?-->

            <div class="progress">
                <div class="progress-bar" style="min-width: 20px;"></div>
            </div>

            <br>

            <!-- jQuery Script -->
            <script>
                let i = 1;

                function makeProgress(max) {
                    if (i < max) {
                        i = i + 1;
                        $(".progress-bar").css("width", i + "%").text(i + "%");
                    }

                    // Wait for sometime before running this script again
                    setTimeout("makeProgress(max)", 1);
                }

                let max = 40;

                makeProgress(max);

            </script>

            <!--?php } ?-->

        </div>










