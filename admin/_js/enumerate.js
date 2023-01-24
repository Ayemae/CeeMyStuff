function enumerate(enumBy, type) {
    var enumTargets = [];
    if (type=='class') {
        enumTargets = document.getElementsByClassName(enumBy);
    } else if (type=='name') {
        // get by the end of the 'name' attribute
        enumTargets = document.getElementsByTagName("input[name$='"+enumBy+"']");
    } else {
        enumTargets = document.querySelectorAll(enumBy);
    }
    var targetsArr = [];
    for (let i=0;i<enumTargets.length;i++) {
        let val = enumTargets[i].value;
        targetsArr.push({domIndex: i, value: val});
    }
    targetsArr.sort((a, b) => (a.value > b.value) ? 1 : -1);
    let num = 1;
    for (let i=0;i<enumTargets.length;i++) {
        enumTargets[targetsArr[i].domIndex].setAttribute("value", num);
        num++;
    }
}