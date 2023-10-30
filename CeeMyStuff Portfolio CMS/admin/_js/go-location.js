function goLocation (select, btnID, href) {
    const target = document.getElementById(btnID);
    let value = select.value;
    if (value != null && value != 'null' && value != undefined) {
        target.href= href+value;
    }
}


if (subdir == null) {
    subdir = '/';
}
// visually reset select option to default if, for instance, the 'back' button is pressed
document.addEventListener( "DOMContentLoaded", function() {
    setTimeout(function() {
        var selects = document.getElementsByTagName('select');
    for (let i=0;i<selects.length;i++) {
        let si = selects[i];
        let opts = si.getElementsByTagName('option');
        for (let i=0;i<1;i++) {
            opts[i].selected = true;
            si.value = opts[i].value;
        }
    }}, 250)
});
