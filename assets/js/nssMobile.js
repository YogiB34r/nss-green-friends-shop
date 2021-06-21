const body = document.getElementsByTagName("BODY")[0];

document.addEventListener('DOMContentLoaded', () => {
    ajaxRefreshCartCount();
    if (body.classList.contains('single-product')) {
        viewCountAjax();
    }
    if (buttonNext !== null) {
        sliderButtons();
        setTimeout(autoSlide, banner_speed.speed * 1000);
    }
});

const userIcon = document.getElementById('nssFancyUser');
const mobileUserMenu = document.getElementById('nssMobileUserMenu');
let mobileUserMenuHeight = 0;
for (let el of mobileUserMenu.children) {
    mobileUserMenuHeight += el.offsetHeight;
}

userIcon.onclick = () => {
    userIcon.classList.toggle('fancy-header-icons');
    mobileUserMenu.classList.toggle('show');
    sliderToggle(mobileUserMenu, `height: ${mobileUserMenuHeight}px;`);
    resetOthers(userIcon);
};
//animation for mobile search slider
const searchIcon = document.getElementById('nssFancySearch');
const searchSlider = document.getElementById('searchSlider');
let searchHeight = 0;
for (let element of searchSlider.children) {
    searchHeight += element.offsetHeight;
}
const searchSliderStyle = 'height: ' + searchHeight + 'px;';
const searchInputMobile = document.getElementById('searchInput');
let searchIconActive = false;
searchIcon.onclick = function () {
    searchIcon.classList.toggle('fancy-header-icons');
    sliderToggle(searchSlider, searchSliderStyle);
    resetOthers(searchIcon);
    if (searchIconActive) {
        searchIconActive = false;
        searchInputMobile.blur();
    } else {
        searchIconActive = true;
        setTimeout(() => searchInputMobile.focus(), 500); //fokusiraj input posle 0.5s (kraj animacije)
    }
};
const searchFormMob = document.getElementById('gfSearchFormMobile');
searchFormMob.addEventListener('submit', searchSubmit);
function searchSubmit() {
    let radio = document.querySelector('[name^=search-radiobutton]:checked');
    if (radio !== null) {
        if (radio.value === 'category') {
            searchFormMob.setAttribute('action', '');
        }
    }
}
//expander for archive page
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

//animation for mobile megaMenu
const megaMenuIcon = document.getElementById('gf-bars-icon-toggle');
const megaMenuList = document.getElementById('mobileMegaMenu');
megaMenuIcon.onclick = function () {
    megaMenuIcon.classList.toggle('fancy-header-icons');
    if (megaMenuList.children.length === 0) {
        getMenu();
        addMegaMenuEvents();
        sliderToggle(megaMenuList, 'padding: 10px; height: 100vh;');
        resetOthers(megaMenuIcon);
    } else {
        sliderToggle(megaMenuList, 'padding: 10px; height: 100vh;');
        resetOthers(megaMenuIcon);
    }

};
/* load megamenu html */
function getMenu() {
    document.getElementById('mobileMegaMenu').innerHTML += mobileMegaMenu.html;
}

//subcategories for megaMenu
function addMegaMenuEvents() {
    const expandIcons = document.getElementsByClassName('openMoreCategories');
    for (let expandIcon of expandIcons) {
        expandIcon.onclick = function () {
            toggleClass(this, ['fa-plus', 'fa-minus']);
            for (let subCat of this.parentNode.children) {
                if (subCat.classList.contains('gf-category-accordion__subitem')) {
                    subCat.classList.toggle('mobileMenuShow');
                }
                if (subCat.classList.contains('gf-category-accordion__item--last')) {
                    subCat.classList.toggle('mobileMenuShow');
                }
            }
        };
    }
}
//description button archive
const descriptionButton = document.querySelector('.gf-archive-description-button');
if (descriptionButton !== null) {
    descriptionButton.addEventListener('click', () => {
        document.querySelector('.gf-archive-description p').classList.toggle('gf-display-category-description');
    });
}

/***Slider banner***/
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
/* slide automatically, banner_speed.speed is from slider.php */
function autoSlide() {
    buttonNext.click();
    setTimeout(autoSlide, banner_speed.speed * 1000);
}

/* product sliders homepage */
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
    }

});
/* prev/next button for product sliders */
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

//billing on login
const billingPostcode = document.getElementById('billing_postcode');
const shippingPostcode = document.getElementById('shipping_postcode');
const billingCompany = document.getElementById('billing_company_checkbox');
const billingCompanySettings = document.querySelector('p#billing_company_field > label > span');
const billingPibSettings = document.querySelector('p#billing_pib_field > label > span');
if (billingPostcode !== null) {
    checkoutCityAjax();
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
/** showing password via checkbox **/
function showPassword() {
    const x = document.getElementById('password');
    if (x.getAttribute('type') === "password") {
        x.setAttribute('type', 'text')
    } else {
        x.setAttribute('type', 'password')
    }
}

/* functions that jQuery basically has, but homemade */
//toggle() from jquery to js, homemade :)
function toggleViewing(domEl) {
    if (window.getComputedStyle(domEl)['display'] === 'none') {
        domEl.style.display = 'block';
    } else {
        domEl.removeAttribute('style');
    }
}
//Function for sliding animation
//Parameter one is element you want to animate, parameter two is style you want to apply
//DISCLAIMER: use CSS3 transitions for this function to work
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
//reset icons styling
function resetOthers(current) {
    let icons = document.getElementsByClassName('fancy-header-icons');
    for (let icon of icons) {
        if (icon !== current) {
            icon.classList.toggle('fancy-header-icons');   //not great, not terrible 6.9
            if (icon === searchIcon) {
                sliderToggle(searchSlider);
            } else if (icon === userIcon) {
                mobileUserMenu.classList.toggle('show');
                sliderToggle(mobileUserMenu);
            } else if (icon === megaMenuIcon) {
                sliderToggle(megaMenuList);
            }
        }
    }
}
function toggleClass(div, params) {
    for (let param of params) {
        div.classList.toggle(param);
    }
}

/* AJAX Requests */
function ajaxRefreshCartCount() {
    let request = new XMLHttpRequest();
    let cartCount = document.getElementById("cartCount");

    let userCartCount = document.querySelector('.nssMobileUserLink:first-child a');

    request.open("POST", "/gf-ajax/", true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            cartCount.innerHTML = this.responseText;
            userCartCount.innerHTML = `Korpa (${this.responseText})`;
        }
    };
    request.send("action=refreshCartCount");
}

function viewCountAjax() {
    let request = new XMLHttpRequest();
    let productNum = document.querySelector('[id^=product]').getAttribute('id').split('-')[1];
    request.open("POST", "/gf-ajax/?viewCount=true&postId=" + productNum, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.send('viewCount=true&postId=' + productNum);
}

//have to finish later
function checkoutCityAjax() {
    const billingCity = document.getElementById('billing_city');
    const shippingCity = document.getElementById('shipping_city');
    billingCity.addEventListener('change', () => {
        let city = billingCity.value,
            name = billingCity.getAttribute('name');
        let request = new XMLHttpRequest();
        request.open("POST", `/gf-ajax/`, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = () => {
            if (this.status >= 200 && this.status <= 400) {
                billingPostcode.value = this.response;
            }
        };
        request.send(`?city=${city}true&action=getZipCode`);
    })
}