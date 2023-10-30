class Modal {
  constructor(id, html, allowClkOff, allowClsX) {
    this.id = id;
    this.html = html;
    this.allowClkOff=allowClkOff;
    this.allowClsX=allowClsX;
  }
  mkModal() {
    let modalDiv = `<div id="${this.id}" class="modal">
    <div class="modal-content modal-animate-from-top">`;
    if (this.allowClsX === true ) {
        modalDiv += `<span class="modal-close">&times;</span>`;
    };
        modalDiv += `<div class="modal-inner">
        ${this.html}
        </div>
    </div>
    </div>`;
    return modalDiv;
  }
  appendToForm(elemID) {
    var newModal = document.createElement('div')
    newModal.innerHTML = this.mkModal();
    document.getElementById(elemID).appendChild(newModal);
  }
  trigger() {
    document.getElementById(this.id).style.display = "block";
  }
  close(e) {
    e.preventDefault();
    document.getElementById(this.id).style.display = "none";
  }
};

document.addEventListener('click',function(e){
  if(e.target && e.target.classList.contains('modal-close')){
    var modalID = e.target.closest("[id]").getAttribute("id");
    document.getElementById(modalID).style.display = "none";
   }
});