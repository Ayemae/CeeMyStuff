function previewImg (fileInputID, previewID) {
    const input = document.getElementById(fileInputID);
    const example = document.getElementById(previewID);
    if (!inputVal) {
        inputVal = '';
    }
    let src = window.URL.createObjectURL(input.files[0]);
    example.src = src;
}