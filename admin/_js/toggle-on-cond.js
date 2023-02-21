// make sure that .indexOf can correctly compare 
function flattenStr(input) {
    if (typeof input == "string") {
        if (isNaN(input)) {
            input.toLowerCase();
        } else {
            input = parseInt(input);
        }
    }
    return input;
}

function toggleOnCond (elemVal, condValArr, target, byExclusion, reverse, togClass) {
    if (!Array.isArray(condValArr)) {
        condValArr = condValArr.split(',');
    }
    elemVal = flattenStr(elemVal);
    for (let i=0;i<condValArr.length;i++) {
        condValArr[i] = flattenStr(condValArr[i]);
    }
    if (!byExclusion || byExclusion=="false") {
        byExclusion=false;
    } else {
        byExclusion=true;
    }
    if (!reverse || reverse=="false") {
        reverse=false;
    } else {
        reverse=true;
    }
    if (!togClass) {togClass = 'hide';}
    let condInArr = condValArr.includes(elemVal);
    let condMet = condInArr !== byExclusion;
    if (condMet) {
        target.classList.remove(togClass);

    } else {
        target.classList.add(togClass);
    }
}

function handleSCChildren(arr, val, init) {
    for (let i=0;i<arr.length;i++) {
        let child = arr[i];
        let conds = (child.dataset.scConditions).split(',');
        let exclude = child.dataset.scExclude;
        let reverse = child.dataset.scReverse;
        let togclass = child.dataset.togclass;
        if (!togclass) {togclass='hide';};
        if (init === true && !reverse) {
            child.classList.add(togclass);
        }
        toggleOnCond(val, conds, arr[i], exclude, reverse, togclass);
    }
}

(function() {
    var selectConds = document.getElementsByClassName("select-cond-container");
    for (let i=0;i<selectConds.length;i++) {
        let master = selectConds[i].getElementsByClassName("select-cond-master")[0];
        let val = master.value;
        let scChildren = selectConds[i].getElementsByClassName("select-cond");
        handleSCChildren(scChildren, val, true);
        master.addEventListener('change', function(e) {
            val = e.target.value;
            handleSCChildren(scChildren, val);
        });
    }
})();