<div>
    <button id="js-button-darkmode" type="button">dark</button>
    <script>
        let darkModeState = false;
        document.getElementById("js-button-darkmode").addEventListener("click", function(){
            darkModeState = !darkModeState;
            if (this.innerHTML === "dark") {
                this.innerHTML = "light";
            } else {
                this.innerHTML = "dark";
            }
            document.getElementsByTagName("body")[0].toggleAttribute("data-ogsc");
        });
    </script>
</div>

<?php echo $content; ?>


