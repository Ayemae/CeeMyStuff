// The "lbArrs" variable should already be defined in the document
var lbSects = document.querySelectorAll('[data-sect-action=lightbox]');
let itemIndex = 0;
var arrIndex = 0;
var lbElems = [];

// // test!!!
// document.addEventListener("click", function(e) {
//     e.preventDefault();
//     console.log('clicked: ', e.target.dataset);
// });


function unwrapLinks(item) {
    let links = item.getElementsByClassName("item-link");
    for (let i=0;i<links.length;i++) {
        links[i].replaceWith(...links[i].children)
    }
};

for (let i=0; i < lbSects.length; i++) {
    let items = lbSects[i].querySelectorAll('[data-lightbox]');
    let paginate = false;
    let hasClose = false;
    let hasArrows = true;
    if (lbSects[i].dataset.lbPaginate==="true") {
        paginate = true;
        if (lbSects[i].dataset.lbformatHasArrows==="false") {
            hasArrows = false;
        }
    }
    if (lbSects[i].dataset.lbformatHasClose==="true") {
        hasClose = true;
    }
    for (let i2=0; i2 < items.length; i2++) {
        let itemLinks = items[i2].querySelectorAll('a[data-lightbox-link]');
        for (let i3=0;i3<itemLinks.length;i3++) {
            itemLinks[i3].addEventListener('click', function(e) {
                console.log('loop: ', e.target.dataset);
                e.preventDefault();
                const lbArrows = document.getElementById('lightbox-arrows');
                const lbClose = document.getElementById('lightbox-close');
                if (lbArrs[i].length<2 || paginate===false || 
                    (paginate===true && hasArrows===true)) {
                    lbArrows.classList.add("off");
                } else {
                    if (lbArrows.classList.contains("off")) {
                        lbArrows.classList.remove("off");
                    }
                }
                if (hasClose===true) {
                    lbClose.classList.add("off");
                } else {
                    if (lbClose.classList.contains("off")) {
                        lbClose.classList.remove("off");
                    }
                }
                triggerLightbox(lbArrs[i], i2);
                itemIndex = i2;
                arrIndex = i;
            }, false)
        }
    }
}

function navItemIndex(arr,i,navNext) {
    const iLast = (arr.length-1);
    if (navNext) {i++;} 
    else {i--;}
    if (i<0) {i = iLast;} 
    else if (i>iLast) {i = 0;}
    return i;
}

function setLbHTML (sectArr, i) {
    document.getElementById('lightbox-inner').innerHTML = sectArr[i];
    unwrapLinks(document.getElementById('lightbox-inner'));
}

function triggerLightbox(sectArr, i) {
    setLbHTML(sectArr, i);
    document.getElementById('lightbox').classList.add("on");
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove("on");
}

document.addEventListener('click',function(e){
    if(e.target && (e.target.id || e.target.parentNode.id) == 'lightbox-close'){
        closeLightbox();
    } else if(e.target && (e.target.id || e.target.parentNode.id) == 'lb-back'){
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,false);
        setLbHTML(lbArrs[arrIndex], itemIndex);
    } else if(e.target && (e.target.id || e.target.parentNode.id) == 'lb-next'){
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,true);
        setLbHTML (lbArrs[arrIndex], itemIndex);
    }
});

// for tabindex/accessibility 
document.addEventListener('keyup',function(e){
    if (e.key === 'Escape') {
        closeLightbox();
    } else if (e.key === 'ArrowLeft' && lbArrs[arrIndex].length>1) {
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,false);
        setLbHTML(lbArrs[arrIndex], itemIndex);
    } else if (e.key === 'ArrowRight' && lbArrs[arrIndex].length>1) {
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,true);
        setLbHTML (lbArrs[arrIndex], itemIndex);
    } else if (e.key === 'Enter') {
        if(e.target && e.target.id == 'lightbox-close'){
            closeLightbox();
        } else if(e.target && e.target.id == 'lb-back'){
            itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,false);
            setLbHTML(lbArrs[arrIndex], itemIndex);
        } else if(e.target && e.target.id == 'lb-next'){
            itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,true);
            setLbHTML (lbArrs[arrIndex], itemIndex);
        }
    }
});