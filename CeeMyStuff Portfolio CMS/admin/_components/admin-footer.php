<footer>
<p>&copy;<? echo ($set["c_year"] && ($set["c_year"] != date("Y")) ? $set["c_year"].'-' : null ); show(date("Y"))?>, Alyssa Alecci | Uicons by <a href="https://www.flaticon.com/uicons">Flaticon</a> | powered by <a href="https://github.com/Ayemae/CeeMyStuff">CeeMyStuff</a></p>
</footer>

<script>
    (function() {
        var elemArr = document.getElementsByClassName('js-check');
        for (let i=0;i<elemArr.length;i++) {
            elemArr[i].classList.add("js-enabled");
        }
    })();
</script>
</body>
</html>