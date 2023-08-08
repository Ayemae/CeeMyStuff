function previewImg (fileInputID, previewIDStrt, toggleRmv) {
  const input = document.getElementById(fileInputID);
  const visual = document.getElementById(previewIDStrt+'-visual');
  const noneTxt = document.getElementById(previewIDStrt+'-none');
  const prvwInput = document.getElementById(previewIDStrt+'-preview')
  let img = window.URL.createObjectURL(input.files[0]);
  prvwInput.value = visual.src = img;
  visual.onload = function() {
      visual.classList.remove('invis');
      visual.classList.add('block');
      noneTxt.classList.add('invis');
      if (toggleRmv) {
        const rmvFile = document.getElementById(previewIDStrt+'-rmv-btn');
        rmvFile.classList.remove('invis');
        URL.revokeObjectURL(visual.src); // free memory
      }
    }
    let fRmvInfo = document.getElementById('icon-img-rmv-info');
    let fRmvBtn = document.getElementById('icon-img-rmv-btn');
    if (fRmvInfo) {
      fRmvInfo.classList.add('invis');
    }
    if (fRmvBtn) {
      fRmvBtn.classList.add('invis');
    }
    if (window.resetUndoBtn) {
      resetUndoBtn(fRmvBtn);
    }
}

