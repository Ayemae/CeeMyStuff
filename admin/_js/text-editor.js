const textarea = document.getElementById("text-editor");
const txtEditPanel = document.getElementById("text-edit-panel");
const wrapStyleBtns = txtEditPanel.getElementsByClassName("wrap-style-btn");
const dropStyleBtns = txtEditPanel.getElementsByClassName("drop-style-btn");
var inputTimer =null;



if (textarea==undefined || !textarea) {
  textarea = document.getElementsByTagName("textarea")[0];
}


var typing=false;
const initText = textarea.value;
var txtHistory = [initText];
var hIndex =0 ;
var lastTxt = initText;
var histNaved = false;

// undo/redo history gathering
function makeTxtHistory() {
  let text = textarea.value;
  if (hIndex>(txtHistory.length-1)) {
    hIndex=txtHistory.length;
  } else if (hIndex<1) {
    hIndex=0;
  }
  if (lastTxt != text && text != txtHistory[(txtHistory.length-1)]) {
    txtHistory.push(text);
    lastTxt = text;
    hIndex++;
    if (hIndex!=(txtHistory.length-1)) {
      txtHistory = txtHistory.slice(0,hIndex);
    }
    if (txtHistory.length>49) {
      txtHistory.shift();
    }
  }
}
// navigate undo/redo history
function navTxtHistory(undo) {
  histNaved=true;
  if (undo===true) {
    if (hIndex>0) {
      hIndex--;
    }
    textarea.value = txtHistory[hIndex];
  } else if (undo===false) {
    // if 'undo' is false, 'redo'
    if (hIndex<(txtHistory.length-1)) {
      hIndex++;
    }
    textarea.value = txtHistory[hIndex];
  }
}

// check for undo/redo
textarea.addEventListener('keydown', function (e) {
  var e = e || window.event;
  var key = e.key.toLowerCase();

  if ((e.ctrlKey || e.metaKey) && e.shiftKey && key=='z') {
    e.preventDefault();
    navTxtHistory(false);
  } else if ((e.ctrlKey || e.metaKey) && key == 'y') {
    e.preventDefault();
    navTxtHistory(false);
  } else if ((e.ctrlKey || e.metaKey) && key == 'z') {
    e.preventDefault();
    navTxtHistory(true);
  } 
});

for (let i=0;i<wrapStyleBtns.length;i++) {
  wrapStyleBtns[i].addEventListener('click', function(e) {
    let text = textarea.value;
    lastTxt = text;
    let tag = this.value;
    let start = textarea.selectionStart;
    let end = textarea.selectionEnd;
    let selection = text.substring(start,end);
    let newText = [text.slice(0, start), '<'+tag+'>', selection, '</'+tag+'>', text.slice(end)].join('');
    textarea.value = newText;
    makeTxtHistory();
    textarea.focus();
  });
}

for (let i=0;i<dropStyleBtns.length;i++) {
  dropStyleBtns[i].addEventListener('click', function(e) {
    var tag = this.value;
    let text = textarea.value;
    lastTxt = text;
    var position = textarea.selectionEnd;
    var newText = [text.slice(0, position), '<'+tag+'/>', text.slice(position)].join('') ;
    textarea.value = newText;
    makeTxtHistory();
    textarea.focus();
  });
}


function srcBtn(strtStr,placeholder,endStr) {
  let text = textarea.value;
  lastTxt = text;
  var start = textarea.selectionStart;
  var end = textarea.selectionEnd;
  var selection;
  if (start === end) {
    selection = placeholder;
  } else {
    selection = text.substring(start,end);
  }
  var newText = [text.slice(0, start), strtStr, selection, endStr, text.slice(end)].join('');
  textarea.value = newText;
  makeTxtHistory();
  textarea.focus();
}

function startInputTimer() {
  inputTimer = window.setTimeout(function(){
    makeTxtHistory();
    typing=false;
  }, 1000)
}

textarea.addEventListener('input', (e) => {
  scriptInput = false;
  if (typing===false) {
    //let text = this.value;
    typing=true;
  }
  if (!histNaved) {
    startInputTimer();
  } else {
    histNaved=false;
  }
  this.addEventListener('keydown', (e) => {
    window.clearInterval(inputTimer);
  })
});