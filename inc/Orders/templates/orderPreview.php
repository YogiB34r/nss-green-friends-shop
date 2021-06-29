<div class="popupOverlay">
    <div class="orderPopupContainer">
        <div class="orderPopupMain">
            <div class="orderPopupHeader">
                <div class="orderPopupHeaderLeft">
                    <div class="orderTitle"><?=$title?></div>
                    <span class="orderStatus"><?=$status?></span>
                </div>
                <div class="closeOrderPopup"><span class="popupClose dashicons dashicons-no"></span></div>
            </div>
            <div class="orderPopupContent">
                <div class="orderPopupContentLeft">
                    <div class="popupSection">
                        <h3>Kupac</h3>
                        <span><?=$billingName?></span>
                        <span><?=$billingAddress?></span>
                        <span><?=$billingCity?></span>
                        <span><?=$billingPostCode?></span>
                    </div>
                    <div class="popupSection">
                        <span class="popupSubtitle">Email</span>
                        <a href="mailto:<?=$email?>"><?=$email?></a>
                    </div>
                    <div class="popupSection">
                        <span class="popupSubtitle">Telefon</span>
                        <a href="tel:<?=$phone?>"><?=$phone?></a>
                    </div>
                    <div class="popupSection">
                        <span class="popupSubtitle">Payment via</span>
                        <span><?=$paymentMethod?></span>
                    </div>
                </div>
                <div class="orderPopupContentRight">
                    <div class="popupSection">
                        <h3>Shipping details</h3>
                        <a target="_blank" href="https://www.google.com/maps/search/<?=$shippingAddress?>,+<?=$shippingCity?>">
                            <span><?=$shippingName?></span>
                            <span><?=$shippingAddress?></span>
                            <span><?=$shippingCity?></span>
                            <span><?=$shippingPostCode?></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="orderPopupFooter">
                <div class="orderPopupFooterHeader">
                    <span>Proizvod</span>
                    <span class="popupAmount">Kolicina</span>
                    <span class="popupTotal">Ukupno</span>
                </div>
                <div class="orderPopupFooterContent">
                    <?php foreach ($products as $product): $data = $product->get_data();?>
                        <div class="popupProduct">
                            <span class="productContent"><?=$data['name']?></span>
                            <span class="amountContent"><?=$data['quantity']?></span>
                            <span class="totalContent"><?=$data['total']?></span>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
        <div class="orderPopupBottom">
            <a href="<?=$editUrl?>" class="popupSettings">Podesavanja</a>
        </div>
    </div>
    <script>
        jQuery('.popupClose').on('click', function (){
            jQuery('.popupOverlay').remove();
            jQuery('body').css('overflow', 'auto');
        })
        jQuery(document).on('keyup', function (e){
            if (e.key === 'Escape'){
                jQuery('.popupOverlay').remove();
                jQuery('body').css('overflow', 'auto');
            }
        })
    </script>
    <style>
        .popupOverlay .previewButton {
            display: none;
        }
        .popupClose{
            cursor:pointer;
        }
        .popupOverlay {
            z-index: 99999999;
            background:rgba(0,0,0,0.6);
            width:100%;
            position:fixed;
            height: 100vh;
            top:0
        }
        body {
            overflow:hidden !important;
        }
        .orderPopupContainer {
            width:500px;
            background:white;
            position:fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            z-index: 999999999;
        }
        .orderPopupMain {
            max-height:550px;
            overflow: auto;
        }
        .orderPopupHeader {
            display:flex;
            padding:1.5rem 1rem;
            align-items: center;
            justify-content: space-between;
            border-bottom:1px solid lightgray;
        }
        .orderPopupHeaderLeft {
            display:flex;
            align-items: center;
            justify-content: space-between;
            flex:1;
        }
        .orderPopupHeaderLeft .orderStatus {
            margin-right:2rem;
        }
        .orderPopupHeaderLeft .orderTitle {
            font-weight:bold;
        }
        .orderPopupContentLeft,
        .orderPopupContentRight {
            width:50%;
            padding:1rem;
        }
        .orderPopupContent {
            display:flex;
            max-height:600px;
        }
        .orderPopupContent .popupSection {
            display:flex;
            flex-direction: column;
            margin-bottom:1rem;
        }
        .orderPopupContent .popupSection span {
            margin-bottom:0.3rem;
        }
        .orderPopupContent .popupSection a {
            margin-bottom:0.3rem;
        }
        .orderPopupContent .popupSection h3 {
            margin-bottom:2rem;
        }
        .orderPopupContent .popupSection .popupSubtitle {
            color:black;
        }
        .orderPopupFooter  .orderPopupFooterHeader {
            display:flex;
            padding:0.5rem 1rem;
        }
        .popupProduct {
            padding:1rem;
            border-top:1px solid lightgray;
            display:flex;
        }
        .orderPopupFooterHeader span {
            font-weight:bold;
        }
        .orderPopupFooterHeader span:first-of-type,
        .orderPopupFooterContent span:first-of-type {
            width:60%;
        }
        .orderPopupFooterHeader span:nth-of-type(2),
        .orderPopupFooterContent span:nth-of-type(2),
        .orderPopupFooterHeader span:nth-of-type(3),
        .orderPopupFooterContent span:nth-of-type(3){
            width:20%;
        }
        .amountContent,
        .totalContent,
        .popupTotal,
        .popupAmount {
            text-align: right;
        }
        .orderPopupBottom {
            display:flex;
            justify-content: flex-end;
            padding:1rem;
        }
        .orderPopupBottom .popupSettings {
            padding:0.5rem 1.5rem;
            background:forestgreen;
            color:white;
            text-decoration: none;
        }

    </style>
</div>

