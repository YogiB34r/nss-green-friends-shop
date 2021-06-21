const body = document.getElementsByTagName("BODY")[0];
document.addEventListener("DOMContentLoaded", function () {
    ajaxRefreshCartCount();
    setMegaMenu();
    if (buttonNext !== null) {
        sliderButtons();
        setTimeout(autoSlide, banner_speed.speed * 1000);
    }
    if (body.classList.contains('single-product')) {
        viewCountAjax();
    }
    gridView();
});
/***Slider buttons***/
let marginLeft = 0;
const buttons = document.getElementsByClassName('sliderButton');
const buttonPrevious = document.querySelector('.buttonPrevious');
const buttonNext = document.querySelector('.buttonNext');

function sliderButtons() {
    let idButton = 0;
    for (const button of buttons) {
        button.addEventListener('click', function () {
            idButton = parseInt(String(this.attributes.id.value).split('-')[1]);
            document.querySelector('.slideImg').style.marginLeft = (-100 * idButton) + "%";
            this.classList.add('active');
            for (let sibling of this.parentNode.children) {
                if (sibling !== this) sibling.classList.remove('active');
            }
            marginLeft -= 10;
        });
    }
    buttonPrevious.addEventListener('click', function () {
        if (idButton === 0)
            buttons[buttons.length - 1].click();
        else
            buttons[idButton - 1].click();
    });
    buttonNext.addEventListener('click', function () {
        if (idButton === buttons.length - 1)
            buttons[0].click();
        else
            buttons[idButton + 1].click();
    });
}

function autoSlide() {
    buttonNext.click();
    setTimeout(autoSlide, banner_speed.speed * 1000);
}
/***slider buttons end***/

function ajaxRefreshCartCount() {
    let request = new XMLHttpRequest();
    let cartCount = document.getElementById("cartCount");
    request.open("POST", "/gf-ajax/", true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            cartCount.innerHTML = this.responseText;
        }
    };
    request.send("action=refreshCartCount");
}

//expander on archive page
const expanderArrow = document.getElementById('nssCatExpander');
const expanderSubCats = document.getElementsByClassName('gf-expander__subcategory-list');
if (expanderArrow != null && expanderArrow.firstElementChild != null) {
    expanderArrow.firstElementChild.onclick = function () {
        toggleClass(this, ['fa-angle-down', 'fa-angle-up']);
        for (let cat of expanderSubCats) {
            let height = cat.parentElement.clientHeight + cat.clientHeight;
            let style = 'height:' + height + 'px;';
            sliderToggle(cat.parentElement, style);
        }
    };
}
const accordionHead = document.getElementById('accordionHead');
const desktopMenu = document.getElementById('nssMegaNav');
//calculate height for open megaMenu
let height = 0;
if (desktopMenu !== null) {
    for (let mainCategory of desktopMenu.children) {
        height += mainCategory.getBoundingClientRect().height;
    }
}
//slider for desktop megamenu
if (accordionHead != null) {
    accordionHead.onclick = function () {
        toggleClass(this.children[1], ['fa-angle-down', 'fa-angle-up']);
        sliderToggle(desktopMenu, 'height:' + height + 'px;');
        //if it looks stupid but it solves a bug it ain't stupid
        setTimeout(function () {
            if (desktopMenu.hasAttribute('style')) {
                desktopMenu.style.overflow = 'unset';
            }
        }, 500);
    };
}
const searchForm = document.getElementById('gfSearchForm');
const searchInput = document.getElementById('gfSearchBox');
if (searchForm != null) {
    searchForm.addEventListener('submit', searchSubmit);
    /** garbage code activates, tbh **/
    let preventSearch = false, timer, searchQuery, delay = 300;
    searchForm.addEventListener('keyup', event => {
        if (event.code === 'Enter') {
            preventSearch = true;
            return false;
        }
        if (searchInput.value.length >= 3) {
            if (searchQuery !== searchInput.value) {
                searchQuery = searchInput.value;
                timer = null;
                timer = setTimeout(function () {
                    if (!preventSearch) {
                        ajaxSearch(searchQuery);
                    }
                }, delay);
            }
        } else {
            document.getElementById('nssSuggestionBox').display = 'none';
            return false;
        }
    });
}

function searchSubmit() {
    let radio = document.querySelector('[name^=search-radiobutton]:checked');
    if (radio !== null && radio.value === 'category') {
        searchForm.setAttribute('action', '');
    }
}

/** seems useless, products and their stickers are aligning by default and position of stickers is just sligthly changing
 const toggleView = document.getElementsByClassName('gridlist-toggle');
 const products = document.getElementsByClassName('products');
 const stickers = document.getElementsByClassName('gf-sticker--center');

 if (toggleView.length !== 0) {
    let toggleViewIcons = toggleView[0].children;
    for (let option of toggleViewIcons) {
        option.onclick = function () {
            for (let product of products) {
                product.classList.toggle('gf-sticker--loop-list');
            }
            for (let sticker of stickers) {
                sticker.classList.toggle('gf-sticker--loop-list');
            }
        };
    }
}
 **/
//load page-specific megaMenu and accordion
function setMegaMenu() {
    if (accordionHead != null) {
        if (body.classList.contains('archive') || body.classList.contains('single-product')
            || body.classList.contains('home') || body.classList.contains('woocommerce-account')) {
            toggleClass(accordionHead.children[1], ['fa-angle-down', 'fa-angle-up']);
        } else {
            sliderToggle(desktopMenu, 'height:' + height + 'px; overflow: unset;');
        }
    }
}

//toggle more than one class: div = html element, params = ['class1', 'class2', ...]
function toggleClass(div, params) {
    for (let param of params) {
        div.classList.toggle(param);
    }
}

function sliderToggle(div, style) {
    let slideOpen = false;
    if (div.hasAttribute('style')) {
        slideOpen = true;
    }
    if (slideOpen) {
        div.removeAttribute('style');
    } else {
        div.style = style;
    }
}

//honestly no idea, old comment said "Tracks product views"
function viewCountAjax() {
    let request = new XMLHttpRequest();
    let productNum = document.querySelector('[id^=product]').getAttribute('id').split('-')[1];
    request.open("POST", "/gf-ajax/?viewCount=true&postId=" + productNum, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send('viewCount=true&postId=' + productNum);
}

/** hover event for megaMenu, needs fixing **/
const mainCategories = document.getElementsByClassName('category-item');
for (let mainCat of mainCategories) {
    let dropDown = mainCat.children[1];
    mainCat.addEventListener('mouseover', function () {
        dropDown.style.left = mainCat.offsetWidth + 'px';
        dropDown.style.top = '0px';
        dropDown.style.bottom = 'unset';
    });
}
const grid = document.getElementById('grid');

//save grid/list view inside of cookie
function gridView() {
    if (grid !== null) {
        if (getCookie('gridcookie') == null) {
            let products = document.getElementsByClassName('products');
            for (let product of products) {
                product.classList.add('grid');
            }
            grid.classList.add('active');
        }
    }
}

//cookie parser
function getCookie(name) {
    let dc = document.cookie;
    let prefix = name + "=";
    let begin = dc.indexOf("; " + prefix);
    if (begin === -1) {
        begin = dc.indexOf(prefix);
        if (begin !== 0) return null;
    } else {
        begin += 2;
        var end = document.cookie.indexOf(";", begin);
        if (end === -1) {
            end = dc.length;
        }
    }
    return decodeURI(dc.substring(begin + prefix.length, end));
}

//some suggestion box
document.onclick = function () {
    if (document.getElementById('nssSuggestionBox') != null && document.getElementById('nssSuggestionBox').hasAttribute('style')) {
        document.getElementById('nssSuggestionBox').removeAttribute('style');
    }
};

function ajaxSearch(value) {
    let request = new XMLHttpRequest();
    request.open('POST', '/gf-ajax/', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            if (this.responseText.trim() !== '') {
                document.getElementById('nssSuggestionBox').innerHTML = this.responseText.trim();
                document.getElementById('nssSuggestionBox').style.display = 'block';
            }
        }
    };
    request.send("query=" + value);
}

//swiper with buttons
const swiper = new Swiper('.swiper-container', {
    slidesPerView: 2,
    spaceBetween: 0,
    loop: true,
    arrows: false,
    breakpoints: {
        320: {
            slidesPerView: 2,
            spaceBetween: 0,
        },
        640: {
            slidesPerView: 2,
            spaceBetween: 5,
        },
        1023: {
            slidesPerView: 2,
            spaceBetween: 5,
        },
        1024: {
            slidesPerView: 3,
            spaceBetween: 10,
        },
        1376: {
            slidesPerView: 4,
            spaceBetween: 15,
        }
    }

});
const prevButtons = document.getElementsByClassName('product-slider__control-prev');
const nextButtons = document.getElementsByClassName('product-slider__control-next');
if (prevButtons != null) {
    for (let prevButton of prevButtons) {
        const swipePrev = prevButton.parentElement.parentElement.parentElement.lastElementChild.swiper;
        prevButton.addEventListener('click', (e) => {
            e.preventDefault();
            swipePrev.slidePrev();
        });
    }
}
if (nextButtons != null) {
    for (let nextButton of nextButtons) {
        const swipeNext = nextButton.parentElement.parentElement.parentElement.lastElementChild.swiper;
        nextButton.addEventListener('click', (e) => {
            e.preventDefault();
            swipeNext.slideNext();
        })
    }
}
//description button archive
const descriptionButton = document.querySelector('.gf-archive-description-button');
if (descriptionButton !== null) {
    descriptionButton.addEventListener('click', () => {
        document.querySelector('.gf-archive-description p').classList.toggle('gf-display-category-description');
    });
}
//billing on login
const billingPostcode = document.getElementById('billing_postcode');
const shippingPostcode = document.getElementById('shipping_postcode');
const billingCompany = document.getElementById('billing_company_checkbox');
const billingCompanySettings = document.querySelector('p#billing_company_field > label > span');
const billingPibSettings = document.querySelector('p#billing_pib_field > label > span');
const billingCity = document.getElementById('billing_city');
const shippingCity = document.getElementById('shipping_city');
if (billingPostcode !== null) {
    billingCity.onchange = (e) => {
        e.preventDefault();
        console.log(e.target.value);
        checkoutCityAjax(e.target.value);
    };
    shippingCity.onchange = (e) => {
        e.preventDefault();
        console.log(e.target.value);
        checkoutCityAjax(e.target.value);
    };
    checkoutCityAjax(billingCity.value);
    document.getElementById('ship-to-different-address-checkbox').checked = false;
    billingCompany.checked = false;
    billingPostcode.addEventListener('keypress', (e) => {
        if (billingPostcode.value.length >= 6) {
            e.preventDefault();
        }
    });
    billingPostcode.addEventListener('keyup', () => {
        billingPostcode.value = billingPostcode.value.replace(/\D/g, '');
    });
    shippingPostcode.addEventListener('keypress', (e) => {
        if (billingPostcode.value.length >= 6) {
            e.preventDefault();
        }
    });
    shippingPostcode.addEventListener('keyup', () => {
        shippingPostcode.value = shippingPostcode.value.replace(/\D/g, '');
    });
    billingCompany.addEventListener('click', (e) => {
        toggleViewing(document.getElementById('billing_pib_field'));
        toggleViewing(document.getElementById('billing_company_field'));
        if (billingCompany.checked) {
            document.getElementById('billing_company_field').classList.add('validate-required');
            document.getElementById('billing_pib_field').classList.add('validate-required');
            billingCompanySettings.classList.remove('optional');
            billingCompanySettings.classList.add('required');
            billingCompanySettings.textContent = '*';
            billingPibSettings.classList.remove('optional');
            billingPibSettings.classList.add('required');
            billingPibSettings.textContent = '*';
        }
    });
}

//toggle() from jquery to js, homemade (:
function toggleViewing(domEl) {
    if (window.getComputedStyle(domEl)['display'] === 'none') {
        domEl.style.display = 'block';
    } else {
        domEl.removeAttribute('style');
    }
}

/** ovo sluzi samo za checkbox na loginu**/
function showPassword() {
    const x = document.getElementById('password');
    if (x.getAttribute('type') === "password") {
        x.setAttribute('type', 'text')
    } else {
        x.setAttribute('type', 'password')
    }
}

//have to finish later
function checkoutCityAjax(city) {
    let request = new XMLHttpRequest();
    request.open("POST", `/gf-ajax/`, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = () => {
        billingPostcode.value = request.response;
    };
    request.send(`city=${city}&action=getZipCode`);
}