// The "lbArrs" variable should already be defined in the document
var lbSects = document.querySelectorAll('[data-sect-action=lightbox]');
let itemIndex = 0;
var arrIndex = 0;
var lbElems = [];

for (let i=0; i < lbSects.length; i++) {
    items = lbSects[i].querySelectorAll('[data-lightbox]');
    let paginate = false;
    if (lbSects[i].dataset.lbPaginate==="true") {
        paginate = true;
    }
    for (let i2=0; i2 < items.length; i2++) {
        items[i2].addEventListener('click', function(e) {
            e.preventDefault();
            const lbArrows = document.getElementById('lightbox-arrows');
            if (lbArrs[i].length<2 || paginate===false) {
                lbArrows.classList.add("off");
            } else {
                if (lbArrows.classList.contains("off")) {
                    lbArrows.classList.remove("off");
                }
            }
            triggerLightbox(lbArrs[i], i2);
            itemIndex = i2;
            arrIndex = i;
        })
    }
    lbElems[i] = items;
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
}

function triggerLightbox(sectArr, i) {
    setLbHTML(sectArr, i);
    document.getElementById('lightbox').classList.add("on");
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove("on");
}

document.addEventListener('click',function(e){
    if(e.target && e.target.id == 'lightbox-close'){
        closeLightbox();
    } else if(e.target && e.target.id == 'lb-back'){
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,false);
        setLbHTML(lbArrs[arrIndex], itemIndex);
    } else if(e.target && e.target.id == 'lb-next'){
        itemIndex = navItemIndex(lbArrs[arrIndex],itemIndex,true);
        setLbHTML (lbArrs[arrIndex], itemIndex);
    }
});

// for tabindex accessibility 
document.addEventListener('keyup',function(e){
    if (e.key === 'Enter') {
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