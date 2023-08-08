function resetUndoBtn(btn){
    btn.classList.remove('undo');
    btn.innerHTML = btn.innerHTML.replace("Undo ", '')
}

function rmvFilePath (bttn, inputID, cID, inputVal, undoInputVal) {
    // setting upload to false by default
    let upload = false;

    // the file input
    var input = document.getElementById(inputID);

    // the element where all of the relevant files are contained
    const container = document.getElementById(cID);

    // if there's nothing in the usual input (usually a stored file/image path), check for an uploader
    if (!input.value) {
        // the file uploader if relevant
        input = container.querySelector("input[type=file]");
        // check if there's a file upload queued
        if (input.files.length>0) {
            upload = true;
        }
    }
    //  the visual/preview of the file 
    let vis = container.getElementsByClassName("visual")[0];

    // the text giving the user feedback that the file has been removed
    let rmvInfo = container.getElementsByClassName("rvm-file-path-info")[0];

    // if there's no alternative input value argument...
    if (!inputVal) {
        inputVal = null;
    }

    if (!bttn.classList.contains("undo")) {
        // if button does NOT contain the 'undo' class, it is a regular file-path-remove button
        input.value = inputVal;
        vis.classList.add('invis');
        if (!upload) {
            bttn.dataset.storedPath = input.value;
            bttn.innerHTML = 'Undo '+bttn.innerHTML;
            bttn.classList.add("undo");
            rmvInfo.classList.remove('invis');
        } else {
            bttn.classList.add("invis")
            URL.revokeObjectURL(vis.src);
        }
    } else {
        // if button DOES contain the 'undo' class, it is an undo-file-removal button
        if (undoInputVal) {
            // if there is an alternative input if 'undo' is used
            input.value = undoInputVal;
        } else if (inputVal == 1) {
            // if inputVal is a binary true/false
            inputVal = 0;
            input.value = inputVal;
        } else if (!upload && inputVal === '' && bttn.dataset.storedPath>'') {
            // if the stored file path was saved on a data attribute
            input.value = bttn.dataset.storedPath;
        } else {
            input.value = null;
        }
        rmvInfo.classList.add('invis');
        vis.classList.remove('invis');
        resetUndoBtn(bttn);
    }
}