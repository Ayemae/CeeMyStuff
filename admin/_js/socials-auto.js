// 'subdir' vairable should be set in the header of the document

const urlInput = document.getElementById('sm-url');
const iconPreview = document.getElementById('icon-img-visual');
const iconStored = document.getElementById('icon-img-stored')
const iconUpload = document.getElementById('icon-img-upload');
const imgNone = document.getElementById('icon-img-none');
const nameInput = document.getElementById('link-name');
const textInput = document.getElementById('link-text');
const rmvFileBtn = document.getElementById('icon-img-rmv-btn');

function socialsAuto (selection) {
    if (subdir == null) {
        subdir = '/';
    }
    var platform = selection.value;
    let url = selection.options[selection.selectedIndex].dataset.url;
    let iconPath = selection.options[selection.selectedIndex].dataset.icon;
    let setUpload = iconUpload.files.length;
    //set URL text input to url from selection data
    if (platform > '') {
        urlInput.value= url;
    }
    nameInput.value = textInput.value = platform;
    //select custom part of URL
    var text = urlInput.value;
    var iFirst = text.indexOf('[');
    var iLast = (text.indexOf(']')+1);
    urlInput.focus();
    urlInput.setSelectionRange(iFirst, iLast);
    //show icon
    if (iconPath && platform>'' && setUpload<1) {
        iconPreview.src= subdir+iconPath;
        iconStored.value= iconPath;
        iconPreview.classList.remove('invis');
        iconPreview.classList.add('block');
        iconPreview.alt= platform+' icon';
        imgNone.classList.add('invis');
    }
    if (rmvFileBtn.classList.contains('invis')) {
        rmvFileBtn.classList.remove('invis');
    }
}