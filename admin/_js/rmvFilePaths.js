function rmvFilePath (inputID, exID, inputVal) {
    const input = document.getElementById(inputID);
    const example = document.getElementById(exID);
    if (!inputVal) {
        inputVal = '';
    }
    input.value = inputVal;
    example.innerHTML = '&#10060; Image Removed';
}