
//oba
document.onclick = function(){
    if(document.getElementById('nssSuggestionBox').hasAttribute('style')){
        document.getElementById('nssSuggestionBox').removeAttribute('style');
    }
};
//zajednicko
const searchForm = document.getElementById('gfSearchForm');
const searchInput = document.getElementById('gfSearchBox');

if(searchForm != null){
    searchForm.addEventListener('submit', searchSubmit);

    /** garbage code activates, tbh **/
    let preventSearch = false, timer, searchQuery, delay = 300;
    searchForm.addEventListener('keyup', event => {

        if(event.code === 'Enter'){
            preventSearch = true;
            return false;
        }
        if(searchInput.value.length >= 3){
            if(searchQuery !== searchInput.value){
                searchQuery = searchInput.value;
                timer = null;
                timer = setTimeout(function(){
                    if(!preventSearch){
                        ajaxSearch(searchQuery);
                    }
                }, delay);
            }
        }
        else{
            document.getElementById('nssSuggestionBox').display = 'none';
            return false;
        }

    });

}

//expander on archive page
//zajednicka
export const expanderArrow = document.getElementById('nssCatExpander');
export const expanderSubCats = document.getElementsByClassName('gf-expander__subcategory-list');
if(expanderArrow != null && expanderArrow.firstElementChild != null){
    expanderArrow.firstElementChild.onclick = function(){
        toggleClass(this, ['fa-angle-down', 'fa-angle-up']);
        for(let cat of expanderSubCats){
            let height = cat.parentElement.clientHeight + cat.clientHeight;
            let style = 'height:' + height + 'px;';
            sliderToggle(cat.parentElement, style);
        }
    };
}


//honestly no idea, old comment said "Tracks product views"
export function viewCountAjax(){
    let request = new XMLHttpRequest();
    let productNum = document.querySelector('[id^=product]').getAttribute('id').split('-')[1];

    request.open("POST", "/gf-ajax/?viewCount=true&postId=" + productNum, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send('viewCount=true&postId='+productNum);
}


// Zamena za jQuery ajax cart refresh
//zajednicka
export function ajaxRefreshCartCount(){
    let request = new XMLHttpRequest();
    let cartCount = document.getElementById("cartCount");
    let userCartCount = document.getElementById("accCartCount");

    request.open("POST", "/gf-ajax/", true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

    request.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            cartCount.innerHTML = this.responseText;
            if(userCartCount != null){
                userCartCount.innerHTML = "Korpa (" + this.responseText + ")";
            }
        }
    };
    request.send("action=refreshCartCount");
}

//toggle more than one class: div = html element, params = ['class1', 'class2', ...]
//zajednicko
export function toggleClass(div, params){
    for(let param of params){
        div.classList.toggle(param);
    }
}

export function sliderToggle(div, style) {
    let slideOpen = false;
    if(div.hasAttribute('style')){
        slideOpen = true;
    }
    if(slideOpen) {
        div.removeAttribute('style');
    }
    else {
        div.style = style;
    }
}

