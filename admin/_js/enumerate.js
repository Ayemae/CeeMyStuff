function enumerate(enumClass) {
    const enumTargets = document.getElementsByClassName(enumClass);
    let num = 1;
    for (let i=0;i<enumTargets.length;i++) {
        let prevVal = document.getElementsByClassName(enumClass)[i].value;
        if (parseInt(prevVal)>= num) {
            num = prevVal;
        }
        document.getElementsByClassName(enumClass)[i].setAttribute("value", num);
        num++;
    }
}