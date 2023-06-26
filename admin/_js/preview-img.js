function previewImg (fileInputID, previewIDStrt) {
  const input = document.getElementById(fileInputID);
  const visual = document.getElementById(previewIDStrt+'-visual');
  //const rmvFile = document.getElementById(previewIDStrt+'-rmv-btn');
  const noneTxt = document.getElementById(previewIDStrt+'-none');
  const prvwInput = document.getElementById(previewIDStrt+'-preview')
  let img = window.URL.createObjectURL(input.files[0]);
  prvwInput.value = visual.src = img;
  visual.onload = function() {
      visual.classList.remove('invis');
      //rmvFile.classList.remove('invis');
      visual.classList.add('block');
      noneTxt.classList.add('invis');
      //URL.revokeObjectURL(visual.src) // free memory
    }
}