function rmvFilePath (bttn, inputID, exID, inputVal, undoInputVal) {
    const input = document.getElementById(inputID);
    const example = document.getElementById(exID);
    let exVis = example.getElementsByClassName("visual")[0];
    let exInfo = example.getElementsByClassName("rvm-file-path-info")[0];
    if (!inputVal) {
        inputVal = '';
    }
    if (!bttn.classList.contains("undo")) {
        bttn.dataset.storedPath = input.value;
        input.value = inputVal;
        exInfo.classList.remove('invis');
        exVis.classList.add('invis');
        bttn.classList.add("undo");
        bttn.innerHTML = 'Undo '+bttn.innerHTML;
    } else {
        if (undoInputVal) {
            input.value = undoInputVal;
        } else if (inputVal == 1) {
            inputVal = 0;
            input.value = inputVal;
        } else if (inputVal === '' && bttn.dataset.storedPath) {
            input.value = bttn.dataset.storedPath;
        } else {
            input.value = '';
        }
        bttn.classList.remove('undo');
        exInfo.classList.add('invis');
        exVis.classList.remove('invis');
        bttn.innerHTML = bttn.innerHTML.replace("Undo ", '')
    }
}