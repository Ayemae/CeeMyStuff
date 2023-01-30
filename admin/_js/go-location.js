function goLocation (select, targetID, href) {
    const target = document.getElementById(targetID);
    let value = select.value;
    if (value != null && value != 'null') {
        target.href= href+value;
    }
}