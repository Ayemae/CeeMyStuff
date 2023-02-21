// 'subdir' vairable should be set in the header of the document

function socialsAuto (selection) {
    if (subdir == null) {
        subdir = '/';
    }
    var platform = selection.value;
    const urlInput = document.getElementById('sm-url');
    const iconWrap = document.getElementById('sm-icon');
    let url = selection.options[selection.selectedIndex].dataset.url;
    let iconPath = selection.options[selection.selectedIndex].dataset.icon;
    //set URL text input to url from selection data
    urlInput.value= url;
    //select custom part of URL
    var text = urlInput.value;
    var iFirst = text.indexOf('[');
    var iLast = (text.indexOf(']')+1);
    urlInput.focus();
    urlInput.setSelectionRange(iFirst, iLast);
    //show icon
    iconWrap.innerHTML='<img src="'+subdir+iconPath+'" alt="'+platform+' Icon">';
}